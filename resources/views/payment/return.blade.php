@extends('layouts.app')

@section('title', 'Status Pembayaran Xenith Pay')

@section('content')
<div class="max-w-xl mx-auto py-10 space-y-6">
    <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm text-center space-y-6">
        @if($transaction && $transaction->status === 'paid')
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                <i data-lucide="check-circle" class="w-10 h-10"></i>
            </div>
            
            <div class="space-y-2">
                <h1 class="text-2xl font-black text-slate-900">Pembayaran Berhasil!</h1>
                <p class="text-slate-600 text-sm">Top-Up saldo poin verifikasi Anda telah sukses diproses.</p>
                @if(isset($isSimulated) && $isSimulated)
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold mt-2">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i> Mode Simulasi Pembayaran (Testing)
                    </span>
                @endif
            </div>

            <div class="bg-slate-50 rounded-xl p-4 text-left text-xs space-y-2.5 border border-slate-200/80">
                <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                    <span class="text-slate-500 font-medium">Nomor Transaksi</span>
                    <span class="font-mono font-bold text-slate-800">{{ $transaction->transaction_no }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                    <span class="text-slate-500 font-medium">Nominal Pembayaran</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                    <span class="text-slate-500 font-medium">Poin Ditambahkan</span>
                    <span class="font-mono font-black text-emerald-600 text-sm">+{{ number_format($transaction->points, 0, ',', '.') }} Poin</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-500 font-medium">Waktu Selesai</span>
                    <span class="text-slate-700 font-semibold">{{ is_object($transaction->updated_at) ? $transaction->updated_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }} WIB</span>
                </div>
            </div>
        @elseif($transaction && $transaction->status === 'pending')
            <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                <i data-lucide="clock" class="w-10 h-10"></i>
            </div>
            
            <div class="space-y-2">
                <h1 class="text-2xl font-black text-slate-900">Menunggu Pembayaran</h1>
                <p class="text-slate-600 text-sm">Transaksi Anda sedang menunggu konfirmasi pembayaran dari Xenith Pay.</p>
            </div>

            <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-4 text-left text-xs space-y-2">
                <p class="font-bold text-amber-900">Nomor Transaksi: <span class="font-mono">{{ $transaction->transaction_no }}</span></p>
                <p class="text-amber-800">Silakan selesaikan pembayaran sesuai petunjuk pada Xenith Pay. Poin akan bertambah secara otomatis begitu pembayaran diterima.</p>
            </div>
        @else
            <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                <i data-lucide="x-circle" class="w-10 h-10"></i>
            </div>
            
            <div class="space-y-2">
                <h1 class="text-2xl font-black text-slate-900">Status Pembayaran Belum Selesai</h1>
                <p class="text-slate-600 text-sm">Pembayaran belum terkonfirmasi atau transaksi telah dibatalkan.</p>
            </div>
        @endif

        <div class="pt-4 border-t border-slate-100">
            <a href="/admin" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-sm transition shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
@endsection
