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
        'sk_number',
        'role',
        'is_verified',
        'verification_status',
        'verified_at',
        'verified_by',
        'registered_at',
        'activated_at',
        'expired_at',
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
     * Cek apakah masa aktif keanggotaan akan segera expired (default <= 30 hari)
     */
    public function isExpiringSoon(int $thresholdDays = 30): bool
    {
        if ($this->role !== 'TRANSLATOR' || !$this->isVerifiedTranslator()) {
            return false;
        }
        $days = $this->daysUntilExpiry();
        return $days !== null && $days >= 0 && $days <= $thresholdDays;
    }

    /**
     * Kirim Email Reminder Perpanjangan Keanggotaan Tahunan IPPTI
     */
    public function sendExpiryReminderEmail(bool $force = false): bool
    {
        if ($this->role !== 'TRANSLATOR' || empty($this->email) || str_ends_with($this->email, '@ippti.or.id')) {
            return false;
        }

        $daysLeft = $this->daysUntilExpiry();
        $expDate = $this->effective_expired_at;
        if (!$expDate) {
            return false;
        }

        $cacheKey = 'member_expiry_reminder_' . $this->id . '_' . date('Y-m-d');
        if (!$force) {
            try {
                if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    return false;
                }
            } catch (\Throwable $e) {}
        }

        $renewalFee = (float)\App\Models\Setting::get('member_renewal_fee', 700000);
        $renewalFeeFormatted = 'Rp ' . number_format($renewalFee, 0, ',', '.');
        $expDateFormatted = $expDate->translatedFormat('d F Y');
        $payUrl = url('/admin?pay_renewal=1');

        $statusText = $daysLeft < 0
            ? "TELAH BERAKHIR (EXPIRED) pada tanggal {$expDateFormatted}"
            : "AKAN BERAKHIR dalam {$daysLeft} hari lagi (pada tanggal {$expDateFormatted})";

        $subject = "[DocVerify IPPTI] Pengingat Perpanjangan Keanggotaan Tahunan IPPTI - {$this->name}";
        $body = "Yth. {$this->name} (No. Anggota IPPTI: {$this->sk_number}),\n\n"
            . "Kami informasikan bahwa masa aktif keanggotaan tahunan IPPTI & akun Penerjemah Tersumpah Anda di portal DocVerify IPPTI {$statusText}.\n\n"
            . "Rincian Perpanjangan Keanggotaan:\n"
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
            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($subject) {
                $message->to($this->email, $this->name)
                        ->subject($subject);
            });
        } catch (\Throwable $e) {
            // Fallback to log if SMTP not configured
        }

        error_log("\n======================================================\n[EMAIL REMINDER PERPANJANGAN KEANGGOTAAN SENT TO {$this->email}]:\n{$body}\n======================================================\n");

        try {
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));
        } catch (\Throwable $e) {}

        return true;
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
