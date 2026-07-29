@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Profil & Layanan</h1>
        <p class="text-slate-500 text-sm mt-1">Lengkapi informasi profil penerjemah tersumpah Anda untuk kebutuhan publik.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="/admin/profile" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-250 text-emerald-800 p-4 rounded-xl text-xs font-bold leading-snug">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-bold leading-snug">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Profile Picture Uploader -->
            <div class="flex flex-col sm:flex-row items-center gap-5 border-b border-slate-100 pb-6">
                <div class="relative w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 shadow-inner overflow-hidden flex-shrink-0">
                    @if($user->profile_picture)
                        <img id="profile-preview" src="{{ $user->profile_picture }}" alt="Profile Photo" class="w-full h-full object-cover" />
                    @else
                        <div id="profile-icon" class="flex items-center justify-center">
                            <i data-lucide="user" class="w-10 h-10 text-slate-400"></i>
                        </div>
                        <img id="profile-preview" src="" alt="Profile Photo" class="w-full h-full object-cover hidden" />
                    @endif
                </div>
                
                <div class="space-y-2 text-center sm:text-left">
                    <label for="profile_picture" class="inline-flex items-center justify-center px-4 py-2 border border-slate-250 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition cursor-pointer shadow-sm">
                        Pilih Foto Profil
                    </label>
                    <input
                        type="file"
                        id="profile_picture"
                        name="profile_picture"
                        accept="image/*"
                        onchange="previewImage(this)"
                        class="hidden"
                    />
                    <p class="text-[10px] text-slate-400">Mendukung format PNG, JPG, atau WEBP (Maksimal 2MB).</p>
                </div>
            </div>

            <!-- Registry Information (Read-only for Security) -->
            <div class="space-y-4 bg-slate-50/60 p-4.5 rounded-2xl border border-slate-200/50">
                <div class="flex items-start gap-2.5 text-xs text-slate-500 mb-2 leading-relaxed">
                    <i data-lucide="info" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                    <span>
                        <strong class="text-slate-800 font-bold">Informasi Akun & Kontak:</strong> Perubahan email atau WhatsApp akan mengirimkan notifikasi ke kontak lama Anda demi keamanan akun. Nomor Anggota IPPTI tidak dapat diubah secara mandiri.
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
                        @if($user->role === 'TRANSLATOR')
                            <input
                                type="text"
                                disabled
                                value="{{ $user->name }}"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-100 text-slate-500 text-sm font-semibold cursor-not-allowed outline-none"
                            />
                            <p class="text-[9px] text-rose-500 mt-1 font-semibold">Nama lengkap resmi hanya dapat diubah oleh administrator IPPTI.</p>
                        @else
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                            />
                        @endif
                    </div>

                    <div>
                        <label for="email" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                        />
                    </div>

                    <div>
                        <label for="whatsapp" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nomor WhatsApp / HP</label>
                        <input
                            type="text"
                            id="whatsapp"
                            name="whatsapp"
                            value="{{ old('whatsapp', $user->whatsapp) }}"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                            placeholder="Contoh: 08123456789"
                        />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Anggota IPPTI / SK (Kunci)</label>
                        <input
                            type="text"
                            disabled
                            value="{{ $user->sk_number }}"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-100 text-slate-500 text-sm font-mono cursor-not-allowed"
                        />
                    </div>
                </div>
            </div>

            <!-- Custom Editable Fields -->
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="globe" class="w-4 h-4 text-emerald-600"></i>
                        <span>Layanan Pasangan Bahasa</span>
                    </label>
                    @if($user->role === 'TRANSLATOR')
                        <input
                            type="text"
                            disabled
                            value="{{ $user->language_services }}"
                            class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-medium text-slate-500 cursor-not-allowed outline-none"
                        />
                        <p class="text-[10px] text-rose-500 mt-1 flex items-center gap-1 font-semibold">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                            <span>Arah bahasa tersertifikasi resmi hanya dapat diubah oleh administrator IPPTI.</span>
                        </p>
                    @else
                        <input
                            type="text"
                            id="language_services"
                            name="language_services"
                            value="{{ old('language_services', $user->language_services) }}"
                            placeholder="Contoh: Inggris - Indonesia, Belanda - Indonesia"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition text-sm font-medium text-slate-800 placeholder-slate-400"
                        />
                    @endif
                </div>

                <div>
                    <label for="bio" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-4 h-4 text-emerald-600"></i>
                        <span>Biografi Singkat / Deskripsi Profil</span>
                    </label>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="4"
                        placeholder="Tuliskan pengalaman penerjemahan Anda, spesialisasi dokumen hukum/teknis, atau informasi kredensial tambahan..."
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition text-sm font-medium text-slate-800 placeholder-slate-400"
                    >{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-6 flex justify-end border-t border-slate-100">
                <button
                    type="submit"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold shadow-sm hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-200 text-sm cursor-pointer"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

    @if(Auth::user()->role === 'SUPERADMIN' || Auth::user()->role === 'ADMIN')
        <!-- iPaymu Payment Gateway Configuration Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl border border-amber-200">
                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Pengaturan Payment Gateway iPaymu</h2>
                        <p class="text-xs text-slate-500">Konfigurasi Virtual Account & API Key iPaymu untuk menerima pembayaran Top-Up Poin penerjemah secara otomatis.</p>
                    </div>
                </div>

                <form action="/admin/settings/ipaymu" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="ipaymu_va" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Virtual Account (VA) iPaymu</label>
                            <input
                                type="text"
                                id="ipaymu_va"
                                name="ipaymu_va"
                                value="{{ old('ipaymu_va', env('IPAYMU_VA', config('services.ipaymu.va'))) }}"
                                placeholder="Contoh: 00000012345678"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono"
                            />
                        </div>

                        <div>
                            <label for="ipaymu_env" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Environment Mode</label>
                            <select
                                id="ipaymu_env"
                                name="ipaymu_env"
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                            >
                                <option value="sandbox" {{ old('ipaymu_env', env('IPAYMU_ENV', config('services.ipaymu.env'))) === 'sandbox' ? 'selected' : '' }}>Sandbox (Pengujian / Testing)</option>
                                <option value="production" {{ old('ipaymu_env', env('IPAYMU_ENV', config('services.ipaymu.env'))) === 'production' ? 'selected' : '' }}>Production (Live / Transaksi Nyata)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="ipaymu_api_key" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">API Key iPaymu</label>
                        <input
                            type="password"
                            id="ipaymu_api_key"
                            name="ipaymu_api_key"
                            value="{{ old('ipaymu_api_key', env('IPAYMU_API_KEY', config('services.ipaymu.api_key'))) }}"
                            placeholder="Masukkan iPaymu Secret API Key"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono"
                        />
                        <p class="text-[10px] text-slate-400 mt-1">Dapatkan Virtual Account & API Key dari dasbor resmi <a href="https://ipaymu.com" target="_blank" class="text-amber-600 underline hover:text-amber-700">iPaymu.com</a> (Menu Integrasi API v2).</p>
                    </div>

                    <div class="pt-3 flex justify-end">
                        <button
                            type="submit"
                            class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold text-xs transition shadow-sm cursor-pointer"
                        >
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Konfigurasi iPaymu</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    function previewImage(input) {
        const file = input.files[0];
        if (file) {
            const preview = document.getElementById('profile-preview');
            const icon = document.getElementById('profile-icon');
            
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            if (icon) {
                icon.classList.add('hidden');
            }
        }
    }
</script>
@endsection
