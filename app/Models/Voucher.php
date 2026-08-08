<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'is_unlimited_expiry',
        'expires_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_unlimited_expiry' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isExpired(): bool
    {
        if ($this->is_unlimited_expiry || is_null($this->expires_at)) {
            return false;
        }

        return now()->isAfter($this->expires_at);
    }

    public function isLimitReached(): bool
    {
        if (is_null($this->usage_limit) || $this->usage_limit <= 0) {
            return false;
        }

        return $this->used_count >= $this->usage_limit;
    }

    public function isValidForAmount(float $amount): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Voucher saat ini tidak aktif.'];
        }

        if ($this->isExpired()) {
            return ['valid' => false, 'message' => 'Voucher telah kadaluarsa pada ' . $this->expires_at->translatedFormat('d F Y H:i') . '.'];
        }

        if ($this->isLimitReached()) {
            return ['valid' => false, 'message' => 'Kuota penggunaan voucher ini telah habis.'];
        }

        if ($amount < (float)$this->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Minimal pembelian untuk menggunakan voucher ini adalah Rp ' . number_format($this->min_order_amount, 0, ',', '.') . '.',
            ];
        }

        $discount = $this->calculateDiscount($amount);

        return [
            'valid' => true,
            'voucher' => $this,
            'discount_amount' => $discount,
            'final_amount' => max(0, $amount - $discount),
            'message' => 'Voucher ' . $this->code . ' berhasil digunakan! Hemat Rp ' . number_format($discount, 0, ',', '.') . '.',
        ];
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'PERCENTAGE') {
            $discount = ($amount * (float)$this->discount_value) / 100;
            if (!is_null($this->max_discount_amount) && (float)$this->max_discount_amount > 0) {
                $discount = min($discount, (float)$this->max_discount_amount);
            }
            return min($amount, $discount);
        }

        // FIXED amount
        return min($amount, (float)$this->discount_value);
    }

    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
