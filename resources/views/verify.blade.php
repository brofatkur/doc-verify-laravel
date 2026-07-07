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
        <div class="w-full max-w-lg mb-8 text-center pt-4">
            <div class="inline-flex items-center justify-center gap-3 mb-2">
                <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-sm" />
                <span class="text-xl font-bold text-slate-900 tracking-tight">DocVerify</span>
            </div>
            <p id="sub-header-portal" class="text-xs text-slate-500 font-medium">Portal Verifikasi Resmi Terjemahan Tersumpah</p>
        </div>

        <!-- Verification Card -->
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-8 relative">
            <!-- Language Selector inside card -->
            <div class="px-6 py-3.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between text-white">
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

            <!-- Header Banner -->
            <div class="bg-gradient-to-br from-emerald-600 via-teal-700 to-emerald-800 p-8 text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-white/10 to-transparent"></div>
                
                <div class="relative z-10 space-y-3">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-emerald-500/30 animate-pulse">
                        <i data-lucide="check-circle-2" class="w-10 h-10 text-emerald-600"></i>
                    </div>
                    <div>
                        <h1 id="label-verified-title" class="text-2xl font-black text-white tracking-widest">TERVERIFIKASI</h1>
                        <p id="label-verified-sub" class="text-xs text-emerald-100 font-semibold tracking-wider uppercase mt-0.5">Rekam Dokumen Resmi IPPTI</p>
                    </div>
                </div>
            </div>

            <!-- Details list -->
            <div class="p-6 sm:p-8 space-y-6 text-left">
                <div id="section-meta-row" class="border-b border-slate-100 pb-6">
                    <div>
                        <p id="label-reg-no" class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">No. Registrasi</p>
                        <p class="text-lg font-bold text-slate-900 font-mono">{{ $document->registration_number }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-4">
                    <div>
                        <p id="label-date" class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Terjemah</p>
                        <p id="value-date" class="text-base font-semibold text-slate-800"></p>
                    </div>

                    <div>
                        <p id="label-status" class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Status Verifikasi</p>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span id="value-status"></span>
                            </span>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <p id="label-client" class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nama di Dokumen (Disamarkan)</p>
                        <p class="text-base font-mono font-bold text-slate-900 mt-1 bg-slate-50 border border-slate-200/80 p-3 rounded-xl select-none tracking-wide text-center">
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

                    <div class="sm:col-span-2 bg-slate-50 rounded-2xl p-4.5 border border-slate-200/60">
                        <p id="label-pair" class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Pasangan Bahasa</p>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-800 bg-white px-3.5 py-1.5 rounded-lg border border-slate-200/80 shadow-sm">{{ $document->language_pair }}</span>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <p id="label-type" class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Dokumen</p>
                        <p class="text-base font-semibold text-slate-800 mt-1">{{ $document->document_type }}</p>
                    </div>

                    <!-- Sworn Translator Badge Box -->
                    <div class="sm:col-span-2 border-t border-slate-100 pt-6">
                        <p id="label-translator" class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Penerjemah Tersumpah</p>
                        <div class="bg-slate-50/60 border border-slate-100 rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0 border border-emerald-100 shadow-sm overflow-hidden">
                                    @if($document->translator->profile_picture)
                                        <img src="{{ $document->translator->profile_picture }}" alt="{{ $document->translator->name }}" class="w-full h-full object-cover" />
                                    @else
                                        <i data-lucide="file-text" class="w-5 h-5 text-emerald-600"></i>
                                    @endif
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-base font-bold text-slate-900 truncate">{{ $document->translator->name }}</p>
                                    <p id="label-translator-member" class="text-xs text-slate-500 font-mono mt-0.5"></p>
                                </div>
                            </div>

                            @if($document->translator->bio)
                                <div class="text-xs border-t border-slate-100/85 pt-3">
                                    <span id="label-bio" class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Biografi:</span>
                                    <p class="text-slate-600 leading-relaxed italic">"{{ $document->translator->bio }}"</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secure Disclaimer box -->
            <div class="bg-amber-50/40 p-6 border-t border-slate-100 border-l-4 border-l-amber-500 text-left">
                <div class="flex items-start gap-3">
                    <p id="label-disclaimer" class="text-xs text-slate-600 leading-relaxed text-justify"></p>
                </div>
            </div>
        </div>
    @endif

    <!-- Bottom Credit -->
    <div class="text-center text-[10px] text-slate-400 uppercase tracking-widest space-y-1 py-4">
        <p>&copy; {{ date('Y') }} DocVerify IPPTI. Seluruh Hak Cipta Dilindungi.</p>
        <p id="label-bottom-credit" class="font-semibold text-slate-400"></p>
    </div>

    <script>
        lucide.createIcons();

        const dateId = "{{ $document ? $document->document_date->translatedFormat('d F Y') : '' }}";
        const dateEn = "{{ $document ? $document->document_date->format('MMMM d, Y') : '' }}";
        const statusId = "{{ $document ? $document->status : '' }}";
        const memberNo = "{{ $document ? $document->translator->sk_number : '' }}";

        const translations = {
            id: {
                credentials: "Document Credentials",
                verified: "TERVERIFIKASI",
                record: "Rekam Dokumen Resmi IPPTI",
                doc_id: "ID Dokumen",
                reg_no: "No. Registrasi",
                trans_date: "Tanggal Terjemah",
                status: "Status Verifikasi",
                masked_name: "Nama di Dokumen (Disamarkan)",
                lang_pair: "Pasangan Bahasa",
                doc_type: "Tipe Dokumen",
                translator: "Nama Penerjemah Tersumpah",
                member_id: "No. Anggota: " + memberNo,
                services: "Layanan Bahasa:",
                bio: "Biografi:",
                disclaimer: `<strong class="text-slate-800 font-bold">Disclaimer Resmi:</strong> Sistem ini memverifikasi bahwa terjemahan dokumen ini telah resmi terdaftar oleh Penerjemah Tersumpah yang terasosiasi di atas. Harap pastikan fisik dokumen memiliki cap basah/segel pengaman yang sesuai untuk validitas hukum sepenuhnya.`,
                bottom_credit: "DIVERIFIKASI SECARA ELEKTRONIK & KRIPTOGRAFIS",
                sub_portal: "Portal Verifikasi Resmi Terjemahan Tersumpah"
            },
            en: {
                credentials: "Document Credentials",
                verified: "VERIFIED",
                record: "IPPTI Official Document Record",
                doc_id: "Document ID",
                reg_no: "Registration No.",
                trans_date: "Translation Date",
                status: "Verification Status",
                masked_name: "Name on Document (Masked)",
                lang_pair: "Language Pair",
                doc_type: "Document Type",
                translator: "Name of Sworn Translator",
                member_id: "Member ID: " + memberNo,
                services: "Language Services:",
                bio: "Biography:",
                disclaimer: `<strong class="text-slate-800 font-bold">Official Disclaimer:</strong> This system verifies that the translation of this document has been officially registered by the sworn translator associated above. Please ensure that the physical document bears the appropriate wet stamp or security seal for full legal validity.`,
                bottom_credit: "ELECTRONICALLY & CRYPTOGRAPHICALLY VERIFIED",
                sub_portal: "Official Sworn Translation Verification Portal"
            },
            zh: {
                credentials: "文件凭证",
                verified: "已验证",
                record: "IPPTI 官方文件记录",
                doc_id: "文件 ID",
                reg_no: "注册号",
                trans_date: "翻译日期",
                status: "验证状态",
                masked_name: "文件姓名（已遮蔽）",
                lang_pair: "语言对",
                doc_type: "文件类型",
                translator: "宣誓翻译员姓名",
                member_id: "成员 ID: " + memberNo,
                services: "语言服务:",
                bio: "个人简介:",
                disclaimer: `<strong class="text-slate-800 font-bold">官方免责声明:</strong> 此系统验证此文件的翻译已由上述关联的宣誓翻译员正式注册。请确保纸质文件上有相应的湿盖章或安全封条，以具备完全的法律效力。`,
                bottom_credit: "经过电子与密码学验证",
                sub_portal: "官方宣誓翻译验证门户"
            },
            ar: {
                credentials: "وثائق المستند",
                verified: "تم التحقق",
                record: "سجل المستندات الرسمي لـ IPPTI",
                doc_id: "معرف المستند",
                reg_no: "رقم التسجيل",
                trans_date: "تاريخ الترجمة",
                status: "حالة التحقق",
                masked_name: "الاسم على المستند (مخفي)",
                lang_pair: "زوج اللغات",
                doc_type: "نوع المستند",
                translator: "اسم المترجم المحلف",
                member_id: "رقم العضوية: " + memberNo,
                services: "خدمات اللغة:",
                bio: "السيرة الذاتية:",
                disclaimer: `<strong class="text-slate-800 font-bold">إخلاء مسؤولية رسمي:</strong> يتحقق هذا النظام من أن ترجمة هذا المستند قد تم تسجيلها رسمياً بواسطة المترجم المحلف المرتبط أعلاه. يرجى التأكد من أن المستند المادي يحمل الختم المائي أو الختم الأمني المناسب للصلاحية القانونية الكاملة.`,
                bottom_credit: "تم التحقق منه إلكترونياً وتشفيرياً",
                sub_portal: "البوابة الرسمية للتحقق من الترجمة المحلفة"
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

            if (subPortal) subPortal.innerText = t.sub_portal;
            if (labelCredentials) labelCredentials.innerText = t.credentials;
            if (labelVerifiedTitle) labelVerifiedTitle.innerText = t.verified;
            if (labelVerifiedSub) labelVerifiedSub.innerText = t.record;
            if (labelDocId) labelDocId.innerText = t.doc_id;
            if (labelRegNo) labelRegNo.innerText = t.reg_no;
            if (labelDate) labelDate.innerText = t.trans_date;
            if (valueDate) valueDate.innerText = currentLang === 'id' ? dateId : dateEn;
            if (labelStatus) labelStatus.innerText = t.status;
            if (valueStatus) valueStatus.innerText = currentLang === 'id' ? statusId : 'Active / Valid';
            if (labelClient) labelClient.innerText = t.masked_name;
            if (labelPair) labelPair.innerText = t.lang_pair;
            if (labelType) labelType.innerText = t.doc_type;
            if (labelTranslator) labelTranslator.innerText = t.translator;
            if (labelTranslatorMember) labelTranslatorMember.innerText = t.member_id;
            if (labelServices) labelServices.innerText = t.services;
            if (labelBio) labelBio.innerText = t.bio;
            if (labelDisclaimer) labelDisclaimer.innerHTML = t.disclaimer;
            if (labelBottomCredit) labelBottomCredit.innerText = t.bottom_credit;

            // Highlight language button
            ['id', 'en', 'zh', 'ar'].forEach(l => {
                const btn = document.getElementById(`lang-${l}`);
                if (btn) {
                    if (l === currentLang) {
                        btn.className = "px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer bg-emerald-600 text-white shadow-sm";
                    } else {
                        btn.className = "px-2 py-0.5 rounded text-[9px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-205";
                    }
                }
            });
        }

        // Initialize UI
        updateUILanguage();
    </script>
</body>
</html>
