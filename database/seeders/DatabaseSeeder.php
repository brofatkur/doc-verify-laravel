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

        // Seed IPPTI Board Admin User
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'IPPTI Admin Staff',
                'sk_number' => 'IPPTI-ADMIN-01',
                'password' => Hash::make('admin123'),
                'role' => 'ADMIN'
            ]
        );

        // Seed Muhammad Arifin Sworn Translator User
        User::updateOrCreate(
            ['email' => 'arifin@example.com'],
            [
                'name' => 'Muhammad Arifin',
                'sk_number' => '25004',
                'password' => Hash::make('penerjemah123'),
                'role' => 'TRANSLATOR',
                'language_services' => 'Indonesia - Inggris, Inggris - Indonesia, Indonesia - Belanda, Belanda - Indonesia',
                'bio' => 'AHU-55 AH.03.07.2022 Tanggal 5 Oktober 2022',
                'no_sk_kemenkum' => 'AHU-55 AH.03.07.2022',
                'tgl_sk' => '5 Oktober 2022',
                'masa_aktif' => 'Seumur Hidup',
                'sk_lengkap' => 'AHU-55 AH.03.07.2022 Tanggal 5 Oktober 2022'
            ]
        );

        // Seed standard Document Types
        $types = [
            'Akta Kelahiran',
            'Akta Kematian',
            'Akta Pernikahan',
            'Akta Perceraian',
            'Kartu Tanda Penduduk (KTP)',
            'Paspor',
            'Kartu Keluarga (KK)',
            'Ijazah',
            'Transkrip Nilai',
            'Surat Keputusan (SK)',
            'Kontrak Perjanjian',
            'Dokumen Medis',
            'Laporan Keuangan',
        ];
        foreach ($types as $type) {
            \App\Models\DocumentType::updateOrCreate(['name' => $type]);
        }

        // Seed 22 official Language Directions (REV-10)
        $directions = [
            'Indonesia - Inggris',
            'Inggris - Indonesia',
            'Indonesia - Belanda',
            'Belanda - Indonesia',
            'Indonesia - Arab',
            'Arab - Indonesia',
            'Indonesia - Prancis',
            'Prancis - Indonesia',
            'Indonesia - Jerman',
            'Jerman - Indonesia',
            'Indonesia - Mandarin',
            'Mandarin - Indonesia',
            'Indonesia - Jepang',
            'Jepang - Indonesia',
            'Indonesia - Korea',
            'Korea - Indonesia',
            'Indonesia - Rusia',
            'Rusia - Indonesia',
            'Indonesia - Spanyol',
            'Spanyol - Indonesia',
            'Indonesia - Italia',
            'Italia - Indonesia',
        ];
        foreach ($directions as $dir) {
            \App\Models\LanguageDirection::updateOrCreate(['name' => $dir]);
        }
    }
}
