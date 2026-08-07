<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - E-Certificate Hasil Verifikasi Dokumen</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                    },
                    colors: {
                        brandPrimary: '#23408E',
                        brandGold: '#F59E0B',
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

        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm !important;
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
                border-radius: 0 !important;
            }
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body id="body-layout" class="bg-slate-100 text-slate-900 min-h-screen flex flex-col items-center justify-between p-4 sm:p-8 selection:bg-emerald-500 selection:text-slate-950">

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
        <div class="w-full max-w-2xl mb-6 text-center pt-2 no-print flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="inline-flex items-center gap-3">
                <a href="https://ippti.or.id" target="_blank" class="cursor-pointer">
                    <img src="/ippti-logo.jpg" alt="IPPTI Logo" crossorigin="anonymous" class="h-10 w-auto rounded-lg bg-white p-1 border border-slate-200 shadow-sm" />
                </a>
                <div class="text-left">
                    <a href="/" class="text-lg font-black text-[#23408E] tracking-tight hover:underline">DocVerify IPPTI</a>
                    <p id="sub-header-portal" class="text-[11px] text-slate-500 font-medium">Portal Verifikasi Resmi Terjemahan Tersumpah</p>
                </div>
            </div>

            <!-- Language Switcher Bar -->
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
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

        <!-- E-Certificate Card Container (A4 Printable Target) -->
        <div class="w-full max-w-2xl mb-6">
            <div id="pdf-card" class="bg-white p-3 sm:p-4 rounded-3xl border-4 border-[#23408E]/25 shadow-xl relative overflow-hidden font-sans text-slate-800">
                <!-- Inner Guilloche Double Ornamental Border -->
                <div class="border-2 border-[#23408E]/40 p-5 sm:p-7 rounded-2xl relative bg-white space-y-5">

                    <!-- Watermark Background Logo -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-[0.04] pointer-events-none z-0">
                        <img src="/ippti-logo.jpg" alt="Watermark" crossorigin="anonymous" class="w-80 h-auto grayscale" />
                    </div>

                    <div class="relative z-10 space-y-5">

                        <!-- Header Two-Column Layout (Logo Left, Title Right) -->
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 border-b-2 border-[#23408E]/20 pb-4">
                            <div class="flex items-center gap-3 text-center sm:text-left">
                                <img src="/ippti-logo.jpg" alt="IPPTI Logo" crossorigin="anonymous" class="h-14 sm:h-16 w-auto object-contain rounded-lg p-1 bg-white border border-slate-200 shadow-xs flex-shrink-0" />
                                <div>
                                    <h2 class="text-xs font-black tracking-wider text-[#23408E] uppercase leading-snug" id="cert-header-org">
                                        IKATAN PENERJEMAH & PENGALIH BAHASA TERSUMPAH DI INDONESIA (IPPTI)
                                    </h2>
                                    <p class="text-[9px] text-slate-500 font-semibold uppercase tracking-widest mt-0.5">NATIONAL SWORN TRANSLATION VERIFICATION</p>
                                </div>
                            </div>

                            <div class="text-center sm:text-right flex-shrink-0">
                                <span id="cert-status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs mb-1">
                                    <i id="cert-badge-icon" data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span id="cert-badge-text">VERIFIED</span>
                                </span>
                                <h1 class="text-lg sm:text-xl font-black text-[#23408E] tracking-tight leading-tight" id="cert-title">E-CERTIFICATE</h1>
                                <p class="text-[11px] font-bold text-amber-600 tracking-wider uppercase" id="cert-subtitle">HASIL VERIFIKASI</p>
                                <p class="text-[9px] text-slate-400 font-medium tracking-wide uppercase" id="cert-desc">DOKUMEN TERJEMAHAN TERSUMPAH</p>
                            </div>
                        </div>

                        <!-- Verification Notice Banner -->
                        <div id="cert-notice-box" class="bg-emerald-50/70 border border-emerald-200/90 p-3.5 rounded-xl flex items-start gap-3">
                            <div id="cert-notice-icon-bg" class="p-2 bg-emerald-600 text-white rounded-lg flex-shrink-0 mt-0.5">
                                <i id="cert-notice-icon" data-lucide="shield-check" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-xs sm:text-sm text-emerald-950 uppercase tracking-wide" id="cert-notice-title">DOKUMEN TELAH DIVERIFIKASI</h3>
                                <p class="text-[11px] text-emerald-900/90 leading-relaxed mt-0.5" id="cert-notice-desc">
                                    Dokumen terjemahan ini telah diverifikasi melalui sistem resmi IPPTI dan dinyatakan sebagai dokumen terjemahan tersumpah yang sah sesuai data registrasi.
                                </p>
                            </div>
                        </div>

                        <!-- Data Verifikasi Section (Grid Table Card) -->
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between border-b border-slate-200/80 pb-1.5">
                                <h4 class="text-xs font-black text-[#23408E] uppercase tracking-wider flex items-center gap-1.5" id="cert-data-title">
                                    <i data-lucide="database" class="w-3.5 h-3.5 text-amber-500"></i>
                                    DATA VERIFIKASI DOKUMEN
                                </h4>
                                <span class="text-[10px] font-mono font-bold text-slate-400">ID: {{ $document->document_id }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/60">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5" id="cert-label-reg-no">Nomor Registrasi</p>
                                    <p class="text-xs sm:text-sm font-black font-mono text-slate-900 notranslate" translate="no">{{ $document->registration_number }}</p>
                                </div>

                                <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/60">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5" id="cert-label-doc-id">ID Dokumen</p>
                                    <p class="text-xs sm:text-sm font-black font-mono text-emerald-700 notranslate" translate="no">{{ $document->document_id }}</p>
                                </div>

                                <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/60">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5" id="cert-label-trans-date">Tanggal Terjemah</p>
                                    <p class="text-xs font-extrabold text-slate-900 notranslate" translate="no" id="cert-value-trans-date"></p>
                                </div>

                                <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/60">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5" id="cert-label-status">Status Verifikasi</p>
                                    <p class="text-xs font-extrabold" id="cert-value-status"></p>
                                </div>

                                <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/60">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5" id="cert-label-doc-type">Jenis Dokumen</p>
                                    <p class="text-xs font-extrabold text-slate-900">{{ $document->document_type }}</p>
                                </div>

                                <div class="p-2.5 bg-slate-50/90 rounded-xl border border-slate-200/60">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5" id="cert-label-lang-pair">Pasangan Bahasa</p>
                                    <span class="inline-block text-xs font-black text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200 notranslate" translate="no">{{ $document->language_pair }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Document Owner Box (Masked Client Name) -->
                        <div class="p-3 bg-slate-900 text-white rounded-xl border border-slate-800 space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" id="cert-label-masked-name">NAMA DI DOKUMEN (DISAMARKAN)</p>
                            @php
                                $masked = '';
                                if ($document->client_name) {
                                    $masked = implode(" ", array_map(function($word) {
                                        if (strlen($word) <= 1) return $word;
                                        return $word[0] . str_repeat("*", strlen($word) - 1);
                                    }, explode(" ", $document->client_name)));
                                }
                            @endphp
                            <p class="text-xs sm:text-sm font-black font-mono tracking-wider text-emerald-400 text-center uppercase notranslate" translate="no">{{ $masked }}</p>
                        </div>

                        <!-- Translator Section -->
                        <div class="p-3.5 bg-slate-50/90 rounded-2xl border border-slate-200/70 space-y-2.5">
                            <p class="text-[10px] font-bold text-[#23408E] uppercase tracking-wider flex items-center gap-1.5" id="cert-label-translator-title">
                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-[#23408E]"></i>
                                PENERJEMAH TERSUMPAH
                            </p>

                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-full bg-white flex items-center justify-center flex-shrink-0 border-2 border-[#23408E]/20 shadow-xs overflow-hidden">
                                    @if($document->translator->profile_picture)
                                        <img src="{{ $document->translator->profile_picture }}" alt="{{ $document->translator->name }}" crossorigin="anonymous" class="w-full h-full object-cover" />
                                    @else
                                        <i data-lucide="award" class="w-5 h-5 text-[#23408E]"></i>
                                    @endif
                                </div>
                                <div class="space-y-0.5 overflow-hidden">
                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-950 notranslate" translate="no">{{ $document->translator->name }}</h4>
                                    <p class="text-[11px] font-mono text-slate-600 notranslate" translate="no" id="cert-value-member-no">No. Anggota: {{ $document->translator->sk_number }}</p>
                                    @if($document->translator->no_sk_kemenkum)
                                        <p class="text-[10px] text-slate-500 notranslate" translate="no">SK Kemenkumham: {{ $document->translator->no_sk_kemenkum }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Verification Box (QR Code & Timestamp) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center p-3 bg-slate-50/90 rounded-2xl border border-slate-200/60">
                            <div class="sm:col-span-1 flex flex-col items-center text-center space-y-1">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode(url('/verify/' . $document->document_id)) }}" alt="QR" crossorigin="anonymous" class="w-18 h-18 sm:w-20 sm:h-20 bg-white p-1 rounded-xl border border-slate-300 shadow-xs" />
                                <span class="text-[9px] font-bold text-slate-600 font-mono" id="cert-qr-title">QR VERIFICATION</span>
                            </div>
                            <div class="sm:col-span-2 space-y-1 text-left">
                                <p class="text-xs font-bold text-slate-800" id="cert-qr-caption">Scan kode QR di atas untuk verifikasi langsung ke database nasional IPPTI.</p>
                                <p class="text-[10px] text-slate-500 font-mono" id="cert-verify-time">Waktu Verifikasi: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
                                <p class="text-[9px] text-[#23408E] font-mono underline truncate notranslate" translate="no">{{ url('/verify/' . $document->document_id) }}</p>
                            </div>
                        </div>

                        <!-- Official Disclaimer -->
                        <div class="p-3 bg-amber-50/60 border-l-4 border-amber-500 rounded-r-xl space-y-0.5">
                            <h5 class="text-[10px] font-extrabold text-amber-900 uppercase tracking-wider" id="cert-disclaimer-title">DISCLAIMER RESMI</h5>
                            <p class="text-[10px] sm:text-[11px] text-amber-900/90 leading-relaxed font-medium" id="cert-disclaimer-text">
                                Sertifikat elektronik ini hanya menyatakan bahwa data dokumen terjemahan telah diverifikasi pada sistem IPPTI. Keabsahan hukum dokumen fisik tetap mengacu pada cap basah penerjemah tersumpah serta ketentuan peraturan perundang-undangan yang berlaku.
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="pt-3 border-t border-slate-200 text-center space-y-1 text-[9px] text-slate-500">
                            <p class="font-bold text-slate-700 uppercase" id="cert-footer-org-bottom">IKATAN PENERJEMAH DAN PENGALIH BAHASA TERSUMPAH DI INDONESIA (IPPTI)</p>
                            <p class="font-mono">Menara Caraka, Lt. 6 / 625, Jl. Mega Kuningan Barat Blok E.4.7 No. 1, Setiabudi, Jakarta Selatan 12950</p>
                            <p class="font-mono">Website: ippti.or.id | Email: info@ippti.or.id | Hotline: +62 811 8117 0118</p>
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
                badge_verified: "TERVERIFIKASI",
                badge_cancelled: "DIBATALKAN",
                notice_title: isArchived ? "DOKUMEN TELAH DIBATALKAN" : "DOKUMEN TELAH DIVERIFIKASI",
                notice_desc: isArchived 
                    ? "Dokumen terjemahan ini telah dicabut/dibatalkan oleh penerjemah yang bersangkutan dan tidak lagi berlaku."
                    : "Dokumen terjemahan ini telah diverifikasi melalui sistem resmi IPPTI dan dinyatakan sebagai dokumen terjemahan tersumpah yang sah sesuai data registrasi.",
                data_title: "DATA VERIFIKASI DOKUMEN",
                reg_no: "Nomor Registrasi",
                doc_id: "ID Dokumen",
                status: "Status Verifikasi",
                status_val: isArchived ? "Dibatalkan — Hubungi Penerjemah" : "Terverifikasi / Asli (Verified)",
                trans_date: "Tanggal Terjemah",
                doc_type: "Jenis Dokumen",
                lang_pair: "Pasangan Bahasa",
                masked_name: "NAMA DI DOKUMEN (DISAMARKAN)",
                translator_title: "PENERJEMAH TERSUMPAH",
                member_no: "No. Anggota / SK: " + memberNo,
                qr_title: "VERIFIKASI QR CODE",
                qr_caption: "Scan kode QR di atas untuk verifikasi langsung ke database nasional IPPTI.",
                verify_time: "Waktu Verifikasi: {{ now()->translatedFormat('d F Y H:i') }} WIB",
                disclaimer_title: "DISCLAIMER RESMI",
                disclaimer_text: "Sertifikat elektronik ini hanya menyatakan bahwa data dokumen terjemahan telah diverifikasi pada sistem IPPTI. Keabsahan hukum dokumen fisik tetap mengacu pada cap basah penerjemah tersumpah serta ketentuan peraturan perundang-undangan yang berlaku.",
                footer_org: "IKATAN PENERJEMAH DAN PENGALIH BAHASA TERSUMPAH DI INDONESIA (IPPTI)",
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
                    : "This translated document has been officially verified through the IPPTI Verification System and declared as a valid sworn translation according to registry data.",
                data_title: "DOCUMENT VERIFICATION DATA",
                reg_no: "Registration Number",
                doc_id: "Document ID",
                status: "Verification Status",
                status_val: isArchived ? "Cancelled — Contact Sworn Translator" : "Verified / Authentic",
                trans_date: "Translation Date",
                doc_type: "Document Type",
                lang_pair: "Language Pair",
                masked_name: "NAME ON DOCUMENT (MASKED)",
                translator_title: "SWORN TRANSLATOR",
                member_no: "Member ID / SK No: " + memberNo,
                qr_title: "QR VERIFICATION",
                qr_caption: "Scan QR code above for instant verification on IPPTI national database.",
                verify_time: "Verification Time: {{ now()->format('F d, Y H:i') }} UTC+7",
                disclaimer_title: "OFFICIAL DISCLAIMER",
                disclaimer_text: "This e-certificate confirms that translation document data has been verified in the IPPTI system. Legal validity of physical documents relies on the wet stamp of the sworn translator and applicable laws.",
                footer_org: "ASSOCIATION OF SWORN TRANSLATORS AND INTERPRETERS IN INDONESIA (IPPTI)",
                bottom_credit: isArchived ? "DOCUMENT STATUS: REVOKED" : "ELECTRONICALLY & CRYPTOGRAPHICALLY VERIFIED",
                sub_portal: "Official Sworn Translation Verification Portal",
            },
            zh: {
                cert_title: "电子证书 (E-CERTIFICATE)",
                cert_subtitle: "验证结果",
                cert_desc: "宣誓翻译文件",
                badge_verified: "已验证 (VERIFIED)",
                badge_cancelled: "已撤销 (REVOKED)",
                notice_title: isArchived ? "文件已被撤销 / 取消" : "文件已正式通过验证",
                notice_desc: isArchived 
                    ? "此文件的翻译注册已被相关翻译员撤销或取消，不再有效。"
                    : "此翻译文件已通过 IPPTI 官方系统验证，根据注册数据声明为有效的宣誓翻译文件。",
                data_title: "文件验证数据",
                reg_no: "注册号",
                doc_id: "文件 ID",
                status: "验证状态",
                status_val: isArchived ? "已取消 — 请联系翻译员" : "已验证 / 真实 (Verified)",
                trans_date: "翻译日期",
                doc_type: "文件类型",
                lang_pair: "语言对",
                masked_name: "文件姓名（已遮蔽）",
                translator_title: "宣誓翻译员",
                member_no: "成员编号 / 决定号: " + memberNo,
                qr_title: "二维码验证",
                qr_caption: "扫描上方二维码在 IPPTI 系统上立即验证文件真实性。",
                verify_time: "验证时间: {{ now()->format('Y-m-d H:i') }}",
                disclaimer_title: "官方免责声明",
                disclaimer_text: "此电子证书仅表明翻译文件数据已在 IPPTI 系统中验证。纸质文件的法律效力仍取决于宣誓翻译员的湿盖章及适用法律法规。",
                footer_org: "印度尼西亚宣誓翻译员与口译员协会 (IPPTI)",
                bottom_credit: isArchived ? "文件状态：已撤销" : "经过电子与密码学验证",
                sub_portal: "官方宣誓翻译验证门户",
            },
            ar: {
                cert_title: "شهادة إلكترونية",
                cert_subtitle: "نتيجة التحقق",
                cert_desc: "مستند مترجم محلف",
                badge_verified: "مُتحقّق (VERIFIED)",
                badge_cancelled: "ملغى (REVOKED)",
                notice_title: isArchived ? "تم إلغاء المستند" : "تم التحقق من المستند رسمياً",
                notice_desc: isArchived 
                    ? "تم إلغاء تسجيل ترجمة هذا المستند بواسطة المترجم المعني ولم يعد صالحاً."
                    : "تم التحقق من هذا المستند المترجم رسمياً من خلال نظام IPPTI وإعلانه كترجمة محلفة صالحة وفقاً لبيانات التسجيل.",
                data_title: "بيانات التحقق من المستند",
                reg_no: "رقم التسجيل",
                doc_id: "معرف المستند",
                status: "حالة التحقق",
                status_val: isArchived ? "ملغى — اتصل بالمترجم" : "تم التحقق منه / أصلي (Verified)",
                trans_date: "تاريخ الترجمة",
                doc_type: "نوع المستند",
                lang_pair: "زوج اللغات",
                masked_name: "الاسم على المستند (مخفي)",
                translator_title: "المترجم المحلف",
                member_no: "رقم العضوية / القرار: " + memberNo,
                qr_title: "التحقق عبر رمز QR",
                qr_caption: "امسح رمز QR أعلاه للتحقق من المستند فوراً في قاعدة بيانات IPPTI.",
                verify_time: "وقت التحقق: {{ now()->format('Y-m-d H:i') }}",
                disclaimer_title: "إخلاء مسؤولية رسمي",
                disclaimer_text: "تؤكد هذه الشهادة الإلكترونية فقط أنه تم التحقق من بيانات المستند المترجم في نظام IPPTI. تعتمد الصلاحية القانونية للمستند المادي على الختم المائي للمترجم والقوانين المعمول بها.",
                footer_org: "جمعية المترجمين المحلفين والمترجمين الفوريين في إندونيسيا (IPPTI)",
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
                'cert-qr-title': t.qr_title,
                'cert-qr-caption': t.qr_caption,
                'cert-verify-time': t.verify_time,
                'cert-disclaimer-title': t.disclaimer_title,
                'cert-disclaimer-text': t.disclaimer_text,
                'cert-footer-org-bottom': t.footer_org,
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

            if (isArchived) {
                if (badgeText) badgeText.innerText = t.badge_cancelled;
                if (statusBadge) statusBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300 shadow-xs mb-1";
                if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'alert-triangle');
                if (noticeBox) noticeBox.className = "bg-rose-50/80 border border-rose-200/90 p-3.5 rounded-xl flex items-start gap-3";
                if (noticeIconBg) noticeIconBg.className = "p-2 bg-rose-600 text-white rounded-lg flex-shrink-0 mt-0.5";
                if (certValStatus) certValStatus.className = "text-xs font-extrabold text-rose-600";
            } else {
                if (badgeText) badgeText.innerText = t.badge_verified;
                if (statusBadge) statusBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs mb-1";
                if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'shield-check');
                if (noticeBox) noticeBox.className = "bg-emerald-50/70 border border-emerald-200/90 p-3.5 rounded-xl flex items-start gap-3";
                if (noticeIconBg) noticeIconBg.className = "p-2 bg-emerald-600 text-white rounded-lg flex-shrink-0 mt-0.5";
                if (certValStatus) certValStatus.className = "text-xs font-extrabold text-emerald-700";
            }

            // Highlight language button
            ['id', 'en', 'zh', 'ar'].forEach(l => {
                const btn = document.getElementById(`lang-${l}`);
                if (btn) {
                    if (l === currentLang) {
                        btn.className = "px-2.5 py-0.5 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-[#23408E] text-white shadow-xs";
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
                    margin:       [6, 6, 6, 6],
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
