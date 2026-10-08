<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'transaction_no',
        'user_id',
        'amount',
        'points',
        'payment_url',
        'session_id',
        'ipaymu_trx_id',
        'payment_method',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'points' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function topupOrder()
    {
        return $this->hasOne(TopupOrder::class, 'order_id', 'transaction_no');
    }

    /**
     * Alias for order_id to ensure compatibility with topup_order consumers
     */
    public function getOrderIdAttribute(): string
    {
        return (string)($this->transaction_no ?? '');
    }

    /**
     * Alias for amount_idr
     */
    public function getAmountIdrAttribute(): float
    {
        return (float)($this->amount ?? 0);
    }

    /**
     * Alias for points_issued
     */
    public function getPointsIssuedAttribute(): int
    {
        return (int)($this->points ?? 0);
    }

    /**
     * Alias for payment_channel
     */
    public function getPaymentChannelAttribute(): string
    {
        return (string)($this->payment_method ?: 'QRIS');
    }

    /**
     * Calculate effective payment gateway fee based on channel:
     * - QRIS: Rp 500 flat + 0.7% of amount
     * - Virtual Account (VA / Bank Transfer): Rp 4.000 flat
     */
    public function getEffectiveFeeAttribute(): float
    {
        return TopupOrder::calculateGatewayFee((float)$this->amount, $this->payment_method);
    }

    /**
     * Net revenue after gateway fee deduction
     */
    public function getEffectiveNetAttribute(): float
    {
        return max(0, (float)$this->amount - $this->effective_fee);
    }

    /**
     * Check if payment status indicates paid/successful transaction
     */
    public function getIsPaidAttribute(): bool
    {
        return in_array(strtolower((string)$this->status), ['paid', 'success', 'completed', 'settled']);
    }
}
