<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Sworn Translator User
        User::updateOrCreate(
            ['email' => 'penerjemah@example.com'],
            [
                'name' => 'Zaki Syah Iqbal, M.Hum.',
                'sk_number' => 'AHU-5432.AH.01.02.Tahun-2025',
                'password' => Hash::make('penerjemah123'),
                'role' => 'TRANSLATOR',
                'bio' => 'Penerjemah Tersumpah resmi dengan spesialisasi dokumen hukum, korporasi, akademis, dan dokumen imigrasi Belanda - Indonesia.',
                'language_services' => 'Belanda - Indonesia, Inggris - Indonesia'
            ]
        );

        // Seed IPPTI Board Super Admin User
        User::updateOrCreate(
            ['email' => 'ippti@example.com'],
            [
                'name' => 'IPPTI Board Administrator',
                'sk_number' => 'IPPTI-HQ-2026',
                'password' => Hash::make('ippti123'),
                'role' => 'SUPERADMIN'
            ]
        );
    }
}
