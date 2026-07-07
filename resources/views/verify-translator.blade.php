<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - Verifikasi Penerjemah Tersumpah</title>
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
<body id="body-layout" class="bg-slate-955 bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between relative overflow-hidden py-8 px-4 sm:px-6 lg:px-8">
    <!-- Background blur blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-emerald-500/5 rounded-full filter blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[45vw] h-[45vw] bg-blue-500/5 rounded-full filter blur-[120px]"></div>

    <!-- Header -->
    <header class="max-w-4xl w-full mx-auto flex items-center justify-between pb-6 border-b border-slate-900 mb-8 z-10">
        <a href="/" class="flex items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-8 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-lg font-bold tracking-tight text-white">DocVerify</span>
        </a>
        <a href="/" id="back-home-text" class="text-xs font-semibold text-slate-400 hover:text-emerald-400 transition-colors">
            Kembali ke Beranda
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-center z-10 max-w-4xl mx-auto w-full">
        <div class="w-full max-w-4xl bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl relative z-10">
            <!-- Header / Language Selector -->
            <div class="px-6 py-4 bg-slate-950 border-b border-slate-800 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-500 animate-pulse"></i>
                    <span id="label-card-title" class="text-xs font-bold text-slate-400 uppercase tracking-widest font-mono">
                        IPPTI Official Registry Card
                    </span>
                </div>
                <div class="flex bg-slate-900 p-0.5 rounded-lg border border-slate-800 dir-ltr" dir="ltr">
                    <button onclick="changeLanguage('id')" id="lang-id" class="px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">ID</button>
                    <button onclick="changeLanguage('en')" id="lang-en" class="px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">EN</button>
                    <button onclick="changeLanguage('zh')" id="lang-zh" class="px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">ZH</button>
                    <button onclick="changeLanguage('ar')" id="lang-ar" class="px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200">AR</button>
                </div>
            </div>

            <!-- Layout Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 p-6 sm:p-8 items-stretch">
                <!-- Left Side: Statement (8 Cols) -->
                <div class="md:col-span-8 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center">
                                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 id="result-title" class="text-lg font-black text-white leading-tight">
                                    Hasil Verifikasi Penerjemah Tersumpah
                                </h2>
                                <p id="result-badge" class="text-[10px] text-emerald-500 font-bold uppercase tracking-wider mt-0.5">
                                    Aktif & Terdaftar
                                </p>
                            </div>
                        </div>

                        <!-- Green decorative bar -->
                        <div class="h-1.5 bg-emerald-600 rounded-full w-full"></div>

                        <!-- Member Details Header -->
                        <div class="space-y-1 pt-2 text-left">
                            <span class="text-[11px] font-semibold text-amber-500 uppercase tracking-widest flex items-center gap-1">
                                <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                <span id="label-member">No. Anggota</span>
                            </span>
                            <p class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                {{ $translator->sk_number }} <span class="text-slate-500 mx-2">—</span> {{ $translator->name }}
                            </p>
                        </div>

                        <!-- Statement Body -->
                        <div id="statement-body" class="text-slate-350 text-sm font-medium leading-relaxed pt-2 text-left space-y-3">
                            <!-- Handled dynamically by JavaScript -->
                        </div>
                    </div>

                    <!-- Kementerian Footer -->
                    <div id="statement-footer" class="border-t border-slate-800/80 pt-4 text-[10px] font-bold text-slate-500 font-mono tracking-wider text-left">
                        Kementerian Hukum dan Hak Asasi Manusia Republik Indonesia &copy; Copyright {{ date('Y') }}
                    </div>
                </div>

                <!-- Right Side: Photo Frame (4 Cols) -->
                <div class="md:col-span-4 flex flex-col items-center justify-center">
                    <div class="relative w-full max-w-[210px] aspect-[3/4] bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl p-1 bg-gradient-to-br from-slate-900 to-slate-950">
                        @if($translator->profile_picture)
                            <img
                                src="{{ $translator->profile_picture }}"
                                alt="{{ $translator->name }}"
                                class="w-full h-full object-cover rounded-xl grayscale-[15%] hover:grayscale-0 transition-all duration-350"
                            />
                        @else
                            <div class="w-full h-full rounded-xl bg-slate-900 flex flex-col items-center justify-center border border-slate-800/50">
                                <i data-lucide="user" class="w-16 h-16 text-slate-650"></i>
                                <span id="label-photo" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2">Foto Resmi</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer-text" class="max-w-4xl w-full mx-auto text-center text-[10px] text-slate-650 pt-8 border-t border-slate-900 mt-12">
        &copy; {{ date('Y') }} Ikatan Penerjemah Pemerintah Indonesia (IPPTI) & Kementerian Hukum dan HAM RI.
    </footer>

    <script>
        lucide.createIcons();

        const name = "{{ $translator->name }}";
        const skNumber = "{{ $translator->sk_number }}";
        const noSkKemenkum = "{{ $translator->no_sk_kemenkum ?: 'AHU-55 AH.03.07.2022' }}";
        const tglSk = "{{ $translator->tgl_sk ?: '5 Oktober 2022' }}";
        const languages = "{{ $translator->language_services ?? 'Indonesia - Inggris, Inggris - Indonesia' }}";
        const masaAktif = "{{ $translator->masa_aktif ?: 'Seumur Hidup' }}";
        const skLengkap = "{{ $translator->sk_lengkap ?: '' }}";
        const currentYear = "{{ date('Y') }}";

        const translations = {
            id: {
                card_title: "IPPTI Official Registry Card",
                verify_title: "Hasil Verifikasi Penerjemah Tersumpah",
                verify_badge: "Aktif & Terdaftar",
                member_label: "No. Anggota",
                lang_services: "Arah Bahasa",
                validity: "Masa Aktif Registrasi",
                sk_desc: "Keterangan SK Lengkap",
                statement: `Benar bahwa penerjemah tersumpah atas nama <strong class="text-white font-bold">${name}</strong> terdaftar resmi sebagai anggota IPPTI dan merupakan penerjemah tersumpah di bawah <strong class="text-white font-bold">Kementerian Hukum dan HAM</strong> sesuai SK nomor <strong class="text-white font-bold">${noSkKemenkum}</strong> yang ditetapkan pada tanggal <strong class="text-white font-bold">${tglSk}</strong>.`,
                footer_kemenkumham: `Kementerian Hukum dan Hak Asasi Manusia Republik Indonesia &copy; Copyright ${currentYear}`,
                official_photo: "Foto Resmi",
                back_home: "Kembali ke Beranda",
                footer_copy: `Ikatan Penerjemah Pemerintah Indonesia (IPPTI) & Kementerian Hukum dan HAM RI.`
            },
            en: {
                card_title: "IPPTI Official Registry Card",
                verify_title: "Sworn Translator Verification Result",
                verify_badge: "Active & Registered",
                member_label: "Member ID",
                lang_services: "Language Pairing",
                validity: "Registration Validity",
                sk_desc: "Decree Full Statement",
                statement: `It is verified that the sworn translator named <strong class="text-white font-bold">${name}</strong> is officially registered as a member of IPPTI and is a sworn translator certified under the <strong class="text-white font-bold">Ministry of Law and Human Rights</strong> of the Republic of Indonesia pursuant to decree number <strong class="text-white font-bold">${noSkKemenkum}</strong> issued on <strong class="text-white font-bold">${tglSk}</strong>.`,
                footer_kemenkumham: `Ministry of Law and Human Rights of the Republic of Indonesia &copy; Copyright ${currentYear}`,
                official_photo: "Official Photo",
                back_home: "Back to Home",
                footer_copy: `Association of Indonesian Government Translators (IPPTI) & Ministry of Law and Human Rights RI.`
            },
            zh: {
                card_title: "IPPTI 官方注册卡",
                verify_title: "宣誓翻译员验证结果",
                verify_badge: "活动与已注册",
                member_label: "成员编号",
                lang_services: "翻译语言对",
                validity: "注册有效期",
                sk_desc: "法令完整声明",
                statement: `确认以下名下的宣誓翻译员 <strong class="text-white font-bold">${name}</strong> 已正式注册为 IPPTI 成员，并根据 <strong class="text-white font-bold">${tglSk}</strong> 颁布的第 <strong class="text-white font-bold">${noSkKemenkum}</strong> 号法令获得印尼共和国<strong class="text-white font-bold">司法与人权部</strong>认证。`,
                footer_kemenkumham: `印度尼西亚共和国司法与人权部 &copy; Copyright ${currentYear}`,
                official_photo: "官方照片",
                back_home: "返回首页",
                footer_copy: `印尼政府翻译员协会 (IPPTI) 和印尼共和国司法与人权部。`
            },
            ar: {
                card_title: "بطاقة التسجيل الرسمية لـ IPPTI",
                verify_title: "نتيجة التحقق من المترجم المحلف",
                verify_badge: "نشط ومسجل",
                member_label: "رقم العضوية",
                lang_services: "زوج اللغات للترجمة",
                validity: "صلاحية التسجيل",
                sk_desc: "البيان الكامل للمرسوم",
                statement: `يؤكد أن المترجم المحلف باسم <strong class="text-white font-bold">${name}</strong> مسجل رسمياً كعضو في IPPTI ومترجم محلف معتمد من قبل <strong class="text-white font-bold">وزارة القانون وحقوق الإنسان</strong> في جمهورية إندونيسيا بموجب المرسوم رقم <strong class="text-white font-bold">${noSkKemenkum}</strong> الصادر في <strong class="text-white font-bold">${tglSk}</strong>.`,
                footer_kemenkumham: `وزارة القانون وحقوق الإنسان في جمهورية إندونيسيا &copy; Copyright ${currentYear}`,
                official_photo: "الصورة الرسمية",
                back_home: "العودة إلى الصفحة الرئيسية",
                footer_copy: `جمعية المترجمين الحكوميين الإندونيسيين (IPPTI) ووزارة القانون وحقوق الإنسان في جمهورية إندونيسيا.`
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
            document.getElementById('back-home-text').innerText = t.back_home;
            document.getElementById('label-card-title').innerText = t.card_title;
            document.getElementById('result-title').innerText = t.verify_title;
            document.getElementById('result-badge').innerText = t.verify_badge;
            document.getElementById('label-member').innerText = t.member_label;
            document.getElementById('statement-footer').innerHTML = t.footer_kemenkumham;
            
            const photoEl = document.getElementById('label-photo');
            if (photoEl) photoEl.innerText = t.official_photo;

            document.getElementById('footer-text').innerHTML = `&copy; ${currentYear} ${t.footer_copy}`;

            let skFullHtml = skLengkap ? `
                <div class="col-span-2 border-t border-slate-850 pt-2 mt-1">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">${t.sk_desc}</p>
                    <p class="text-slate-350 mt-1 font-medium italic">${skLengkap}</p>
                </div>
            ` : '';

            const valMasaAktif = (currentLang === 'ar' && masaAktif === 'Seumur Hidup') ? 'مدى الحياة' : masaAktif;

            document.getElementById('statement-body').innerHTML = `
                <p>
                    ${t.statement}
                </p>
                <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 border border-slate-800 rounded-2xl text-xs mt-3">
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">${t.lang_services}</p>
                        <p class="text-white font-semibold mt-1">${languages}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">${t.validity}</p>
                        <p class="text-emerald-400 font-bold mt-1">${valMasaAktif}</p>
                    </div>
                    ${skFullHtml}
                </div>
            `;

            // Highlight language button
            ['id', 'en', 'zh', 'ar'].forEach(l => {
                const btn = document.getElementById(`lang-${l}`);
                if (btn) {
                    if (l === currentLang) {
                        btn.className = "px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-emerald-600 text-white shadow-md";
                    } else {
                        btn.className = "px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200";
                    }
                }
            });
        }

        // Initialize UI
        updateUILanguage();
    </script>
</body>
</html>
