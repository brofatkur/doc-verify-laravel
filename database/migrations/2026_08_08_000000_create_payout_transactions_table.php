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
        Schema::create('payout_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payout_id')->nullable()->index();
            $table->string('reference_code')->unique()->index();
            $table->enum('recipient_type', ['IPPTI', 'BENLARIS'])->default('IPPTI');
            $table->decimal('amount', 15, 2);
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_transactions');
    }
};
