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
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('topup_orders') && !\Illuminate\Support\Facades\Schema::hasColumn('topup_orders', 'voucher_code')) {
                \Illuminate\Support\Facades\Schema::table('topup_orders', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('voucher_code')->nullable()->after('amount_idr');
                    $table->decimal('discount_amount', 15, 2)->default(0)->after('voucher_code');
                    $table->decimal('final_amount', 15, 2)->nullable()->after('discount_amount');
                });
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
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignore during early installation
        }
    }
}
