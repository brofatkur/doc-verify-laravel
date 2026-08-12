@extends('layouts.app')

@section('title', 'Laporan Keuangan & Rekap Bagi Hasil - DocVerify IPPTI')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 rounded-2xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 tracking-wide uppercase">
                        Admin Treasury IPPTI
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-800/80 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Xenith Pay Production (Live)
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Laporan Keuangan & Rekap Bagi Hasil</h1>
                <p class="text-slate-400 text-xs sm:text-sm">
                    Monitoring saldo kas realtime dari server Xenith Pay Production, ringkasan bagi hasil 50% IPPTI & 50% Benlaris, dan histori transaksi.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Sync Button -->
                <form action="{{ url('/admin/finance/sync') }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-900/30 hover:shadow-emerald-900/50 transition cursor-pointer active:scale-95"
                        title="Tarik data transaksi & saldo terbaru langsung dari server Xenith Pay"
                    >
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Sinkronkan Data Realtime</span>
                    </button>
                </form>

                <div class="flex items-center gap-2 bg-slate-800/90 px-3.5 py-2.5 rounded-xl border border-slate-700 text-xs text-slate-300">
                    <i data-lucide="clock" class="w-4 h-4 text-emerald-400"></i>
                    <span>{{ now()->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>
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
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Saldo Kas Gateway</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">
                    Rp {{ number_format($balanceData['available_balance'] ?? 0, 0, ',', '.') }}
                </h3>
                <div class="mt-1 flex items-center justify-between text-[11px] font-medium">
                    <span class="flex items-center gap-1 text-emerald-600 font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Tersedia Realtime
                    </span>
                    @if(($balanceData['pending_balance'] ?? 0) > 0)
                        <span class="text-amber-600 font-bold">Pending: Rp {{ number_format($balanceData['pending_balance'], 0, ',', '.') }}</span>
                    @endif
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
                    {{ number_format($totalPayinCount) }} Transaksi Masuk ({{ number_format($totalPointsIssued, 0, ',', '.') }} Poin)
                </p>
            </div>
        </div>

        <!-- Hak IPPTI 50% -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Hak Bagi Hasil IPPTI (50%)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="landmark" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl sm:text-2xl font-black text-emerald-700">
                    Rp {{ number_format($splitIppti, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-emerald-600 font-bold">
                    50% Porsi Kas Organisasi IPPTI
                </p>
            </div>
        </div>

        <!-- Hak Benlaris 50% -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Hak Bagi Hasil Benlaris (50%)</span>
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i data-lucide="building-2" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl sm:text-2xl font-black text-blue-700">
                    Rp {{ number_format($splitBenlaris, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-blue-600 font-bold">
                    50% Porsi Pengembang Benlaris
                </p>
            </div>
        </div>
    </div>

    <!-- Rekapitulasi Pembagian Hasil 50:50 & Rekening Tujuan -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="space-y-0.5">
                <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-emerald-600"></i>
                    <span>Rekapitulasi Pembagian Hasil (50% IPPTI & 50% Benlaris)</span>
                </h2>
                <p class="text-xs text-slate-500">Rincian alokasi bagi hasil dan data rekening bank resmi masing-masing pihak berdasarkan data transaksi live.</p>
            </div>
            <button
                type="button"
                onclick="document.getElementById('form-bank-settings').classList.toggle('hidden')"
                class="flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer self-start sm:self-auto"
            >
                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                <span>Kelola Data Rekening</span>
            </button>
        </div>

        <!-- 2 Visual Cards for IPPTI & Benlaris Split -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- IPPTI Card -->
            <div class="bg-gradient-to-br from-emerald-50/90 to-teal-50/50 rounded-2xl p-5 border border-emerald-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-600 text-white uppercase tracking-wider">
                            IPPTI (50%)
                        </span>
                        <span class="text-xs font-bold text-emerald-950">Ikatan Penerjemah Indonesia</span>
                    </div>
                    <i data-lucide="landmark" class="w-5 h-5 text-emerald-600"></i>
                </div>

                <div class="bg-white/80 backdrop-blur-xs p-4 rounded-xl border border-emerald-100 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Total Akumulasi Bagi Hasil:</span>
                        <span class="text-lg font-black text-emerald-700 font-mono">Rp {{ number_format($splitIppti, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500">Saldo Siap Tarik (Kas Realtime):</span>
                        <span class="font-bold text-emerald-800 font-mono">Rp {{ number_format($splitIpptiAvailable ?? $splitIppti, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-emerald-100"></div>
                    <div class="text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Bank:</span>
                            <span class="font-bold text-slate-800">{{ $bankSettings['bank_name_ippti'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Rekening:</span>
                            <span class="font-mono font-black text-slate-900">{{ $bankSettings['bank_account_ippti'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Atas Nama:</span>
                            <span class="font-bold text-slate-800">{{ $bankSettings['bank_holder_ippti'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benlaris Card -->
            <div class="bg-gradient-to-br from-blue-50/90 to-indigo-50/50 rounded-2xl p-5 border border-blue-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-600 text-white uppercase tracking-wider">
                            Benlaris (50%)
                        </span>
                        <span class="text-xs font-bold text-blue-950">PT Benlaris Sukses Indonesia</span>
                    </div>
                    <i data-lucide="building-2" class="w-5 h-5 text-blue-600"></i>
                </div>

                <div class="bg-white/80 backdrop-blur-xs p-4 rounded-xl border border-blue-100 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Total Akumulasi Bagi Hasil:</span>
                        <span class="text-lg font-black text-blue-700 font-mono">Rp {{ number_format($splitBenlaris, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500">Saldo Siap Tarik (Kas Realtime):</span>
                        <span class="font-bold text-blue-800 font-mono">Rp {{ number_format($splitBenlarisAvailable ?? $splitBenlaris, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-blue-100"></div>
                    <div class="text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Bank:</span>
                            <span class="font-bold text-slate-800">{{ $bankSettings['bank_name_benlaris'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Rekening:</span>
                            <span class="font-mono font-black text-slate-900">{{ $bankSettings['bank_account_benlaris'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Atas Nama:</span>
                            <span class="font-bold text-slate-800">{{ $bankSettings['bank_holder_benlaris'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collapsible Bank Account Form for updating bank details -->
        <div id="form-bank-settings" class="hidden pt-4 border-t border-slate-100">
            <form action="/admin/finance/settings" method="POST" class="space-y-5 bg-slate-50 p-5 rounded-2xl border border-slate-200/80">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- IPPTI Settings -->
                    <div class="space-y-3 bg-white p-4 rounded-xl border border-slate-200">
                        <h4 class="text-xs font-black text-emerald-800 uppercase tracking-wider">Ubah Rekening IPPTI (50%)</h4>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name_ippti" value="{{ $bankSettings['bank_name_ippti'] }}" required class="w-full px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Bank Channel Code</label>
                            <input type="text" name="bank_channel_ippti" value="{{ $bankSettings['bank_channel_ippti'] }}" required class="w-full px-3 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_ippti" value="{{ $bankSettings['bank_account_ippti'] }}" required class="w-full px-3 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Atas Nama Rekening</label>
                            <input type="text" name="bank_holder_ippti" value="{{ $bankSettings['bank_holder_ippti'] }}" required class="w-full px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
                        </div>
                    </div>

                    <!-- Benlaris Settings -->
                    <div class="space-y-3 bg-white p-4 rounded-xl border border-slate-200">
                        <h4 class="text-xs font-black text-blue-800 uppercase tracking-wider">Ubah Rekening Benlaris (50%)</h4>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name_benlaris" value="{{ $bankSettings['bank_name_benlaris'] }}" required class="w-full px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Bank Channel Code</label>
                            <input type="text" name="bank_channel_benlaris" value="{{ $bankSettings['bank_channel_benlaris'] }}" required class="w-full px-3 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_benlaris" value="{{ $bankSettings['bank_account_benlaris'] }}" required class="w-full px-3 py-1.5 text-xs font-mono font-bold rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Atas Nama Rekening</label>
                            <input type="text" name="bank_holder_benlaris" value="{{ $bankSettings['bank_holder_benlaris'] }}" required class="w-full px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        </div>
                    </div>
                </div>

                <input type="hidden" name="min_payout_threshold" value="{{ (int)$bankSettings['min_payout_threshold'] }}" />

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('form-bank-settings').classList.add('hidden')" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition">
                        Tutup
                    </button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span>Simpan Perubahan Rekening</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat Transaksi Top-Up Masuk (Inflow) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="border-b border-slate-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-emerald-600"></i>
                    <span>Riwayat Transaksi Top-Up Poin Masuk</span>
                </h3>
                <p class="text-xs text-slate-500">Daftar transaksi pembayaran dan aktivasi poin oleh penerjemah.</p>
            </div>
            <div class="text-xs font-bold text-slate-500">
                Total: <span class="text-slate-900 font-mono font-black">{{ $payinOrders->total() ?? 0 }} Transaksi</span>
            </div>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold">
                            <th class="py-3 px-4">No. Pesanan</th>
                            <th class="py-3 px-4">Nama Penerjemah</th>
                            <th class="py-3 px-4">Nominal IDR</th>
                            <th class="py-3 px-4">Poin Diterbitkan</th>
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
                                <td class="py-3.5 px-4 font-black text-emerald-600 font-mono">
                                    Rp {{ number_format($order->amount_idr, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    +{{ number_format($order->points_issued ?? 0, 0, ',', '.') }} Poin
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 uppercase">
                                        {{ $order->payment_method ?: 'Xenith Pay' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($order->status === 'success')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                            <i data-lucide="check" class="w-3 h-3"></i> Lunas
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Menunggu
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
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
                                <td colspan="7" class="py-10 text-center text-slate-400 font-medium">
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
@endsection
