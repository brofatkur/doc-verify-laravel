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

    @php
        $pendingApplicants = $translators->where('role', 'TRANSLATOR')->filter(fn($u) => !$u->isVerifiedTranslator());
    @endphp

    @if($pendingApplicants->isNotEmpty())
        <!-- Antrean Verifikasi Pendaftar Anggota Baru IPPTI -->
        <div class="bg-amber-50/70 border-2 border-amber-300 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-amber-200 pb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-xs">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">
                            Antrean Verifikasi Pendaftar Anggota Baru IPPTI ({{ $pendingApplicants->count() }} Pendaftar)
                        </h2>
                        <p class="text-xs text-slate-600">
                            Pendaftar di bawah ini sudah memiliki akun login, namun <strong>belum dapat menginput dokumen terjemahan</strong> sebelum diverifikasi oleh Admin.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                @foreach($pendingApplicants as $applicant)
                    <div class="bg-white border border-amber-200 rounded-xl p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-xs">
                        <div class="flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-600 overflow-hidden flex-shrink-0">
                                @if($applicant->profile_picture)
                                    <img src="{{ $applicant->profile_picture }}" alt="{{ $applicant->name }}" class="w-full h-full object-cover" />
                                @else
                                    {{ substr($applicant->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-extrabold text-sm text-slate-900">{{ $applicant->name }}</span>
                                    @if(strtolower($applicant->verification_status ?? 'pending') === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200 uppercase">Ditolak / Menunggu Revisi</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300 uppercase">Menunggu Verifikasi</span>
                                    @endif
                                    <span class="font-mono text-[11px] font-bold text-slate-500">No. Reg/Anggota: {{ $applicant->sk_number }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-slate-600">
                                    <div><strong>Email:</strong> {{ $applicant->email }}</div>
                                    <div><strong>WhatsApp:</strong> {{ $applicant->whatsapp ?: '-' }}</div>
                                    <div><strong>No. SK Kemenkum RI:</strong> <span class="font-mono font-bold text-slate-900">{{ $applicant->effective_no_sk ?: '-' }}</span></div>
                                    <div><strong>Tanggal SK:</strong> {{ $applicant->effective_tgl_sk ?: '-' }}</div>
                                    <div class="sm:col-span-2"><strong>Arah Bahasa:</strong> <span class="text-emerald-700 font-semibold">{{ $applicant->language_services ?: '-' }}</span></div>
                                    @if($applicant->nik || $applicant->city || $applicant->address)
                                        <div class="sm:col-span-2 text-[11px] text-slate-500">
                                            @if($applicant->nik) <strong>NIK:</strong> {{ $applicant->nik }} &bull; @endif
                                            @if($applicant->city) <strong>Kota:</strong> {{ $applicant->city }} &bull; @endif
                                            @if($applicant->address) <strong>Alamat:</strong> {{ $applicant->address }} @endif
                                        </div>
                                    @endif
                                </div>

                                @if($applicant->sk_document_path || $applicant->profile_picture)
                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                        @if($applicant->sk_document_path)
                                            <a href="{{ $applicant->sk_document_path }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-[11px] font-bold transition">
                                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                                <span>Lihat Berkas SK Kemenkum</span>
                                            </a>
                                        @endif
                                        @if($applicant->profile_picture)
                                            <a href="{{ $applicant->profile_picture }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-[11px] font-bold transition">
                                                <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                                <span>Lihat Pasfoto</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 lg:flex-shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            <button
                                type="button"
                                onclick='openVerifyMemberModal(@json([
                                    "id" => $applicant->id,
                                    "name" => $applicant->name,
                                    "email" => $applicant->email,
                                    "sk_number" => $applicant->sk_number,
                                    "no_sk_kemenkum" => $applicant->effective_no_sk !== $applicant->sk_number ? $applicant->effective_no_sk : ($applicant->no_sk_kemenkum ?? ""),
                                    "tgl_sk" => $applicant->effective_tgl_sk,
                                    "language_services" => $applicant->language_services,
                                ]))'
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-xs transition cursor-pointer"
                            >
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                <span>Verifikasi & Aktifkan</span>
                            </button>
                            <button
                                type="button"
                                onclick='openRejectMemberModal(@json([
                                    "id" => $applicant->id,
                                    "name" => $applicant->name,
                                    "rejection_reason" => $applicant->rejection_reason ?? "",
                                ]))'
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer"
                            >
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                <span>Tolak / Revisi</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
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
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Alamat Email</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Peran & Status</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">No. Anggota & SK Kemenkum</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Masa Keanggotaan (Tgl Daftar & Expired)</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Saldo Poin</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Dokumen</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($translators as $t)
                        @php
                            $regDate = $t->effective_registered_at;
                            $actDate = $t->effective_activated_at;
                            $expDate = $t->effective_expired_at;
                            $daysLeft = $t->daysUntilExpiry();
                            $isExpired = $t->isMembershipExpired();
                            $isExpiringSoon = $t->isExpiringSoon();
                        @endphp
                        <tr class="hover:bg-slate-50/40 transition-colors duration-150 item-row">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900 target-name">{{ $t->name }}</p>
                                @if($t->role === 'TRANSLATOR' && $t->language_services)
                                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $t->language_services }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 font-medium target-email text-xs">{{ $t->email }}</td>
                            <td class="px-5 py-4">
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
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100 shadow-sm">
                                            PENERJEMAH
                                        </span>
                                        @if(!$t->isVerifiedTranslator())
                                            @if(strtolower($t->verification_status ?? 'pending') === 'rejected')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <i data-lucide="x-circle" class="w-2.5 h-2.5"></i> Perlu Revisi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <i data-lucide="clock" class="w-2.5 h-2.5"></i> Belum Diverifikasi
                                                </span>
                                            @endif
                                        @elseif($isExpired)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-rose-100 text-rose-700 border border-rose-300">
                                                <i data-lucide="alert-circle" class="w-2.5 h-2.5"></i> EXPIRED
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i data-lucide="check-circle-2" class="w-2.5 h-2.5"></i> Terverifikasi & Aktif
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 font-mono text-xs target-sk">
                                <span class="font-bold text-slate-800">{{ $t->sk_number ?: '-' }}</span>
                                @if($t->role === 'TRANSLATOR')
                                    <p class="text-[10px] text-slate-500 mt-0.5">
                                        SK: {{ $t->effective_no_sk ?: '-' }}{{ $t->effective_tgl_sk ? ' (' . $t->effective_tgl_sk . ')' : '' }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs">
                                @if($t->role === 'TRANSLATOR')
                                    <div class="space-y-0.5 font-mono text-[11px]">
                                        <div class="text-slate-500">
                                            Daftar: <strong class="text-slate-800">{{ $regDate ? $regDate->format('d/m/Y') : '-' }}</strong>
                                        </div>
                                        @if($t->isVerifiedTranslator())
                                            <div class="text-slate-500">
                                                Aktivasi: <strong class="text-slate-800">{{ $actDate ? $actDate->format('d/m/Y') : '-' }}</strong>
                                            </div>
                                            <div class="{{ $isExpired ? 'text-rose-600 font-black' : ($daysLeft !== null && $daysLeft <= 30 ? 'text-amber-600 font-bold' : 'text-emerald-700 font-bold') }}">
                                                Expired: {{ $expDate ? $expDate->format('d/m/Y') : '-' }}
                                                @if($isExpired)
                                                    <span class="ml-1 px-1.5 py-0.2 rounded bg-rose-100 text-rose-700 text-[9px] font-black uppercase">Expired</span>
                                                @elseif($daysLeft !== null && $daysLeft <= 30)
                                                    <span class="ml-1 px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-bold">{{ $daysLeft }} hr lagi</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-amber-600 font-semibold text-[10px] italic">
                                                Menunggu Aktivasi Admin
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 font-mono text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($t->role === 'TRANSLATOR')
                                    <span class="inline-flex items-center gap-1 font-mono font-bold text-xs {{ ($t->points ?? 0) <= 10000 ? 'text-amber-600' : 'text-emerald-700' }} bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-full">
                                        <i data-lucide="coins" class="w-3 h-3 text-amber-500"></i>
                                        {{ number_format($t->points ?? 0, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-slate-650">
                                {{ $t->role !== 'TRANSLATOR' ? '-' : $t->documents_count }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($t->role === 'TRANSLATOR' && !$t->isVerifiedTranslator())
                                        <button
                                            onclick='openVerifyMemberModal(@json([
                                                "id" => $t->id,
                                                "name" => $t->name,
                                                "email" => $t->email,
                                                "sk_number" => $t->sk_number,
                                                "no_sk_kemenkum" => $t->effective_no_sk !== $t->sk_number ? $t->effective_no_sk : ($t->no_sk_kemenkum ?? ""),
                                                "tgl_sk" => $t->effective_tgl_sk,
                                                "language_services" => $t->language_services,
                                                "registered_at" => $regDate ? $regDate->format("Y-m-d") : date("Y-m-d"),
                                            ]))'
                                            class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-extrabold transition cursor-pointer flex items-center gap-1 shadow-xs"
                                            title="Verifikasi & Aktifkan Akun Penerjemah"
                                        >
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                            <span>Verifikasi</span>
                                        </button>
                                    @elseif($t->role === 'TRANSLATOR')
                                        <button
                                            onclick='openRenewMemberModal(@json([
                                                "id" => $t->id,
                                                "name" => $t->name,
                                                "sk_number" => $t->sk_number,
                                                "activated_at" => $actDate ? $actDate->format("d/m/Y") : "-",
                                                "expired_at_display" => $expDate ? $expDate->format("d/m/Y") : "-",
                                                "next_expired_at" => ($expDate && $expDate->isFuture() ? $expDate->copy()->addYear() : now()->addYear())->format("Y-m-d"),
                                                "is_expired" => $isExpired,
                                            ]))'
                                            class="px-2.5 py-1.5 {{ $isExpired ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }} rounded-lg text-[10px] font-extrabold transition cursor-pointer flex items-center gap-1"
                                            title="Perpanjang Masa Aktif Keanggotaan Tahunan (+1 Tahun)"
                                        >
                                            <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i>
                                            <span>Perpanjang</span>
                                        </button>
                                        <form action="/admin/users/{{ $t->id }}/send-reminder" method="POST" class="inline" onsubmit="return confirm('Kirim pengingat perpanjangan keanggotaan via Email ({{ $t->email }}) & WhatsApp Kirimdev.com ({{ $t->whatsapp ?: '-' }}) ke {{ $t->name }}?')">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="p-2 {{ ($isExpired || $isExpiringSoon) ? 'text-amber-600 bg-amber-50 hover:bg-amber-100 border border-amber-200' : 'text-slate-500 hover:bg-slate-100' }} rounded-lg transition-colors cursor-pointer"
                                                title="Kirim Reminder Perpanjangan via Email & WhatsApp (Kirimdev.com)"
                                            >
                                                <i data-lucide="bell-ring" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button
                                        onclick="openTopupModal({ id: '{{ $t->id }}', name: @js($t->name), points: {{ $t->points ?? 0 }} })"
                                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer"
                                        title="Top-Up Saldo Poin"
                                    >
                                        <i data-lucide="coins" class="w-4 h-4"></i>
                                    </button>
                                    <button
                                        onclick='openUserModal("edit", @json([
                                            "id" => $t->id,
                                            "name" => $t->name,
                                            "email" => $t->email,
                                            "whatsapp" => $t->whatsapp,
                                            "role" => $t->role,
                                            "sk_number" => $t->sk_number,
                                            "no_sk_kemenkum" => $t->effective_no_sk !== $t->sk_number ? $t->effective_no_sk : ($t->no_sk_kemenkum ?? ""),
                                            "tgl_sk" => $t->effective_tgl_sk,
                                            "language_services" => $t->language_services,
                                            "masa_aktif" => $t->masa_aktif ?: "Seumur Hidup",
                                            "sk_lengkap" => $t->effective_sk_lengkap,
                                            "verification_status" => $t->isVerifiedTranslator() ? "approved" : ($t->verification_status ?: "pending"),
                                            "registered_at" => $regDate ? $regDate->format("Y-m-d") : date("Y-m-d"),
                                            "activated_at" => $actDate ? $actDate->format("Y-m-d") : "",
                                            "expired_at" => $expDate ? $expDate->format("Y-m-d") : "",
                                        ]))'
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                        title="Edit Profil & Masa Aktif Member"
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
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl p-6 border border-slate-100 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-5">
            <h3 id="modal-title" class="text-lg font-bold text-slate-800">Tambah Pengguna Baru</h3>
            <button onclick="closeUserModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-user" method="POST" class="space-y-4 text-left">
            @csrf
            <input type="hidden" id="modal-user-id" name="id" />
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="modal-role" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Peran Pengguna</label>
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
                    <label for="modal-name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
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
                    <label for="modal-email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Alamat Email</label>
                    <input
                        id="modal-email"
                        name="email"
                        type="email"
                        required
                        placeholder="arifin@example.com"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                    />
                </div>

                <div>
                    <label for="modal-whatsapp" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nomor WhatsApp / HP</label>
                    <input
                        id="modal-whatsapp"
                        name="whatsapp"
                        type="text"
                        placeholder="Contoh: 08123456789"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                    />
                </div>
            </div>

            <div id="field-sk" class="space-y-4 pt-2 border-t border-slate-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="modal-sk" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nomor Anggota IPPTI</label>
                        <input
                            id="modal-sk"
                            name="sk_number"
                            type="text"
                            placeholder="Contoh: 25004"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        />
                    </div>
                    <div>
                        <label for="modal-masa-aktif" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Masa Aktif Registrasi</label>
                        <input
                            id="modal-masa-aktif"
                            name="masa_aktif"
                            type="text"
                            placeholder="Contoh: Seumur Hidup"
                            value="Seumur Hidup"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        />
                    </div>
                    <div>
                        <label for="modal-no-sk-kemenkum" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nomor SK Menteri Hukum RI</label>
                        <input
                            id="modal-no-sk-kemenkum"
                            name="no_sk_kemenkum"
                            type="text"
                            oninput="autoSyncSkLengkapModal()"
                            placeholder="Contoh: AHU-75.AH.03.07.2022"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        />
                    </div>
                    <div>
                        <label for="modal-tgl-sk" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal SK Menteri Hukum RI</label>
                        <input
                            id="modal-tgl-sk"
                            name="tgl_sk"
                            type="text"
                            oninput="autoSyncSkLengkapModal()"
                            placeholder="Contoh: 5 Oktober 2022"
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                        />
                    </div>
                </div>

                <div>
                    <label for="modal-language-services" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Layanan Pasangan / Arah Bahasa</label>
                    <input
                        id="modal-language-services"
                        name="language_services"
                        type="text"
                        placeholder="Contoh: Indonesia - Inggris, Inggris - Indonesia"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                    />
                </div>

                <div>
                    <label for="modal-sk-lengkap" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan SK Lengkap (Otomatis Sinkron)</label>
                    <input
                        id="modal-sk-lengkap"
                        name="sk_lengkap"
                        type="text"
                        placeholder="Otomatis terisi dari Nomor SK & Tanggal SK"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-850 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all bg-slate-50"
                    />
                    <p class="text-[10px] text-slate-400 mt-1">Tampil pada Kartu Verifikasi Penerjemah & tersinkronisasi dengan E-Certificate Hasil Verifikasi Dokumen.</p>
                </div>

                <div>
                    <label for="modal-verification-status" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status Verifikasi Keanggotaan IPPTI</label>
                    <select
                        id="modal-verification-status"
                        name="verification_status"
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer"
                    >
                        <option value="approved">Terverifikasi & Aktif (Dapat Input Dokumen)</option>
                        <option value="pending">Menunggu Verifikasi (Fitur Input Dokumen Terkunci)</option>
                        <option value="rejected">Ditolak / Perlu Revisi</option>
                    </select>
                </div>

                <!-- Tanggal Daftar, Tanggal Aktivasi, & Tanggal Expired Tahunan -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-emerald-50/50 border border-emerald-200/70 p-3.5 rounded-xl">
                    <div>
                        <label for="modal-registered-at" class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Tanggal Daftar</label>
                        <input
                            id="modal-registered-at"
                            name="registered_at"
                            type="date"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold font-mono text-slate-800 bg-white focus:outline-none focus:border-emerald-500"
                        />
                    </div>
                    <div>
                        <label for="modal-activated-at" class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Tanggal Aktivasi</label>
                        <input
                            id="modal-activated-at"
                            name="activated_at"
                            type="date"
                            onchange="autoCalcExpiredFromActivation('modal-activated-at', 'modal-expired-at')"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-bold font-mono text-slate-800 bg-white focus:outline-none focus:border-emerald-500"
                        />
                    </div>
                    <div>
                        <label for="modal-expired-at" class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wider mb-1">Tanggal Expired (1 Thn)</label>
                        <input
                            id="modal-expired-at"
                            name="expired_at"
                            type="date"
                            class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-xs font-bold font-mono text-emerald-900 bg-white focus:outline-none focus:border-emerald-600"
                        />
                    </div>
                    <p class="sm:col-span-3 text-[10px] text-slate-500">
                        Setiap member memiliki Tanggal Expired berbeda sesuai Tanggal Aktivasi (+{{ (int)\App\Models\Setting::get('member_duration_years', 1) }} Tahun).
                    </p>
                </div>
            </div>

            <div>
                <label id="label-password" for="modal-password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Password Akses</label>
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
                    Simpan & Sinkronkan
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
                    <table class="w-full text-left text-[10px]">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2 font-bold text-slate-650">No. Anggota</th>
                                <th class="px-3 py-2 font-bold text-slate-650">Nama</th>
                                <th class="px-3 py-2 font-bold text-slate-650">No SK Kemenkum</th>
                                <th class="px-3 py-2 font-bold text-slate-650">Tanggal SK</th>
                                <th class="px-3 py-2 font-bold text-slate-650">Arah Bahasa</th>
                                <th class="px-3 py-2 font-bold text-slate-650">Masa Aktif</th>
                                <th class="px-3 py-2 font-bold text-slate-650">SK Lengkap</th>
                            </tr>
                        </thead>
                        <tbody id="excel-preview-tbody" class="divide-y divide-slate-100 bg-white">
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

    function autoSyncSkLengkapModal() {
        const noSk = (document.getElementById('modal-no-sk-kemenkum')?.value || '').trim();
        const tglSk = (document.getElementById('modal-tgl-sk')?.value || '').trim();
        const skLengkapInput = document.getElementById('modal-sk-lengkap');
        if (skLengkapInput && noSk) {
            skLengkapInput.value = noSk + (tglSk ? ' tanggal ' + tglSk : '');
        }
    }

    const defaultDurationYears = {{ max(1, (int)\App\Models\Setting::get('member_duration_years', 1)) }};

    function autoCalcExpiredFromActivation(actInputId, expInputId) {
        const actVal = document.getElementById(actInputId)?.value;
        const expEl = document.getElementById(expInputId);
        if (actVal && expEl) {
            const d = new Date(actVal);
            if (!isNaN(d.getTime())) {
                d.setFullYear(d.getFullYear() + defaultDurationYears);
                expEl.value = d.toISOString().split('T')[0];
            }
        }
    }

    function openUserModal(action, data = null) {
        const modal = document.getElementById('modal-user');
        const title = document.getElementById('modal-title');
        const form = document.getElementById('form-user');
        const passLabel = document.getElementById('label-password');
        const passInput = document.getElementById('modal-password');

        const todayStr = new Date().toISOString().split('T')[0];
        const nextYearDate = new Date();
        nextYearDate.setFullYear(nextYearDate.getFullYear() + defaultDurationYears);
        const nextYearStr = nextYearDate.toISOString().split('T')[0];

        // Reset
        document.getElementById('modal-user-id').value = '';
        document.getElementById('modal-name').value = '';
        document.getElementById('modal-email').value = '';
        if (document.getElementById('modal-whatsapp')) document.getElementById('modal-whatsapp').value = '';
        document.getElementById('modal-sk').value = '';
        if (document.getElementById('modal-no-sk-kemenkum')) document.getElementById('modal-no-sk-kemenkum').value = '';
        if (document.getElementById('modal-tgl-sk')) document.getElementById('modal-tgl-sk').value = '';
        if (document.getElementById('modal-language-services')) document.getElementById('modal-language-services').value = '';
        if (document.getElementById('modal-masa-aktif')) document.getElementById('modal-masa-aktif').value = 'Seumur Hidup';
        if (document.getElementById('modal-sk-lengkap')) document.getElementById('modal-sk-lengkap').value = '';
        if (document.getElementById('modal-verification-status')) document.getElementById('modal-verification-status').value = 'approved';
        if (document.getElementById('modal-registered-at')) document.getElementById('modal-registered-at').value = todayStr;
        if (document.getElementById('modal-activated-at')) document.getElementById('modal-activated-at').value = todayStr;
        if (document.getElementById('modal-expired-at')) document.getElementById('modal-expired-at').value = nextYearStr;
        document.getElementById('modal-password').value = '';
        document.getElementById('modal-role').value = 'TRANSLATOR';

        if (action === 'create') {
            title.innerText = 'Tambah Pengguna Baru';
            form.setAttribute('action', '/admin/users');
            passLabel.innerText = 'Password Akses';
            passInput.setAttribute('required', 'required');
        } else {
            title.innerText = 'Edit Profil & Masa Keanggotaan';
            form.setAttribute('action', '/admin/users/' + data.id + '/update');
            
            document.getElementById('modal-user-id').value = data.id;
            document.getElementById('modal-name').value = data.name || '';
            document.getElementById('modal-email').value = data.email || '';
            if (document.getElementById('modal-whatsapp')) document.getElementById('modal-whatsapp').value = data.whatsapp || '';
            document.getElementById('modal-sk').value = data.sk_number || '';
            if (document.getElementById('modal-no-sk-kemenkum')) document.getElementById('modal-no-sk-kemenkum').value = data.no_sk_kemenkum || '';
            if (document.getElementById('modal-tgl-sk')) document.getElementById('modal-tgl-sk').value = data.tgl_sk || '';
            if (document.getElementById('modal-language-services')) document.getElementById('modal-language-services').value = data.language_services || '';
            if (document.getElementById('modal-masa-aktif')) document.getElementById('modal-masa-aktif').value = data.masa_aktif || 'Seumur Hidup';
            if (document.getElementById('modal-sk-lengkap')) document.getElementById('modal-sk-lengkap').value = data.sk_lengkap || '';
            if (document.getElementById('modal-verification-status')) document.getElementById('modal-verification-status').value = data.verification_status || 'approved';
            if (document.getElementById('modal-registered-at')) document.getElementById('modal-registered-at').value = data.registered_at || todayStr;
            if (document.getElementById('modal-activated-at')) document.getElementById('modal-activated-at').value = data.activated_at || '';
            if (document.getElementById('modal-expired-at')) document.getElementById('modal-expired-at').value = data.expired_at || '';
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
                const nameIdx = headers.findIndex(h => h === 'namapenerjemah' || h === 'nama' || h === 'name');
                const emailIdx = headers.findIndex(h => h === 'email' || h === 'surel');
                const noSkKemenkumIdx = headers.findIndex(h => h === 'noskkemenkum' || h === 'no_sk_kemenkum' || h === 'skkemenkumham');
                const tglSkIdx = headers.findIndex(h => h === 'tglsk' || h === 'tgl_sk' || h === 'tanggalsk');
                const arahBahasaIdx = headers.findIndex(h => h === 'arahbahasa' || h === 'arah_bahasa' || h === 'bahasa');
                const masaAktifIdx = headers.findIndex(h => h === 'masaaktif' || h === 'masa_aktif');
                const urlFotoIdx = headers.findIndex(h => h === 'urlfoto' || h === 'url_foto' || h === 'profilepicture');
                const skLengkapIdx = headers.findIndex(h => h === 'sklengkap' || h === 'sk_lengkap' || h === 'bio');

                const warningCard = document.getElementById('excel-warning-card');
                const confirmBtn = document.getElementById('excel-confirm-btn');

                if (noAnggotaIdx === -1 || nameIdx === -1) {
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
                        email: emailIdx !== -1 ? String(row[emailIdx] || '').trim() : '',
                        no_sk_kemenkum: noSkKemenkumIdx !== -1 ? String(row[noSkKemenkumIdx] || '').trim() : '',
                        tgl_sk: tglSkIdx !== -1 ? String(row[tglSkIdx] || '').trim() : '',
                        arah_bahasa: arahBahasaIdx !== -1 ? String(row[arahBahasaIdx] || '').trim() : '',
                        masa_aktif: masaAktifIdx !== -1 ? String(row[masaAktifIdx] || '').trim() : '',
                        url_foto: urlFotoIdx !== -1 ? String(row[urlFotoIdx] || '').trim() : '',
                        sk_lengkap: skLengkapIdx !== -1 ? String(row[skLengkapIdx] || '').trim() : '',
                    };

                    parsedTranslators.push(rowData);

                    if (previewRowsCount < 5) {
                        previewTbody.innerHTML += `
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-3 py-2 font-mono text-[9px] text-slate-700 truncate max-w-[80px]">${rowData.no_anggota}</td>
                                <td class="px-3 py-2 text-slate-800 font-bold truncate max-w-[100px]">${rowData.name}</td>
                                <td class="px-3 py-2 text-slate-600 truncate max-w-[100px]">${rowData.no_sk_kemenkum || '-'}</td>
                                <td class="px-3 py-2 text-slate-650 truncate max-w-[80px]">${rowData.tgl_sk || '-'}</td>
                                <td class="px-3 py-2 text-slate-600 truncate max-w-[90px]">${rowData.arah_bahasa || '-'}</td>
                                <td class="px-3 py-2 text-slate-550 truncate max-w-[70px]">${rowData.masa_aktif || '-'}</td>
                                <td class="px-3 py-2 text-slate-500 truncate max-w-[100px]">${rowData.sk_lengkap || '-'}</td>
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

    function openTopupModal(user) {
        document.getElementById('form-topup').action = '/admin/users/' + user.id + '/topup';
        document.getElementById('topup-user-name').innerText = user.name;
        document.getElementById('topup-user-points').innerText = new Intl.NumberFormat('id-ID').format(user.points) + ' Poin';
        document.getElementById('topup-points-input').value = 100000;
        document.getElementById('modal-topup').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeTopupModal() {
        document.getElementById('modal-topup').classList.add('hidden');
    }

    function setTopupPreset(val) {
        document.getElementById('topup-points-input').value = val;
    }

    function openVerifyMemberModal(applicant) {
        const todayStr = new Date().toISOString().split('T')[0];
        const nextYearDate = new Date();
        nextYearDate.setFullYear(nextYearDate.getFullYear() + defaultDurationYears);
        const nextYearStr = nextYearDate.toISOString().split('T')[0];

        document.getElementById('form-verify-member').action = '/admin/users/' + applicant.id + '/verify';
        document.getElementById('verify-member-name').innerText = applicant.name || '';
        document.getElementById('verify-member-email').innerText = applicant.email || '';
        document.getElementById('verify-sk-number').value = applicant.sk_number || '';
        document.getElementById('verify-no-sk-kemenkum').value = applicant.no_sk_kemenkum || '';
        document.getElementById('verify-tgl-sk').value = applicant.tgl_sk || '';
        document.getElementById('verify-language-services').value = applicant.language_services || '';
        if (document.getElementById('verify-activated-at')) document.getElementById('verify-activated-at').value = todayStr;
        if (document.getElementById('verify-expired-at')) document.getElementById('verify-expired-at').value = nextYearStr;
        document.getElementById('modal-verify-member').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeVerifyMemberModal() {
        document.getElementById('modal-verify-member').classList.add('hidden');
    }

    function openRenewMemberModal(member) {
        document.getElementById('form-renew-member').action = '/admin/users/' + member.id + '/renew';
        document.getElementById('renew-member-name').innerText = member.name || '';
        document.getElementById('renew-member-sk').innerText = member.sk_number || '-';
        document.getElementById('renew-current-expired').innerText = member.expired_at_display || '-';
        document.getElementById('renew-expired-at-input').value = member.next_expired_at || '';
        document.getElementById('modal-renew-member').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeRenewMemberModal() {
        document.getElementById('modal-renew-member').classList.add('hidden');
    }

    function openRejectMemberModal(applicant) {
        document.getElementById('form-reject-member').action = '/admin/users/' + applicant.id + '/reject';
        document.getElementById('reject-member-name').innerText = applicant.name || '';
        document.getElementById('reject-reason-input').value = applicant.rejection_reason || '';
        document.getElementById('modal-reject-member').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeRejectMemberModal() {
        document.getElementById('modal-reject-member').classList.add('hidden');
    }
</script>

<!-- ========================================== -->
<!--       VERIFY & ACTIVATE MEMBER MODAL       -->
<!-- ========================================== -->
<div id="modal-verify-member" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl p-6 border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm">
                <i data-lucide="user-check" class="w-5 h-5 text-emerald-600"></i>
                <span>Verifikasi & Aktivasi Anggota Baru IPPTI</span>
            </div>
            <button onclick="closeVerifyMemberModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-verify-member" method="POST" class="space-y-4 text-left">
            @csrf
            <div class="bg-emerald-50/70 border border-emerald-200 p-3.5 rounded-xl space-y-1 text-xs">
                <p class="text-slate-500">Pendaftar Penerjemah Tersumpah:</p>
                <p id="verify-member-name" class="font-extrabold text-slate-900 text-sm"></p>
                <p id="verify-member-email" class="text-slate-600 font-medium"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="sm:col-span-2">
                    <label for="verify-sk-number" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                        Nomor Anggota Resmi IPPTI <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="verify-sk-number"
                        name="sk_number"
                        required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-bold font-mono outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                        placeholder="Contoh: 25009"
                    />
                    <p class="text-[10px] text-slate-400 mt-1">Anda dapat menetapkan atau menyesuaikan Nomor Anggota resmi IPPTI untuk pendaftar ini.</p>
                </div>

                <div>
                    <label for="verify-no-sk-kemenkum" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                        Nomor SK Menteri Hukum RI
                    </label>
                    <input
                        type="text"
                        id="verify-no-sk-kemenkum"
                        name="no_sk_kemenkum"
                        required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-semibold font-mono outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                    />
                </div>

                <div>
                    <label for="verify-tgl-sk" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                        Tanggal SK Menteri Hukum RI
                    </label>
                    <input
                        type="text"
                        id="verify-tgl-sk"
                        name="tgl_sk"
                        required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label for="verify-language-services" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                        Pasangan / Arah Bahasa Tersumpah
                    </label>
                    <input
                        type="text"
                        id="verify-language-services"
                        name="language_services"
                        required
                        class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-900 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                    />
                </div>

                <div>
                    <label for="verify-activated-at" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                        Tanggal Aktivasi Member
                    </label>
                    <input
                        type="date"
                        id="verify-activated-at"
                        name="activated_at"
                        required
                        onchange="autoCalcExpiredFromActivation('verify-activated-at', 'verify-expired-at')"
                        class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-slate-900 text-xs font-bold font-mono outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                    />
                </div>

                <div>
                    <label for="verify-expired-at" class="block text-xs font-bold text-emerald-700 uppercase tracking-wider mb-1">
                        Tanggal Expired (+{{ max(1, (int)\App\Models\Setting::get('member_duration_years', 1)) }} Tahun)
                    </label>
                    <input
                        type="date"
                        id="verify-expired-at"
                        name="expired_at"
                        required
                        class="w-full px-3.5 py-2 border border-emerald-300 bg-emerald-50/40 rounded-xl text-emerald-900 text-xs font-bold font-mono outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition"
                    />
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 p-3 rounded-xl text-[11px] text-slate-600 leading-relaxed">
                Dengan mengklik <strong>Setujui & Aktifkan Akun</strong>, akun penerjemah ini akan berstatus <strong>Terverifikasi</strong> dan langsung dapat menggunakan fitur <strong>Input Data Dokumen & QR Verifikasi</strong>.
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    onclick="closeVerifyMemberModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold transition shadow-sm cursor-pointer flex items-center gap-1.5"
                >
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Setujui & Aktifkan Akun</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!--         REJECT / REVISION MEMBER MODAL     -->
<!-- ========================================== -->
<div id="modal-reject-member" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2 text-rose-700 font-bold text-sm">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600"></i>
                <span>Tolak / Minta Revisi Pendaftaran</span>
            </div>
            <button onclick="closeRejectMemberModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-reject-member" method="POST" class="space-y-4 text-left">
            @csrf
            <div class="bg-rose-50/70 border border-rose-200 p-3.5 rounded-xl space-y-1 text-xs">
                <p class="text-slate-500">Pendaftar:</p>
                <p id="reject-member-name" class="font-extrabold text-slate-900 text-sm"></p>
            </div>

            <div>
                <label for="reject-reason-input" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    Alasan Penolakan / Catatan Revisi untuk Pendaftar
                </label>
                <textarea
                    id="reject-reason-input"
                    name="rejection_reason"
                    rows="3"
                    required
                    placeholder="Contoh: Mohon unggah salinan SK Menteri Hukum RI yang jelas dan sesuaikan nomor SK..."
                    class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-xs font-semibold outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition"
                ></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    onclick="closeRejectMemberModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
                >
                    Simpan Status Revisi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!--       RENEW ANNUAL MEMBERSHIP MODAL        -->
<!-- ========================================== -->
<div id="modal-renew-member" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm">
                <i data-lucide="calendar-plus" class="w-5 h-5 text-emerald-600"></i>
                <span>Perpanjangan Keanggotaan Tahunan IPPTI</span>
            </div>
            <button onclick="closeRenewMemberModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-renew-member" method="POST" class="space-y-4 text-left">
            @csrf
            <div class="bg-emerald-50/70 border border-emerald-200 p-3.5 rounded-xl space-y-1 text-xs">
                <p class="text-slate-500">Member Penerjemah Tersumpah:</p>
                <p id="renew-member-name" class="font-extrabold text-slate-900 text-sm"></p>
                <p class="text-slate-600">No. Anggota: <strong id="renew-member-sk" class="font-mono"></strong></p>
                <p class="text-slate-600">Tanggal Expired Saat Ini: <strong id="renew-current-expired" class="font-mono text-amber-700"></strong></p>
            </div>

            <div>
                <label for="renew-expired-at-input" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    Tanggal Expired Baru (+{{ max(1, (int)\App\Models\Setting::get('member_duration_years', 1)) }} Tahun)
                </label>
                <input
                    type="date"
                    id="renew-expired-at-input"
                    name="expired_at"
                    required
                    class="w-full px-3.5 py-2.5 border border-emerald-300 rounded-xl text-emerald-950 text-sm font-bold font-mono outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition bg-emerald-50/30"
                />
                <p class="text-[10.5px] text-slate-500 mt-1">
                    Biaya Perpanjangan Tahunan: <strong>Rp {{ number_format((float)\App\Models\Setting::get('member_renewal_fee', 700000), 0, ',', '.') }}</strong>.
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    onclick="closeRenewMemberModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold transition shadow-sm cursor-pointer flex items-center gap-1.5"
                >
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Proses Perpanjangan Tahunan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!--              TOP-UP POINTS MODAL           -->
<!-- ========================================== -->
<div id="modal-topup" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2 text-amber-700 font-bold text-sm">
                <i data-lucide="coins" class="w-5 h-5 text-amber-500"></i>
                <span>Top-Up Saldo Poin Penerjemah</span>
            </div>
            <button onclick="closeTopupModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-topup" method="POST" class="space-y-4 text-left">
            @csrf
            <div class="bg-amber-50/60 border border-amber-100 p-3.5 rounded-xl space-y-1 text-xs">
                <p class="text-slate-500">Target Penerjemah:</p>
                <p id="topup-user-name" class="font-bold text-slate-900 text-sm"></p>
                <p class="text-slate-500 mt-1">Saldo Poin Saat Ini: <strong id="topup-user-points" class="font-mono font-bold text-emerald-700"></strong></p>
            </div>

            <div>
                <label for="topup-points-input" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Poin Tambahan</label>
                <input
                    type="number"
                    id="topup-points-input"
                    name="points"
                    required
                    min="1"
                    step="1000"
                    value="100000"
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm font-semibold outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition"
                    placeholder="Contoh: 100000"
                />
                <p class="text-[10px] text-slate-400 mt-1">1 Dokumen = 1.000 Poin. Contoh: Top-up 100.000 Poin = 100 Dokumen.</p>
            </div>

            <!-- Quick topup presets -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <button type="button" onclick="setTopupPreset(50000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer">+50.000</button>
                <button type="button" onclick="setTopupPreset(100000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer">+100.000</button>
                <button type="button" onclick="setTopupPreset(250000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer">+250.000</button>
                <button type="button" onclick="setTopupPreset(500000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition cursor-pointer">+500.000</button>
            </div>

            <div class="flex justify-end gap-3 pt-3">
                <button
                    type="button"
                    onclick="closeTopupModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
                >
                    Proses Top-Up Poin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
