<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'nik',
        'email',
        'password',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_otp_code',
        'two_factor_otp_expires_at',
        'sk_number',
        'role',
        'is_verified',
        'verification_status',
        'verified_at',
        'verified_by',
        'registered_at',
        'activated_at',
        'expired_at',
        'last_reminder_stage',
        'last_reminder_sent_at',
        'rejection_reason',
        'whatsapp',
        'address',
        'city',
        'language_services',
        'bio',
        'profile_picture',
        'no_sk_kemenkum',
        'tgl_sk',
        'masa_aktif',
        'sk_lengkap',
        'sk_document_path',
        'points',
        'user_level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_otp_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'registered_at' => 'datetime',
            'activated_at' => 'datetime',
            'expired_at' => 'datetime',
            'last_reminder_sent_at' => 'datetime',
            'two_factor_otp_expires_at' => 'datetime',
            'is_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke Document
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'translator_id');
    }

    /**
     * Relasi ke Ledger Mutasi Poin (point_transactions)
     */
    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class, 'user_id');
    }

    /**
     * Single Source of Truth for Point Balance calculation.
     * Dynamic calculation: SUM(credit) - SUM(debit)
     */
    public function getPointsAttribute()
    {
        $credit = $this->pointTransactions()->where('type', 'credit')->sum('amount');
        $debit = $this->pointTransactions()->where('type', 'debit')->sum('amount');
        return (int)($credit - $debit);
    }

    /**
     * Helper to credit points to user ledger with idempotency
     */
    public function creditPoints($amount, $description, $refType = null, $refId = null, $idempotencyKey = null, $metadata = null)
    {
        if ($idempotencyKey && PointTransaction::where('idempotency_key', $idempotencyKey)->exists()) {
            return PointTransaction::where('idempotency_key', $idempotencyKey)->first();
        }

        return PointTransaction::create([
            'user_id' => $this->id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'idempotency_key' => $idempotencyKey,
            'metadata' => is_array($metadata) ? json_encode($metadata) : $metadata,
        ]);
    }

    /**
     * Helper to debit points from user ledger with idempotency
     */
    public function debitPoints($amount, $description, $refType = null, $refId = null, $idempotencyKey = null, $metadata = null)
    {
        if ($idempotencyKey && PointTransaction::where('idempotency_key', $idempotencyKey)->exists()) {
            return PointTransaction::where('idempotency_key', $idempotencyKey)->first();
        }

        return PointTransaction::create([
            'user_id' => $this->id,
            'type' => 'debit',
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'idempotency_key' => $idempotencyKey,
            'metadata' => is_array($metadata) ? json_encode($metadata) : $metadata,
        ]);
    }

    /**
     * Cek apakah akun berstatus PRO
     */
    public function isPro(): bool
    {
        return strtolower($this->user_level ?? 'reguler') === 'pro';
    }

    /**
     * Cek apakah akun berstatus REGULER
     */
    public function isReguler(): bool
    {
        return strtolower($this->user_level ?? 'reguler') === 'reguler';
    }

    /**
     * Cek apakah user mengaktifkan Two-Factor Authentication (2FA Google Authenticator)
     */
    public function hasTwoFactorEnabled(): bool
    {
        $method = strtolower((string)($this->two_factor_method ?? 'none'));
        return $method === 'totp' && !empty($this->two_factor_secret);
    }

    /**
     * Generate & kirim kode OTP 6-digit untuk 2FA Login melalui Email & WhatsApp (Kirimdev.com)
     */
    public function sendTwoFactorOtp(): array
    {
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);

        try {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $this->id)
                ->update([
                    'two_factor_otp_code' => \Illuminate\Support\Facades\Hash::make($otp),
                    'two_factor_otp_expires_at' => $expiresAt,
                ]);
            $this->two_factor_otp_code = \Illuminate\Support\Facades\Hash::make($otp);
            $this->two_factor_otp_expires_at = $expiresAt;
        } catch (\Throwable $e) {}

        $emailSent = false;
        $waSent = false;

        if (!empty($this->email) && !str_ends_with($this->email, '@ippti.or.id')) {
            $subject = "[DocVerify IPPTI] Kode OTP Verifikasi Login 2FA: {$otp}";
            $body = "Yth. {$this->name},\n\n"
                . "Kode OTP Verifikasi Login 2FA Anda di portal DocVerify IPPTI adalah:\n\n"
                . "    {$otp}\n\n"
                . "Kode ini berlaku selama 5 menit (hingga {$expiresAt->format('H:i')} WIB). Jangan berikan kode ini kepada siapa pun.\n\n"
                . "Hormat kami,\n"
                . "Pengurus Nasional IPPTI\n"
                . "Ikatan Penerjemah dan Pengalih Bahasa Tersumpah di Indonesia";

            try {
                \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($subject) {
                    $message->to($this->email, $this->name)->subject($subject);
                });
                $emailSent = true;
            } catch (\Throwable $e) {}
        }

        if (!empty($this->whatsapp)) {
            $waMessage = "*[DocVerify IPPTI - Kode OTP Login 2FA]*\n\n"
                . "Halo *{$this->name}*,\n"
                . "Kode OTP verifikasi masuk (Login 2FA) Anda adalah:\n\n"
                . "*{$otp}*\n\n"
                . "_Berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun._";

            $waRes = static::sendWhatsappViaKirimdev($this->whatsapp, $waMessage);
            $waSent = !empty($waRes['success']);
        }

        return [
            'email_sent' => $emailSent,
            'whatsapp_sent' => $waSent,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verifikasi kode 2FA (mendukung Google Authenticator TOTP maupun OTP Email/WhatsApp)
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        $cleanCode = preg_replace('/[^0-9]/', '', trim($code));
        if (strlen($cleanCode) !== 6) {
            return false;
        }

        $method = strtolower((string)($this->two_factor_method ?? 'none'));

        // 1. Jika metode TOTP (Google Authenticator), cek dengan TotpAuthenticator
        if ($method === 'totp' && !empty($this->two_factor_secret)) {
            if (\App\Support\TotpAuthenticator::verifyCode($this->two_factor_secret, $cleanCode)) {
                return true;
            }
        }

        // 2. Cek kode OTP Email / WhatsApp jika masih berlaku
        if (!empty($this->two_factor_otp_code) && !empty($this->two_factor_otp_expires_at)) {
            $exp = \Carbon\Carbon::parse($this->two_factor_otp_expires_at);
            if ($exp->isFuture() && \Illuminate\Support\Facades\Hash::check($cleanCode, $this->two_factor_otp_code)) {
                try {
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $this->id)
                        ->update([
                            'two_factor_otp_code' => null,
                            'two_factor_otp_expires_at' => null,
                        ]);
                } catch (\Throwable $e) {}
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah akun Penerjemah sudah diverifikasi oleh Admin IPPTI
     * Jika belum diverifikasi, fitur input/impor dokumen belum aktif meskipun sudah bisa login.
     */
    public function isVerifiedTranslator(): bool
    {
        if ($this->role !== 'TRANSLATOR') {
            return true;
        }

        if (array_key_exists('is_verified', $this->attributes) && !$this->is_verified) {
            return false;
        }

        if (in_array(strtolower((string)($this->verification_status ?? 'approved')), ['pending', 'rejected'])) {
            return false;
        }

        return true;
    }

    /**
     * Tanggal Daftar Efektif
     */
    public function getEffectiveRegisteredAtAttribute()
    {
        return $this->registered_at ?: $this->created_at;
    }

    /**
     * Tanggal Aktivasi Efektif
     */
    public function getEffectiveActivatedAtAttribute()
    {
        if ($this->activated_at) {
            return $this->activated_at;
        }
        if ($this->isVerifiedTranslator()) {
            return $this->verified_at ?: $this->created_at;
        }
        return null;
    }

    /**
     * Tanggal Expired Tahunan Efektif (Sesuai dengan Tanggal Aktivasi masing-masing member)
     */
    public function getEffectiveExpiredAtAttribute()
    {
        if ($this->expired_at) {
            return \Carbon\Carbon::parse($this->expired_at);
        }
        if ($this->isVerifiedTranslator() && $this->effective_activated_at) {
            $years = max(1, (int)\App\Models\Setting::get('member_duration_years', 1));
            return \Carbon\Carbon::parse($this->effective_activated_at)->copy()->addYears($years);
        }
        return null;
    }

    /**
     * Cek apakah masa aktif keanggotaan tahunan sudah expired
     */
    public function isMembershipExpired(): bool
    {
        if ($this->role !== 'TRANSLATOR') {
            return false;
        }
        if (!$this->isVerifiedTranslator()) {
            return false;
        }
        $exp = $this->effective_expired_at;
        if ($exp && $exp->copy()->endOfDay()->isPast()) {
            return true;
        }
        return false;
    }

    /**
     * Sisa hari sebelum expired (negatif jika sudah expired)
     */
    public function daysUntilExpiry(): ?int
    {
        $exp = $this->effective_expired_at;
        if (!$exp) {
            return null;
        }
        return (int)now()->startOfDay()->diffInDays($exp->copy()->startOfDay(), false);
    }

    /**
     * Cek apakah masa aktif keanggotaan akan segera expired (default mengikuti setting member_reminder_days_1 = 30 hari)
     */
    public function isExpiringSoon(?int $thresholdDays = null): bool
    {
        if ($this->role !== 'TRANSLATOR' || !$this->isVerifiedTranslator()) {
            return false;
        }
        if ($thresholdDays === null) {
            $d1 = max(1, (int)\App\Models\Setting::get('member_reminder_days_1', 30));
            $d2 = max(1, (int)\App\Models\Setting::get('member_reminder_days_2', 10));
            $thresholdDays = max($d1, $d2);
        }
        $days = $this->daysUntilExpiry();
        return $days !== null && $days >= 0 && $days <= $thresholdDays;
    }

    /**
     * Tentukan tahap reminder saat ini berdasarkan setting 30 hari sebelumnya (Tahap 1) & 10 hari sebelumnya (Tahap 2)
     */
    public function determineReminderStage(): ?array
    {
        $expDate = $this->effective_expired_at;
        $daysLeft = $this->daysUntilExpiry();
        if (!$expDate || $daysLeft === null) {
            return null;
        }

        $d1 = max(1, (int)\App\Models\Setting::get('member_reminder_days_1', 30));
        $d2 = max(1, (int)\App\Models\Setting::get('member_reminder_days_2', 10));
        $firstReminderDays = max($d1, $d2);
        $secondReminderDays = min($d1, $d2);
        $expKey = $expDate->format('Y-m-d');

        if ($daysLeft < 0) {
            return [
                'code' => 'EXPIRED',
                'label' => 'Masa Aktif Telah Berakhir (Expired)',
                'stage_key' => "EXPIRED_{$expKey}",
                'days_left' => $daysLeft,
                'target_days' => 0,
            ];
        }

        if ($daysLeft <= $secondReminderDays) {
            return [
                'code' => "H-{$secondReminderDays}",
                'label' => "Pengingat H-{$secondReminderDays} ({$daysLeft} Hari Sebelum Expired)",
                'stage_key' => "H{$secondReminderDays}_{$expKey}",
                'days_left' => $daysLeft,
                'target_days' => $secondReminderDays,
            ];
        }

        if ($daysLeft <= $firstReminderDays) {
            return [
                'code' => "H-{$firstReminderDays}",
                'label' => "Pengingat H-{$firstReminderDays} ({$daysLeft} Hari Sebelum Expired)",
                'stage_key' => "H{$firstReminderDays}_{$expKey}",
                'days_left' => $daysLeft,
                'target_days' => $firstReminderDays,
            ];
        }

        return null;
    }

    /**
     * Format nomor WhatsApp ke standar internasional (+628...) untuk Kirimdev.com (Meta WhatsApp Cloud API)
     */
    public static function formatWhatsappNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9+]/', '', trim($phone));
        if (empty($cleaned)) {
            return null;
        }

        if (str_starts_with($cleaned, '08')) {
            return '+62' . substr($cleaned, 1);
        }
        if (str_starts_with($cleaned, '628')) {
            return '+' . $cleaned;
        }
        if (str_starts_with($cleaned, '8')) {
            return '+62' . $cleaned;
        }
        if (!str_starts_with($cleaned, '+')) {
            return '+' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Kirim pesan WhatsApp melalui API Kirimdev.com (https://api.kirimdev.com/v1/{phone_number_id}/messages)
     */
    public static function sendWhatsappViaKirimdev(string $phone, string $message): array
    {
        $formattedPhone = static::formatWhatsappNumber($phone);
        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'status' => null,
                'message' => 'Nomor WhatsApp kosong atau tidak valid.',
                'to' => null,
            ];
        }

        $apiKey = trim((string)\App\Models\Setting::get('kirimdev_api_key', env('KIRIMDEV_API_KEY', '')));
        $phoneNumberId = trim((string)\App\Models\Setting::get('kirimdev_phone_number_id', env('KIRIMDEV_PHONE_NUMBER_ID', '')));
        $baseUrl = rtrim(trim((string)\App\Models\Setting::get('kirimdev_base_url', env('KIRIMDEV_BASE_URL', 'https://api.kirimdev.com/v1'))), '/');

        if (empty($apiKey) || empty($phoneNumberId)) {
            error_log("\n======================================================\n[WHATSAPP KIRIMDEV.COM SIMULATION - SENT TO {$formattedPhone}]:\n{$message}\n(Catatan: Isi API Key & Phone Number ID Kirimdev.com di Pengaturan Sistem untuk pengiriman live)\n======================================================\n");
            return [
                'success' => true,
                'simulated' => true,
                'status' => 200,
                'message' => 'Tercatat di log sistem (API Key / Phone Number ID Kirimdev.com belum diisi).',
                'to' => $formattedPhone,
            ];
        }

        $endpoint = "{$baseUrl}/{$phoneNumberId}/messages";
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $formattedPhone,
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message,
            ],
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'simulated' => false,
                    'status' => $response->status(),
                    'message' => 'WhatsApp berhasil dikirim via Kirimdev.com',
                    'to' => $formattedPhone,
                    'response' => $response->json(),
                ];
            }

            error_log("[KIRIMDEV.COM WA ERROR {$response->status()} TO {$formattedPhone}]: " . $response->body());
            return [
                'success' => false,
                'simulated' => false,
                'status' => $response->status(),
                'message' => 'Gagal kirim via Kirimdev.com: ' . ($response->json('error.message') ?? $response->body()),
                'to' => $formattedPhone,
            ];
        } catch (\Throwable $e) {
            error_log("[KIRIMDEV.COM WA EXCEPTION TO {$formattedPhone}]: " . $e->getMessage());
            return [
                'success' => false,
                'simulated' => false,
                'status' => 500,
                'message' => 'Koneksi ke Kirimdev.com gagal: ' . $e->getMessage(),
                'to' => $formattedPhone,
            ];
        }
    }

    /**
     * Kirim Reminder Perpanjangan Keanggotaan Tahunan IPPTI secara Otomatis via Email & WhatsApp (Kirimdev.com)
     * Mendukung jadwal H-30 (Tahap 1), H-10 (Tahap 2), dan Expired.
     */
    public function sendExpiryReminder(bool $force = false): array
    {
        if ($this->role !== 'TRANSLATOR') {
            return ['sent' => false, 'email_sent' => false, 'whatsapp_sent' => false, 'stage' => null];
        }

        $expDate = $this->effective_expired_at;
        $daysLeft = $this->daysUntilExpiry();
        if (!$expDate || $daysLeft === null) {
            return ['sent' => false, 'email_sent' => false, 'whatsapp_sent' => false, 'stage' => null];
        }

        $stage = $this->determineReminderStage();
        if (!$stage && !$force) {
            return ['sent' => false, 'email_sent' => false, 'whatsapp_sent' => false, 'stage' => null];
        }

        $stageKey = $stage ? $stage['stage_key'] : ('MANUAL_' . date('Y-m-d'));
        $stageLabel = $stage ? $stage['label'] : "Pengingat Perpanjangan ({$daysLeft} Hari Sebelum Expired)";
        $cacheKey = 'member_expiry_stage_' . $this->id . '_' . $stageKey;

        if (!$force) {
            if ($this->last_reminder_stage === $stageKey) {
                return ['sent' => false, 'email_sent' => false, 'whatsapp_sent' => false, 'stage' => $stageKey, 'reason' => 'already_sent'];
            }
            try {
                if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    return ['sent' => false, 'email_sent' => false, 'whatsapp_sent' => false, 'stage' => $stageKey, 'reason' => 'already_cached'];
                }
            } catch (\Throwable $e) {}
        }

        $renewalFee = (float)\App\Models\Setting::get('member_renewal_fee', 700000);
        $renewalFeeFormatted = 'Rp ' . number_format($renewalFee, 0, ',', '.');
        $expDateFormatted = $expDate->translatedFormat('d F Y');
        $payUrl = url('/admin?pay_renewal=1');

        $statusText = $daysLeft < 0
            ? "TELAH BERAKHIR (EXPIRED) pada tanggal {$expDateFormatted}"
            : ($daysLeft === 0
                ? "BERAKHIR HARI INI ({$expDateFormatted})"
                : "AKAN BERAKHIR dalam {$daysLeft} hari lagi (pada tanggal {$expDateFormatted})");

        $emailEnabled = (bool)\App\Models\Setting::get('member_reminder_email_enabled', 1);
        $waEnabled = (bool)\App\Models\Setting::get('member_reminder_whatsapp_enabled', 1);

        $emailSent = false;
        $waSent = false;
        $waResult = null;

        // 1. Pengiriman via Email
        if (($emailEnabled || $force) && !empty($this->email) && !str_ends_with($this->email, '@ippti.or.id')) {
            $subject = "[DocVerify IPPTI] {$stageLabel} - Perpanjangan Keanggotaan {$this->name}";
            $emailBody = "Yth. {$this->name} (No. Anggota IPPTI: {$this->sk_number}),\n\n"
                . "Kami informasikan bahwa masa aktif keanggotaan tahunan IPPTI & akun Penerjemah Tersumpah Anda di portal DocVerify IPPTI {$statusText}.\n\n"
                . "Rincian Perpanjangan Keanggotaan:\n"
                . "- Tahap Pengingat : {$stageLabel}\n"
                . "- Nama Penerjemah : {$this->name}\n"
                . "- Nomor Anggota   : {$this->sk_number}\n"
                . "- Tanggal Expired : {$expDateFormatted}\n"
                . "- Biaya Perpanjangan Tahunan : {$renewalFeeFormatted} / Tahun\n\n"
                . "Silakan klik tautan berikut untuk melakukan pembayaran perpanjangan keanggotaan secara online via Payment Gateway:\n"
                . "{$payUrl}\n\n"
                . "Setelah pembayaran perpanjangan berhasil, masa aktif keanggotaan Anda akan otomatis diperpanjang selama 1 (satu) tahun ke depan.\n\n"
                . "Hormat kami,\n"
                . "Pengurus Nasional IPPTI\n"
                . "Ikatan Penerjemah dan Pengalih Bahasa Tersumpah di Indonesia";

            try {
                \Illuminate\Support\Facades\Mail::raw($emailBody, function ($message) use ($subject) {
                    $message->to($this->email, $this->name)
                            ->subject($subject);
                });
            } catch (\Throwable $e) {
                // Fallback to error_log if SMTP not configured
            }

            error_log("\n======================================================\n[EMAIL REMINDER ({$stageKey}) SENT TO {$this->email}]:\n{$emailBody}\n======================================================\n");
            $emailSent = true;
        }

        // 2. Pengiriman via WhatsApp (Kirimdev.com)
        if (($waEnabled || $force) && !empty($this->whatsapp)) {
            $waBody = "*[DocVerify IPPTI - {$stageLabel}]*\n\n"
                . "Yth. *{$this->name}*\n"
                . "No. Anggota IPPTI: *{$this->sk_number}*\n\n"
                . "Kami mengingatkan bahwa masa aktif keanggotaan tahunan IPPTI Anda *{$statusText}*.\n\n"
                . "*Rincian Perpanjangan:*\n"
                . "• Tanggal Expired: *{$expDateFormatted}*\n"
                . "• Biaya Perpanjangan: *{$renewalFeeFormatted} / Tahun*\n\n"
                . "Silakan klik link berikut untuk langsung melakukan pembayaran perpanjangan via Payment Gateway:\n"
                . "{$payUrl}\n\n"
                . "_Masa aktif keanggotaan Anda akan otomatis bertambah +1 Tahun begitu pembayaran terkonfirmasi._\n\n"
                . "Terima kasih,\n"
                . "*Pengurus Nasional IPPTI*";

            $waResult = static::sendWhatsappViaKirimdev($this->whatsapp, $waBody);
            $waSent = !empty($waResult['success']);
        }

        if ($emailSent || $waSent) {
            try {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $this->id)
                    ->update([
                        'last_reminder_stage' => $stageKey,
                        'last_reminder_sent_at' => now(),
                    ]);
                $this->last_reminder_stage = $stageKey;
                $this->last_reminder_sent_at = now();
            } catch (\Throwable $e) {}

            try {
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDays(40));
            } catch (\Throwable $e) {}
        }

        return [
            'sent' => ($emailSent || $waSent),
            'email_sent' => $emailSent,
            'whatsapp_sent' => $waSent,
            'whatsapp_result' => $waResult,
            'stage' => $stageKey,
            'stage_label' => $stageLabel,
        ];
    }

    /**
     * Alias kompatibilitas untuk memicu pengiriman Email + WhatsApp Reminder
     */
    public function sendExpiryReminderEmail(bool $force = false): bool
    {
        $result = $this->sendExpiryReminder($force);
        return !empty($result['sent']);
    }

    /**
     * Jalankan pengecekan & pengiriman otomatis H-30 dan H-10 untuk seluruh member aktif
     */
    public static function dispatchAutomaticExpiryReminders(bool $force = false): int
    {
        if (!$force) {
            try {
                if (\Illuminate\Support\Facades\Cache::has('ippti_auto_reminder_sweep_lock')) {
                    return 0;
                }
                \Illuminate\Support\Facades\Cache::put('ippti_auto_reminder_sweep_lock', true, now()->addMinutes(15));
            } catch (\Throwable $e) {}
        }

        $d1 = max(1, (int)\App\Models\Setting::get('member_reminder_days_1', 30));
        $d2 = max(1, (int)\App\Models\Setting::get('member_reminder_days_2', 10));
        $maxDays = max($d1, $d2);

        $translators = static::where('role', 'TRANSLATOR')->get();
        $sentCount = 0;

        foreach ($translators as $translator) {
            if (!$translator->isVerifiedTranslator()) {
                continue;
            }
            if ($translator->isExpiringSoon($maxDays) || $translator->isMembershipExpired()) {
                $res = $translator->sendExpiryReminder($force);
                if (!empty($res['sent'])) {
                    $sentCount++;
                }
            }
        }

        return $sentCount;
    }

    /**
     * Cek apakah penerjemah boleh menginput dokumen (Sudah diverifikasi DAN Belum expired)
     */
    public function canInputDocuments(): bool
    {
        if ($this->role !== 'TRANSLATOR') {
            return true;
        }
        return $this->isVerifiedTranslator() && !$this->isMembershipExpired();
    }

    /**
     * Get synchronized Nomor SK Menteri Hukum RI
     */
    public function getEffectiveNoSkAttribute(): string
    {
        if (!empty($this->no_sk_kemenkum)) {
            return trim((string)$this->no_sk_kemenkum);
        }

        // Fallback: extract AHU decree from sk_lengkap or bio if present
        foreach ([$this->sk_lengkap, $this->bio] as $text) {
            if (!empty($text) && preg_match('/(AHU-[A-Z0-9\.\-\s\/]+?\d{4})/i', (string)$text, $m)) {
                return trim($m[1]);
            }
        }

        return trim((string)($this->sk_number ?? ''));
    }

    /**
     * Get synchronized Tanggal SK Menteri Hukum RI
     */
    public function getEffectiveTglSkAttribute(): string
    {
        if (!empty($this->tgl_sk)) {
            return trim((string)$this->tgl_sk);
        }

        foreach ([$this->sk_lengkap, $this->bio] as $text) {
            if (!empty($text) && preg_match('/tanggal\s+([0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4}|\d{4}-\d{2}-\d{2})/i', (string)$text, $m)) {
                return trim($m[1]);
            }
        }

        return '';
    }

    /**
     * Get synchronized Keterangan SK Lengkap
     */
    public function getEffectiveSkLengkapAttribute(): string
    {
        $noSk = $this->effective_no_sk;
        $tglSk = $this->effective_tgl_sk;

        // If no_sk_kemenkum is set, ensure sk_lengkap reflects the current no_sk_kemenkum & tgl_sk
        if (!empty($this->no_sk_kemenkum)) {
            if (empty($this->sk_lengkap) || !str_contains((string)$this->sk_lengkap, (string)$this->no_sk_kemenkum)) {
                return trim($this->no_sk_kemenkum . ($tglSk ? ' tanggal ' . $tglSk : ''));
            }
        }

        return trim((string)($this->sk_lengkap ?: ($noSk . ($tglSk ? ' tanggal ' . $tglSk : ''))));
    }

    /**
     * Synchronize profile fields across any duplicate translator records sharing the same sk_number
     */
    public function syncDuplicateTranslatorRecords(): void
    {
        if ($this->role !== 'TRANSLATOR' || empty($this->sk_number)) {
            return;
        }

        try {
            $syncFields = [
                'name' => $this->name,
                'no_sk_kemenkum' => $this->no_sk_kemenkum,
                'tgl_sk' => $this->tgl_sk,
                'language_services' => $this->language_services,
                'masa_aktif' => $this->masa_aktif,
                'sk_lengkap' => $this->sk_lengkap,
                'bio' => $this->bio,
                'is_verified' => $this->is_verified,
                'verification_status' => $this->verification_status,
                'registered_at' => $this->registered_at,
                'activated_at' => $this->activated_at,
                'expired_at' => $this->expired_at,
                'updated_at' => now(),
            ];

            if (!empty($this->profile_picture)) {
                $syncFields['profile_picture'] = $this->profile_picture;
            }

            static::where('role', 'TRANSLATOR')
                ->where('sk_number', $this->sk_number)
                ->where('id', '!=', $this->id)
                ->update($syncFields);
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }

    protected static function booted()
    {
        static::saving(function (User $user) {
            if ($user->role === 'TRANSLATOR') {
                // Auto-populate no_sk_kemenkum from sk_lengkap/bio if empty
                if (empty($user->no_sk_kemenkum)) {
                    foreach ([$user->sk_lengkap, $user->bio] as $text) {
                        if (!empty($text) && preg_match('/(AHU-[A-Z0-9\.\-\s\/]+?\d{4})/i', (string)$text, $m)) {
                            $user->no_sk_kemenkum = trim($m[1]);
                            break;
                        }
                    }
                }

                // Auto-populate tgl_sk from sk_lengkap/bio if empty
                if (empty($user->tgl_sk)) {
                    foreach ([$user->sk_lengkap, $user->bio] as $text) {
                        if (!empty($text) && preg_match('/tanggal\s+([0-9]{1,2}\s+[A-Za-z]+\s+[0-9]{4}|\d{4}-\d{2}-\d{2})/i', (string)$text, $m)) {
                            $user->tgl_sk = trim($m[1]);
                            break;
                        }
                    }
                }

                // When no_sk_kemenkum or tgl_sk is updated, keep sk_lengkap synchronized
                if (($user->isDirty('no_sk_kemenkum') || $user->isDirty('tgl_sk')) && !$user->isDirty('sk_lengkap')) {
                    if (!empty($user->no_sk_kemenkum)) {
                        $user->sk_lengkap = trim($user->no_sk_kemenkum . ($user->tgl_sk ? ' tanggal ' . $user->tgl_sk : ''));
                    }
                } elseif (empty($user->sk_lengkap) && !empty($user->no_sk_kemenkum)) {
                    $user->sk_lengkap = trim($user->no_sk_kemenkum . ($user->tgl_sk ? ' tanggal ' . $user->tgl_sk : ''));
                }
                // When expired_at is set, keep masa_aktif label synchronized
                if (!empty($user->expired_at)) {
                    try {
                        $expDate = \Carbon\Carbon::parse($user->expired_at);
                        $user->masa_aktif = 'Aktif s/d ' . $expDate->translatedFormat('d F Y');
                    } catch (\Throwable $e) {}
                }
            }
        });

        static::saved(function (User $user) {
            if ($user->role === 'TRANSLATOR') {
                $user->syncDuplicateTranslatorRecords();
            }
        });
    }
}
