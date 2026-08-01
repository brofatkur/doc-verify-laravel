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
        'email',
        'password',
        'sk_number',
        'role',
        'whatsapp',
        'language_services',
        'bio',
        'profile_picture',
        'no_sk_kemenkum',
        'tgl_sk',
        'masa_aktif',
        'sk_lengkap',
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
}
