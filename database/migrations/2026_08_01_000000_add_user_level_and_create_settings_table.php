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
        // 1. Tambahkan kolom user_level ke tabel users
        if (!Schema::hasColumn('users', 'user_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('user_level', 20)->default('reguler')->after('role')->comment('Level akun penerjemah: reguler atau pro');
            });
        }

        // 2. Buat tabel settings untuk penyimpanan konfigurasi dinamis (WordPress-style)
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('setting_key', 100)->unique()->comment('Kunci konfigurasi unik');
                $table->longText('setting_value')->nullable()->comment('Nilai konfigurasi');
                $table->timestamps();
            });
        }

        // 3. Seeder data awal (default settings)
        $defaultSettings = [
            [
                'setting_key' => 'trial_bonus_points',
                'setting_value' => '10000',
            ],
            [
                'setting_key' => 'pro_activation_price',
                'setting_value' => '300000',
            ],
            [
                'setting_key' => 'pro_activation_points',
                'setting_value' => '100000',
            ],
            [
                'setting_key' => 'low_point_threshold',
                'setting_value' => '20000',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            $exists = DB::table('settings')->where('setting_key', $setting['setting_key'])->exists();
            if (!$exists) {
                DB::table('settings')->insert([
                    'setting_key' => $setting['setting_key'],
                    'setting_value' => $setting['setting_value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');

        if (Schema::hasColumn('users', 'user_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_level');
            });
        }
    }
};
