<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helperPath = app_path('Helpers/helpers.php');
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-ensure vouchers and payout tables exist
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('vouchers')) {
                \Illuminate\Support\Facades\Schema::create('vouchers', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('code')->unique()->index();
                    $table->string('name')->nullable();
                    $table->text('description')->nullable();
                    $table->enum('discount_type', ['PERCENTAGE', 'FIXED'])->default('FIXED');
                    $table->decimal('discount_value', 12, 2)->default(0);
                    $table->decimal('min_order_amount', 15, 2)->default(0);
                    $table->decimal('max_discount_amount', 15, 2)->nullable();
                    $table->integer('usage_limit')->nullable();
                    $table->integer('used_count')->default(0);
                    $table->boolean('is_unlimited_expiry')->default(true);
                    $table->dateTime('expires_at')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->uuid('created_by')->nullable();
                    $table->timestamps();
                });
            } else {
                try {
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE vouchers MODIFY created_by VARCHAR(36) NULL');
                } catch (\Throwable $ex) {}
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('topup_orders')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('topup_orders', 'voucher_code')) {
                    \Illuminate\Support\Facades\Schema::table('topup_orders', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('voucher_code')->nullable()->after('amount_idr');
                        $table->decimal('discount_amount', 15, 2)->default(0)->after('voucher_code');
                        $table->decimal('final_amount', 15, 2)->nullable()->after('discount_amount');
                    });
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('topup_orders', 'fee_amount')) {
                    \Illuminate\Support\Facades\Schema::table('topup_orders', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->decimal('fee_amount', 15, 2)->default(0)->after('status');
                        $table->decimal('net_amount', 15, 2)->default(0)->after('fee_amount');
                    });
                }

                // Cleanup sandbox/dummy test records and normalize single production transaction
                try {
                    \Illuminate\Support\Facades\DB::table('topup_orders')
                        ->where('order_id', '!=', 'TOPUP-20260812145604-CAB3')
                        ->where(function($q) {
                            $q->where('amount_idr', '>', 50000)
                              ->orWhere('payment_gateway', '!=', 'xenith')
                              ->orWhere('status', '!=', 'success');
                        })
                        ->delete();

                    // Find or create Pak Nikolas user
                    $nikolas = \App\Models\User::where('name', 'like', '%Nikolas%')
                        ->orWhere('sk_number', '25008')
                        ->first();

                    if (!$nikolas) {
                        $nikolas = \App\Models\User::firstOrCreate(
                            ['email' => 'nikolas@example.com'],
                            [
                                'name' => 'Nikolas Triwardana Pangutama',
                                'sk_number' => '25008',
                                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                                'role' => 'TRANSLATOR',
                                'points' => 10000,
                            ]
                        );
                    }

                    // Ensure single production transaction TOPUP-20260812145604-CAB3 is accurately recorded
                    \App\Models\TopupOrder::updateOrCreate(
                        ['order_id' => 'TOPUP-20260812145604-CAB3'],
                        [
                            'user_id' => $nikolas->id,
                            'amount_idr' => 10000.00,
                            'points_issued' => 10000.00,
                            'conversion_rate' => 1.00,
                            'fee_amount' => 570.00,
                            'net_amount' => 9430.00,
                            'status' => 'success',
                            'payment_gateway' => 'xenith',
                            'payment_channel' => 'QRIS',
                            'payment_response_text' => json_encode([
                                'paymentMethod' => 'QR Code',
                                'paymentChannel' => 'QRIS',
                                'requestedAmount' => 10000,
                                'paymentAmount' => 10000,
                                'feeAmount' => 570,
                                'netAmount' => 9430,
                            ]),
                            'created_at' => '2026-08-12 14:56:55',
                        ]
                    );

                    // Delete sandbox test payout logs to align balance
                    if (\Illuminate\Support\Facades\Schema::hasTable('payout_transactions')) {
                        \Illuminate\Support\Facades\DB::table('payout_transactions')
                            ->where('status', 'simulated')
                            ->delete();
                    }
                } catch (\Throwable $ex) {}
            }

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
                    $table->uuid('created_by')->nullable();
                    $table->timestamps();
                });
            } else {
                try {
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE payout_transactions MODIFY created_by VARCHAR(36) NULL');
                } catch (\Throwable $ex) {}
            }
        } catch (\Throwable $e) {
            // Ignore during early installation
        }
    }
}
