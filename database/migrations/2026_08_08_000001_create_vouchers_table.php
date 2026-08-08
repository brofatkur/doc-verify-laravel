<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->enum('discount_type', ['PERCENTAGE', 'FIXED'])->default('FIXED');
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_order_amount', 15, 2)->default(0);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // null = unlimited usage
            $table->integer('used_count')->default(0);
            $table->boolean('is_unlimited_expiry')->default(true);
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('topup_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('topup_orders', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('amount_idr');
                $table->decimal('discount_amount', 15, 2)->default(0)->after('voucher_code');
                $table->decimal('final_amount', 15, 2)->nullable()->after('discount_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');

        Schema::table('topup_orders', function (Blueprint $table) {
            if (Schema::hasColumn('topup_orders', 'voucher_code')) {
                $table->dropColumn(['voucher_code', 'discount_amount', 'final_amount']);
            }
        });
    }
};
