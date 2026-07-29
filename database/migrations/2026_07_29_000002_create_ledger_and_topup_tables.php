<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABEL LEDGER MUTASI POIN
        if (!Schema::hasTable('point_transactions')) {
            Schema::create('point_transactions', function (Blueprint $table) {
                $table->id()->comment('ID unik internal transaksi mutasi poin');
                $table->uuid('user_id')->comment('Relasi ke ID pengguna/translator (users.id)');
                $table->string('type', 10)->comment('Arah mutasi poin: credit (poin bertambah) atau debit (poin berkurang)');
                $table->decimal('amount', 15, 2)->comment('Nominal poin yang masuk atau keluar');
                $table->string('description', 255)->comment('Deskripsi manusiawi transaksi');
                $table->string('reference_type', 50)->nullable()->comment('Jenis entitas pemicu transaksi: topup, document_verification, refund, adjustment');
                $table->string('reference_id', 100)->nullable()->comment('ID referensi dari sistem/tabel pemicu');
                $table->string('idempotency_key', 100)->nullable()->unique()->comment('Kunci unik untuk mencegah transaksi ganda');
                $table->longText('metadata')->nullable()->comment('Penyimpanan data teks/JSON fleksibel untuk konteks tambahan');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'type'], 'idx_user_type');
                $table->index('created_at', 'idx_created_at');
                $table->index(['reference_type', 'reference_id'], 'idx_ref');
            });
        }

        // 2. TABEL ORDER TOPUP PAYMENT GATEWAY
        if (!Schema::hasTable('topup_orders')) {
            Schema::create('topup_orders', function (Blueprint $table) {
                $table->id()->comment('ID internal sistem untuk data order topup');
                $table->string('order_id', 100)->unique()->comment('Order ID unik publik yang dikirim ke Payment Gateway');
                $table->uuid('user_id')->comment('Relasi ke ID pengguna/translator yang melakukan topup');
                $table->decimal('amount_idr', 15, 2)->comment('Total nominal tagihan dalam mata uang Rupiah (IDR)');
                $table->decimal('points_issued', 15, 2)->comment('Total poin yang akan/telah diterbitkan ke user');
                $table->decimal('conversion_rate', 15, 2)->default(1.00)->comment('Rasio konversi IDR ke Poin saat order dibuat');
                $table->string('status', 20)->default('pending')->comment('Status transaksi PG: pending, success, failed, expired');
                $table->string('payment_gateway', 50)->nullable()->comment('Nama penyedia Payment Gateway (misal: ipaymu, midtrans)');
                $table->string('payment_channel', 50)->nullable()->comment('Metode/saluran pembayaran spesifik');
                $table->text('payment_response_text')->nullable()->comment('Log/payload respon mentah dari webhook Payment Gateway');
                $table->longText('metadata')->nullable()->comment('Penyimpanan data teks/JSON fleksibel');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Seed initial credit mutasi for existing users if users table has points column
        if (Schema::hasColumn('users', 'points')) {
            $users = DB::table('users')->get();
            foreach ($users as $u) {
                $existingPoint = (float)($u->points ?? 100000);
                if ($existingPoint > 0) {
                    $idempotencyKey = 'initial_seed_' . $u->id;
                    $exists = DB::table('point_transactions')->where('idempotency_key', $idempotencyKey)->exists();
                    if (!$exists) {
                        DB::table('point_transactions')->insert([
                            'user_id' => $u->id,
                            'type' => 'credit',
                            'amount' => $existingPoint,
                            'description' => 'Initial Point Balance Migration',
                            'reference_type' => 'adjustment',
                            'reference_id' => 'INIT-SEED',
                            'idempotency_key' => $idempotencyKey,
                            'metadata' => json_encode(['migrated_from' => 'users.points']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_orders');
        Schema::dropIfExists('point_transactions');
    }
};
