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

    public static function ensureTableExists(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('payout_transactions')) {
                \Illuminate\Support\Facades\Schema::create('payout_transactions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('payout_id')->nullable()->index();
                    $table->string('reference_code')->unique()->index();
                    $table->enum('recipient_type', ['IPPTI', 'BENLARIS'])->default('IPPTI');
                    $table->decimal('amount', 15, 2)->default(0);
                    $table->decimal('fee_amount', 10, 2)->default(0);
                    $table->string('bank_name')->nullable();
                    $table->string('bank_channel')->nullable();
                    $table->string('account_number')->nullable();
                    $table->string('account_holder_name')->nullable();
                    $table->enum('status', ['pending', 'processing', 'success', 'failed', 'simulated'])->default('pending');
                    $table->enum('trigger_type', ['manual', 'auto_monthly', 'simulation'])->default('manual');
                    $table->text('raw_request')->nullable();
                    $table->text('raw_response')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
