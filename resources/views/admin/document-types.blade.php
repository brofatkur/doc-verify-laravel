@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Master Data: Tipe Dokumen</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola daftar tipe dokumen resmi yang terdaftar pada sistem DocVerify.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-bold leading-snug">
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-bold leading-snug">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Add Form -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4 h-fit">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>Tambah Tipe Baru</span>
            </h3>
            <form action="/admin/document-types" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label for="name" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Tipe Dokumen</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200 text-sm font-medium text-slate-800"
                        placeholder="Contoh: Akta Lahir"
                    />
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                    Tambah Tipe
                </button>
            </form>
        </div>

        <!-- List Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden md:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Tipe Dokumen</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($types->isEmpty())
                            <tr>
                                <td colspan="2" class="px-6 py-8 text-center text-slate-400 text-xs">
                                    Belum ada tipe dokumen terdaftar.
                                </td>
                            </tr>
                        @else
                            @foreach($types as $type)
                                <tr class="hover:bg-slate-50/40 transition-colors duration-150">
                                    <td class="px-6 py-4 font-semibold text-slate-800 text-sm">
                                        <span id="name-display-{{ $type->id }}">{{ $type->name }}</span>
                                        <form id="edit-form-{{ $type->id }}" action="/admin/document-types/{{ $type->id }}/update" method="POST" class="hidden flex items-center gap-2">
                                            @csrf
                                            <input
                                                type="text"
                                                name="name"
                                                value="{{ $type->name }}"
                                                required
                                                class="px-3 py-1 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                                            />
                                            <button type="submit" class="p-1 text-emerald-600 hover:bg-emerald-50 rounded transition cursor-pointer" title="Simpan">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </button>
                                            <button type="button" onclick="cancelEdit('{{ $type->id }}')" class="p-1 text-rose-600 hover:bg-rose-50 rounded transition cursor-pointer" title="Batal">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                                        <button onclick="enableEdit('{{ $type->id }}')" id="edit-btn-{{ $type->id }}" class="text-slate-500 p-1.5 hover:bg-slate-100 rounded-lg transition cursor-pointer" title="Ubah Nama">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <form action="/admin/document-types/{{ $type->id }}/delete" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe dokumen ini?')">
                                            @csrf
                                            <button type="submit" class="text-rose-600 p-1.5 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Hapus">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function enableEdit(id) {
        document.getElementById('name-display-' + id).classList.add('hidden');
        document.getElementById('edit-btn-' + id).classList.add('hidden');
        document.getElementById('edit-form-' + id).classList.remove('hidden');
    }

    function cancelEdit(id) {
        document.getElementById('name-display-' + id).classList.remove('hidden');
        document.getElementById('edit-btn-' + id).classList.remove('hidden');
        document.getElementById('edit-form-' + id).classList.add('hidden');
    }
</script>
@endsection
