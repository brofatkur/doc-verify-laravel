<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'amount_idr',
        'points_issued',
        'conversion_rate',
        'voucher_code',
        'discount_amount',
        'final_amount',
        'fee_amount',
        'net_amount',
        'status',
        'payment_gateway',
        'payment_channel',
        'payment_response_text',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amount_idr' => 'decimal:2',
        'points_issued' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calculate Fee Gateway based on Payment Channel:
     * - QRIS: Rp 500 flat + 0.7% of transaction amount
     * - Virtual Account (VA / Bank Transfer): Rp 4.000 flat
     */
    public static function calculateGatewayFee(float $amount, ?string $channel): float
    {
        $channelUpper = strtoupper((string)$channel);
        if (str_contains($channelUpper, 'QRIS') || str_contains($channelUpper, 'QR CODE') || str_contains($channelUpper, 'QR')) {
            return 500 + ($amount * 0.007); // Rp 500 + 0.7%
        }
        if (str_contains($channelUpper, 'VA') || str_contains($channelUpper, 'VIRTUAL') || str_contains($channelUpper, 'BANK') || str_contains($channelUpper, 'TRANSFER')) {
            return 4000; // Rp 4.000
        }
        return 0;
    }

    public function getEffectiveFeeAttribute(): float
    {
        if ($this->attributes['fee_amount'] ?? null) {
            return (float)$this->attributes['fee_amount'];
        }
        return self::calculateGatewayFee((float)$this->amount_idr, $this->payment_channel);
    }

    public function getEffectiveNetAttribute(): float
    {
        if ($this->attributes['net_amount'] ?? null) {
            return (float)$this->attributes['net_amount'];
        }
        return max(0, (float)$this->amount_idr - $this->effective_fee);
    }

    protected static function booted()
    {
        static::saving(function ($order) {
            if (empty($order->fee_amount) || (float)$order->fee_amount <= 0) {
                $order->fee_amount = self::calculateGatewayFee((float)$order->amount_idr, $order->payment_channel);
            }
            if (empty($order->net_amount) || (float)$order->net_amount <= 0) {
                $order->net_amount = max(0, (float)$order->amount_idr - (float)$order->fee_amount);
            }
        });
    }
}
