@extends('layouts.app')

@section('title', 'DocVerify IPPTI - Riwayat Pembelian & Poin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-4">
        <div class="flex items-center gap-3">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Pembelian & Mutasi Poin</h1>
                <p class="text-slate-500 text-sm mt-0.5">Catatan lengkap mutasi poin saldo, bonus registrasi, dan aktivasi paket Pro Anda.</p>
            </div>
        </div>

        @if(Auth::user()->role === 'TRANSLATOR')
            @if(Auth::user()->isReguler())
                <a href="/admin/upgrade" class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm transition text-xs">
                    <i data-lucide="zap" class="w-4 h-4 fill-white"></i>
                    <span>Upgrade Mode PRO</span>
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                    <i data-lucide="award" class="w-4 h-4 text-amber-600"></i> STATUS AKUN PRO
                </span>
            @endif
        @endif
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <i data-lucide="coins" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Saldo Poin Saat Ini</p>
                <p class="text-2xl font-black text-slate-900 font-mono mt-0.5">{{ number_format(Auth::user()->points ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <i data-lucide="arrow-down-left" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Poin Masuk (Credit)</p>
                <p class="text-2xl font-black text-blue-600 font-mono mt-0.5">+{{ number_format($totalCredit ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i data-lucide="arrow-up-right" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Terpakai (Debit)</p>
                <p class="text-2xl font-black text-rose-600 font-mono mt-0.5">-{{ number_format($totalDebit ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-slate-500"></i>
                Catatan Mutasi Buku Besar (Ledger)
            </h3>
            <span class="text-xs text-slate-500">Total Transaksi: {{ $transactions->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="px-6 py-3">Waktu & Tanggal</th>
                        <th class="px-6 py-3">Jenis Transaksi</th>
                        <th class="px-6 py-3">Deskripsi / Keterangan</th>
                        <th class="px-6 py-3 text-right">Jumlah Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono">
                                {{ $trx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($trx->reference_type === 'pro_activation')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-200 text-[10px]">
                                        <i data-lucide="award" class="w-3 h-3 text-amber-600"></i> Aktivasi Pro
                                    </span>
                                @elseif($trx->reference_type === 'trial_bonus')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px]">
                                        <i data-lucide="gift" class="w-3 h-3 text-emerald-600"></i> Bonus Trial
                                    </span>
                                @elseif($trx->type === 'credit')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-blue-100 text-blue-800 border border-blue-200 text-[10px]">
                                        <i data-lucide="plus-circle" class="w-3 h-3 text-blue-600"></i> Top-Up Poin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-slate-100 text-slate-700 border border-slate-200 text-[10px]">
                                        <i data-lucide="file-check" class="w-3 h-3 text-slate-500"></i> Verifikasi Dokumen
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-700 font-medium">
                                {{ $trx->description }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-sm whitespace-nowrap">
                                @if($trx->type === 'credit')
                                    <span class="text-emerald-600">+{{ number_format($trx->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-rose-600">-{{ number_format($trx->amount, 0, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="font-semibold text-slate-600">Belum ada riwayat transaksi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
