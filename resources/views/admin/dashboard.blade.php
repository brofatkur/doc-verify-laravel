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

        <!-- Document Type Distribution (REV-10) -->
        @if(isset($docTypeStats) && !$docTypeStats->isEmpty())
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-600"></i>
                    <span>Distribusi Tipe Dokumen Resmi</span>
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($docTypeStats as $stat)
                        @php
                            $percentage = $totalDocs > 0 ? round(($stat->count / $totalDocs) * 100, 1) : 0;
                        @endphp
                        <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3.5 space-y-1">
                            <span class="block text-slate-400 text-[10px] font-bold uppercase tracking-wider truncate" title="{{ $stat->document_type }}">{{ $stat->document_type }}</span>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-xl font-black text-slate-900">{{ $stat->count }}</span>
                                <span class="text-xs text-slate-500 font-semibold">({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1 mt-1">
                                <div class="bg-emerald-600 h-1 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif



        <!-- Log Audit Dokumen -->
        <div class="space-y-4 pt-2">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-emerald-600"></i>
                <span>Log Audit Registrasi Dokumen Nasional</span>
            </h2>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <!-- Search Filter Bar (REV-18) -->
                <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
                    <div class="relative flex-1 max-w-md">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input
                            type="text"
                            id="superadmin-document-search"
                            oninput="filterSuperadminDocuments()"
                            placeholder="Cari dokumen nasional (nama pemilik/klien, registrasi, ID, tipe)..."
                            class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition text-sm text-slate-800"
                        />
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="superadmin-table" class="w-full text-left text-sm">
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
                                    <tr class="hover:bg-slate-50/40 transition-colors duration-150" data-search-text="{{ strtolower($doc->document_id . ' ' . $doc->registration_number . ' ' . $doc->client_name . ' ' . $doc->document_type . ' ' . $doc->language_pair . ' ' . ($doc->translator->name ?? '')) }}">
                                        <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                                            {{ $doc->document_id }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-700 font-semibold font-mono text-xs">{{ $doc->registration_number }}</td>
                                        <td class="px-6 py-4 text-slate-800 font-semibold text-xs">
                                            {{ $doc->client_name }}
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
                <div class="flex items-center gap-2 mb-1.5">
                    @if(Auth::user()->isPro())
                        <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300 shadow-xs">
                            <i data-lucide="award" class="w-3.5 h-3.5 text-amber-600"></i> AKUN PRO
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            AKUN REGULER (TRIAL)
                        </span>
                        <a href="/admin/upgrade" class="text-xs font-black text-amber-600 hover:text-amber-700 hover:underline flex items-center gap-1">
                            <i data-lucide="zap" class="w-3.5 h-3.5 fill-amber-500 text-amber-500"></i> Upgrade Pro
                        </a>
                    @endif
                </div>
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

        <!-- Low Balance Polite Reminder Alert (points <= low_point_threshold) -->
        @if(Auth::check() && Auth::user()->points <= (int)\App\Models\Setting::get('low_point_threshold', 20000))
            <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4.5 rounded-2xl text-xs font-semibold flex items-start justify-between gap-4 shadow-xs">
                <div class="flex items-start gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                    <div class="space-y-1">
                        <p class="font-bold text-amber-950 text-sm">Pengingat Saldo Poin Layanan IPPTI</p>
                        <p class="leading-relaxed">
                            Demi kelancaran penggunaan layanan verifikasi dokumen IPPTI, mohon untuk melakukan pengisian ulang (top-up) poin saldo Anda. Saldo poin Anda saat ini tersisa <strong class="font-black text-amber-700 font-mono text-xs">{{ number_format(Auth::user()->points, 0, ',', '.') }} Poin</strong> (Biaya registrasi: 1.000 Poin / dokumen).
                        </p>
                    </div>
                </div>
                @if(Auth::user()->isReguler())
                    <a href="/admin/upgrade" class="flex-shrink-0 flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl text-xs font-extrabold shadow-sm transition">
                        <i data-lucide="zap" class="w-3.5 h-3.5 fill-white"></i>
                        <span>Upgrade Pro</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- Translator Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3.5 bg-amber-50 text-amber-600 rounded-xl">
                        <i data-lucide="coins" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-slate-900">{{ number_format(Auth::user()->points ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-0.5">Saldo Poin Penggunaan</p>
                    </div>
                </div>
                <button
                    onclick="openIpaymuModal()"
                    class="flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                    title="Top-Up Saldo Poin Mandiri via iPaymu"
                >
                    <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                    <span>Top-Up</span>
                </button>
            </div>
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
            <!-- Search Filter Bar (REV-04) -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        id="document-search-input"
                        oninput="filterTranslatorDocuments()"
                        placeholder="Cari berdasarkan nama pemilik/klien, no registrasi, ID, tipe dokumen..."
                        class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition text-sm text-slate-800"
                    />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="translator-table" class="w-full text-left text-sm">
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
                                <tr class="hover:bg-slate-50/40 transition-colors duration-150" data-search-text="{{ strtolower($doc->document_id . ' ' . $doc->registration_number . ' ' . $doc->client_name . ' ' . $doc->document_type . ' ' . $doc->language_pair) }}">
                                    <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                                        {{ $doc->document_id }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 font-semibold font-mono text-xs">{{ $doc->registration_number }}</td>
                                    <td class="px-6 py-4 text-slate-800 font-semibold text-xs">
                                        {{ $doc->client_name }}
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

    <!-- ========================================== -->
    <!--            EXCEL IMPORT PREVIEW MODAL      -->
    <!-- ========================================== -->
    <div id="modal-import-preview" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl p-6 border border-slate-100 max-h-[85vh] flex flex-col justify-between">
            <div class="space-y-4 overflow-y-auto pr-1">
                <div class="flex items-center gap-3">
                    <i data-lucide="eye" class="w-7 h-7 text-blue-650 flex-shrink-0"></i>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Preview Data Impor</h3>
                        <p class="text-xs text-slate-400 font-semibold">Tinjau data dari file Excel sebelum disimpan ke database (Transaksi All-or-Nothing).</p>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 border-b border-slate-200 text-slate-600">
                            <tr>
                                <th class="px-4 py-2 font-bold uppercase tracking-wider">Nama di Dokumen</th>
                                <th class="px-4 py-2 font-bold uppercase tracking-wider">Tipe Dokumen</th>
                                <th class="px-4 py-2 font-bold uppercase tracking-wider">Pasangan Bahasa</th>
                                <th class="px-4 py-2 font-bold uppercase tracking-wider">No. Reg</th>
                            </tr>
                        </thead>
                        <tbody id="import-preview-rows" class="divide-y divide-slate-250">
                            <!-- Rows dynamically populated -->
                        </tbody>
                    </table>
                </div>

                <div id="import-preview-summary" class="text-xs font-semibold text-slate-500 bg-blue-50/50 border border-blue-100 p-3 rounded-xl">
                    Total records found: 0.
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4 flex-shrink-0">
                <button
                    onclick="cancelImportPreview()"
                    class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-lg text-xs font-semibold transition cursor-pointer"
                >
                    Batalkan
                </button>
                <button
                    id="btn-confirm-import"
                    onclick="submitExcelImport()"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-sm"
                >
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Konfirmasi & Impor</span>
                </button>
            </div>
        </div>
    </div>

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
        let parsedImportRows = [];

        async function handleExcelImport(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const sheetName = workbook.SheetNames[0];
                    const sheet = workbook.Sheets[sheetName];
                    const rows = XLSX.utils.sheet_to_json(sheet, { defval: "" });

                    if (rows.length === 0) {
                        alert('Berkas Excel kosong.');
                        input.value = '';
                        return;
                    }

                    // Normalize key helper for JS
                    const normalizeKey = (k) => k.toString().toLowerCase().replace(/[^a-z0-9]/g, '');

                    // Validate columns in JS first (REV-22)
                    const firstRow = rows[0];
                    const headers = Object.keys(firstRow);
                    let hasClientName = false;
                    let hasDocType = false;
                    let hasLangPair = false;

                    headers.forEach(h => {
                        const nk = normalizeKey(h);
                        if (['namaklien', 'namadidokumen', 'clientname', 'klien'].includes(nk)) hasClientName = true;
                        if (['tipedokumen', 'documenttype', 'tipe'].includes(nk)) hasDocType = true;
                        if (['arahbahasa', 'pasanganbahasa', 'languagepair', 'bahasa'].includes(nk)) hasLangPair = true;
                    });

                    if (!hasClientName || !hasDocType || !hasLangPair) {
                        alert('Format kolom Excel tidak valid. Pastikan file memiliki kolom: Nama di Dokumen, Tipe Dokumen, dan Pasangan Bahasa.');
                        input.value = '';
                        return;
                    }

                    parsedImportRows = rows;

                    // Render Preview Rows
                    const previewTbody = document.getElementById('import-preview-rows');
                    previewTbody.innerHTML = '';

                    // Display up to 8 rows for preview
                    const previewLimit = Math.min(rows.length, 8);
                    for (let i = 0; i < previewLimit; i++) {
                        const row = rows[i];
                        const normalizedRow = {};
                        Object.keys(row).forEach(k => {
                            normalizedRow[normalizeKey(k)] = row[k];
                        });

                        const client = normalizedRow['namaklien'] || normalizedRow['namadidokumen'] || normalizedRow['clientname'] || normalizedRow['klien'] || '-';
                        const type = normalizedRow['tipedokumen'] || normalizedRow['documenttype'] || normalizedRow['tipe'] || '-';
                        const pair = normalizedRow['arahbahasa'] || normalizedRow['pasanganbahasa'] || normalizedRow['languagepair'] || normalizedRow['bahasa'] || '-';
                        const reg = normalizedRow['noregister'] || normalizedRow['noregistrasi'] || normalizedRow['nomorregistrasi'] || normalizedRow['registrationnumber'] || '<span class="text-slate-400 italic">Otomatis</span>';

                        const tr = document.createElement('tr');
                        tr.className = 'border-b border-slate-100 hover:bg-slate-100/50';
                        tr.innerHTML = `
                            <td class="px-4 py-2 font-medium text-slate-800">${client}</td>
                            <td class="px-4 py-2 text-slate-650">${type}</td>
                            <td class="px-4 py-2 text-slate-650">${pair}</td>
                            <td class="px-4 py-2 font-mono text-slate-650">${reg}</td>
                        `;
                        previewTbody.appendChild(tr);
                    }

                    if (rows.length > 8) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td colspan="4" class="px-4 py-2 text-center text-slate-400 italic bg-slate-50">+ ${rows.length - 8} baris lainnya...</td>`;
                        previewTbody.appendChild(tr);
                    }

                    document.getElementById('import-preview-summary').innerText = `Total ditemukan ${rows.length} dokumen. Klik "Konfirmasi & Impor" untuk menyimpan data ke database.`;
                    document.getElementById('modal-import-preview').classList.remove('hidden');

                } catch (err) {
                    alert('Gagal memproses berkas Excel: ' + err.message);
                    input.value = '';
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function cancelImportPreview() {
            document.getElementById('modal-import-preview').classList.add('hidden');
            document.getElementById('excel-upload-input').value = '';
            parsedImportRows = [];
        }

        async function submitExcelImport() {
            const btn = document.getElementById('btn-confirm-import');
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
            btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Mengimpor...</span>';

            try {
                const response = await fetch('/admin/documents/import-json', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ rows: parsedImportRows })
                });

                const res = await response.json();
                document.getElementById('modal-import-preview').classList.add('hidden');
                
                if (res.success) {
                    showImportResult(true, res.importedCount, res.skippedCount, res.errors);
                } else {
                    alert('Impor gagal: ' + (res.error || 'Terjadi kesalahan.'));
                    resetConfirmBtn();
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
                resetConfirmBtn();
            }

            function resetConfirmBtn() {
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
                btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i><span>Konfirmasi & Impor</span>';
                lucide.createIcons();
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

        // Table search filters (REV-04, REV-18)
        function filterSuperadminDocuments() {
            const query = document.getElementById('superadmin-document-search').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#superadmin-table tbody tr');
            rows.forEach(row => {
                if (row.cells.length < 2) return;
                const dataSearch = row.getAttribute('data-search-text') || '';
                const text = (dataSearch + ' ' + row.textContent).toLowerCase();
                if (!query || text.includes(query)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        function filterTranslatorDocuments() {
            const query = document.getElementById('document-search-input').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#translator-table tbody tr');
            rows.forEach(row => {
                if (row.cells.length < 2) return;
                const dataSearch = row.getAttribute('data-search-text') || '';
                const text = (dataSearch + ' ' + row.textContent).toLowerCase();
                if (!query || text.includes(query)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        function openIpaymuModal() {
            document.getElementById('modal-ipaymu-topup').classList.remove('hidden');
            document.getElementById('ipaymu-error-msg').classList.add('hidden');
            lucide.createIcons();
        }

        function closeIpaymuModal() {
            document.getElementById('modal-ipaymu-topup').classList.add('hidden');
        }

        function selectIpaymuPackage(amount) {
            document.getElementById('custom-ipaymu-amount').value = amount;
        }

        async function processIpaymuPayment() {
            const amount = document.getElementById('custom-ipaymu-amount').value;
            const btn = document.getElementById('btn-process-ipaymu');
            const errDiv = document.getElementById('ipaymu-error-msg');

            errDiv.classList.add('hidden');
            btn.disabled = true;
            btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> <span>Menyiapkan Tagihan...</span>';

            try {
                const response = await fetch('/payment/xenith/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ amount: amount })
                });

                const data = await response.json();

                if (data.success && data.payment_url) {
                    window.location.href = data.payment_url;
                } else {
                    errDiv.innerText = data.error || 'Gagal memproses pembayaran.';
                    errDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="external-link" class="w-4 h-4"></i> <span>Bayar Sekarang / Pay Now</span>';
                    lucide.createIcons();
                }
            } catch (err) {
                errDiv.innerText = 'Terjadi kesalahan jaringan ke server: ' + err.message;
                errDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="external-link" class="w-4 h-4"></i> <span>Bayar Sekarang / Pay Now</span>';
                lucide.createIcons();
            }
        }
    </script>

    <!-- ========================================== -->
    <!--       TOP-UP SALDO POIN ONLINE MODAL       -->
    <!-- ========================================== -->
    <div id="modal-ipaymu-topup" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2 text-amber-700 font-bold text-sm">
                    <i data-lucide="credit-card" class="w-5 h-5 text-amber-500"></i>
                    <span>Top-Up Saldo Poin Online</span>
                </div>
                <button onclick="closeIpaymuModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="bg-amber-50/70 border border-amber-100 p-3.5 rounded-xl space-y-1 text-xs">
                <p class="text-slate-500">Metode Pembayaran Online Resmi:</p>
                <p class="font-bold text-slate-900 text-sm">QRIS, VA (BCA, Mandiri, BNI, BRI), E-Wallet & CC</p>
                <p class="text-slate-500 mt-1">Saldo Poin Saat Ini: <strong class="font-mono font-bold text-emerald-700">{{ number_format(Auth::user()->points ?? 0, 0, ',', '.') }} Poin</strong></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pilih Paket Top-Up</label>
                <div class="grid grid-cols-2 gap-2.5 mb-3">
                    <button type="button" onclick="selectIpaymuPackage(100000)" class="pkg-btn border-2 border-amber-500 bg-amber-50/50 p-3 rounded-xl text-left transition cursor-pointer">
                        <p class="text-xs text-amber-700 font-bold">Rp 100.000 (Minimal PRO)</p>
                        <p class="text-sm font-black text-slate-900">100.000 Poin</p>
                    </button>
                    <button type="button" onclick="selectIpaymuPackage(200000)" class="pkg-btn border border-slate-200 p-3 rounded-xl hover:border-amber-500 hover:bg-amber-50/40 text-left transition cursor-pointer">
                        <p class="text-xs text-slate-500 font-medium">Rp 200.000</p>
                        <p class="text-sm font-black text-slate-800">200.000 Poin</p>
                    </button>
                    <button type="button" onclick="selectIpaymuPackage(500000)" class="pkg-btn border border-slate-200 p-3 rounded-xl hover:border-amber-500 hover:bg-amber-50/40 text-left transition cursor-pointer">
                        <p class="text-xs text-slate-500 font-medium">Rp 500.000</p>
                        <p class="text-sm font-black text-slate-800">500.000 Poin</p>
                    </button>
                    <button type="button" onclick="selectIpaymuPackage(1000000)" class="pkg-btn border border-slate-200 p-3 rounded-xl hover:border-amber-500 hover:bg-amber-50/40 text-left transition cursor-pointer">
                        <p class="text-xs text-slate-500 font-medium">Rp 1.000.000</p>
                        <p class="text-sm font-black text-slate-800">1.000.000 Poin</p>
                    </button>
                </div>

                <label for="custom-ipaymu-amount" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nominal Pembayaran (Rp)</label>
                <input
                    type="number"
                    id="custom-ipaymu-amount"
                    min="100000"
                    step="10000"
                    value="100000"
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono"
                />
                <p class="text-[10px] text-slate-400 mt-1">1 Rupiah = 1 Poin (Biaya registrasi: 1.000 Poin / dokumen).</p>
            </div>

            <div id="ipaymu-error-msg" class="hidden bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded-xl text-xs font-bold"></div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button
                    type="button"
                    onclick="closeIpaymuModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="button"
                    id="btn-process-ipaymu"
                    onclick="processIpaymuPayment()"
                    class="flex items-center gap-2 px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
                >
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Bayar Sekarang / Pay Now</span>
                </button>
            </div>
        </div>
    </div>
@endif
@endsection
