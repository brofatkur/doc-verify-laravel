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

                <!-- pro_activation_price -->
                <div class="space-y-2 p-4 bg-slate-50/60 rounded-2xl border border-slate-200/60">
                    <label for="pro_activation_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="award" class="w-4 h-4 text-blue-600"></i>
                        <span>Harga Aktivasi Mode Pro (Rp)</span>
                    </label>
                    <input
                        type="number"
                        id="pro_activation_price"
                        name="pro_activation_price"
                        value="{{ old('pro_activation_price', \App\Models\Setting::get('pro_activation_price', 300000)) }}"
                        required
                        min="0"
                        step="10000"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition bg-white"
                    />
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Harga paket upgrade sekali bayar ke akun PRO yang ditagihkan kepada penerjemah di halaman checkout.
                    </p>
                </div>

                <!-- pro_activation_points -->
                <div class="space-y-2 p-4 bg-slate-50/60 rounded-2xl border border-slate-200/60">
                    <label for="pro_activation_points" class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="coins" class="w-4 h-4 text-amber-500"></i>
                        <span>Bonus Poin Aktivasi Pro</span>
                    </label>
                    <input
                        type="number"
                        id="pro_activation_points"
                        name="pro_activation_points"
                        value="{{ old('pro_activation_points', \App\Models\Setting::get('pro_activation_points', 100000)) }}"
                        required
                        min="0"
                        step="1000"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-black font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition bg-white"
                    />
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Total poin saldo yang langsung dikreditkan ke akun penerjemah setelah berhasil melakukan upgrade ke PRO.
                    </p>
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
</div>
@endsection
