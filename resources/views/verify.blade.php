<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - Verifikasi Resmi Dokumen</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
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
                size: auto;
                margin: 8mm 12mm !important;
            }
            body {
                background: white !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
            }
            .no-print {
                display: none !important;
            }
            #body-layout {
                padding: 0 !important;
                background: white !important;
                display: block !important;
            }
            
            /* Compact card container */
            .max-w-lg {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                max-width: 145mm !important; /* Scale to A4/Letter print width */
                margin: 4mm auto !important; /* Small margin */
                padding: 0 !important;
                border-radius: 16px !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
            }

            /* Shrink card padding */
            .p-6, .p-8 {
                padding: 1rem !important;
            }
            .p-5 {
                padding: 0.75rem !important;
            }
            .pb-6 {
                padding-bottom: 0.75rem !important;
            }
            .space-y-6 > :not([hidden]) ~ :not([hidden]) {
                margin-top: 0.75rem !important;
            }
            .gap-y-6 {
                gap-y: 0.75rem !important;
            }

            /* Shrink gradient banner */
            #verified-banner {
                background: linear-gradient(135deg, #059669, #0f766e) !important;
                color: white !important;
                padding: 1.25rem 1rem !important;
            }

            #verified-icon-wrapper {
                width: 3rem !important;
                height: 3rem !important;
                margin-bottom: 0.5rem !important;
            }

            #verified-icon-wrapper svg, #verified-icon-wrapper i {
                width: 1.75rem !important;
                height: 1.75rem !important;
            }

            .text-2xl {
                font-size: 1.25rem !important;
            }

            /* Shrink translator section box */
            .w-12.h-12 {
                width: 2.25rem !important;
                height: 2.25rem !important;
            }

            /* Adjust bottom margin */
            .mt-8 {
                margin-top: 0.5rem !important;
            }
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body id="body-layout" class="bg-slate-50 text-slate-900 min-h-screen flex flex-col items-center justify-between p-4 sm:p-8 selection:bg-emerald-500 selection:text-slate-950">

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
        <!-- Header -->
        <div class="w-full max-w-lg mb-8 text-center pt-4 no-print">
            <div class="inline-flex items-center justify-center gap-3 mb-2">
                <a href="https://ippti.or.id" target="_blank" class="cursor-pointer">
                    <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-sm" />
                </a>
                <a href="/" class="text-xl font-bold text-slate-900 tracking-tight hover:underline">DocVerify</a>
            </div>
            <p id="sub-header-portal" class="text-xs text-slate-500 font-medium">Portal Verifikasi Resmi Terjemahan Tersumpah</p>
        </div>

        <!-- Verification Card -->
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-8 relative">
            <!-- Language Selector inside card -->
            <div class="px-6 py-3.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between text-white no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                    <span id="label-credentials" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest font-mono">
                        Document Credentials
                    </span>
                </div>
                <div class="flex bg-slate-800 p-0.5 rounded-lg border border-slate-700 dir-ltr" dir="ltr">
                    <button onclick="changeLanguage('id')" id="lang-id" class="px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">ID</button>
                    <button onclick="changeLanguage('en')" id="lang-en" class="px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">EN</button>
                    <button onclick="changeLanguage('zh')" id="lang-zh" class="px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">ZH</button>
                    <button onclick="changeLanguage('ar')" id="lang-ar" class="px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">AR</button>
                </div>
            </div>

            <div id="pdf-card" class="bg-white">
                <!-- Header Banner -->
                <div id="verified-banner" class="bg-gradient-to-br from-emerald-600 via-teal-700 to-emerald-800 p-8 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                    <div class="absolute inset-0 bg-gradient-to-b from-white/10 to-transparent"></div>
                    
                    <!-- IPPTI Logo inside the certificate card (REV-05, Print Friendly) -->
                    <div class="absolute top-4 right-4 flex items-center gap-2 bg-white/10 backdrop-blur px-2.5 py-1 rounded-lg border border-white/20">
                        <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-6 w-auto rounded bg-white p-0.5 object-contain" />
                        <span class="text-[9px] font-black text-white tracking-widest">IPPTI</span>
                    </div>
                    
                    <div class="relative z-10 space-y-3">
                        <div id="verified-icon-wrapper" class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-emerald-500/30 animate-pulse">
                            <i id="icon-active" data-lucide="check-circle-2" class="w-10 h-10 text-emerald-600"></i>
                            <i id="icon-archived" data-lucide="alert-triangle" class="w-10 h-10 text-rose-600 hidden"></i>
                        </div>
                        <div>
                            <h1 id="label-verified-title" class="text-2xl font-black text-white tracking-widest">TERVERIFIKASI</h1>
                            <p id="label-verified-sub" class="text-xs text-emerald-100 font-semibold tracking-wider uppercase mt-0.5">Rekam Dokumen Resmi IPPTI</p>
                        </div>
                    </div>
                </div>

                <!-- Details list -->
                <div class="p-6 sm:p-8 space-y-6 text-left">
                    <div id="section-meta-row" class="border-b border-slate-100 pb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p id="label-reg-no" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">No. Registrasi</p>
                            <p class="text-lg font-black text-slate-950 font-mono notranslate" translate="no">{{ $document->registration_number }}</p>
                        </div>
                        <div>
                            <p id="label-doc-id" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">ID Dokumen</p>
                            <p class="text-lg font-black text-emerald-700 font-mono notranslate" translate="no">{{ $document->document_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-4">
                        <div>
                            <p id="label-date" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Terjemah</p>
                            <p id="value-date" class="text-base font-extrabold text-slate-950 notranslate" translate="no"></p>
                        </div>

                        <div>
                            <p id="label-status" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status Verifikasi</p>
                            <div>
                                <span id="status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-sm notranslate" translate="no">
                                    <span id="status-dot" class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span id="value-status"></span>
                                </span>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <p id="label-client" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama di Dokumen (Disamarkan)</p>
                            <p class="text-base font-mono font-black text-slate-950 mt-1 bg-slate-50 border border-slate-200/80 p-3 rounded-xl select-none tracking-wide text-center notranslate" translate="no">
                                @php
                                    $masked = '';
                                    if ($document->client_name) {
                                        $masked = implode(" ", array_map(function($word) {
                                            if (strlen($word) <= 1) return $word;
                                            return $word[0] . str_repeat("*", strlen($word) - 1);
                                        }, explode(" ", $document->client_name)));
                                    }
                                @endphp
                                {{ $masked }}
                            </p>
                        </div>

                        <div class="sm:col-span-1 bg-slate-50 rounded-2xl p-4 border border-slate-200/60 flex flex-col justify-between">
                            <p id="label-pair" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pasangan Bahasa</p>
                            <div class="flex items-center">
                                <span class="text-xs font-bold text-slate-950 bg-white px-3 py-1.5 rounded-lg border border-slate-200/80 shadow-sm notranslate" translate="no">{{ $document->language_pair }}</span>
                            </div>
                        </div>

                        <div class="sm:col-span-1 bg-slate-50 rounded-2xl p-4 border border-slate-200/60 flex flex-col justify-between">
                            <p id="label-type" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tipe Dokumen</p>
                            <p class="text-sm font-extrabold text-slate-950 mt-1">{{ $document->document_type }}</p>
                        </div>

                        <!-- Combined Sworn Translator & QR Code Row -->
                        <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-slate-100 pt-6">
                            <!-- Translator Badge Box -->
                            <div class="sm:col-span-2 bg-slate-50/60 border border-slate-100 rounded-2xl p-4 flex flex-col justify-between gap-3">
                                <p id="label-translator" class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Penerjemah Tersumpah</p>
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0 border border-emerald-100 shadow-sm overflow-hidden">
                                        @if($document->translator->profile_picture)
                                            <img src="{{ $document->translator->profile_picture }}" alt="{{ $document->translator->name }}" class="w-full h-full object-cover" />
                                        @else
                                            <i data-lucide="file-text" class="w-5 h-5 text-emerald-600"></i>
                                        @endif
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-sm font-bold text-slate-950 truncate notranslate" translate="no">{{ $document->translator->name }}</p>
                                        <p id="label-translator-member" class="text-[10px] text-slate-650 font-mono mt-0.5 notranslate" translate="no"></p>
                                    </div>
                                </div>
                                @if($document->translator->bio)
                                    <div class="text-[10px] border-t border-slate-100/85 pt-2">
                                        <p class="text-slate-600 leading-relaxed italic">"{{ $document->translator->bio }}"</p>
                                    </div>
                                @endif
                            </div>

                            <!-- QR Code (spans 1 column) -->
                            <div class="bg-slate-50/60 border border-slate-100 rounded-2xl p-3 flex flex-col items-center justify-center gap-2">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(url('/verify/' . $document->document_id)) }}" alt="QR" class="w-20 h-20 bg-white p-1 rounded-lg border border-slate-200 shadow-sm flex-shrink-0" />
                                <span class="text-[9px] font-bold text-emerald-700 font-mono tracking-wider notranslate" translate="no">{{ $document->document_id }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secure Disclaimer box -->
                <div class="bg-amber-50/40 p-6 border-t border-slate-100 border-l-4 border-l-amber-500 text-left">
                    <div class="flex items-start gap-3">
                        <p id="label-disclaimer" class="text-xs text-slate-700 leading-relaxed text-justify font-medium"></p>
                    </div>
                </div>

                <!-- Official IPPTI Footer (Address & Contact Info) -->
                <div class="border-t border-slate-150 bg-slate-50/70 px-6 py-4 text-center space-y-2">
                    <div class="flex items-center justify-center gap-2">
                        <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-5 w-auto rounded bg-white p-0.5 object-contain" />
                        <span class="text-[9px] font-extrabold text-slate-800 tracking-wider">IKATAN PENERJEMAH & PENGALIH BAHASA TERSUMPAH DI INDONESIA (IPPTI)</span>
                    </div>
                    <p class="text-[8px] text-slate-600 font-medium leading-relaxed max-w-sm mx-auto notranslate" translate="no">
                        Menara Caraka, Lt. 6 / 625, Jl. Mega Kuningan Barat Blok E.4.7 No. 1, Setiabudi, Jakarta Selatan 12950
                    </p>
                    <div class="flex items-center justify-center gap-4 text-[8px] text-slate-600 font-bold notranslate" translate="no">
                        <span class="flex items-center gap-1"><i data-lucide="mail" class="w-2.5 h-2.5 text-slate-500"></i> info@ippti.or.id</span>
                        <span class="flex items-center gap-1"><i data-lucide="phone" class="w-2.5 h-2.5 text-slate-500"></i> +62 811-8117-0118</span>
                        <span class="flex items-center gap-1"><i data-lucide="globe" class="w-2.5 h-2.5 text-slate-500"></i> ippti.or.id</span>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Action Buttons (no-print) (REV-15, REV-21) -->
        <div class="flex flex-col sm:flex-row gap-4 w-full max-w-lg no-print mb-8">
            <button onclick="downloadPDF()" class="flex-1 flex items-center justify-center gap-2 py-3 px-5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold transition shadow-sm cursor-pointer active:scale-[0.98]">
                <i data-lucide="download" class="w-4.5 h-4.5 text-white"></i>
                <span>Download PDF</span>
            </button>
            <a href="/" class="flex-1 flex items-center justify-center gap-2 py-3 px-5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold transition shadow-sm cursor-pointer active:scale-[0.98]">
                <i data-lucide="search" class="w-4.5 h-4.5 text-slate-400"></i>
                <span>Verifikasi Lainnya</span>
            </a>
        </div>
    @endif

    <!-- Bottom Credit -->
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

        const translations = {
            id: {
                credentials: "Document Credentials",
                verified: isArchived ? "DIBATALKAN" : "TERVERIFIKASI",
                record: isArchived ? "Dokumen Telah Dicabut / Dibatalkan" : "Rekam Dokumen Resmi IPPTI",
                doc_id: "ID Dokumen",
                reg_no: "No. Registrasi",
                trans_date: "Tanggal Terjemah",
                status: "Status Verifikasi",
                status_val: isArchived ? "Dibatalkan — Hubungi Penerjemah" : "Terverifikasi / Asli",
                masked_name: "Nama di Dokumen (Disamarkan)",
                lang_pair: "Pasangan Bahasa",
                doc_type: "Tipe Dokumen",
                translator: "Nama Penerjemah Tersumpah",
                member_id: "No. Anggota: " + memberNo,
                services: "Layanan Bahasa:",
                bio: "Biografi:",
                disclaimer: isArchived
                    ? `<strong class="text-rose-800 font-bold">Peringatan Penting:</strong> Dokumen terjemahan dengan nomor registrasi ini telah dicabut atau dibatalkan oleh penerjemah yang bersangkutan. Dokumen ini tidak lagi berlaku untuk keperluan resmi.`
                    : `<strong class="text-slate-800 font-bold">Disclaimer Resmi:</strong> Sistem ini memverifikasi bahwa terjemahan dokumen ini telah resmi terdaftar oleh Penerjemah Tersumpah yang terasosiasi di atas. Harap pastikan fisik dokumen memiliki cap basah/segel pengaman yang sesuai untuk validitas hukum sepenuhnya.`,
                bottom_credit: isArchived ? "STATUS DOKUMEN: DIBATALKAN" : "DIVERIFIKASI SECARA ELEKTRONIK & KRIPTOGRAFIS",
                sub_portal: "Portal Verifikasi Resmi Terjemahan Tersumpah",
                qr_title: "QR Code Verifikasi Resmi",
                qr_desc: "Pindai kode QR ini dengan kamera ponsel untuk memverifikasi dokumen secara instan dan aman pada sistem database nasional IPPTI."
            },
            en: {
                credentials: "Document Credentials",
                verified: isArchived ? "CANCELLED" : "VERIFIED",
                record: isArchived ? "Document Registration Revoked / Cancelled" : "IPPTI Official Document Record",
                doc_id: "Document ID",
                reg_no: "Registration No.",
                trans_date: "Translation Date",
                status: "Verification Status",
                status_val: isArchived ? "Cancelled — Contact Sworn Translator" : "Verified / Authentic",
                masked_name: "Name on Document (Masked)",
                lang_pair: "Language Pair",
                doc_type: "Document Type",
                translator: "Name of Sworn Translator",
                member_id: "Member ID: " + memberNo,
                services: "Language Services:",
                bio: "Biography:",
                disclaimer: isArchived
                    ? `<strong class="text-rose-800 font-bold">Important Warning:</strong> The registration of this document has been revoked or cancelled by the respective translator. This document is no longer valid for official purposes.`
                    : `<strong class="text-slate-800 font-bold">Official Disclaimer:</strong> This system verifies that the translation of this document has been officially registered by the sworn translator associated above. Please ensure that the physical document bears the appropriate wet stamp or security seal for full legal validity.`,
                bottom_credit: isArchived ? "DOCUMENT STATUS: REVOKED / CANCELLED" : "ELECTRONICALLY & CRYPTOGRAPHICALLY VERIFIED",
                sub_portal: "Official Sworn Translation Verification Portal",
                qr_title: "Official Verification QR Code",
                qr_desc: "Scan this QR code with your mobile camera to verify the document instantly and securely on the IPPTI national database system."
            },
            zh: {
                credentials: "文件凭证",
                verified: isArchived ? "已取消" : "已验证",
                record: isArchived ? "文件注册已撤销 / 已取消" : "IPPTI 官方文件记录",
                doc_id: "文件 ID",
                reg_no: "注册号",
                trans_date: "翻译日期",
                status: "验证状态",
                status_val: isArchived ? "已取消 — 请联系翻译员" : "已验证 / 真实",
                masked_name: "文件姓名（已遮蔽）",
                lang_pair: "语言对",
                doc_type: "文件类型",
                translator: "宣誓翻译员姓名",
                member_id: "成员 ID: " + memberNo,
                services: "语言服务:",
                bio: "个人简介:",
                disclaimer: isArchived
                    ? `<strong class="text-rose-800 font-bold">重要警示:</strong> 此文件的翻译注册已被相关翻译员撤销或取消。此文件在官方用途上不再有效。`
                    : `<strong class="text-slate-800 font-bold">官方免责声明:</strong> 此系统验证此文件的翻译已由上述关联的宣誓翻译员正式注册。请确保纸质文件上有相应的湿盖章或安全封条，以具备完全的法律效力。`,
                bottom_credit: isArchived ? "文件状态：已取消" : "经过电子与密码学验证",
                sub_portal: "官方宣誓翻译验证门户",
                qr_title: "官方验证二维码",
                qr_desc: "使用手机摄像头扫描此二维码，即可在 IPPTI 国家数据库系统上立即安全地验证该文件。"
            },
            ar: {
                credentials: "وثائق المستند",
                verified: isArchived ? "ملغى" : "تم التحقق",
                record: isArchived ? "تم إلغاء / سحب تسجيل المستند" : "سجل المستندات الرسمي لـ IPPTI",
                doc_id: "معرف المستند",
                reg_no: "رقم التسجيل",
                trans_date: "تاريخ الترجمة",
                status: "حالة التحقق",
                status_val: isArchived ? "ملغى — اتصل بالمترجم" : "تم التحقق منه / أصلي",
                masked_name: "الاسم على المستند (مخفي)",
                lang_pair: "زوج اللغات",
                doc_type: "نوع المستند",
                translator: "اسم المترجم المحلف",
                member_id: "رقم العضوية: " + memberNo,
                services: "خدمات اللغة:",
                bio: "السيرة الذاتية:",
                disclaimer: isArchived
                    ? `<strong class="text-rose-800 font-bold">تحذير مهم:</strong> تم إلغاء أو سحب تسجيل ترجمة هذا المستند بواسطة المترجم المعني. لم يعد هذا المستند صالحاً للأغراض الرسمية.`
                    : `<strong class="text-slate-800 font-bold">إخلاء مسؤولية رسمي:</strong> يتحقق هذا النظام من أن ترجمة هذا المستند قد تم تسجيلها رسمياً بواسطة المترجم المحلف المرتبط أعلاه. يرجى التأكد من أن المستند المادي يحمل الختم المائي أو الختم الأمني المناسب للصلاحية القانونية الكاملة.`,
                bottom_credit: isArchived ? "حالة المستند: ملغى" : "تم التحقق منه إلكترونياً وتشفيرياً",
                sub_portal: "البوابة الرسمية للتحقق من الترجمة المحلفة",
                qr_title: "رمز الاستجابة السريعة للتحقق الرسمي",
                qr_desc: "امسح رمز الاستجابة السريعة (QR) هذا بكاميرا الهاتف للتحقق من المستند فوراً وبأمان على نظام قاعدة البيانات الوطنية لـ IPPTI."
            }
        };

        let currentLang = localStorage.getItem('docverify_lang') || 'id';

        function changeLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('docverify_lang', lang);
            updateUILanguage();
        }

        function updateUILanguage() {
            const t = translations[currentLang];
            const isRtl = currentLang === 'ar';

            // Set layout direction
            const bodyLayout = document.getElementById('body-layout');
            bodyLayout.dir = isRtl ? 'rtl' : 'ltr';

            // Update text nodes
            const labelVerifiedTitle = document.getElementById('label-verified-title');
            const labelVerifiedSub = document.getElementById('label-verified-sub');
            const labelDocId = document.getElementById('label-doc-id');
            const labelRegNo = document.getElementById('label-reg-no');
            const labelDate = document.getElementById('label-date');
            const valueDate = document.getElementById('value-date');
            const labelStatus = document.getElementById('label-status');
            const valueStatus = document.getElementById('value-status');
            const labelClient = document.getElementById('label-client');
            const labelPair = document.getElementById('label-pair');
            const labelType = document.getElementById('label-type');
            const labelTranslator = document.getElementById('label-translator');
            const labelTranslatorMember = document.getElementById('label-translator-member');
            const labelServices = document.getElementById('label-services');
            const labelBio = document.getElementById('label-bio');
            const labelDisclaimer = document.getElementById('label-disclaimer');
            const labelBottomCredit = document.getElementById('label-bottom-credit');
            const subPortal = document.getElementById('sub-header-portal');
            const labelCredentials = document.getElementById('label-credentials');
            const labelQrTitle = document.getElementById('label-qr-title');
            const labelQrDesc = document.getElementById('label-qr-desc');

            if (subPortal) subPortal.innerText = t.sub_portal;
            if (labelCredentials) labelCredentials.innerText = t.credentials;
            if (labelVerifiedTitle) labelVerifiedTitle.innerText = t.verified;
            if (labelVerifiedSub) labelVerifiedSub.innerText = t.record;
            if (labelDocId) labelDocId.innerText = t.doc_id;
            if (labelRegNo) labelRegNo.innerText = t.reg_no;
            if (labelDate) labelDate.innerText = t.trans_date;
            if (valueDate) valueDate.innerText = currentLang === 'id' ? dateId : dateEn;
            if (labelStatus) labelStatus.innerText = t.status;
            if (valueStatus) valueStatus.innerText = t.status_val;
            if (labelClient) labelClient.innerText = t.masked_name;
            if (labelPair) labelPair.innerText = t.lang_pair;
            if (labelType) labelType.innerText = t.doc_type;
            if (labelTranslator) labelTranslator.innerText = t.translator;
            if (labelTranslatorMember) labelTranslatorMember.innerText = t.member_id;
            if (labelServices) labelServices.innerText = t.services;
            if (labelBio) labelBio.innerText = t.bio;
            if (labelDisclaimer) labelDisclaimer.innerHTML = t.disclaimer;
            if (labelBottomCredit) labelBottomCredit.innerText = t.bottom_credit;
            if (labelQrTitle) labelQrTitle.innerText = t.qr_title;
            if (labelQrDesc) labelQrDesc.innerText = t.qr_desc;

            // Apply status badge classes dynamically (REV-20)
            const statusBadge = document.getElementById('status-badge');
            const statusDot = document.getElementById('status-dot');
            const verifiedBanner = document.getElementById('verified-banner');
            const iconActive = document.getElementById('icon-active');
            const iconArchived = document.getElementById('icon-archived');

            if (isArchived) {
                if (statusBadge) {
                    statusBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100 shadow-sm";
                }
                if (statusDot) {
                    statusDot.className = "w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse";
                }
                if (verifiedBanner) {
                    verifiedBanner.className = "bg-gradient-to-br from-rose-600 via-red-700 to-rose-800 p-8 text-center relative overflow-hidden";
                }
                if (iconActive) iconActive.classList.add('hidden');
                if (iconArchived) iconArchived.classList.remove('hidden');
            } else {
                if (statusBadge) {
                    statusBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm";
                }
                if (statusDot) {
                    statusDot.className = "w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse";
                }
                if (verifiedBanner) {
                    verifiedBanner.className = "bg-gradient-to-br from-emerald-600 via-teal-700 to-emerald-800 p-8 text-center relative overflow-hidden";
                }
                if (iconActive) iconActive.classList.remove('hidden');
                if (iconArchived) iconArchived.classList.add('hidden');
            }

            // Highlight language button
            ['id', 'en', 'zh', 'ar'].forEach(l => {
                const btn = document.getElementById(`lang-${l}`);
                if (btn) {
                    if (l === currentLang) {
                        btn.className = "px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer bg-emerald-600 text-white shadow-sm";
                    } else {
                        btn.className = "px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200";
                    }
                }
            });
        }

        // Initialize UI
        updateUILanguage();

        function downloadPDF() {
            const element = document.getElementById('pdf-card');
            if (!element) return;

            const download = () => {
                const opt = {
                    margin:       10,
                    filename:     'Verifikasi_Dokumen_' + '{{ $document ? $document->document_id : "doc" }}' + '.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 3, useCORS: true, logging: false },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                html2pdf().set(opt).from(element).save();
            };

            if (window.html2pdf) {
                download();
            } else {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                script.integrity = 'sha512-GsLlZN/3F2ErC5IfS5Q/Go6XXnEQgp1Ckb9spipdI68x5rKxQcALzSZKCfyUlXOUMGq8MuvOdxTeRLgpRQGiKw==';
                script.crossOrigin = 'anonymous';
                script.onload = download;
                document.body.appendChild(script);
            }
        }
    </script>
</body>
</html>
