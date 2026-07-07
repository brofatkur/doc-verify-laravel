@extends('layouts.app')

@section('content')
<div class="space-y-8">
    @if($isSuperAdmin)
        <!-- ========================================== -->
        <!--             SUPER ADMIN DASHBOARD          -->
        <!-- ========================================== -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
            <div>
                <div class="flex items-center gap-2 text-emerald-700 font-bold text-xs bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full w-max mb-2.5">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                    <span class="tracking-wider uppercase">PORTAL AUDIT NASIONAL IPPTI</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Ikhtisar Registrasi Nasional</h1>
                <p class="text-slate-500 text-sm mt-1">Sistem audit terpadu untuk registrasi penerjemah tersumpah dan dokumen resmi se-Indonesia.</p>
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

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalTranslators }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-0.5">Penerjemah Terdaftar</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3.5 bg-blue-50 text-blue-600 rounded-xl">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalDocs }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-0.5">Total Dokumen Terdaftar</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3.5 bg-amber-50 text-amber-500 rounded-xl">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalQrCodes }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-0.5">Verifikasi QR Aktif</p>
                </div>
            </div>
        </div>



        <!-- Log Audit Dokumen -->
        <div class="space-y-4 pt-2">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-emerald-600"></i>
                <span>Log Audit Registrasi Dokumen Nasional</span>
            </h2>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">ID Dokumen</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nomor Registrasi</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama di Dokumen</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tipe Dokumen</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Arah Bahasa</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Penerjemah</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi QR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if($documents->isEmpty())
                                <tr>
                                    <td colSpan={7} className="px-6 py-12 text-center text-slate-400">
                                        Belum ada dokumen terjemahan yang diunggah ke sistem.
                                    </td>
                                </tr>
                            @else
                                @foreach($documents as $doc)
                                    <tr class="hover:bg-slate-50/40 transition-colors duration-150">
                                        <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                                            {{ $doc->document_id }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-700 font-semibold font-mono text-xs">{{ $doc->registration_number }}</td>
                                        <td class="px-6 py-4 text-slate-600 font-mono text-xs">
                                            @php
                                                $words = explode(' ', $doc->client_name);
                                                $masked = array_map(function($w) {
                                                    return strlen($w) <= 1 ? $w : $w[0] . str_repeat('*', strlen($w) - 1);
                                                }, $words);
                                                echo implode(' ', $masked);
                                            @endphp
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 truncate max-w-[150px] font-medium" title="{{ $doc->document_type }}">{{ $doc->document_type }}</td>
                                        <td class="px-6 py-4">
                                            <span class="text-slate-700 font-semibold bg-slate-100 border border-slate-200/50 px-2 py-0.5 rounded text-xs">
                                                {{ $doc->language_pair }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-800 text-xs">{{ $doc->translator->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-medium font-mono mt-0.5">No. Anggota: {{ $doc->translator->sk_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @include('admin.partials.qr_actions', ['doc' => $doc])
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        <!-- ========================================== -->
        <!--            TRANSLATOR DASHBOARD            -->
        <!-- ========================================== -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Dokumen</h1>
                <p class="text-slate-500 text-sm mt-1">Daftarkan dokumen terjemahan tersumpah Anda dan kelola kode QR verifikasi publik.</p>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Excel Import Button -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                    <div>
                        <input
                            type="file"
                            id="excel-upload-input"
                            onchange="handleExcelImport(this)"
                            accept=".xlsx,.xls,.csv"
                            class="hidden"
                        />
                        <label
                            for="excel-upload-input"
                            id="btn-import-excel"
                            class="flex items-center gap-2 border border-slate-200 hover:border-emerald-500 hover:text-emerald-700 bg-white text-gray-700 px-4 py-2.5 rounded-xl font-semibold transition cursor-pointer text-sm shadow-sm"
                        >
                            <i data-lucide="upload" class="w-4 h-4 text-gray-400"></i>
                            <span>Impor Excel</span>
                        </label>
                    </div>
                    <a
                        href="/template-impor-dokumen.csv"
                        download
                        class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline flex items-center gap-1 px-1 py-1"
                        title="Unduh template Excel/CSV yang benar"
                    >
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh Template</span>
                    </a>
                </div>

                <a
                    href="/admin/documents/new"
                    class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-sm hover:-translate-y-0.5 transition-all duration-200 text-sm cursor-pointer"
                >
                    <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
                    <span>Dokumen Baru</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-bold leading-snug">
                {{ session('success') }}
            </div>
        @endif

        <!-- Translator Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalDocs }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-0.5">Total Dokumen Terdaftar</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="p-3.5 bg-blue-50 text-blue-600 rounded-xl">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-3xl font-black text-slate-900">{{ $totalQrCodes }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-0.5">QR Verifikasi Terbit</p>
                </div>
            </div>
        </div>

        <!-- Document List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">ID Dokumen</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nomor Registrasi</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama di Dokumen</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tipe Dokumen</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Arah Bahasa</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Input</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($documents->isEmpty())
                            <tr>
                                <td colSpan={7} className="px-6 py-16 text-center text-slate-400">
                                    Dokumen belum terdaftar. Silakan tambah dokumen baru secara manual atau impor dari berkas Excel untuk memulai.
                                </td>
                            </tr>
                        @else
                            @foreach($documents as $doc)
                                <tr class="hover:bg-slate-50/40 transition-colors duration-150">
                                    <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                                        {{ $doc->document_id }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 font-semibold font-mono text-xs">{{ $doc->registration_number }}</td>
                                    <td class="px-6 py-4 text-slate-600 font-mono text-xs">
                                        @php
                                            $words = explode(' ', $doc->client_name);
                                            $masked = array_map(function($w) {
                                                return strlen($w) <= 1 ? $w : $w[0] . str_repeat('*', strlen($w) - 1);
                                            }, $words);
                                            echo implode(' ', $masked);
                                        @endphp
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 truncate max-w-[200px] font-medium" title="{{ $doc->document_type }}">{{ $doc->document_type }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-slate-700 font-semibold bg-slate-100 border border-slate-200/50 px-2 py-0.5 rounded text-xs">
                                            {{ $doc->language_pair }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 font-medium">
                                        {{ $doc->document_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @include('admin.partials.qr_actions', ['doc' => $doc])
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- ========================================== -->
<!--            EXCEL IMPORT PROGRESS MODAL     -->
<!-- ========================================== -->
@if(!$isSuperAdmin)
    <!-- xlsx.js library CDN to parse Excel locally in browser -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <div id="modal-import-result" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl p-6 border border-slate-100 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center gap-3 mb-4">
                <i data-lucide="check-circle" id="import-result-icon" class="w-8 h-8 flex-shrink-0"></i>
                <h3 id="import-result-title" class="text-xl font-bold text-slate-800">Impor Selesai</h3>
            </div>

            <div id="import-result-body" class="space-y-4">
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
                    <p class="text-sm font-semibold text-slate-700">Detail Impor:</p>
                    <div id="import-details-logs" class="bg-slate-50 border border-slate-100 rounded-xl p-3 max-h-40 overflow-y-auto text-xs font-mono text-slate-600 space-y-1">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    onclick="closeImportModal()"
                    class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold transition cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        async function handleExcelImport(input) {
            const file = input.files[0];
            if (!file) return;

            const btn = document.getElementById('btn-import-excel');
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
            btn.innerHTML = '<div class="w-4 h-4 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></div><span>Mengimpor...</span>';

            const reader = new FileReader();
            reader.onload = async (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const sheetName = workbook.SheetNames[0];
                    const sheet = workbook.Sheets[sheetName];
                    const rows = XLSX.utils.sheet_to_json(sheet, { defval: "" });

                    if (rows.length === 0) {
                        alert('Berkas Excel kosong.');
                        resetBtn();
                        return;
                    }

                    // POST parsed rows directly to Laravel API via AJAX
                    const response = await fetch('/admin/documents/import-json', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ rows: rows })
                    });

                    const res = await response.json();
                    
                    if (res.success) {
                        showImportResult(true, res.importedCount, res.skippedCount, res.errors);
                    } else {
                        alert('Impor gagal: ' + (res.error || 'Terjadi kesalahan.'));
                        resetBtn();
                    }

                } catch (err) {
                    alert('Gagal memproses berkas Excel: ' + err.message);
                    resetBtn();
                }
            };
            reader.readAsArrayBuffer(file);

            function resetBtn() {
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
                btn.innerHTML = '<i data-lucide="upload" class="w-4 h-4 text-gray-400"></i><span>Impor Excel</span>';
                lucide.createIcons();
                input.value = '';
            }
        }

        function showImportResult(success, imported, skipped, errors) {
            const modal = document.getElementById('modal-import-result');
            const icon = document.getElementById('import-result-icon');
            const title = document.getElementById('import-result-title');
            
            const successCount = document.getElementById('import-success-count');
            const skippedCount = document.getElementById('import-skipped-count');
            const detailsContainer = document.getElementById('import-details-container');
            const detailsLogs = document.getElementById('import-details-logs');

            successCount.innerText = imported;
            skippedCount.innerText = skipped;

            if (success) {
                icon.className = "w-8 h-8 text-emerald-500 flex-shrink-0";
                icon.setAttribute('data-lucide', 'check-circle');
                title.innerText = "Impor Selesai";
            } else {
                icon.className = "w-8 h-8 text-rose-500 flex-shrink-0";
                icon.setAttribute('data-lucide', 'alert-triangle');
                title.innerText = "Impor Gagal";
            }

            if (errors && errors.length > 0) {
                detailsLogs.innerHTML = errors.map(err => `<div class="pb-1 border-b border-slate-100/50 last:border-0">${err}</div>`).join('');
                detailsContainer.classList.remove('hidden');
            } else {
                detailsContainer.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            lucide.createIcons();
        }

        function closeImportModal() {
            document.getElementById('modal-import-result').classList.add('hidden');
            window.location.reload();
        }
    </script>
@endif
@endsection
