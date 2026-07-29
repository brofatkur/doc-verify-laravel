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
        'status',
        'payment_gateway',
        'payment_channel',
        'payment_response_text',
        'metadata',
    ];

    protected $casts = [
        'amount_idr' => 'decimal:2',
        'points_issued' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
