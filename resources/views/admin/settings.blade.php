@extends('layouts.app')

@section('title', 'DocVerify IPPTI - Pengaturan Aplikasi (Dynamic Settings)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3 border-b border-slate-200 pb-4">
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100">
            <i data-lucide="sliders" class="w-6 h-6"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Aplikasi Dinamis</h1>
            <p class="text-slate-500 text-sm mt-0.5">Kelola parameter poin trial, harga aktivasi Pro, bonus poin, dan ambang notifikasi poin (Arsitektur WordPress-style).</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-bold leading-snug flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-bold leading-snug">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="/admin/settings" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- trial_bonus_points -->
                <div class="space-y-2 p-4 bg-slate-50/60 rounded-2xl border border-slate-200/60">
                    <label for="trial_bonus_points" class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="gift" class="w-4 h-4 text-emerald-600"></i>
                        <span>Poin Gratis Awal (Trial)</span>
                    </label>
                    <input
                        type="number"
                        id="trial_bonus_points"
                        name="trial_bonus_points"
                        value="{{ old('trial_bonus_points', \App\Models\Setting::get('trial_bonus_points', 10000)) }}"
                        required
                        min="0"
                        step="1000"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition bg-white"
                    />
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Jumlah poin gratis yang otomatis dikreditkan ke penerjemah baru saat pendaftaran akun reguler.
                    </p>
                </div>

                <!-- low_point_threshold -->
                <div class="space-y-2 p-4 bg-amber-50/40 rounded-2xl border border-amber-200/60">
                    <label for="low_point_threshold" class="block text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                        <span>Batas Notifikasi Poin Menipis</span>
                    </label>
                    <input
                        type="number"
                        id="low_point_threshold"
                        name="low_point_threshold"
                        value="{{ old('low_point_threshold', \App\Models\Setting::get('low_point_threshold', 20000)) }}"
                        required
                        min="0"
                        step="1000"
                        class="w-full px-4 py-2.5 border border-amber-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition bg-white"
                    />
                    <p class="text-[11px] text-amber-800/80 leading-relaxed">
                        Jika saldo poin penerjemah &le; nilai ini, banner pengingat dan email notifikasi santun akan dipicu.
                    </p>
                </div>

                <!-- min_topup_amount -->
                <div class="space-y-2 p-4 bg-slate-50/60 rounded-2xl border border-slate-200/60 col-span-1 md:col-span-2">
                    <label for="min_topup_amount" class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="award" class="w-4 h-4 text-blue-600"></i>
                        <span>Minimal Top-Up / Aktivasi Akun PRO (Rp)</span>
                    </label>
                    <input
                        type="number"
                        id="min_topup_amount"
                        name="min_topup_amount"
                        value="{{ old('min_topup_amount', \App\Models\Setting::get('min_topup_amount', 100000)) }}"
                        required
                        min="10000"
                        step="10000"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition bg-white"
                    />
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Bebas biaya awal aktivasi! 100% nominal top-up langsung menjadi Poin Verifikasi. Top-up minimal nominal ini (Default Rp 100.000) otomatis mengaktifkan status Akun PRO secara permanen.
                    </p>
                </div>

                <!-- Pengaturan Keanggotaan Tahunan IPPTI (Harga Daftar Awal & Perpanjangan Tahunan) -->
                <div class="col-span-1 md:col-span-2 pt-3 border-t border-slate-200/80">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 mb-3">
                        <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i>
                        <span>Pengaturan Biaya Keanggotaan IPPTI (Daftar Awal & Perpanjangan Tahunan)</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- member_registration_fee -->
                        <div class="space-y-2 p-4 bg-emerald-50/40 rounded-2xl border border-emerald-200/60">
                            <label for="member_registration_fee" class="block text-xs font-bold text-emerald-900 uppercase tracking-wider">
                                Harga Daftar Awal Member Baru (Rp)
                            </label>
                            <input
                                type="number"
                                id="member_registration_fee"
                                name="member_registration_fee"
                                value="{{ old('member_registration_fee', \App\Models\Setting::get('member_registration_fee', 850000)) }}"
                                required
                                min="0"
                                step="10000"
                                class="w-full px-3.5 py-2.5 border border-emerald-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition bg-white"
                            />
                            <p class="text-[10.5px] text-emerald-800/80 leading-relaxed">
                                Biaya pendaftaran awal bagi pendaftar anggota baru IPPTI (Default: Rp 850.000).
                            </p>
                        </div>

                        <!-- member_renewal_fee -->
                        <div class="space-y-2 p-4 bg-blue-50/40 rounded-2xl border border-blue-200/60">
                            <label for="member_renewal_fee" class="block text-xs font-bold text-blue-900 uppercase tracking-wider">
                                Harga Perpanjangan Tahunan (Rp)
                            </label>
                            <input
                                type="number"
                                id="member_renewal_fee"
                                name="member_renewal_fee"
                                value="{{ old('member_renewal_fee', \App\Models\Setting::get('member_renewal_fee', 700000)) }}"
                                required
                                min="0"
                                step="10000"
                                class="w-full px-3.5 py-2.5 border border-blue-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition bg-white"
                            />
                            <p class="text-[10.5px] text-blue-800/80 leading-relaxed">
                                Biaya perpanjangan keanggotaan tahunan ketika masa aktif member expired (Default: Rp 700.000).
                            </p>
                        </div>

                        <!-- member_duration_years -->
                        <div class="space-y-2 p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                            <label for="member_duration_years" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Siklus Perpanjangan (Tahun)
                            </label>
                            <input
                                type="number"
                                id="member_duration_years"
                                name="member_duration_years"
                                value="{{ old('member_duration_years', \App\Models\Setting::get('member_duration_years', 1)) }}"
                                required
                                min="1"
                                max="10"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition bg-white"
                            />
                            <p class="text-[10.5px] text-slate-500 leading-relaxed">
                                Masa berlaku keanggotaan sejak tanggal aktivasi masing-masing member (Default: 1 Tahun).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Jadwal Reminder Perpanjangan Member (30 Hari & 10 Hari Sebelumnya via Email & WhatsApp Kirimdev.com) -->
                <div class="col-span-1 md:col-span-2 pt-3 border-t border-slate-200/80">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 mb-3">
                        <i data-lucide="bell-ring" class="w-4 h-4 text-amber-600"></i>
                        <span>Pengaturan Jadwal Reminder Perpanjangan Member (Otomatis Email & WhatsApp Kirimdev.com)</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- member_reminder_days_1 (Default 30 Hari Sebelumnya) -->
                        <div class="space-y-2 p-4 bg-amber-50/50 rounded-2xl border border-amber-200/70">
                            <label for="member_reminder_days_1" class="block text-xs font-bold text-amber-950 uppercase tracking-wider flex items-center justify-between">
                                <span>Reminder Tahap 1 (Hari Sebelum Expired)</span>
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[10px] font-black">Default: H-30</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    id="member_reminder_days_1"
                                    name="member_reminder_days_1"
                                    value="{{ old('member_reminder_days_1', \App\Models\Setting::get('member_reminder_days_1', 30)) }}"
                                    required
                                    min="1"
                                    max="365"
                                    class="w-full px-3.5 py-2.5 pr-28 border border-amber-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition bg-white"
                                />
                                <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">Hari Sebelum</span>
                            </div>
                            <p class="text-[10.5px] text-amber-900/80 leading-relaxed">
                                Pengingat pertama otomatis dikirim via Email & WhatsApp (Kirimdev.com) saat masa aktif tersisa <strong>30 hari sebelum expired</strong>.
                            </p>
                        </div>

                        <!-- member_reminder_days_2 (Default 10 Hari Sebelumnya) -->
                        <div class="space-y-2 p-4 bg-rose-50/50 rounded-2xl border border-rose-200/70">
                            <label for="member_reminder_days_2" class="block text-xs font-bold text-rose-950 uppercase tracking-wider flex items-center justify-between">
                                <span>Reminder Tahap 2 (Hari Sebelum Expired)</span>
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded text-[10px] font-black">Default: H-10</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    id="member_reminder_days_2"
                                    name="member_reminder_days_2"
                                    value="{{ old('member_reminder_days_2', \App\Models\Setting::get('member_reminder_days_2', 10)) }}"
                                    required
                                    min="1"
                                    max="365"
                                    class="w-full px-3.5 py-2.5 pr-28 border border-rose-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition bg-white"
                                />
                                <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">Hari Sebelum</span>
                            </div>
                            <p class="text-[10.5px] text-rose-900/80 leading-relaxed">
                                Pengingat kedua otomatis dikirim via Email & WhatsApp (Kirimdev.com) saat masa aktif tersisa <strong>10 hari sebelum expired</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <label class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-100/70 transition">
                            <input
                                type="checkbox"
                                name="member_reminder_email_enabled"
                                value="1"
                                {{ (bool)\App\Models\Setting::get('member_reminder_email_enabled', 1) ? 'checked' : '' }}
                                class="w-4 h-4 accent-emerald-600 rounded"
                            />
                            <div class="text-xs">
                                <p class="font-extrabold text-slate-800">Aktifkan Pengiriman Otomatis via Email</p>
                                <p class="text-[10.5px] text-slate-500">Kirim email pengingat H-30, H-10, & saat expired beserta link pembayaran</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-200 cursor-pointer hover:bg-emerald-50 transition">
                            <input
                                type="checkbox"
                                name="member_reminder_whatsapp_enabled"
                                value="1"
                                {{ (bool)\App\Models\Setting::get('member_reminder_whatsapp_enabled', 1) ? 'checked' : '' }}
                                class="w-4 h-4 accent-emerald-600 rounded"
                            />
                            <div class="text-xs">
                                <p class="font-extrabold text-emerald-950">Aktifkan Pengiriman Otomatis via WhatsApp (Kirimdev.com)</p>
                                <p class="text-[10.5px] text-emerald-700">Kirim pesan WhatsApp otomatis H-30, H-10, & saat expired via API Kirimdev.com</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button
                    type="submit"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold shadow-sm transition text-sm cursor-pointer"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan Aplikasi</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Payment Gateway Configuration Box -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Pengaturan Payment Gateway (Xenith Pay)</h2>
                    <p class="text-xs text-slate-500">Konfigurasi Access Key, Secret Key, dan Mode Environment (Sandbox / Production).</p>
                </div>
            </div>
        </div>

        <form action="/admin/settings/ipaymu" method="POST" class="p-6 space-y-5">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="settings_ipaymu_va" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Access Key API (Xenith Pay)</label>
                    <input
                        type="text"
                        id="settings_ipaymu_va"
                        name="ipaymu_va"
                        value="{{ old('ipaymu_va', \App\Models\Setting::get('xenith_access_key', env('XENITH_ACCESS_KEY', config('services.xenith.access_key')))) }}"
                        placeholder="ak-..."
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono bg-white"
                    />
                </div>

                <div>
                    <label for="settings_ipaymu_env" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Environment Mode</label>
                    <select
                        id="settings_ipaymu_env"
                        name="ipaymu_env"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition bg-white"
                    >
                        <option value="sandbox" {{ old('ipaymu_env', \App\Models\Setting::get('xenith_env', env('XENITH_ENV', config('services.xenith.env', 'sandbox')))) === 'sandbox' ? 'selected' : '' }}>Sandbox (Pengujian / Testing)</option>
                        <option value="production" {{ old('ipaymu_env', \App\Models\Setting::get('xenith_env', env('XENITH_ENV', config('services.xenith.env', 'sandbox')))) === 'production' ? 'selected' : '' }}>Production (Live / Transaksi Nyata)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="settings_ipaymu_api_key" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Secret Key API (Xenith Pay)</label>
                    <input
                        type="password"
                        id="settings_ipaymu_api_key"
                        name="ipaymu_api_key"
                        value="{{ old('ipaymu_api_key', \App\Models\Setting::get('xenith_secret_key', env('XENITH_SECRET_KEY', config('services.xenith.secret_key')))) }}"
                        placeholder="sk-..."
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono bg-white"
                    />
                </div>

                <div>
                    <label for="settings_ipaymu_webhook_secret" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Webhook Signature Secret (Xenith)</label>
                    <input
                        type="password"
                        id="settings_ipaymu_webhook_secret"
                        name="ipaymu_webhook_secret"
                        value="{{ old('ipaymu_webhook_secret', \App\Models\Setting::get('xenith_webhook_secret', env('XENITH_WEBHOOK_SECRET', config('services.xenith.webhook_secret')))) }}"
                        placeholder="Masukkan Webhook Signature Secret"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono bg-white"
                    />
                </div>
            </div>

            <!-- Webhook & Callback URL Guide for Xenith Pay -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2.5">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                    <i data-lucide="link" class="w-4 h-4 text-emerald-600"></i>
                    <span>URL Webhook & Callback yang perlu diisi di Dashboard Xenith Pay:</span>
                </div>
                <div class="space-y-1.5 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 bg-white p-2.5 rounded-lg border border-slate-200">
                        <span class="font-bold text-slate-600">Payin / Payment Callback URL:</span>
                        <code class="font-mono text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded text-[11px] select-all">{{ url('/xenith/callback') }}</code>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 bg-white p-2.5 rounded-lg border border-slate-200">
                        <span class="font-bold text-slate-600">Redirect / Return URL:</span>
                        <code class="font-mono text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded text-[11px] select-all">{{ url('/xenith/return') }}</code>
                    </div>
                </div>
            </div>

            <div class="pt-3 flex justify-end">
                <button
                    type="submit"
                    class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-xl font-bold text-xs transition shadow-sm cursor-pointer"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan Gateway</span>
                </button>
            </div>
        </form>
    </div>

    <!-- WhatsApp Gateway Configuration Box (Kirimdev.com) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-200">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Pengaturan WhatsApp Gateway (Kirimdev.com)</h2>
                    <p class="text-xs text-slate-500">Integrasi resmi Meta WhatsApp Cloud API via <strong>kirimdev.com</strong> untuk pengiriman otomatis Reminder Perpanjangan H-{{ \App\Models\Setting::get('member_reminder_days_1', 30) }} & H-{{ \App\Models\Setting::get('member_reminder_days_2', 10) }}.</p>
                </div>
            </div>
            <a href="https://docs.kirimdev.com" target="_blank" rel="noopener" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 flex items-center gap-1">
                <span>Docs Kirimdev</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <form action="/admin/settings/kirimdev" method="POST" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="kirimdev_api_key" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        API Key Kirimdev (Bearer Token)
                    </label>
                    <input
                        type="password"
                        id="kirimdev_api_key"
                        name="kirimdev_api_key"
                        value="{{ old('kirimdev_api_key', \App\Models\Setting::get('kirimdev_api_key', env('KIRIMDEV_API_KEY', ''))) }}"
                        placeholder="kdv_live_..."
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition font-mono bg-white"
                    />
                    <p class="text-[11px] text-slate-500 mt-1">
                        Dapatkan API Key (format <code class="font-mono text-emerald-700">kdv_live_...</code>) dari dashboard <a href="https://kirimdev.com" target="_blank" class="underline text-emerald-700">kirimdev.com</a>.
                    </p>
                </div>

                <div>
                    <label for="kirimdev_phone_number_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        WhatsApp Phone Number ID
                    </label>
                    <input
                        type="text"
                        id="kirimdev_phone_number_id"
                        name="kirimdev_phone_number_id"
                        value="{{ old('kirimdev_phone_number_id', \App\Models\Setting::get('kirimdev_phone_number_id', env('KIRIMDEV_PHONE_NUMBER_ID', ''))) }}"
                        placeholder="Contoh: 105928374619283"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition font-mono bg-white"
                    />
                    <p class="text-[11px] text-slate-500 mt-1">
                        ID Nomor WhatsApp pengirim dari dashboard Kirimdev / endpoint <code class="font-mono text-slate-700">/v1/accounts</code>.
                    </p>
                </div>
            </div>

            <div>
                <label for="kirimdev_base_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Base Endpoint URL Kirimdev
                </label>
                <input
                    type="url"
                    id="kirimdev_base_url"
                    name="kirimdev_base_url"
                    value="{{ old('kirimdev_base_url', \App\Models\Setting::get('kirimdev_base_url', env('KIRIMDEV_BASE_URL', 'https://api.kirimdev.com/v1'))) }}"
                    placeholder="https://api.kirimdev.com/v1"
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition font-mono bg-white"
                />
                <p class="text-[11px] text-slate-500 mt-1">
                    Endpoint pengiriman: <code class="font-mono text-emerald-700">POST https://api.kirimdev.com/v1/{phone_number_id}/messages</code>
                </p>
            </div>

            <div class="pt-2 flex justify-end">
                <button
                    type="submit"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs transition shadow-sm cursor-pointer"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Konfigurasi Kirimdev.com</span>
                </button>
            </div>
        </form>

        <!-- Form Uji Coba Kirim Pesan WhatsApp via Kirimdev.com -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/80">
            <form action="/admin/settings/kirimdev/test" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                @csrf
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                    <i data-lucide="send" class="w-4 h-4 text-emerald-600"></i>
                    <span>Uji Coba Kirim Reminder WhatsApp (Kirimdev.com):</span>
                </div>
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <input
                        type="text"
                        name="test_phone"
                        placeholder="Nomor WA Tujuan (cth: 08123456789)"
                        required
                        class="flex-1 px-3.5 py-2 border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-800 bg-white outline-none focus:border-emerald-500"
                    />
                    <button
                        type="submit"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-extrabold transition cursor-pointer whitespace-nowrap flex items-center gap-1.5"
                    >
                        <i data-lucide="message-Square" class="w-3.5 h-3.5"></i>
                        <span>Test Kirim WA</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
