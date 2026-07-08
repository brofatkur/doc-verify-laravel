@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="/admin" class="p-2 hover:bg-slate-200 rounded-xl transition text-slate-500">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Dokumen</h1>
                <p class="text-slate-500 text-sm mt-1">Ubah rincian metadata dokumen terjemahan resmi Anda.</p>
            </div>
        </div>

        <!-- Archive Button Form -->
        <form action="/admin/documents/{{ $document->id }}/archive" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan dokumen ini? Dokumen yang diarsipkan tidak dapat diubah kembali dan status verifikasinya akan ditandai sebagai Dibatalkan.')">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl border border-rose-200 font-semibold transition cursor-pointer text-sm shadow-sm">
                <i data-lucide="archive" class="w-4 h-4"></i>
                <span>Arsipkan Dokumen</span>
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="/admin/documents/{{ $document->id }}/update" method="POST" class="p-6 space-y-6">
            @csrf
            
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-semibold leading-snug">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID Dokumen (Readonly) -->
                <div class="space-y-2 md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">ID Dokumen (Kriptografis)</label>
                    <input
                        type="text"
                        value="{{ $document->document_id }}"
                        disabled
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-500 cursor-not-allowed outline-none"
                    />
                    <p class="text-xs text-slate-400">ID Dokumen unik diterbitkan sistem dan tidak dapat diubah.</p>
                </div>

                <div class="space-y-2">
                    <label for="registration_number" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nomor Registrasi</label>
                    <input
                        type="text"
                        id="registration_number"
                        name="registration_number"
                        value="{{ old('registration_number', $document->registration_number) }}"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Contoh: REG-001"
                    />
                </div>

                <div class="space-y-2">
                    <label for="document_date" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal Dokumen</label>
                    <input
                        type="date"
                        id="document_date"
                        name="document_date"
                        value="{{ old('document_date', $document->document_date ? $document->document_date->format('Y-m-d') : date('Y-m-d')) }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800"
                    />
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label for="client_name" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama di Dokumen</label>
                    <input
                        type="text"
                        id="client_name"
                        name="client_name"
                        value="{{ old('client_name', $document->client_name) }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Contoh: Zaki Syah Iqbal"
                    />
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label for="document_type" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipe Dokumen</label>
                    <input
                        type="text"
                        id="document_type"
                        name="document_type"
                        list="document-types"
                        value="{{ old('document_type', $document->document_type) }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Pilih atau ketik tipe dokumen baru"
                    />
                    <datalist id="document-types">
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->name }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label for="language_pair" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Pasangan Bahasa</label>
                    <input
                        type="text"
                        id="language_pair"
                        name="language_pair"
                        list="language-directions"
                        value="{{ old('language_pair', $document->language_pair) }}"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800 placeholder-slate-400"
                        placeholder="Pilih atau ketik pasangan bahasa baru"
                    />
                    <datalist id="language-directions">
                        @foreach($languageDirections as $dir)
                            <option value="{{ $dir->name }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="pt-6 flex justify-end gap-3 border-t border-slate-100">
                <a href="/admin" class="px-6 py-2.5 border border-slate-300 rounded-xl font-semibold text-slate-700 hover:bg-slate-50 active:scale-[0.98] transition-all duration-150 text-sm">
                    Batal
                </a>
                <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-200 shadow-sm text-sm cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
