@extends('layouts.app')

@section('title', 'Manajemen Keuangan & Payout Bagi Hasil - DocVerify IPPTI')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 rounded-2xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 tracking-wide uppercase">
                        Super Admin Treasury
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                        Xenith Pay API v1 & v2
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Manajemen Keuangan & Payout</h1>
                <p class="text-slate-400 text-xs sm:text-sm">
                    Monitoring saldo kas, histori transaksi top-up, dan bagi hasil otomatis 50% IPPTI & 50% Benlaris.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="document.getElementById('modal-disburse').classList.remove('hidden')" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-emerald-950/40 hover:shadow-emerald-900/60 transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Cairkan Bagi Hasil 50:50</span>
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/25 text-emerald-700 p-4 rounded-xl text-xs sm:text-sm font-semibold flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/25 text-rose-700 p-4 rounded-xl text-xs sm:text-sm font-semibold flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 4 Key Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Live Available Xenith Balance -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Saldo Xenith Pay</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">
                    Rp {{ number_format($balanceData['available_balance'] ?? 0, 0, ',', '.') }}
                </h3>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] font-medium text-emerald-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Tersedia Real-time di Gateway</span>
                </div>
            </div>
        </div>

        <!-- Total Inflow Top-up -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pemasukan (Inflow)</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">
                    Rp {{ number_format($totalInflow, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-slate-500 font-medium">
                    {{ number_format($totalPayinCount) }} Transaksi ({{ number_format($totalPointsIssued, 0, ',', '.') }} Poin)
                </p>
            </div>
        </div>

        <!-- Ready to Disburse (50:50) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Saldo Siap Cair</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="scale" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl sm:text-2xl font-black text-amber-600">
                    Rp {{ number_format($readyToDisburse, 0, ',', '.') }}
                </h3>
                <div class="mt-1 flex items-center justify-between text-[11px] font-bold text-slate-600">
                    <span>IPPTI: Rp {{ number_format($splitIppti, 0, ',', '.') }}</span>
                    <span>Benlaris: Rp {{ number_format($splitBenlaris, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Payouts Completed -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sudah Dicairkan</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">
                    Rp {{ number_format($totalPayoutDisbursed, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-slate-500 font-medium">
                    IPPTI: Rp {{ number_format($totalPayoutIppti, 0, ',', '.') }} | Benlaris: Rp {{ number_format($totalPayoutBenlaris, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- 50:50 Revenue Split Interactive Visualization & Bank Settings -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Bank Account Settings for 50:50 Split & Auto Monthly Payout -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="space-y-0.5">
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                        <i data-lucide="building-2" class="w-5 h-5 text-emerald-600"></i>
                        <span>Pengaturan Rekening Tujuan Bagi Hasil (50:50)</span>
                    </h2>
                    <p class="text-xs text-slate-500">Nomor rekening tujuan transfer saat payout manual maupun otomatis via Xenith Pay.</p>
                </div>
            </div>

            <form action="/admin/finance/settings" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- IPPTI Account (50%) -->
                    <div class="bg-slate-50/80 rounded-xl p-5 border border-slate-200/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                Rekening IPPTI (50%)
                            </span>
                            <i data-lucide="landmark" class="w-4 h-4 text-emerald-600"></i>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name_ippti" value="{{ $bankSettings['bank_name_ippti'] }}" required class="w-full px-3 py-2 text-xs font-semibold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Contoh: Bank BCA" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Bank Channel Code (Xenith)</label>
                            <input type="text" name="bank_channel_ippti" value="{{ $bankSettings['bank_channel_ippti'] }}" required class="w-full px-3 py-2 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="CENAIDJA / BMRIIDJA / BRINIDJA" />
                            <span class="text-[10px] text-slate-400">Kode channel API: CENAIDJA (BCA), BMRIIDJA (Mandiri), BRINIDJA (BRI), BNINIDJA (BNI)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_ippti" value="{{ $bankSettings['bank_account_ippti'] }}" required class="w-full px-3 py-2 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Nomor Rekening Bank" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Atas Nama Pemilik Rekening</label>
                            <input type="text" name="bank_holder_ippti" value="{{ $bankSettings['bank_holder_ippti'] }}" required class="w-full px-3 py-2 text-xs font-semibold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Nama Pemilik Rekening" />
                        </div>
                    </div>

                    <!-- Benlaris Account (50%) -->
                    <div class="bg-slate-50/80 rounded-xl p-5 border border-slate-200/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-800 uppercase tracking-wider">
                                Rekening Benlaris (50%)
                            </span>
                            <i data-lucide="landmark" class="w-4 h-4 text-blue-600"></i>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name_benlaris" value="{{ $bankSettings['bank_name_benlaris'] }}" required class="w-full px-3 py-2 text-xs font-semibold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Contoh: Bank Mandiri" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Bank Channel Code (Xenith)</label>
                            <input type="text" name="bank_channel_benlaris" value="{{ $bankSettings['bank_channel_benlaris'] }}" required class="w-full px-3 py-2 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="BMRIIDJA / CENAIDJA / BRINIDJA" />
                            <span class="text-[10px] text-slate-400">Kode channel API: BMRIIDJA (Mandiri), CENAIDJA (BCA), BRINIDJA (BRI)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_benlaris" value="{{ $bankSettings['bank_account_benlaris'] }}" required class="w-full px-3 py-2 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Nomor Rekening Bank" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Atas Nama Pemilik Rekening</label>
                            <input type="text" name="bank_holder_benlaris" value="{{ $bankSettings['bank_holder_benlaris'] }}" required class="w-full px-3 py-2 text-xs font-semibold rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Nama Pemilik Rekening" />
                        </div>
                    </div>
                </div>

                <!-- Monthly Automatic Payout Trigger Settings -->
                <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-4.5 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar-clock" class="w-4 h-4 text-amber-700"></i>
                            <h4 class="text-xs font-extrabold text-amber-950 uppercase tracking-wide">Jadwal Pencairan Otomatis Awal Bulan</h4>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_payout_monthly" value="1" {{ $bankSettings['auto_payout_monthly'] === '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                    <p class="text-xs text-amber-900 leading-relaxed">
                        Jika diaktifkan, sistem akan otomatis mengeksekusi pencairan bagi hasil 50% IPPTI & 50% Benlaris pada setiap tanggal 1 awal bulan (pukul 00:01 WIB) jika saldo siap cair melebihi batas minimal.
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center gap-2">
                        <label class="text-xs font-bold text-amber-950">Minimal Saldo Pencairan Otomatis:</label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">Rp</span>
                            <input type="number" name="min_payout_threshold" value="{{ (int)$bankSettings['min_payout_threshold'] }}" min="10000" step="10000" class="w-44 px-3 py-1.5 text-xs font-bold rounded-lg border border-amber-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan Rekening & Jadwal</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Col: Instant 50:50 Quick Action & Documentation Helper -->
        <div class="space-y-6">
            <!-- Quick Trigger Card -->
            <div class="bg-gradient-to-br from-emerald-950 via-slate-900 to-slate-950 rounded-2xl p-6 text-white border border-emerald-800/40 shadow-lg space-y-5">
                <div class="space-y-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wide">
                        Eksekusi Cepat Payout
                    </span>
                    <h3 class="text-lg font-bold text-white">Bagi Hasil Realtime</h3>
                    <p class="text-xs text-slate-400">Cairkan saldo siap bagi hasil ke rekening IPPTI & Benlaris secara instan.</p>
                </div>

                <div class="bg-slate-900/90 rounded-xl p-4 border border-slate-800 space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400">Saldo Siap Dicairkan</span>
                        <span class="font-black text-amber-400 text-sm">Rp {{ number_format($readyToDisburse, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-slate-800"></div>
                    <div class="space-y-1 text-[11px]">
                        <div class="flex justify-between items-center text-emerald-400 font-semibold">
                            <span>50% Rekening IPPTI</span>
                            <span class="font-mono">Rp {{ number_format($splitIppti, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-blue-400 font-semibold">
                            <span>50% Rekening Benlaris</span>
                            <span class="font-mono">Rp {{ number_format($splitBenlaris, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <form action="/admin/finance/payout" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses pencairan 50:50 ke rekening IPPTI dan Benlaris sekarang via Xenith Pay?')">
                    @csrf
                    <input type="hidden" name="amount" value="{{ $readyToDisburse }}" />
                    <button type="submit" {{ $readyToDisburse < 10000 ? 'disabled' : '' }} class="w-full py-3 px-4 rounded-xl font-bold text-xs sm:text-sm text-white bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg shadow-emerald-950/60 flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                        <span>Proses Payout 50:50 Sekarang</span>
                    </button>
                </form>
            </div>

            <!-- Documentation & Integration Specs Box -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-3 text-xs text-slate-600">
                <h4 class="font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                    <span>Integrasi API Xenith Payout</span>
                </h4>
                <ul class="space-y-2 list-disc list-inside text-slate-600 leading-relaxed">
                    <li>Menggunakan signature HMAC-SHA256 V2 dengan idempotency key unik untuk mencegah duplikasi transfer.</li>
                    <li>Status pencairan diperbarui secara realtime melalui Webhook <code class="bg-slate-100 px-1.5 py-0.5 rounded text-[10px] font-mono text-slate-800">/xenith/payout-callback</code>.</li>
                    <li>Sistem otomatis menjadwalkan trigger awal bulan melalui <code class="bg-slate-100 px-1.5 py-0.5 rounded text-[10px] font-mono text-slate-800">finance:monthly-payout</code>.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Transaction History (Inflow & Outflow Tabs) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="border-b border-slate-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-emerald-600"></i>
                    <span>Riwayat Transaksi Keuangan</span>
                </h3>
                <p class="text-xs text-slate-500">Daftar transaksi masuk (top-up poin) dan transaksi pencairan bagi hasil ke rekening.</p>
            </div>

            <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl">
                <button type="button" onclick="showTab('tab-payout')" id="btn-tab-payout" class="px-4 py-1.5 rounded-lg text-xs font-bold transition bg-white text-slate-900 shadow-xs cursor-pointer">
                    Riwayat Payout (Outflow)
                </button>
                <button type="button" onclick="showTab('tab-payin')" id="btn-tab-payin" class="px-4 py-1.5 rounded-lg text-xs font-bold transition text-slate-600 hover:text-slate-900 cursor-pointer">
                    Riwayat Top-up Masuk (Inflow)
                </button>
            </div>
        </div>

        <!-- Tab 1: Payout Outflow History -->
        <div id="tab-payout" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                            <th class="py-3 px-4">No. Referensi</th>
                            <th class="py-3 px-4">Penerima</th>
                            <th class="py-3 px-4">Rekening Tujuan</th>
                            <th class="py-3 px-4">Nominal Transfer</th>
                            <th class="py-3 px-4">Tipe Trigger</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($payoutTransactions as $payout)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $payout->reference_code }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $payout->recipient_type === 'IPPTI' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $payout->recipient_type }} (50%)
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 space-y-0.5">
                                    <p class="font-bold text-slate-900">{{ $payout->bank_name }} ({{ $payout->account_number }})</p>
                                    <p class="text-[10px] text-slate-500">{{ $payout->account_holder_name }}</p>
                                </td>
                                <td class="py-3.5 px-4 font-black text-slate-900">
                                    Rp {{ number_format($payout->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $payout->trigger_type === 'auto_monthly' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $payout->trigger_type === 'auto_monthly' ? 'Jadwal Awal Bulan' : 'Manual Payout' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($payout->status === 'success')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                            <i data-lucide="check" class="w-3 h-3"></i> Berhasil
                                        </span>
                                    @elseif($payout->status === 'simulated')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                            <i data-lucide="info" class="w-3 h-3"></i> Simulasi Testing
                                        </span>
                                    @elseif($payout->status === 'processing' || $payout->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                            <i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i> Diproses
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                            <i data-lucide="x" class="w-3 h-3"></i> Gagal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-medium whitespace-nowrap">
                                    {{ $payout->created_at->translatedFormat('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                                    Belum ada transaksi pencairan / payout.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payoutTransactions->appends(request()->except('payout_page'))->links() }}
            </div>
        </div>

        <!-- Tab 2: Payin Inflow History -->
        <div id="tab-payin" class="p-6 hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                            <th class="py-3 px-4">No. Pesanan</th>
                            <th class="py-3 px-4">Nama Penerjemah</th>
                            <th class="py-3 px-4">Nominal IDR</th>
                            <th class="py-3 px-4">Poin Masuk</th>
                            <th class="py-3 px-4">Metode / Gateway</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($payinOrders as $order)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $order->order_id }}</td>
                                <td class="py-3.5 px-4 space-y-0.5">
                                    <p class="font-bold text-slate-900">{{ $order->user->name ?? 'Penerjemah' }}</p>
                                    <p class="text-[10px] text-slate-500 font-mono">No. Anggota: {{ $order->user->sk_number ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4 font-black text-emerald-600">
                                    Rp {{ number_format($order->amount_idr, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    +{{ number_format($order->points, 0, ',', '.') }} Poin
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 uppercase">
                                        {{ $order->payment_method ?: 'Xenith Pay' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($order->status === 'success')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                            <i data-lucide="check" class="w-3 h-3"></i> Lunas
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Menunggu
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                            <i data-lucide="x" class="w-3 h-3"></i> Batal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-medium whitespace-nowrap">
                                    {{ $order->created_at->translatedFormat('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                                    Belum ada transaksi top-up masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payinOrders->appends(request()->except('payin_page'))->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Custom Payout -->
<div id="modal-disburse" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-200 shadow-2xl space-y-5 relative">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="space-y-0.5">
                <h3 class="text-lg font-extrabold text-slate-900">Pencairan Bagi Hasil 50:50</h3>
                <p class="text-xs text-slate-500">Transfer dana bagi hasil ke rekening IPPTI & Benlaris via Xenith Pay.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-disburse').classList.add('hidden')" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="/admin/finance/payout" method="POST" class="space-y-4">
            @csrf

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-slate-500">Saldo Siap Dicairkan:</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($readyToDisburse, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-emerald-600">
                    <span>50% Rekening IPPTI:</span>
                    <span id="label-split-ippti">Rp {{ number_format($splitIppti, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-blue-600">
                    <span>50% Rekening Benlaris:</span>
                    <span id="label-split-benlaris">Rp {{ number_format($splitBenlaris, 0, ',', '.') }}</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nominal Total yang Dicairkan (IDR)</label>
                <input type="number" id="input-payout-amount" name="amount" value="{{ $readyToDisburse }}" min="10000" step="1000" oninput="updateSplitPreview(this.value)" required class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                <span class="text-[11px] text-slate-400">Dana ini akan otomatis dibagi 50% ke rekening IPPTI dan 50% ke Benlaris.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-disburse').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Eksekusi Payout Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    function showTab(tabId) {
        document.getElementById('tab-payout').classList.add('hidden');
        document.getElementById('tab-payin').classList.add('hidden');
        
        document.getElementById('btn-tab-payout').className = "px-4 py-1.5 rounded-lg text-xs font-bold transition text-slate-600 hover:text-slate-900 cursor-pointer";
        document.getElementById('btn-tab-payin').className = "px-4 py-1.5 rounded-lg text-xs font-bold transition text-slate-600 hover:text-slate-900 cursor-pointer";

        if (tabId === 'tab-payout') {
            document.getElementById('tab-payout').classList.remove('hidden');
            document.getElementById('btn-tab-payout').className = "px-4 py-1.5 rounded-lg text-xs font-bold transition bg-white text-slate-900 shadow-xs cursor-pointer";
        } else {
            document.getElementById('tab-payin').classList.remove('hidden');
            document.getElementById('btn-tab-payin').className = "px-4 py-1.5 rounded-lg text-xs font-bold transition bg-white text-slate-900 shadow-xs cursor-pointer";
        }
        lucide.createIcons();
    }

    function updateSplitPreview(amount) {
        const total = parseFloat(amount) || 0;
        const half = Math.floor(total / 2);
        const formatted = new Intl.NumberFormat('id-ID').format(half);
        document.getElementById('label-split-ippti').innerText = 'Rp ' + formatted;
        document.getElementById('label-split-benlaris').innerText = 'Rp ' + formatted;
    }
</script>
@endsection
