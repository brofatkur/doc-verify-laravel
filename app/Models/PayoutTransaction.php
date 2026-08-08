<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutTransaction extends Model
{
    protected $fillable = [
        'payout_id',
        'reference_code',
        'recipient_type',
        'amount',
        'fee_amount',
        'bank_name',
        'bank_channel',
        'account_number',
        'account_holder_name',
        'status',
        'trigger_type',
        'raw_request',
        'raw_response',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
