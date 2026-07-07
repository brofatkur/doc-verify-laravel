@extends('layouts.app')

@section('title', 'DocVerify IPPTI - Manajemen Pengguna')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2 text-emerald-700 font-bold text-xs bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full w-max mb-2.5">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span class="tracking-wider uppercase">PORTAL MANAJEMEN USER IPPTI</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola data login, peran, nomor SK Kemenkumham untuk Pengurus Admin dan Penerjemah Tersumpah.</p>
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

    <!-- Control Actions Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="award" class="w-5 h-5 text-emerald-600"></i>
            <span>Daftar Pengguna Sistem ({{ $translators->count() }})</span>
        </h2>
        
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Search Field -->
            <div class="relative flex-1 sm:w-60">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    id="search-translators"
                    placeholder="Cari pengguna..."
                    onkeyup="filterTranslatorsTable()"
                    class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-200"
                />
            </div>

            <!-- Download Template Excel -->
            <a
                href="/template-impor-penerjemah.xlsx"
                class="flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer"
                download
            >
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Unduh Templat Excel</span>
            </a>

            <!-- Import Excel Trigger -->
            <input type="file" id="excel-file-input" accept=".xlsx, .xls" class="hidden" onchange="handleExcelImport(event)" />
            <button
                onclick="document.getElementById('excel-file-input').click()"
                class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100/70 px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer"
            >
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                <span>Impor Excel</span>
            </button>

            <!-- Add Button -->
            <button
                onclick="openUserModal('create')"
                class="flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5 stroke-[3px]"></i>
                <span>Tambah Pengguna</span>
            </button>
        </div>
    </div>

    <!-- Table Users -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="table-translators">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Alamat Email</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Peran</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nomor Anggota</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Dokumen</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($translators as $t)
                        <tr class="hover:bg-slate-50/40 transition-colors duration-150 item-row">
                            <td class="px-6 py-4 font-bold text-slate-900 target-name">{{ $t->name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium target-email">{{ $t->email }}</td>
                            <td class="px-6 py-4">
                                @if($t->role === 'SUPERADMIN')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-100 shadow-sm">
                                        <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                        SUPER ADMIN
                                    </span>
                                @elseif($t->role === 'ADMIN')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                                        <i data-lucide="shield" class="w-3 h-3"></i>
                                        ADMIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100 shadow-sm">
                                        PENERJEMAH
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs target-sk">{{ $t->sk_number ?: '-' }}</td>
                            <td class="px-6 py-4 text-center font-bold text-slate-650">
                                {{ $t->role !== 'TRANSLATOR' ? '-' : $t->documents_count }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        onclick="openUserModal('edit', { id: '{{ $t->id }}', name: '{{ $t->name }}', email: '{{ $t->email }}', role: '{{ $t->role }}', sk_number: '{{ $t->sk_number }}' })"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Profil"
                                    >
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                    @if(Auth::id() !== $t->id && ($t->role === 'TRANSLATOR' || Auth::user()->role === 'SUPERADMIN'))
                                        <form action="/admin/users/{{ $t->id }}/delete" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini?')">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                title="Hapus Pengguna"
                                            >
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!--              CREATE/EDIT USER MODAL        -->
<!-- ========================================== -->
<div id="modal-user" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-5">
            <h3 id="modal-title" class="text-lg font-bold text-slate-800">Tambah Pengguna Baru</h3>
            <button onclick="closeUserModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-user" method="POST" class="space-y-4 text-left">
            @csrf
            <input type="hidden" id="modal-user-id" name="id" />
            
            <div>
                <label for="modal-role" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Peran Pengguna</label>
                <select
                    id="modal-role"
                    name="role"
                    onchange="toggleSkField()"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer"
                >
                    <option value="TRANSLATOR">Penerjemah Tersumpah</option>
                    @if(Auth::user()->role === 'SUPERADMIN')
                        <option value="ADMIN">Pengurus IPPTI (Admin)</option>
                        <option value="SUPERADMIN">Pengurus IPPTI (Super Admin)</option>
                    @endif
                </select>
            </div>

            <div>
                <label for="modal-name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <input
                    id="modal-name"
                    name="name"
                    type="text"
                    required
                    placeholder="Contoh: Muhammad Arifin"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                />
            </div>

            <div>
                <label for="modal-email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Alamat Email</label>
                <input
                    id="modal-email"
                    name="email"
                    type="email"
                    required
                    placeholder="arifin@example.com"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                />
            </div>

            <div id="field-sk">
                <label for="modal-sk" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nomor Anggota</label>
                <input
                    id="modal-sk"
                    name="sk_number"
                    type="text"
                    placeholder="Contoh: 25004"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                />
            </div>

            <div>
                <label id="label-password" for="modal-password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Password Akses</label>
                <input
                    id="modal-password"
                    name="password"
                    type="password"
                    placeholder="Min. 6 karakter"
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                />
            </div>

            <div class="pt-3 flex justify-end gap-3 border-t border-slate-100">
                <button
                    type="button"
                    onclick="closeUserModal()"
                    class="px-5 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-sm transition cursor-pointer"
                >
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!--            EXCEL PREVIEW & IMPORT MODAL    -->
<!-- ========================================== -->
<div id="modal-excel-preview" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl p-6 border border-slate-100 max-h-[90vh] flex flex-col justify-between">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-5 h-5 text-emerald-600"></i>
                <span>Pratinjau Data Impor Penerjemah</span>
            </h3>
            <button onclick="closeExcelModal()" class="p-1 text-slate-400 hover:text-slate-650 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto space-y-4 pr-1 text-left">
            <!-- Warning Banner -->
            <div id="excel-warning-card" class="hidden bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl space-y-2">
                <div class="flex items-center gap-2 font-bold text-xs">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>PERINGATAN: FORMAT FILE TIDAK SESUAI</span>
                </div>
                <p class="text-xs font-semibold leading-relaxed">
                    Kolom pada file Anda tidak sesuai dengan format templat kami. Harap gunakan templat Excel yang disediakan agar proses impor berhasil.
                </p>
            </div>

            <!-- Summary metrics -->
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Baris Ditemukan</p>
                    <p class="text-2xl font-black text-slate-900" id="excel-row-count">0</p>
                </div>
                <div class="border-l border-slate-200 pl-4">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Password Default</p>
                    <p class="text-sm font-black text-emerald-700 font-mono mt-1">penerjemah123</p>
                </div>
            </div>

            <!-- Table Preview -->
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tampilan 5 Baris Pertama:</p>
                <div class="border border-slate-200/80 rounded-xl overflow-hidden bg-slate-50/50">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5 font-bold text-slate-650">No. Anggota</th>
                                <th class="px-4 py-2.5 font-bold text-slate-650">Nama</th>
                                <th class="px-4 py-2.5 font-bold text-slate-650">Email</th>
                                <th class="px-4 py-2.5 font-bold text-slate-650">No SK Kemenkumham</th>
                                <th class="px-4 py-2.5 font-bold text-slate-650">Arah Bahasa</th>
                            </tr>
                        </thead>
                        <tbody id="excel-preview-tbody" class="divide-y divide-slate-200 bg-white">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 font-mono">Diverifikasi lokal oleh XLSX Parser</span>
            <div class="flex gap-3">
                <button
                    onclick="closeExcelModal()"
                    class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    id="excel-confirm-btn"
                    onclick="submitExcelImport()"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer"
                >
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Konfirmasi Impor Data</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!--            EXCEL IMPORT PROGRESS/RESULT MODAL -->
<!-- ========================================== -->
<div id="modal-import-result" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-100">
        <div class="flex items-center gap-3 mb-4">
            <i data-lucide="check-circle" id="import-result-icon" class="w-8 h-8 text-emerald-600 flex-shrink-0"></i>
            <h3 id="import-result-title" class="text-xl font-bold text-slate-800">Impor Selesai</h3>
        </div>

        <div class="space-y-4 text-left">
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div class="text-center">
                    <p class="text-2xl font-black text-emerald-600" id="import-success-count">0</p>
                    <p class="text-xs text-slate-500 font-semibold">Berhasil Diimpor</p>
                </div>
                <div class="text-center border-l border-slate-200">
                    <p class="text-2xl font-black text-amber-500" id="import-skipped-count">0</p>
                    <p class="text-xs text-slate-500 font-semibold">Dilewati / Duplikat</p>
                </div>
            </div>

            <div id="import-details-container" class="space-y-2 hidden">
                <p class="text-sm font-semibold text-slate-700">Detail Hasil:</p>
                <div id="import-details-logs" class="bg-slate-50 border border-slate-100 rounded-xl p-3 max-h-40 overflow-y-auto text-xs font-mono text-slate-650 space-y-1">
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button
                onclick="window.location.reload()"
                class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold transition cursor-pointer"
            >
                Reload Halaman
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    function toggleSkField() {
        const role = document.getElementById('modal-role').value;
        const skField = document.getElementById('field-sk');
        const skInput = document.getElementById('modal-sk');

        if (role === 'SUPERADMIN' || role === 'ADMIN') {
            skField.style.display = 'none';
            skInput.removeAttribute('required');
        } else {
            skField.style.display = 'block';
            skInput.setAttribute('required', 'required');
        }
    }

    function openUserModal(action, data = null) {
        const modal = document.getElementById('modal-user');
        const title = document.getElementById('modal-title');
        const form = document.getElementById('form-user');
        const passLabel = document.getElementById('label-password');
        const passInput = document.getElementById('modal-password');

        // Reset
        document.getElementById('modal-user-id').value = '';
        document.getElementById('modal-name').value = '';
        document.getElementById('modal-email').value = '';
        document.getElementById('modal-sk').value = '';
        document.getElementById('modal-password').value = '';
        document.getElementById('modal-role').value = 'TRANSLATOR';

        if (action === 'create') {
            title.innerText = 'Tambah Pengguna Baru';
            form.setAttribute('action', '/admin/users');
            passLabel.innerText = 'Password Akses';
            passInput.setAttribute('required', 'required');
        } else {
            title.innerText = 'Edit Profil Pengguna';
            form.setAttribute('action', '/admin/users/' + data.id + '/update');
            
            document.getElementById('modal-user-id').value = data.id;
            document.getElementById('modal-name').value = data.name;
            document.getElementById('modal-email').value = data.email;
            document.getElementById('modal-sk').value = data.sk_number || '';
            document.getElementById('modal-role').value = data.role;
            
            passLabel.innerText = 'Ganti Password (Kosongkan jika tidak diubah)';
            passInput.removeAttribute('required');
        }

        toggleSkField();
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeUserModal() {
        document.getElementById('modal-user').classList.add('hidden');
    }

    function filterTranslatorsTable() {
        const query = document.getElementById('search-translators').value.toLowerCase();
        const rows = document.querySelectorAll('#table-translators .item-row');

        rows.forEach(row => {
            const name = row.querySelector('.target-name').innerText.toLowerCase();
            const email = row.querySelector('.target-email').innerText.toLowerCase();
            const sk = row.querySelector('.target-sk').innerText.toLowerCase();

            if (name.includes(query) || email.includes(query) || sk.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Client-side Excel parsing & verification
    let parsedTranslators = [];

    function handleExcelImport(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            try {
                const data = new Uint8Array(evt.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const rawJson = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                if (rawJson.length < 2) {
                    alert("Berkas Excel kosong atau tidak memiliki baris data.");
                    return;
                }

                // Check header columns
                const headers = rawJson[0].map(h => String(h || '').toLowerCase().replace(/[^a-z0-9]/g, ''));
                
                const noAnggotaIdx = headers.findIndex(h => h === 'noanggota' || h === 'no_anggota' || h === 'nomoranggota');
                const nameIdx = headers.findIndex(h => h === 'nama' || h === 'name' || h === 'namapenerjemah');
                const emailIdx = headers.findIndex(h => h === 'email' || h === 'surel');
                const skIdx = headers.findIndex(h => h === 'sk' || h === 'nomorsk' || h === 'nosk');
                const arahBahasaIdx = headers.findIndex(h => h === 'arahbahasa' || h === 'arah_bahasa' || h === 'bahasa');

                const warningCard = document.getElementById('excel-warning-card');
                const confirmBtn = document.getElementById('excel-confirm-btn');

                if (noAnggotaIdx === -1 || nameIdx === -1 || emailIdx === -1) {
                    warningCard.classList.remove('hidden');
                    confirmBtn.setAttribute('disabled', 'disabled');
                    confirmBtn.className = "px-5 py-2 bg-slate-350 text-slate-500 rounded-xl text-xs font-bold cursor-not-allowed flex items-center gap-2";
                } else {
                    warningCard.classList.add('hidden');
                    confirmBtn.removeAttribute('disabled');
                    confirmBtn.className = "px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer";
                }

                parsedTranslators = [];
                const previewTbody = document.getElementById('excel-preview-tbody');
                previewTbody.innerHTML = '';

                let previewRowsCount = 0;

                for (let i = 1; i < rawJson.length; i++) {
                    const row = rawJson[i];
                    if (row.length === 0 || !row[nameIdx]) continue;

                    const rowData = {
                        no_anggota: String(row[noAnggotaIdx] || '').trim(),
                        name: String(row[nameIdx] || '').trim(),
                        email: String(row[emailIdx] || '').trim(),
                        sk: skIdx !== -1 ? String(row[skIdx] || '').trim() : '',
                        arah_bahasa: arahBahasaIdx !== -1 ? String(row[arahBahasaIdx] || '').trim() : '',
                    };

                    parsedTranslators.push(rowData);

                    if (previewRowsCount < 5) {
                        previewTbody.innerHTML += `
                            <tr>
                                <td class="px-4 py-2">${rowData.no_anggota}</td>
                                <td class="px-4 py-2 font-bold">${rowData.name}</td>
                                <td class="px-4 py-2">${rowData.email}</td>
                                <td class="px-4 py-2">${rowData.sk || '-'}</td>
                                <td class="px-4 py-2 font-medium text-slate-700">${rowData.arah_bahasa || '-'}</td>
                            </tr>
                        `;
                        previewRowsCount++;
                    }
                }

                document.getElementById('excel-row-count').innerText = parsedTranslators.length;
                document.getElementById('modal-excel-preview').classList.remove('hidden');
                lucide.createIcons();
            } catch (err) {
                alert("Gagal membaca file Excel: " + err.message);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function closeExcelModal() {
        document.getElementById('modal-excel-preview').classList.add('hidden');
        document.getElementById('excel-file-input').value = '';
    }

    async function submitExcelImport() {
        if (parsedTranslators.length === 0) return;

        if (!confirm("Apakah Anda yakin ingin mengimpor data ini?")) {
            return;
        }

        const previewModal = document.getElementById('modal-excel-preview');
        const resultModal = document.getElementById('modal-import-result');
        const confirmBtn = document.getElementById('excel-confirm-btn');

        confirmBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> <span>Mengimpor...</span>';
        confirmBtn.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch('/admin/users/import-json', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ translators: parsedTranslators })
            });
            const data = await response.json();

            previewModal.classList.add('hidden');
            resultModal.classList.remove('hidden');

            if (data.success) {
                document.getElementById('import-success-count').innerText = data.importedCount;
                document.getElementById('import-skipped-count').innerText = data.skippedCount;

                const detailsContainer = document.getElementById('import-details-container');
                const logsDiv = document.getElementById('import-details-logs');
                logsDiv.innerHTML = '';

                if (data.errors && data.errors.length > 0) {
                    detailsContainer.classList.remove('hidden');
                    data.errors.forEach(err => {
                        logsDiv.innerHTML += `<p class="leading-relaxed text-rose-500">${err}</p>`;
                    });
                } else {
                    detailsContainer.classList.add('hidden');
                }
            } else {
                document.getElementById('import-result-icon').className = 'w-8 h-8 text-rose-600 flex-shrink-0';
                document.getElementById('import-result-title').innerText = 'Impor Gagal';
                document.getElementById('import-success-count').innerText = '0';
                document.getElementById('import-skipped-count').innerText = parsedTranslators.length;
            }
        } catch (err) {
            previewModal.classList.add('hidden');
            resultModal.classList.remove('hidden');
            document.getElementById('import-result-icon').className = 'w-8 h-8 text-rose-600 flex-shrink-0';
            document.getElementById('import-result-title').innerText = 'Kesalahan Sistem';
            document.getElementById('import-details-container').classList.remove('hidden');
            document.getElementById('import-details-logs').innerHTML = `<p class="text-rose-500">Terjadi error koneksi ke server.</p>`;
        }
    }
</script>
@endsection
