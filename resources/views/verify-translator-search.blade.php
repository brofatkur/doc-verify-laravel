<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - Portal Validasi Resmi Penerjemah Tersumpah</title>
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
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.08); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 8s infinite alternate ease-in-out;
        }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
        .dir-ltr { direction: ltr !important; }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body id="body-layout" class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between relative overflow-x-hidden selection:bg-emerald-500 selection:text-slate-950">
    
    <!-- Background blur blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-emerald-500/5 rounded-full filter blur-[120px] animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[45vw] h-[45vw] bg-blue-500/5 rounded-full filter blur-[120px] animate-blob animation-delay-2000"></div>

    <!-- Header -->
    <header class="py-6 px-6 md:px-12 border-b border-slate-900 bg-slate-950/60 backdrop-blur-xl flex items-center justify-between z-10 flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="https://ippti.or.id" target="_blank" title="Kunjungi Website Resmi IPPTI">
                <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-9 w-auto rounded bg-white p-0.5 object-contain shadow-md hover:opacity-90 transition" />
            </a>
            <a href="/" class="text-xl font-bold tracking-tight text-white hover:underline">DocVerify</a>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="/" id="nav-verify-doc" class="text-sm font-semibold text-emerald-400 hover:text-emerald-350 hover:underline transition-all duration-200">
                Verifikasi Dokumen
            </a>

            <!-- Language Switcher -->
            <div class="flex bg-slate-900 p-0.5 rounded-lg border border-slate-800 dir-ltr" dir="ltr">
                <button onclick="changeLanguage('id')" id="lang-id" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-205">ID</button>
                <button onclick="changeLanguage('en')" id="lang-en" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-205">EN</button>
                <button onclick="changeLanguage('zh')" id="lang-zh" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-205">ZH</button>
                <button onclick="changeLanguage('ar')" id="lang-ar" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-205">AR</button>
            </div>
        </div>
    </header>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 z-10 max-w-4xl mx-auto w-full">
        <div class="text-center space-y-6 mb-12">
            <div id="hero-badge" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold tracking-wide uppercase">
                <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Portal Validasi Resmi Penerjemah
            </div>

            <h1 id="hero-title" class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight">
                Temukan Penerjemah <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-blue-400">
                    Tersumpah Resmi.
                </span>
            </h1>

            <p id="hero-desc" class="text-sm sm:text-base text-slate-400 max-w-xl mx-auto leading-relaxed">
                Cari dan validasi sertifikasi resmi serta keanggotaan penerjemah tersumpah Indonesia.
            </p>
        </div>

        <!-- Tab Container -->
        <div class="w-full max-w-md bg-slate-900/80 backdrop-blur-2xl border border-slate-800/80 rounded-2xl shadow-2xl p-6 sm:p-8">
            <div class="flex border-b border-slate-800 pb-3.5 mb-6">
                <div class="flex-1 flex items-center justify-center gap-2 pb-3 text-xs font-bold border-b-2 border-emerald-400 text-emerald-400">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    <span id="tab-search-trans">Cari Penerjemah</span>
                </div>
            </div>

            <!-- Error Notification -->
            <div id="search-error" class="hidden flex items-start gap-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-sm font-medium mb-5">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                <span id="search-error-msg" class="leading-snug"></span>
            </div>

            <div class="space-y-5">
                <form onsubmit="searchTranslators(event)" class="space-y-4">
                    <div>
                        <label id="label-trans" for="translator-input" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5 text-left">
                            Nama, Nomor Anggota, atau No SK Kemenkumham
                        </label>
                        <div class="relative">
                            <input
                                id="translator-input"
                                type="text"
                                required
                                placeholder="Cari nama penerjemah, bahasa, atau nomor anggota..."
                                class="w-full pl-4 pr-12 py-3.5 border border-slate-800 rounded-xl bg-slate-950/60 text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            />
                            <button
                                type="submit"
                                id="search-btn-arrow"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md"
                            >
                                <i data-lucide="arrow-right" id="arrow-icon" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <p id="desc-trans" class="text-xs text-slate-500 leading-relaxed text-left">
                        Cari penerjemah tersumpah terdaftar berdasarkan Nama, Nomor SK Kemenkumham, Nomor Anggota, atau Arah Bahasa.
                    </p>
                </form>

                <!-- Results -->
                <div id="translator-results" class="hidden space-y-3 pt-2 text-left">
                    <p id="results-header" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hasil Pencarian:</p>
                    <div id="translator-results-list" class="space-y-2.5 max-h-56 overflow-y-auto pr-1"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer-text" class="py-6 px-6 border-t border-slate-900 text-center text-xs text-slate-500 z-10 bg-slate-950/40">
        &copy; {{ date('Y') }} DocVerify IPPTI. Keamanan Terjemahan Tersumpah Resmi.
    </footer>

    <script>
        lucide.createIcons();

        const translations = {
            id: {
                title_trans: "Portal Validasi Resmi Penerjemah",
                hero_title: `Temukan Penerjemah <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-blue-400">Tersumpah Resmi.</span>`,
                hero_desc: "Cari dan validasi sertifikasi resmi serta keanggotaan penerjemah tersumpah Indonesia.",
                tab_search_trans: "Cari Penerjemah",
                label_trans: "Nama, Nomor Anggota, atau No SK Kemenkumham",
                placeholder_trans: "Cari nama penerjemah, bahasa, atau nomor anggota...",
                desc_trans: "Cari penerjemah tersumpah terdaftar berdasarkan Nama, Nomor SK Kemenkumham, Nomor Anggota, atau Arah Bahasa.",
                not_found_trans: "Penerjemah tidak ditemukan.",
                nav_verify_doc: "Verifikasi Dokumen",
                footer: "DocVerify IPPTI. Keamanan Terjemahan Tersumpah Resmi.",
                results_header: "Hasil Pencarian:",
                search_loading: "Mencari...",
                member_prefix: "No. Anggota: "
            },
            en: {
                title_trans: "Official Sworn Translator Validation Portal",
                hero_title: `Find Official <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-blue-400">Sworn Translators.</span>`,
                hero_desc: "Search and validate official certification and membership of Indonesian sworn translators.",
                tab_search_trans: "Search Translator",
                label_trans: "Name, Member ID, or Kemenkumham Decree Number",
                placeholder_trans: "Search translator's name, language, or member ID...",
                desc_trans: "Search registered sworn translators by Name, Decree Number, Member ID, or Language Pairing.",
                not_found_trans: "Translator not found.",
                nav_verify_doc: "Verify Document",
                footer: "DocVerify IPPTI. Official Sworn Translation Security.",
                results_header: "Search Results:",
                search_loading: "Searching...",
                member_prefix: "Member ID: "
            },
            zh: {
                title_trans: "官方宣誓翻译员验证门户",
                hero_title: `查找官方 <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-blue-400">宣誓翻译员。</span>`,
                hero_desc: "搜索并验证印尼宣誓翻译员的官方认证和成员身份。",
                tab_search_trans: "搜索翻译员",
                label_trans: "姓名、成员 ID 或司法与人权部法令编号",
                placeholder_trans: "搜索翻译员姓名、语言或成员 ID...",
                desc_trans: "按姓名、法令编号、成员 ID 或语言对搜索已注册的宣誓翻译员。",
                not_found_trans: "未找到翻译员。",
                nav_verify_doc: "验证文件",
                footer: "DocVerify IPPTI. 官方宣誓翻译安全。",
                results_header: "搜索结果:",
                search_loading: "搜索中...",
                member_prefix: "成员 ID: "
            },
            ar: {
                title_trans: "البوابة الرسمية للتحقق من المترجمين المحلفين",
                hero_title: `البحث عن المترجمين <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-blue-400">المحلفين الرسميين.</span>`,
                hero_desc: "ابحث وتحقق من الشهادة الرسمية وعضوية المترجمين المحلفين الإندونيسيين.",
                tab_search_trans: "البحث عن مترجم",
                label_trans: "الاسم، رقم العضوية، أو رقم مرسوم وزارة القانون",
                placeholder_trans: "البحث عن اسم المترجم، اللغة، أو رقم العضوية...",
                desc_trans: "ابحث عن المترجمين المحلفين المسجلين حسب الاسم، رقم المرسوم، رقم العضوية، أو زوج اللغات.",
                not_found_trans: "المترجم غير موجود.",
                nav_verify_doc: "التحقق من المستندات",
                footer: "DocVerify IPPTI. أمان الترجمة المحلفة الرسمية.",
                results_header: "نتائج البحث:",
                search_loading: "جاري البحث...",
                member_prefix: "رقم العضوية: "
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

            // Update arrow classes for RTL
            const arrowBtn = document.getElementById('search-btn-arrow');
            if (isRtl) {
                arrowBtn.className = "absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md";
                document.getElementById('arrow-icon').style.transform = 'rotate(180deg)';
            } else {
                arrowBtn.className = "absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md";
                document.getElementById('arrow-icon').style.transform = 'rotate(0deg)';
            }

            // Update text nodes
            document.getElementById('nav-verify-doc').innerText = t.nav_verify_doc;
            document.getElementById('hero-badge').innerHTML = `<span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>${t.title_trans}`;
            document.getElementById('hero-title').innerHTML = t.hero_title;
            document.getElementById('hero-desc').innerText = t.hero_desc;
            document.getElementById('tab-search-trans').innerText = t.tab_search_trans;
            document.getElementById('label-trans').innerText = t.label_trans;
            document.getElementById('translator-input').placeholder = t.placeholder_trans;
            document.getElementById('desc-trans').innerText = t.desc_trans;
            document.getElementById('results-header').innerText = t.results_header;
            document.getElementById('footer-text').innerHTML = `&copy; ${new Date().getFullYear()} ${t.footer}`;

            // Highlight language button
            ['id', 'en', 'zh', 'ar'].forEach(l => {
                const btn = document.getElementById(`lang-${l}`);
                if (l === currentLang) {
                    btn.className = "px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-emerald-600 text-white shadow-md";
                } else {
                    btn.className = "px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-205";
                }
            });
            
            // Re-render search results language if list is populated
            const list = document.getElementById('translator-results-list');
            if (list.children.length > 0) {
                const items = list.querySelectorAll('.member-tag');
                items.forEach(item => {
                    const no = item.getAttribute('data-sk');
                    item.innerText = `${t.member_prefix}${no}`;
                });
            }
        }

        function searchTranslators(e) {
            e.preventDefault();
            const query = document.getElementById('translator-input').value.trim();
            const errorDiv = document.getElementById('search-error');
            const errorMsg = document.getElementById('search-error-msg');
            const resultsDiv = document.getElementById('translator-results');
            const resultsList = document.getElementById('translator-results-list');

            errorDiv.classList.add('hidden');
            resultsDiv.classList.add('hidden');
            resultsList.innerHTML = '';

            if (!query) return;

            fetch(`/search-translators?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.translators && data.translators.length > 0) {
                        resultsDiv.classList.remove('hidden');
                        data.translators.forEach(t => {
                            const avatar = t.profile_picture ? 
                                `<img src="${t.profile_picture}" alt="${t.name}" class="w-full h-full object-cover" />` :
                                `<i data-lucide="user" class="w-4 h-4 text-slate-400"></i>`;
                                
                            const item = document.createElement('a');
                            item.href = `/verify-translator/${t.id}`;
                            item.className = "flex items-center gap-3 p-3 bg-slate-950/60 border border-slate-800 rounded-xl hover:border-emerald-500/60 hover:bg-slate-900/60 transition group cursor-pointer";
                            item.innerHTML = `
                                <div class="w-9 h-9 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 font-bold overflow-hidden flex-shrink-0">
                                    ${avatar}
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <p class="text-sm font-bold text-white group-hover:text-emerald-400 transition truncate">${t.name}</p>
                                    <p class="text-[10px] text-slate-450 truncate font-mono member-tag" data-sk="${t.sk_number}">${translations[currentLang].member_prefix}${t.sk_number}</p>
                                </div>
                            `;
                            resultsList.appendChild(item);
                        });
                        lucide.createIcons();
                    } else {
                        errorMsg.innerText = translations[currentLang].not_found_trans;
                        errorDiv.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    errorMsg.innerText = translations[currentLang].not_found_trans;
                    errorDiv.classList.remove('hidden');
                });
        }

        // Initialize UI
        updateUILanguage();
    </script>
</body>
</html>
