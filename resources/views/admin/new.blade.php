@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="/admin" class="p-2 hover:bg-slate-200 rounded-xl transition text-slate-500">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Dokumen Baru</h1>
            <p class="text-slate-500 text-sm mt-1">Daftarkan dokumen terjemahan tersumpah baru ke dalam sistem resmi.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="/admin/documents" method="POST" class="p-6 space-y-6">
            @csrf
            
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-semibold leading-snug">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label htmlFor="registration_number" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nomor Registrasi</label>
                    <input
                        type="text"
                        id="registration_number"
                        name="registration_number"
                        value="{{ old('registration_number') }}"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Kosongkan untuk nomor otomatis"
                    />
                </div>

                <div class="space-y-2">
                    <label htmlFor="document_date" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal Dokumen</label>
                    <input
                        type="date"
                        id="document_date"
                        name="document_date"
                        value="{{ old('document_date', date('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800"
                    />
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label htmlFor="client_name" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama di Dokumen</label>
                    <input
                        type="text"
                        id="client_name"
                        name="client_name"
                        value="{{ old('client_name') }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Contoh: Zaki Syah Iqbal"
                    />
                    <p class="text-xs text-slate-400">Nama ini akan disamarkan secara otomatis pada halaman verifikasi publik (contoh: Z*** S*** I***).</p>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label htmlFor="document_type" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipe Dokumen</label>
                    <input
                        type="text"
                        id="document_type"
                        name="document_type"
                        value="{{ old('document_type') }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Contoh: Akta Pendaftaran Keputusan Pengadilan"
                    />
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label htmlFor="language_pair" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Pasangan Bahasa</label>
                    <input
                        type="text"
                        id="language_pair"
                        name="language_pair"
                        value="{{ old('language_pair') }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Contoh: Belanda - Indonesia"
                    />
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <label class="flex items-center gap-3.5 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="is_qr_generated" checked class="sr-only peer" />
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-100/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-350 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors duration-150">Buat Kode QR Verifikasi Secara Instan</span>
                        <span class="block text-xs text-slate-500">Aktifkan opsi ini untuk langsung membuat kode QR setelah dokumen disimpan.</span>
                    </div>
                </label>
            </div>

            <div class="pt-6 flex justify-end gap-3 border-t border-slate-100">
                <a href="/admin" class="px-6 py-2.5 border border-slate-300 rounded-xl font-semibold text-slate-700 hover:bg-slate-50 active:scale-[0.98] transition-all duration-150 text-sm">
                    Batal
                </a>
                <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-200 shadow-sm text-sm cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Dokumen</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
