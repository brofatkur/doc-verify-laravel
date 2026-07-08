<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - Portal Validasi Resmi Dokumen Terjemahan Tersumpah</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jsQR Library for scanning -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
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
<body id="body-layout" class="bg-slate-50 text-blue-950 min-h-screen flex flex-col justify-between relative overflow-x-hidden selection:bg-blue-500/20 selection:text-blue-950">
    
    <!-- Background blur blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-blue-500/10 rounded-full filter blur-[120px] animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[45vw] h-[45vw] bg-indigo-500/10 rounded-full filter blur-[120px] animate-blob animation-delay-2000"></div>

    <!-- Header -->
    <header class="py-6 px-6 md:px-12 border-b border-slate-200 bg-white/80 backdrop-blur-xl flex items-center justify-between z-10 flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="https://ippti.or.id" target="_blank" title="Kunjungi Website Resmi IPPTI">
                <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-9 w-auto rounded bg-white p-0.5 object-contain shadow-md hover:opacity-90 transition" />
            </a>
            <a href="/" class="text-xl font-bold tracking-tight text-blue-950 hover:underline">DocVerify</a>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="/verify-translator" id="nav-verify-trans" class="text-sm font-semibold text-blue-900 hover:text-blue-950 hover:underline transition-all duration-200">
                Verifikasi Penerjemah
            </a>

            <!-- Language Switcher -->
            <div class="flex bg-slate-200/60 p-0.5 rounded-lg border border-slate-200 dir-ltr" dir="ltr">
                <button onclick="changeLanguage('id')" id="lang-id" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-800">ID</button>
                <button onclick="changeLanguage('en')" id="lang-en" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-800">EN</button>
                <button onclick="changeLanguage('zh')" id="lang-zh" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-800">ZH</button>
                <button onclick="changeLanguage('ar')" id="lang-ar" class="px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-800">AR</button>
            </div>
        </div>
    </header>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 z-10 max-w-4xl mx-auto w-full">
        <div class="text-center space-y-6 mb-12">
            <div id="hero-badge" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-100 text-blue-800 text-xs font-semibold tracking-wide uppercase">
                <span class="flex h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                Portal Validasi Resmi Dokumen
            </div>

                        <h1 id="hero-title" class="text-3xl sm:text-5xl font-extrabold tracking-tight text-blue-950 leading-tight">
                Search a Registration Number
            </h1>

            <p id="hero-desc" class="hidden text-sm sm:text-base text-slate-500 max-w-xl mx-auto leading-relaxed">
            </p>
        </div>

        <!-- Tab Container -->
        <div class="w-full max-w-md bg-white border border-slate-200/80 rounded-2xl shadow-xl p-6 sm:p-8">
            <!-- Tabs -->
            <div class="flex border-b border-slate-200 pb-3.5 mb-6 overflow-x-auto gap-2 scrollbar-none">
                <button
                    id="btn-tab-search"
                    onclick="switchTab('search')"
                    class="flex-1 flex items-center justify-center gap-2 pb-3 text-xs font-semibold border-b-2 border-blue-950 text-blue-950 transition-all duration-200 cursor-pointer flex-shrink-0 whitespace-nowrap"
                >
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    <span id="tab-reg-text">Nomor Registrasi</span>
                </button>
                <button
                    id="btn-tab-scan"
                    onclick="switchTab('scan')"
                    class="flex-1 flex items-center justify-center gap-2 pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-655 transition-all duration-200 cursor-pointer flex-shrink-0 whitespace-nowrap"
                >
                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                    <span id="tab-qr-text">Pindai QR</span>
                </button>
            </div>

            <!-- Error Notification -->
            @if(session('error'))
                <div id="session-error" class="flex items-start gap-2.5 bg-rose-50 border border-rose-100 text-rose-700 p-3.5 rounded-xl text-sm font-medium mb-5">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                    <span class="leading-snug">{{ session('error') }}</span>
                </div>
            @endif
            <div id="scan-error" class="hidden flex items-start gap-2.5 bg-rose-50 border border-rose-100 text-rose-700 p-3.5 rounded-xl text-sm font-medium mb-5">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                <span id="scan-error-msg" class="leading-snug"></span>
            </div>

            <!-- Tab 1: Search Form -->
            <form id="form-search" action="/search" method="GET" class="space-y-4">
                <div>
                    <label id="label-reg" for="search-input" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5">
                        Nomor Registrasi atau 8-Karakter ID Dokumen
                    </label>
                    <div class="relative">
                        <input
                            id="search-input"
                            name="query"
                            type="text"
                            required
                            placeholder="Contoh: REG-Belanda-001 atau VFY7A8B9"
                            class="w-full pl-4 pr-12 py-3.5 border border-slate-200 rounded-xl bg-slate-50 text-blue-950 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-950/20 focus:border-blue-950 text-sm font-semibold uppercase tracking-wide transition-all duration-200"
                        />
                        <button
                            type="submit"
                            id="search-btn-arrow"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-blue-950 hover:bg-blue-900 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md"
                        >
                            <i data-lucide="arrow-right" id="arrow-icon" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <p id="desc-reg" class="text-xs text-slate-500 leading-relaxed">
                    Verifikasi dokumen dengan mencari nomor registrasi yang dibubuhkan oleh penerjemah tersumpah, atau 8-karakter ID verifikasi unik.
                </p>
            </form>

            <!-- Tab 2: QR Scanner -->
            <div id="div-scan" class="hidden space-y-4 text-center">
                <input
                    type="file"
                    accept="image/*"
                    capture="environment"
                    id="qr-camera-input"
                    onchange="handleQrUpload(this)"
                    class="hidden"
                />
                
                <label
                    for="qr-camera-input"
                    id="scan-label"
                    class="flex flex-col items-center justify-center p-8 border border-dashed border-slate-200 hover:border-blue-950 rounded-xl bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition-all duration-300 group"
                >
                    <div id="scan-icon-container">
                        <i data-lucide="camera" class="w-10 h-10 text-slate-400 group-hover:text-blue-950 transition-colors duration-200 mb-4"></i>
                    </div>
                    <span id="scan-text" class="text-sm font-semibold text-slate-700 group-hover:text-blue-950 transition-colors duration-200">
                        Ambil Foto atau Unggah QR
                    </span>
                    <span id="scan-desc-sub" class="text-xs text-slate-500 mt-1.5">
                        Mendukung jepretan kamera langsung & unggahan gambar
                    </span>
                </label>
                
                <p id="desc-qr" class="text-xs text-slate-500 leading-relaxed">
                    Gunakan kamera perangkat Anda untuk memindai kode QR verifikasi pada dokumen fisik, atau unggah foto/tangkapan layar kode QR.
                </p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer-text" class="py-6 px-6 border-t border-slate-200 text-center text-xs text-slate-500 z-10 bg-white/70">
        &copy; {{ date('Y') }} DocVerify IPPTI. Keamanan Terjemahan Tersumpah Resmi.
    </footer>

    <script>
        lucide.createIcons();

        const translations = {
            id: {
                title_doc: "Portal Validasi Resmi Dokumen",
                hero_title: "Cari Nomor Registrasi",
                hero_desc: "",
                hero_desc: "",
                tab_reg: "Nomor Registrasi",
                tab_qr: "Pindai QR",
                label_reg: "Nomor Registrasi atau 8-Karakter ID Dokumen",
                placeholder_reg: "Contoh: REG-Belanda-001 atau VFY7A8B9",
                desc_reg: "Verifikasi dokumen dengan mencari nomor registrasi yang dibubuhkan oleh penerjemah tersumpah, atau 8-karakter ID verifikasi unik.",
                upload_qr: "Ambil Foto atau Pindai QR",
                upload_qr_desc: "Mendukung jepretan kamera langsung & unggahan gambar",
                desc_qr: "Gunakan kamera perangkat Anda untuk memindai kode QR verifikasi pada dokumen fisik, atau unggah foto/tangkapan layar kode QR.",
                nav_verify_trans: "Verifikasi Penerjemah",
                footer: "DocVerify IPPTI. Keamanan Terjemahan Tersumpah Resmi.",
                scan_loading: "Membaca Berkas...",
                scan_err_canvas: "Gagal memproses kanvas gambar.",
                scan_err_qr: "Tidak dapat menemukan kode QR yang terbaca. Coba pindai kembali dengan gambar yang lebih jelas.",
                scan_err_decode: "Kode QR ini tidak dikenali sebagai URL verifikasi DocVerify atau ID Dokumen yang valid.",
                scan_err_load: "Gagal memuat berkas gambar."
            },
            en: {
                title_doc: "Official Document Validation Portal",
                hero_title: "Search a Registration Number",
                hero_desc: "",
                hero_desc: "",
                tab_reg: "Registration Number",
                tab_qr: "Scan QR",
                label_reg: "Registration Number or 8-Character Document ID",
                placeholder_reg: "Example: REG-Dutch-001 or VFY7A8B9",
                desc_reg: "Verify document by searching registration number issued by sworn translator, or unique 8-character verification ID.",
                upload_qr: "Capture Photo or Scan QR",
                upload_qr_desc: "Supports direct camera shots & image uploads",
                desc_qr: "Use your device camera to scan the verification QR code on the physical document, or upload a photo/screenshot of the QR code.",
                nav_verify_trans: "Verify Translator",
                footer: "DocVerify IPPTI. Official Sworn Translation Security.",
                scan_loading: "Scanning File...",
                scan_err_canvas: "Failed to process image canvas.",
                scan_err_qr: "Could not find a readable QR code. Try scanning again with a clearer image.",
                scan_err_decode: "This QR code is not recognized as a valid DocVerify verification URL or Document ID.",
                scan_err_load: "Failed to load image file."
            },
            zh: {
                title_doc: "官方文件验证门户",
                hero_title: "搜索注册编号",
                hero_desc: "",
                hero_desc: "",
                tab_reg: "注册编号",
                tab_qr: "扫描二维码",
                label_reg: "注册编号或8位文件 ID",
                placeholder_reg: "例如: REG-Dutch-001 或 VFY7A8B9",
                desc_reg: "通过搜索宣誓翻译员发放 of 注册号或唯一的8位验证 ID 来验证文件。",
                upload_qr: "拍摄照片或扫描二维码",
                upload_qr_desc: "支持直接相机拍摄和图片上传",
                desc_qr: "使用您的设备摄像头扫描纸质文件上的验证二维码，或上传二维码的照片/屏幕截图。",
                nav_verify_trans: "验证翻译员",
                footer: "DocVerify IPPTI. 官方宣誓翻译安全。",
                scan_loading: "正在读取文件...",
                scan_err_canvas: "处理图像画布失败。",
                scan_err_qr: "找不到可读取的二维码。请使用更清晰的图片重新扫描。",
                scan_err_decode: "此二维码未被识别为有效的 DocVerify 验证 URL 或文件 ID。",
                scan_err_load: "加载图像文件失败。"
            },
            ar: {
                title_doc: "البوابة الرسمية للتحقق من المستندات",
                hero_title: "البحث عن رقم التسجيل",
                hero_desc: "",
                hero_desc: "",
                tab_reg: "رقم التسجيل",
                tab_qr: "مسح رمز QR",
                label_reg: "رقم التسجيل أو معرف مستند من 8 أحرف",
                placeholder_reg: "مثال: REG-Dutch-001 أو VFY7A8B9",
                desc_reg: "تحقق من المستند بالبحث عن رقم التسجيل الصادر عن المترجم المحلف، أو معرف التحقق الفريد المكون من 8 أحرف.",
                upload_qr: "التقاط صورة أو مسح رمز QR",
                upload_qr_desc: "يدعم لقطات الكاميرا المباشرة وتحميل الصور",
                desc_qr: "استخدم كاميرا جهازك لمسح رمز QR للتحقق على المستند الفعلي، أو قم بتحميل صورة/لقطة شاشة لرمز QR.",
                nav_verify_trans: "التحقق من المترجم",
                footer: "DocVerify IPPTI. أمان الترجمة المحلفة الرسمية.",
                scan_loading: "جاري فحص الملف...",
                scan_err_canvas: "فشل في معالجة كانفاس الصورة.",
                scan_err_qr: "لم يتم العثور على رمز QR صالح للقراءة. يرجى المحاولة مرة أخرى بصورة أوضح.",
                scan_err_decode: "لا يتم التعرف على رمز QR هذا كعنوان URL صالح للتحقق من DocVerify أو معرف مستند.",
                scan_err_load: "فشل في تحميل ملف الصورة."
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

            // Update header classes for RTL
            const arrowBtn = document.getElementById('search-btn-arrow');
            if (isRtl) {
                arrowBtn.className = "absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-blue-950 hover:bg-blue-900 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md";
                document.getElementById('arrow-icon').style.transform = 'rotate(180deg)';
            } else {
                arrowBtn.className = "absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-blue-950 hover:bg-blue-900 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md";
                document.getElementById('arrow-icon').style.transform = 'rotate(0deg)';
            }

            // Update text nodes
            document.getElementById('nav-verify-trans').innerText = t.nav_verify_trans;
            document.getElementById('hero-badge').innerHTML = `<span class="flex h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>${t.title_doc}`;
            document.getElementById('hero-title').innerHTML = t.hero_title;
            document.getElementById('hero-desc').innerText = t.hero_desc;
            document.getElementById('tab-reg-text').innerText = t.tab_reg;
            document.getElementById('tab-qr-text').innerText = t.tab_qr;
            document.getElementById('label-reg').innerText = t.label_reg;
            document.getElementById('search-input').placeholder = t.placeholder_reg;
            document.getElementById('desc-reg').innerText = t.desc_reg;
            document.getElementById('scan-text').innerText = t.upload_qr;
            document.getElementById('scan-desc-sub').innerText = t.upload_qr_desc;
            document.getElementById('desc-qr').innerText = t.desc_qr;
            document.getElementById('footer-text').innerHTML = `&copy; ${new Date().getFullYear()} ${t.footer}`;

            // Highlight language button
            ['id', 'en', 'zh', 'ar'].forEach(l => {
                const btn = document.getElementById(`lang-${l}`);
                if (l === currentLang) {
                    btn.className = "px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-blue-950 text-white shadow-md";
                } else {
                    btn.className = "px-2 py-1 rounded text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-500 hover:text-slate-800";
                }
            });
        }

        function switchTab(tab) {
            const btnSearch = document.getElementById('btn-tab-search');
            const btnScan = document.getElementById('btn-tab-scan');
            
            const formSearch = document.getElementById('form-search');
            const divScan = document.getElementById('div-scan');
            const errorDiv = document.getElementById('scan-error');

            errorDiv.classList.add('hidden');

            // Reset active classes
            btnSearch.classList.remove('border-blue-950', 'text-blue-950');
            btnSearch.classList.add('border-transparent', 'text-slate-400');
            btnScan.classList.remove('border-blue-950', 'text-blue-950');
            btnScan.classList.add('border-transparent', 'text-slate-400');

            formSearch.classList.add('hidden');
            divScan.classList.add('hidden');

            if (tab === 'search') {
                btnSearch.classList.remove('border-transparent', 'text-slate-400');
                btnSearch.classList.add('border-blue-950', 'text-blue-950');
                formSearch.classList.remove('hidden');
            } else if (tab === 'scan') {
                btnScan.classList.remove('border-transparent', 'text-slate-400');
                btnScan.classList.add('border-blue-950', 'text-blue-950');
                divScan.classList.remove('hidden');
            }
        }

        // QR Code Upload Decoder
        function handleQrUpload(input) {
            const file = input.files[0];
            if (!file) return;

            const errorDiv = document.getElementById('scan-error');
            const errorMsg = document.getElementById('scan-error-msg');
            const labelText = document.getElementById('scan-text');
            const iconContainer = document.getElementById('scan-icon-container');
            const sessionErr = document.getElementById('session-error');

            if (sessionErr) sessionErr.classList.add('hidden');
            errorDiv.classList.add('hidden');

            // Set loading state
            labelText.innerText = translations[currentLang].scan_loading;
            iconContainer.innerHTML = `<i data-lucide="loader-2" class="w-10 h-10 text-emerald-400 animate-spin mb-4"></i>`;
            lucide.createIcons();

            const reader = new FileReader();
            reader.onload = function(e) {
                const image = new Image();
                image.onload = function() {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    if (!context) {
                        showScanError(translations[currentLang].scan_err_canvas);
                        return;
                    }

                    canvas.width = image.width;
                    canvas.height = image.height;
                    context.drawImage(image, 0, 0);

                    const imageData = context.getImageData(0, 0, image.width, image.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert'
                    });

                    if (code && code.data) {
                        const match = code.data.match(/\/verify\/([A-Z0-9]{8})/i);
                        if (match && match[1]) {
                            window.location.href = '/verify/' + match[1];
                        } else {
                            const docIdClean = code.data.trim();
                            if (docIdClean.length === 8 && /^[A-Z0-9]+$/i.test(docIdClean)) {
                                window.location.href = '/verify/' + docIdClean.toUpperCase();
                            } else {
                                showScanError(translations[currentLang].scan_err_decode);
                            }
                        }
                    } else {
                        showScanError(translations[currentLang].scan_err_qr);
                    }
                };
                image.onerror = function() {
                    showScanError(translations[currentLang].scan_err_load);
                };
                image.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function showScanError(msg) {
            const errorDiv = document.getElementById('scan-error');
            const errorMsg = document.getElementById('scan-error-msg');
            const labelText = document.getElementById('scan-text');
            const iconContainer = document.getElementById('scan-icon-container');

            errorMsg.innerText = msg;
            errorDiv.classList.remove('hidden');

            // Reset upload label
            labelText.innerText = translations[currentLang].upload_qr;
            iconContainer.innerHTML = `<i data-lucide="camera" class="w-10 h-10 text-slate-400 group-hover:text-blue-950 transition-colors duration-200 mb-4"></i>`;
            lucide.createIcons();
        }

        // Initialize UI
        updateUILanguage();
    </script>
</body>
</html>
