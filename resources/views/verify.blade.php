<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - E-Certificate Hasil Verifikasi Dokumen</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- html2pdf.js for instant A4 PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"Space Mono"', 'monospace'],
                    },
                    colors: {
                        brandBlue: '#1E3A8A',
                        brandGold: '#F59E0B',
                        brandGreen: '#16A34A',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: "Plus Jakarta Sans", sans-serif;
        }
        .dir-ltr { direction: ltr !important; }

        /* Guilloche Certificate Border Styling */
        .guilloche-border {
            border: 12px double #1E3A8A;
            box-shadow: inset 0 0 0 2px #F59E0B;
            position: relative;
        }

        /* Tab fieldset badge header */
        .section-tab-badge {
            position: absolute;
            top: -12px;
            left: 16px;
            background-color: #1E3A8A;
            color: #ffffff;
            padding: 2px 12px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 4mm !important;
            }
            body {
                background: white !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            #body-layout {
                padding: 0 !important;
                background: white !important;
                display: block !important;
            }
            .max-w-2xl {
                max-width: 100% !important;
                margin: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body id="body-layout" class="bg-slate-100 text-slate-900 min-h-screen flex flex-col items-center justify-between p-3 sm:p-6 selection:bg-emerald-500 selection:text-slate-950">

    @if(!$document)
        <!-- Document Not Found Container -->
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden text-center p-8 border border-gray-200 my-auto">
            <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i data-lucide="x-circle" class="w-10 h-10 text-rose-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Dokumen Tidak Ditemukan</h1>
            <p class="text-gray-500 mb-6 leading-relaxed text-sm">
                Kami tidak dapat memverifikasi dokumen ini. Kode QR mungkin tidak valid, telah dicabut, atau dokumen belum terdaftar di sistem kami.
            </p>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
                <p class="text-sm font-mono text-gray-500">ID Dokumen: {{ $documentId }}</p>
            </div>
            <a href="/" class="inline-flex items-center justify-center w-full px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-medium transition-colors duration-200">
                Kembali ke Beranda
            </a>
        </div>
    @else
        <!-- Top Web Header (no-print) -->
        <div class="w-full max-w-2xl mb-4 text-center pt-2 no-print flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="inline-flex items-center gap-3">
                <a href="https://ippti.or.id" target="_blank" class="cursor-pointer">
                    <img src="/ippti-logo.jpg" alt="IPPTI Logo" crossorigin="anonymous" class="h-10 w-auto rounded-lg bg-white p-1 border border-slate-200 shadow-xs" />
                </a>
                <div class="text-left">
                    <a href="/" class="text-lg font-black text-[#1E3A8A] tracking-tight hover:underline">DocVerify IPPTI</a>
                    <p id="sub-header-portal" class="text-[11px] text-slate-500 font-medium">Portal Verifikasi Resmi Terjemahan Tersumpah</p>
                </div>
            </div>

            <!-- Language Switcher Bar -->
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-xs">
                <i data-lucide="globe" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bahasa:</span>
                <div class="flex bg-slate-100 p-0.5 rounded-lg border border-slate-200 dir-ltr" dir="ltr">
                    <button onclick="changeLanguage('id')" id="lang-id" class="px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-900">ID</button>
                    <button onclick="changeLanguage('en')" id="lang-en" class="px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-900">EN</button>
                    <button onclick="changeLanguage('zh')" id="lang-zh" class="px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-900">ZH</button>
                    <button onclick="changeLanguage('ar')" id="lang-ar" class="px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-900">AR</button>
                </div>
            </div>
        </div>

        <!-- E-Certificate Card Container (Exact Mockup Match & A4 Printable) -->
        <div class="w-full max-w-2xl mb-6">
            <div id="pdf-card" class="bg-white p-5 sm:p-7 rounded-2xl guilloche-border relative shadow-2xl overflow-hidden font-sans text-slate-800">
                
                <!-- Watermark Background Logo -->
                <div class="absolute inset-0 flex items-center justify-center opacity-[0.05] pointer-events-none z-0">
                    <img src="/ippti-logo.jpg" alt="Watermark" crossorigin="anonymous" class="w-96 h-auto grayscale" />
                </div>

                <div class="relative z-10 space-y-4">

                    <!-- Header Row: Logo Left, Title & Green Badge Right -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 border-b-2 border-[#1E3A8A]/20 pb-4">
                        <!-- Left Logo & Organization Info -->
                        <div class="flex items-center gap-3 text-center sm:text-left">
                            <img src="/ippti-logo.jpg" alt="IPPTI Logo" crossorigin="anonymous" class="h-16 w-auto object-contain flex-shrink-0" />
                            <div>
                                <h2 class="text-2xl font-black text-[#1E3A8A] tracking-tight leading-none">IPPTI</h2>
                                <p class="text-[9px] font-bold text-[#1E3A8A] uppercase tracking-wide mt-1 max-w-[220px] leading-tight" id="cert-header-org">
                                    IKATAN PENERJEMAH DAN PENGALIH BAHASA TERSUMPAH DI INDONESIA
                                </p>
                            </div>
                        </div>

                        <!-- Right Certificate Title & Green Badge -->
                        <div class="flex items-start gap-3 text-center sm:text-right">
                            <div>
                                <h1 class="text-xl font-black text-[#1E3A8A] tracking-tight leading-none" id="cert-title">E-CERTIFICATE</h1>
                                <p class="text-sm font-black text-[#F59E0B] tracking-wider uppercase mt-0.5" id="cert-subtitle">HASIL VERIFIKASI</p>
                                <p class="text-[9px] font-bold text-[#1E3A8A] tracking-wider uppercase" id="cert-desc">DOKUMEN TERJEMAHAN TERSUMPAH</p>
                            </div>

                            <!-- Verified Square Badge -->
                            <div id="cert-status-badge" class="w-12 h-12 bg-[#16A34A] text-white rounded-xl flex flex-col items-center justify-center flex-shrink-0 shadow-sm">
                                <i id="cert-badge-icon" data-lucide="check-circle-2" class="w-6 h-6 stroke-[2.5]"></i>
                                <span id="cert-badge-text" class="text-[8px] font-black tracking-wider uppercase mt-0.5">VERIFIED</span>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate ID Sub-Header Box (Right Aligned) -->
                    <div class="flex justify-end items-center gap-2">
                        <div class="text-right">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">CERTIFICATE ID</span>
                            <div class="inline-block bg-white border-2 border-slate-300 px-5 py-1 rounded-xl shadow-xs">
                                <span class="text-sm font-black font-mono text-slate-900 tracking-wider notranslate" translate="no">{{ $document->document_id }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Verification Notice Section -->
                    <div id="cert-notice-box" class="flex items-center gap-4 p-2 text-left">
                        <div id="cert-notice-icon-bg" class="w-14 h-14 bg-[#16A34A] text-white rounded-2xl flex items-center justify-center flex-shrink-0 shadow-md ring-4 ring-emerald-500/20">
                            <i id="cert-notice-icon" data-lucide="shield-check" class="w-9 h-9"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-[#16A34A] uppercase tracking-wide leading-tight" id="cert-notice-title">DOKUMEN TELAH DIVERIFIKASI</h3>
                            <p class="text-xs text-slate-600 font-medium leading-relaxed mt-1" id="cert-notice-desc">
                                Dokumen terjemahan ini telah berhasil diverifikasi melalui sistem resmi IPPTI dan dinyatakan sebagai dokumen terjemahan tersumpah yang sah sesuai data registrasi.
                            </p>
                        </div>
                    </div>

                    <!-- DATA VERIFIKASI Section Box -->
                    <div class="border border-slate-300 rounded-xl relative pt-4 pb-3 px-4 bg-white/80">
                        <span class="section-tab-badge" id="cert-data-title">DATA VERIFIKASI</span>

                        <div class="divide-y divide-slate-100 text-xs">
                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
                                    <span class="font-semibold text-slate-600" id="cert-label-reg-no">Nomor Registrasi</span>
                                </div>
                                <span class="font-black font-mono text-slate-900 text-sm notranslate" translate="no">{{ $document->registration_number }}</span>
                            </div>

                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i data-lucide="id-card" class="w-4 h-4 text-slate-400"></i>
                                    <span class="font-semibold text-slate-600" id="cert-label-doc-id">ID Dokumen</span>
                                </div>
                                <span class="font-black font-mono text-slate-900 text-sm notranslate" translate="no">{{ $document->document_id }}</span>
                            </div>

                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i data-lucide="shield" class="w-4 h-4 text-slate-400"></i>
                                    <span class="font-semibold text-slate-600" id="cert-label-status">Status Verifikasi</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-extrabold uppercase text-xs" id="cert-value-status">VERIFIED</span>
                                    <i id="cert-status-check" data-lucide="check-circle-2" class="w-4 h-4 text-[#16A34A]"></i>
                                </div>
                            </div>

                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                                    <span class="font-semibold text-slate-600" id="cert-label-trans-date">Tanggal Terjemah</span>
                                </div>
                                <span class="font-bold text-slate-900 notranslate" translate="no" id="cert-value-trans-date"></span>
                            </div>

                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i data-lucide="file" class="w-4 h-4 text-slate-400"></i>
                                    <span class="font-semibold text-slate-600" id="cert-label-doc-type">Jenis Dokumen</span>
                                </div>
                                <span class="font-extrabold text-slate-900 text-right max-w-[280px] truncate">{{ $document->document_type }}</span>
                            </div>

                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <i data-lucide="languages" class="w-4 h-4 text-slate-400"></i>
                                    <span class="font-semibold text-slate-600" id="cert-label-lang-pair">Pasangan Bahasa</span>
                                </div>
                                <span class="font-extrabold text-slate-900 notranslate" translate="no">{{ $document->language_pair }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- NAMA DI DOKUMEN (DISAMARKAN) Section Box -->
                    <div class="border border-slate-300 rounded-xl relative pt-4 pb-3 px-4 bg-slate-50/50">
                        <span class="section-tab-badge" id="cert-label-masked-name">NAMA DI DOKUMEN (DISAMARKAN)</span>
                        @php
                            $masked = '';
                            if ($document->client_name) {
                                $masked = implode(" ", array_map(function($word) {
                                    if (strlen($word) <= 1) return $word;
                                    return $word[0] . str_repeat("*", strlen($word) - 1);
                                }, explode(" ", $document->client_name)));
                            }
                        @endphp
                        <p class="text-base sm:text-lg font-black font-mono tracking-widest text-slate-900 text-center uppercase py-1 notranslate" translate="no">{{ $masked }}</p>
                    </div>

                    <!-- PENERJEMAH TERSUMPAH & QR CODE Section Box -->
                    <div class="border border-slate-300 rounded-xl relative pt-4 pb-3 px-4 bg-white/80">
                        <span class="section-tab-badge" id="cert-label-translator-title">PENERJEMAH TERSUMPAH</span>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                            <!-- Left Translator Info (2 columns) -->
                            <div class="sm:col-span-2 space-y-2 border-b sm:border-b-0 sm:border-r border-slate-200 pb-3 sm:pb-0 sm:pr-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 border-2 border-[#1E3A8A] shadow-sm overflow-hidden">
                                        @if($document->translator->profile_picture)
                                            <img src="{{ $document->translator->profile_picture }}" alt="{{ $document->translator->name }}" crossorigin="anonymous" class="w-full h-full object-cover" />
                                        @else
                                            <i data-lucide="user" class="w-8 h-8 text-[#1E3A8A]"></i>
                                        @endif
                                    </div>
                                    <div class="space-y-0.5 overflow-hidden">
                                        <h4 class="text-sm font-extrabold text-slate-950 notranslate" translate="no">{{ $document->translator->name }}</h4>
                                        <p class="text-xs font-bold text-slate-700 font-mono notranslate" translate="no" id="cert-value-member-no">No. Anggota : {{ $document->translator->sk_number }}</p>
                                        <p class="text-[11px] font-semibold text-slate-600" id="cert-value-translator-service">Penerjemah Tersumpah Bahasa {{ $document->translator->language_services ?: $document->language_pair }}</p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-500 font-medium leading-tight border-t border-slate-100 pt-2" id="cert-value-decree">
                                    Sesuai dengan SK Menteri Hukum dan HAM {{ $document->translator->no_sk_kemenkum ?: 'AHU-56 AH.03.07.2022' }} {{ $document->translator->tgl_sk ? 'tanggal ' . \Carbon\Carbon::parse($document->translator->tgl_sk)->translatedFormat('d F Y') : '' }}
                                </p>
                            </div>

                            <!-- Right QR Code (1 column) -->
                            <div class="sm:col-span-1 flex flex-col items-center justify-center text-center space-y-1">
                                <span class="text-[9px] font-bold text-[#1E3A8A] uppercase tracking-wider block" id="cert-qr-title">QR VERIFICATION</span>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(url('/verify/' . $document->document_id)) }}" alt="QR" crossorigin="anonymous" class="w-24 h-24 bg-white p-1 rounded-xl border border-slate-300 shadow-xs" />
                                <span class="text-[8px] text-slate-500 font-medium leading-tight max-w-[120px]" id="cert-qr-caption">Scan untuk verifikasi keaslian dokumen di sistem IPPTI</span>
                            </div>
                        </div>
                    </div>

                    <!-- Digital Signature & Verification Time Sub-Boxes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Digital Signature Sub-Box -->
                        <div class="border border-slate-300 rounded-xl p-3 flex items-center gap-3 bg-slate-50/50">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center flex-shrink-0 shadow-xs">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block" id="cert-label-digisig">DIGITAL SIGNATURE</span>
                                <p class="text-xs font-bold text-slate-800" id="cert-val-digisig-title">Tanda Tangan Digital</p>
                                <span class="text-[10px] font-extrabold text-[#16A34A]" id="cert-val-digisig-sub">Valid & Terverifikasi</span>
                            </div>
                        </div>

                        <!-- Verification Time Sub-Box -->
                        <div class="border border-slate-300 rounded-xl p-3 flex items-center gap-3 bg-slate-50/50">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center flex-shrink-0 shadow-xs">
                                <i data-lucide="clock" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block" id="cert-label-ver-time">VERIFICATION TIME</span>
                                <p class="text-xs font-black font-mono text-slate-900" id="cert-verify-time">{{ now()->translatedFormat('d M Y H:i') }} WIB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Solid Green Status Banner Bar -->
                    <div id="cert-status-banner-bar" class="bg-[#16A34A] text-white p-3 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                                <i id="cert-banner-check-icon" data-lucide="check-circle-2" class="w-4 h-4 text-white"></i>
                            </div>
                            <span class="text-base font-black tracking-widest uppercase" id="cert-banner-status-text">VERIFIED</span>
                        </div>
                        <p class="text-[11px] text-emerald-100 font-semibold tracking-wide text-center sm:text-right" id="cert-banner-status-desc">
                            This translated document has been officially verified by IPPTI Verification System.
                        </p>
                    </div>

                    <!-- Disclaimer Resmi Section -->
                    <div class="space-y-1 relative pt-1">
                        <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider" id="cert-disclaimer-title">DISCLAIMER RESMI</h5>
                        <p class="text-[10px] sm:text-[11px] text-slate-600 leading-relaxed font-medium text-justify max-w-xl" id="cert-disclaimer-text">
                            Sertifikat elektronik ini hanya menyatakan bahwa data dokumen terjemahan telah diverifikasi pada sistem IPPTI. Keabsahan hukum dokumen fisik tetap mengacu pada cap basah, tanda tangan penerjemah tersumpah, dan ketentuan peraturan perundang-undangan yang berlaku.
                        </p>

                        <!-- Hologram Stamp Watermark Bottom-Right -->
                        <div class="absolute right-0 bottom-0 opacity-80 pointer-events-none">
                            <div class="w-16 h-16 rounded-full border-2 border-slate-300 bg-gradient-to-tr from-amber-100 via-emerald-100 to-blue-100 flex items-center justify-center shadow-xs">
                                <span class="text-[9px] font-black text-[#1E3A8A] tracking-wider">IPPTI</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Bar -->
                    <div class="pt-3 border-t-2 border-[#1E3A8A]/20 flex flex-col sm:flex-row items-center justify-between gap-2 text-[9px] text-slate-600">
                        <div class="flex items-center gap-2">
                            <img src="/ippti-logo.jpg" alt="IPPTI Logo" crossorigin="anonymous" class="h-5 w-auto" />
                            <span class="font-bold text-[#1E3A8A]" id="cert-footer-org-bottom">IPPTI – Ikatan Penerjemah dan Pengalih Bahasa Tersumpah di Indonesia</span>
                        </div>
                        <div class="flex items-center gap-3 font-semibold dir-ltr" dir="ltr">
                            <span class="flex items-center gap-1"><i data-lucide="globe" class="w-3 h-3 text-[#1E3A8A]"></i> ippti.or.id</span>
                            <span class="flex items-center gap-1"><i data-lucide="mail" class="w-3 h-3 text-[#1E3A8A]"></i> info@ippti.or.id</span>
                            <span class="flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3 text-[#1E3A8A]"></i> +62 811 8117 0118</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Action Buttons (no-print) -->
        <div class="flex flex-col sm:flex-row gap-4 w-full max-w-2xl no-print mb-8">
            <button id="btn-download-pdf" onclick="downloadPDF()" class="flex-1 flex items-center justify-center gap-2 py-3.5 px-6 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl text-sm font-bold transition shadow-md hover:shadow-lg cursor-pointer active:scale-[0.98]">
                <i data-lucide="download" class="w-4.5 h-4.5 text-white"></i>
                <span>Download E-Sertifikat PDF (A4)</span>
            </button>
            <a href="/" class="flex-1 flex items-center justify-center gap-2 py-3.5 px-6 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-sm font-bold transition shadow-md cursor-pointer active:scale-[0.98]">
                <i data-lucide="search" class="w-4.5 h-4.5 text-slate-400"></i>
                <span>Verifikasi Dokumen Lain</span>
            </a>
        </div>
    @endif

    <!-- Bottom Copyright Credit -->
    <div class="text-center text-[10px] text-slate-400 uppercase tracking-widest space-y-1 py-4 no-print">
        <p>&copy; {{ date('Y') }} DocVerify IPPTI. Seluruh Hak Cipta Dilindungi.</p>
        <p id="label-bottom-credit" class="font-semibold text-slate-400"></p>
    </div>

    <script>
        lucide.createIcons();

        const dateId = "{{ $document ? $document->document_date->translatedFormat('d F Y') : '' }}";
        const dateEn = "{{ $document ? $document->document_date->format('F d, Y') : '' }}";
        const memberNo = "{{ $document ? $document->translator->sk_number : '' }}";
        const isArchived = {{ ($document && $document->trashed()) ? 'true' : 'false' }};

        const certTranslations = {
            id: {
                cert_title: "E-CERTIFICATE",
                cert_subtitle: "HASIL VERIFIKASI",
                cert_desc: "DOKUMEN TERJEMAHAN TERSUMPAH",
                badge_verified: "VERIFIED",
                badge_cancelled: "REVOKED",
                notice_title: isArchived ? "DOKUMEN TELAH DIBATALKAN" : "DOKUMEN TELAH DIVERIFIKASI",
                notice_desc: isArchived 
                    ? "Dokumen terjemahan ini telah dicabut/dibatalkan oleh penerjemah yang bersangkutan dan tidak lagi berlaku."
                    : "Dokumen terjemahan ini telah berhasil diverifikasi melalui sistem resmi IPPTI dan dinyatakan sebagai dokumen terjemahan tersumpah yang sah sesuai data registrasi.",
                data_title: "DATA VERIFIKASI",
                reg_no: "Nomor Registrasi",
                doc_id: "ID Dokumen",
                status: "Status Verifikasi",
                status_val: isArchived ? "Dibatalkan / Revoked" : "VERIFIED",
                trans_date: "Tanggal Terjemah",
                doc_type: "Jenis Dokumen",
                lang_pair: "Pasangan Bahasa",
                masked_name: "NAMA DI DOKUMEN (DISAMARKAN)",
                translator_title: "PENERJEMAH TERSUMPAH",
                member_no: "No. Anggota : " + memberNo,
                translator_service: "Penerjemah Tersumpah Bahasa {{ $document ? ($document->translator->language_services ?: $document->language_pair) : '' }}",
                decree_text: "Sesuai dengan SK Menteri Hukum dan HAM {{ $document ? ($document->translator->no_sk_kemenkum ?: 'AHU-56 AH.03.07.2022') : '' }} {{ $document && $document->translator->tgl_sk ? 'tanggal ' . \Carbon\Carbon::parse($document->translator->tgl_sk)->translatedFormat('d F Y') : '' }}",
                qr_title: "QR VERIFICATION",
                qr_caption: "Scan untuk verifikasi keaslian dokumen di sistem IPPTI",
                digisig_label: "DIGITAL SIGNATURE",
                digisig_title: "Tanda Tangan Digital",
                digisig_sub: "Valid & Terverifikasi",
                ver_time_label: "VERIFICATION TIME",
                verify_time: "{{ now()->translatedFormat('d M Y H:i') }} WIB",
                banner_desc: "This translated document has been officially verified by IPPTI Verification System.",
                disclaimer_title: "DISCLAIMER RESMI",
                disclaimer_text: "Sertifikat elektronik ini hanya menyatakan bahwa data dokumen terjemahan telah diverifikasi pada sistem IPPTI. Keabsahan hukum dokumen fisik tetap mengacu pada cap basah, tanda tangan penerjemah tersumpah, dan ketentuan peraturan perundang-undangan yang berlaku.",
                footer_org: "IKATAN PENERJEMAH DAN PENGALIH BAHASA TERSUMPAH DI INDONESIA",
                footer_org_bottom: "IPPTI – Ikatan Penerjemah dan Pengalih Bahasa Tersumpah di Indonesia",
                bottom_credit: isArchived ? "STATUS DOKUMEN: DIBATALKAN" : "DIVERIFIKASI SECARA ELEKTRONIK & KRIPTOGRAFIS",
                sub_portal: "Portal Verifikasi Resmi Terjemahan Tersumpah",
            },
            en: {
                cert_title: "E-CERTIFICATE",
                cert_subtitle: "VERIFICATION RESULT",
                cert_desc: "SWORN TRANSLATED DOCUMENT",
                badge_verified: "VERIFIED",
                badge_cancelled: "REVOKED",
                notice_title: isArchived ? "DOCUMENT REVOKED / CANCELLED" : "DOCUMENT OFFICIALLY VERIFIED",
                notice_desc: isArchived 
                    ? "This document translation registration has been revoked or cancelled by the translator and is no longer valid."
                    : "This translated document has been successfully verified through the official IPPTI system and declared as a valid sworn translation according to registry data.",
                data_title: "VERIFICATION DATA",
                reg_no: "Registration Number",
                doc_id: "Document ID",
                status: "Verification Status",
                status_val: isArchived ? "Revoked / Cancelled" : "VERIFIED",
                trans_date: "Translation Date",
                doc_type: "Document Type",
                lang_pair: "Language Pair",
                masked_name: "NAME ON DOCUMENT (MASKED)",
                translator_title: "SWORN TRANSLATOR",
                member_no: "Member ID : " + memberNo,
                translator_service: "Sworn Translator for {{ $document ? ($document->translator->language_services ?: $document->language_pair) : '' }}",
                decree_text: "Pursuant to Decree of Minister of Law and Human Rights {{ $document ? ($document->translator->no_sk_kemenkum ?: 'AHU-56 AH.03.07.2022') : '' }}",
                qr_title: "QR VERIFICATION",
                qr_caption: "Scan to verify document authenticity on IPPTI system",
                digisig_label: "DIGITAL SIGNATURE",
                digisig_title: "Digital Signature",
                digisig_sub: "Valid & Verified",
                ver_time_label: "VERIFICATION TIME",
                verify_time: "{{ now()->format('d M Y H:i') }} UTC+7",
                banner_desc: "This translated document has been officially verified by IPPTI Verification System.",
                disclaimer_title: "OFFICIAL DISCLAIMER",
                disclaimer_text: "This e-certificate confirms that translation document data has been verified in the IPPTI system. Legal validity of physical documents relies on the wet stamp, sworn translator signature, and applicable laws.",
                footer_org: "ASSOCIATION OF SWORN TRANSLATORS AND INTERPRETERS IN INDONESIA",
                footer_org_bottom: "IPPTI – Association of Sworn Translators and Interpreters in Indonesia",
                bottom_credit: isArchived ? "DOCUMENT STATUS: REVOKED" : "ELECTRONICALLY & CRYPTOGRAPHICALLY VERIFIED",
                sub_portal: "Official Sworn Translation Verification Portal",
            },
            zh: {
                cert_title: "电子证书 (E-CERTIFICATE)",
                cert_subtitle: "验证结果",
                cert_desc: "宣誓翻译文件",
                badge_verified: "VERIFIED",
                badge_cancelled: "REVOKED",
                notice_title: isArchived ? "文件已被撤销 / 取消" : "文件已正式通过验证",
                notice_desc: isArchived 
                    ? "此文件的翻译注册已被相关翻译员撤销或取消，不再有效。"
                    : "此翻译文件已通过 IPPTI 官方系统成功验证，根据注册数据声明为有效的宣誓翻译文件。",
                data_title: "验证数据",
                reg_no: "注册号",
                doc_id: "文件 ID",
                status: "验证状态",
                status_val: isArchived ? "已取消 / Revoked" : "VERIFIED",
                trans_date: "翻译日期",
                doc_type: "文件类型",
                lang_pair: "语言对",
                masked_name: "文件姓名（已遮蔽）",
                translator_title: "宣誓翻译员",
                member_no: "成员编号 : " + memberNo,
                translator_service: "宣誓翻译员 - 语言服务: {{ $document ? ($document->translator->language_services ?: $document->language_pair) : '' }}",
                decree_text: "依据印尼法律与人权部长法令 {{ $document ? ($document->translator->no_sk_kemenkum ?: 'AHU-56 AH.03.07.2022') : '' }}",
                qr_title: "QR VERIFICATION",
                qr_caption: "扫描上方二维码在 IPPTI 系统上验证文件真实性",
                digisig_label: "DIGITAL SIGNATURE",
                digisig_title: "数字签名",
                digisig_sub: "有效并已验证",
                ver_time_label: "VERIFICATION TIME",
                verify_time: "{{ now()->format('Y-m-d H:i') }} UTC+7",
                banner_desc: "This translated document has been officially verified by IPPTI Verification System.",
                disclaimer_title: "官方免责声明",
                disclaimer_text: "此电子证书仅表明翻译文件数据已在 IPPTI 系统中验证。纸质文件的法律效力仍取决于宣誓翻译员的湿盖章、签名及适用法律法规。",
                footer_org: "印度尼西亚宣誓翻译员与口译员协会",
                footer_org_bottom: "IPPTI – 印度尼西亚宣誓翻译员与口译员协会",
                bottom_credit: isArchived ? "文件状态：已撤销" : "经过电子与密码学验证",
                sub_portal: "官方宣誓翻译验证门户",
            },
            ar: {
                cert_title: "شهادة إلكترونية",
                cert_subtitle: "نتيجة التحقق",
                cert_desc: "مستند مترجم محلف",
                badge_verified: "VERIFIED",
                badge_cancelled: "REVOKED",
                notice_title: isArchived ? "تم إلغاء المستند" : "تم التحقق من المستند رسمياً",
                notice_desc: isArchived 
                    ? "تم إلغاء تسجيل ترجمة هذا المستند بواسطة المترجم المعني ولم يعد صالحاً."
                    : "تم التحقق من هذا المستند المترجم بنجاح من خلال نظام IPPTI وإعلانه كترجمة محلفة صالحة وفقاً لبيانات التسجيل.",
                data_title: "بيانات التحقق",
                reg_no: "رقم التسجيل",
                doc_id: "معرف المستند",
                status: "حالة التحقق",
                status_val: isArchived ? "ملغى / Revoked" : "VERIFIED",
                trans_date: "تاريخ الترجمة",
                doc_type: "نوع المستند",
                lang_pair: "زوج اللغات",
                masked_name: "الاسم على المستند (مخفي)",
                translator_title: "المترجم المحلف",
                member_no: "رقم العضوية : " + memberNo,
                translator_service: "مترجم محلف للغة {{ $document ? ($document->translator->language_services ?: $document->language_pair) : '' }}",
                decree_text: "وفقاً لقرار وزير القانون وحقوق الإنسان {{ $document ? ($document->translator->no_sk_kemenkum ?: 'AHU-56 AH.03.07.2022') : '' }}",
                qr_title: "QR VERIFICATION",
                qr_caption: "امسح رمز QR للتحقق من صحة المستند في نظام IPPTI",
                digisig_label: "DIGITAL SIGNATURE",
                digisig_title: "التوقيع الرقمي",
                digisig_sub: "صالح ومتحقق منه",
                ver_time_label: "VERIFICATION TIME",
                verify_time: "{{ now()->format('Y-m-d H:i') }} UTC+7",
                banner_desc: "This translated document has been officially verified by IPPTI Verification System.",
                disclaimer_title: "إخلاء مسؤولية رسمي",
                disclaimer_text: "تؤكد هذه الشهادة الإلكترونية فقط أنه تم التحقق من بيانات المستند المترجم في نظام IPPTI. تعتمد الصلاحية القانونية للمستند المادي على الختم المائي وتوقيع المترجم المحلف والقوانين المعمول بها.",
                footer_org: "جمعية المترجمين المحلفين والمترجمين الفوريين في إندونيسيا",
                footer_org_bottom: "IPPTI – جمعية المترجمين المحلفين والمترجمين الفوريين في إندونيسيا",
                bottom_credit: isArchived ? "حالة المستند: ملغى" : "تم التحقق منه إلكترونياً وتشفيرياً",
                sub_portal: "البوابة الرسمية للتحقق من الترجمة المحلفة",
            }
        };

        let currentLang = localStorage.getItem('docverify_lang') || 'id';

        function changeLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('docverify_lang', lang);
            updateUILanguage();
        }

        function updateUILanguage() {
            const t = certTranslations[currentLang] || certTranslations['id'];
            const isRtl = currentLang === 'ar';

            const bodyLayout = document.getElementById('body-layout');
            if (bodyLayout) bodyLayout.dir = isRtl ? 'rtl' : 'ltr';

            const pdfCard = document.getElementById('pdf-card');
            if (pdfCard) pdfCard.dir = isRtl ? 'rtl' : 'ltr';

            // Set translated texts
            const elMap = {
                'cert-header-org': t.footer_org,
                'cert-title': t.cert_title,
                'cert-subtitle': t.cert_subtitle,
                'cert-desc': t.cert_desc,
                'cert-notice-title': t.notice_title,
                'cert-notice-desc': t.notice_desc,
                'cert-data-title': t.data_title,
                'cert-label-reg-no': t.reg_no,
                'cert-label-doc-id': t.doc_id,
                'cert-label-trans-date': t.trans_date,
                'cert-value-trans-date': currentLang === 'id' ? dateId : dateEn,
                'cert-label-status': t.status,
                'cert-value-status': t.status_val,
                'cert-label-doc-type': t.doc_type,
                'cert-label-lang-pair': t.lang_pair,
                'cert-label-masked-name': t.masked_name,
                'cert-label-translator-title': t.translator_title,
                'cert-value-member-no': t.member_no,
                'cert-value-translator-service': t.translator_service,
                'cert-value-decree': t.decree_text,
                'cert-qr-title': t.qr_title,
                'cert-qr-caption': t.qr_caption,
                'cert-label-digisig': t.digisig_label,
                'cert-val-digisig-title': t.digisig_title,
                'cert-val-digisig-sub': t.digisig_sub,
                'cert-label-ver-time': t.ver_time_label,
                'cert-verify-time': t.verify_time,
                'cert-banner-status-desc': t.banner_desc,
                'cert-disclaimer-title': t.disclaimer_title,
                'cert-disclaimer-text': t.disclaimer_text,
                'cert-footer-org-bottom': t.footer_org_bottom,
                'label-bottom-credit': t.bottom_credit,
                'sub-header-portal': t.sub_portal,
            };

            for (const [id, text] of Object.entries(elMap)) {
                const node = document.getElementById(id);
                if (node) node.innerText = text;
            }

            // Status Badge & Notice Banner Styling
            const statusBadge = document.getElementById('cert-status-badge');
            const badgeText = document.getElementById('cert-badge-text');
            const badgeIcon = document.getElementById('cert-badge-icon');
            const noticeBox = document.getElementById('cert-notice-box');
            const noticeIconBg = document.getElementById('cert-notice-icon-bg');
            const certValStatus = document.getElementById('cert-value-status');
            const certBannerBar = document.getElementById('cert-status-banner-bar');
            const certBannerText = document.getElementById('cert-banner-status-text');
            const certStatusCheck = document.getElementById('cert-status-check');

            if (isArchived) {
                if (badgeText) badgeText.innerText = t.badge_cancelled;
                if (statusBadge) statusBadge.className = "w-12 h-12 bg-[#DC2626] text-white rounded-xl flex flex-col items-center justify-center flex-shrink-0 shadow-sm";
                if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'alert-triangle');
                if (noticeBox) noticeBox.className = "flex items-center gap-4 p-2 text-left bg-rose-50/80 rounded-xl border border-rose-200";
                if (noticeIconBg) noticeIconBg.className = "w-14 h-14 bg-[#DC2626] text-white rounded-2xl flex items-center justify-center flex-shrink-0 shadow-md ring-4 ring-rose-500/20";
                if (certValStatus) certValStatus.className = "font-extrabold uppercase text-xs text-rose-600";
                if (certBannerBar) certBannerBar.className = "bg-[#DC2626] text-white p-3 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-sm";
                if (certBannerText) certBannerText.innerText = "REVOKED";
                if (certStatusCheck) {
                    certStatusCheck.setAttribute('data-lucide', 'x-circle');
                    certStatusCheck.className = "w-4 h-4 text-rose-600";
                }
            } else {
                if (badgeText) badgeText.innerText = t.badge_verified;
                if (statusBadge) statusBadge.className = "w-12 h-12 bg-[#16A34A] text-white rounded-xl flex flex-col items-center justify-center flex-shrink-0 shadow-sm";
                if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'check-circle-2');
                if (noticeBox) noticeBox.className = "flex items-center gap-4 p-2 text-left bg-emerald-50/70 rounded-xl border border-emerald-200/90";
                if (noticeIconBg) noticeIconBg.className = "w-14 h-14 bg-[#16A34A] text-white rounded-2xl flex items-center justify-center flex-shrink-0 shadow-md ring-4 ring-emerald-500/20";
                if (certValStatus) certValStatus.className = "font-extrabold uppercase text-xs text-[#16A34A]";
                if (certBannerBar) certBannerBar.className = "bg-[#16A34A] text-white p-3 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-2 shadow-sm";
                if (certBannerText) certBannerText.innerText = "VERIFIED";
                if (certStatusCheck) {
                    certStatusCheck.setAttribute('data-lucide', 'check-circle-2');
                    certStatusCheck.className = "w-4 h-4 text-[#16A34A]";
                }
            }

            // Highlight language button
            ['id', 'en', 'zh', 'ar'].forEach(l => {
                const btn = document.getElementById(`lang-${l}`);
                if (btn) {
                    if (l === currentLang) {
                        btn.className = "px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-[#1E3A8A] text-white shadow-xs";
                    } else {
                        btn.className = "px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-900";
                    }
                }
            });

            if (window.lucide) lucide.createIcons();
        }

        // Initialize UI language
        updateUILanguage();

        async function downloadPDF() {
            const btn = document.getElementById('btn-download-pdf');
            const element = document.getElementById('pdf-card');
            if (!element) return;

            const originalBtnHTML = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Menyiapkan E-Sertifikat PDF...</span>';
            }

            const docId = "{{ $document ? $document->document_id : 'doc' }}";
            const filename = 'E-Sertifikat_Verifikasi_IPPTI_' + docId + '.pdf';

            try {
                if (typeof html2pdf === 'undefined') {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                        script.crossOrigin = 'anonymous';
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                }

                const opt = {
                    margin:       [4, 4, 4, 4],
                    filename:     filename,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { 
                        scale: 2.5, 
                        useCORS: true, 
                        allowTaint: true,
                        logging: false,
                        windowWidth: 1024
                    },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
                };

                await html2pdf().set(opt).from(element).save();
            } catch (err) {
                console.error('Generasi PDF bermasalah:', err);
                window.print();
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHTML || '<i data-lucide="download" class="w-4.5 h-4.5 text-white"></i><span>Download E-Sertifikat PDF (A4)</span>';
                    if (window.lucide) lucide.createIcons();
                }
            }
        }
    </script>
</body>
</html>
