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
            }
        });

        static::saved(function (User $user) {
            if ($user->role === 'TRANSLATOR') {
                $user->syncDuplicateTranslatorRecords();
            }
        });
    }
}
