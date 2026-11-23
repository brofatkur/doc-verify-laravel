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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'is_verified')) {
                    $table->boolean('is_verified')->default(true)->after('role');
                }
                if (!Schema::hasColumn('users', 'verification_status')) {
                    $table->string('verification_status')->default('approved')->after('is_verified');
                }
                if (!Schema::hasColumn('users', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('verification_status');
                }
                if (!Schema::hasColumn('users', 'verified_by')) {
                    $table->uuid('verified_by')->nullable()->after('verified_at');
                }
                if (!Schema::hasColumn('users', 'registered_at')) {
                    $table->timestamp('registered_at')->nullable()->after('verified_by');
                }
                if (!Schema::hasColumn('users', 'activated_at')) {
                    $table->timestamp('activated_at')->nullable()->after('registered_at');
                }
                if (!Schema::hasColumn('users', 'expired_at')) {
                    $table->timestamp('expired_at')->nullable()->after('activated_at');
                }
                if (!Schema::hasColumn('users', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('expired_at');
                }
                if (!Schema::hasColumn('users', 'nik')) {
                    $table->string('nik', 50)->nullable()->after('name');
                }
                if (!Schema::hasColumn('users', 'address')) {
                    $table->text('address')->nullable()->after('whatsapp');
                }
                if (!Schema::hasColumn('users', 'city')) {
                    $table->string('city', 150)->nullable()->after('address');
                }
                if (!Schema::hasColumn('users', 'sk_document_path')) {
                    $table->string('sk_document_path')->nullable()->after('sk_lengkap');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $cols = [
                    'is_verified',
                    'verification_status',
                    'verified_at',
                    'verified_by',
                    'registered_at',
                    'activated_at',
                    'expired_at',
                    'rejection_reason',
                    'nik',
                    'address',
                    'city',
                    'sk_document_path',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
