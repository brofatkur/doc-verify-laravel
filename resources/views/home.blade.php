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
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between relative overflow-hidden selection:bg-emerald-500 selection:text-slate-950">
    
    <!-- Background blur blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-emerald-500/5 rounded-full filter blur-[120px] animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[45vw] h-[45vw] bg-blue-500/5 rounded-full filter blur-[120px] animate-blob animation-delay-2000"></div>
    <div class="absolute top-[30%] right-[10%] w-[35vw] h-[35vw] bg-teal-500/5 rounded-full filter blur-[120px] animate-blob animation-delay-4000"></div>

    <!-- Header -->
    <header class="py-6 px-6 md:px-12 border-b border-slate-900 bg-slate-950/60 backdrop-blur-xl flex items-center justify-between z-10">
        <div class="flex items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-9 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-xl font-bold tracking-tight text-white">DocVerify</span>
        </div>
        <a href="/admin" class="text-sm font-semibold text-emerald-400 hover:text-emerald-350 hover:underline transition-all duration-200">
            Akses Penerjemah
        </a>
    </header>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 z-10 max-w-4xl mx-auto w-full">
        <div class="text-center space-y-6 mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold tracking-wide uppercase">
                <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Portal Validasi Resmi Dokumen Terjemahan
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight">
                Kepercayaan Berlandaskan <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-blue-400">
                    Bukti Kriptografis.
                </span>
            </h1>

            <p class="text-sm sm:text-base text-slate-400 max-w-xl mx-auto leading-relaxed">
                Verifikasi pendaftaran dan keaslian dokumen terjemahan tersumpah Anda secara instan menggunakan alat pemeriksa validasi kami.
            </p>
        </div>

        <!-- Tab Container -->
        <div class="w-full max-w-md bg-slate-900/80 backdrop-blur-2xl border border-slate-800/80 rounded-2xl shadow-2xl p-6 sm:p-8">
            <!-- Tabs -->
            <div class="flex border-b border-slate-800 pb-3.5 mb-6 overflow-x-auto gap-2 scrollbar-none">
                <button
                    id="btn-tab-search"
                    onclick="switchTab('search')"
                    class="flex-1 flex items-center justify-center gap-2 pb-3 text-xs font-semibold border-b-2 border-emerald-400 text-emerald-400 transition-all duration-200 cursor-pointer flex-shrink-0 whitespace-nowrap"
                >
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    <span>Nomor Registrasi</span>
                </button>
                <button
                    id="btn-tab-scan"
                    onclick="switchTab('scan')"
                    class="flex-1 flex items-center justify-center gap-2 pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition-all duration-200 cursor-pointer flex-shrink-0 whitespace-nowrap"
                >
                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                    <span>Pindai QR</span>
                </button>
                <button
                    id="btn-tab-translator"
                    onclick="switchTab('translator')"
                    class="flex-1 flex items-center justify-center gap-2 pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition-all duration-200 cursor-pointer flex-shrink-0 whitespace-nowrap"
                >
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Cari Penerjemah</span>
                </button>
            </div>

            <!-- Error Notification -->
            @if(session('error'))
                <div class="flex items-start gap-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-sm font-medium mb-5">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                    <span class="leading-snug">{{ session('error') }}</span>
                </div>
            @endif
            <div id="scan-error" class="hidden flex items-start gap-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-sm font-medium mb-5">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                <span id="scan-error-msg" class="leading-snug"></span>
            </div>

            <!-- Tab 1: Search Form -->
            <form id="form-search" action="/search" method="GET" class="space-y-4">
                <div>
                    <label for="search-input" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">
                        Nomor Registrasi atau 8-Karakter ID Dokumen
                    </label>
                    <div class="relative">
                        <input
                            id="search-input"
                            name="query"
                            type="text"
                            required
                            placeholder="Contoh: REG-Belanda-001 atau VFY7A8B9"
                            class="w-full pl-4 pr-12 py-3.5 border border-slate-800 rounded-xl bg-slate-950/60 text-white placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold uppercase tracking-wide transition-all duration-200"
                        />
                        <button
                            type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md"
                        >
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
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
                    class="flex flex-col items-center justify-center p-8 border border-dashed border-slate-800 hover:border-emerald-500 rounded-xl bg-slate-950/30 cursor-pointer transition-all duration-300 group"
                >
                    <div id="scan-icon-container">
                        <i data-lucide="camera" class="w-10 h-10 text-slate-500 group-hover:text-emerald-400 transition-colors duration-200 mb-4"></i>
                    </div>
                    <span id="scan-text" class="text-sm font-semibold text-slate-200 group-hover:text-emerald-400 transition-colors duration-200">
                        Ambil Foto atau Unggah QR
                    </span>
                    <span class="text-xs text-slate-500 mt-1.5">
                        Mendukung jepretan kamera langsung & unggahan gambar
                    </span>
                </label>
                
                <p class="text-xs text-slate-500 leading-relaxed">
                    Ambil foto kode QR yang dibubuhkan pada dokumen terjemahan untuk mendekode dan memverifikasi secara otomatis.
                </p>
            </div>

            <!-- Tab 3: Translator Search -->
            <div id="div-translator" class="hidden space-y-5">
                <form onsubmit="searchTranslators(event)" class="space-y-4">
                    <div>
                        <label for="translator-input" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">
                            Nama, Nomor Anggota, atau No SK Kemenkumham
                        </label>
                        <div class="relative">
                            <input
                                id="translator-input"
                                type="text"
                                required
                                placeholder="Contoh: Muhammad Arifin atau 25004"
                                class="w-full pl-4 pr-12 py-3.5 border border-slate-800 rounded-xl bg-slate-950/60 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            />
                            <button
                                type="submit"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-all duration-200 cursor-pointer shadow-md"
                            >
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Translator Results -->
                <div id="translator-results" class="hidden space-y-3 pt-2">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hasil Pencarian:</p>
                    <div id="translator-results-list" class="space-y-2.5 max-h-56 overflow-y-auto pr-1"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 px-6 border-t border-slate-900 text-center text-xs text-slate-500 z-10 bg-slate-950/40">
        &copy; {{ date('Y') }} DocVerify IPPTI. Keamanan Terjemahan Tersumpah Resmi.
    </footer>

    <script>
        lucide.createIcons();

        function switchTab(tab) {
            const btnSearch = document.getElementById('btn-tab-search');
            const btnScan = document.getElementById('btn-tab-scan');
            const btnTranslator = document.getElementById('btn-tab-translator');
            
            const formSearch = document.getElementById('form-search');
            const divScan = document.getElementById('div-scan');
            const divTranslator = document.getElementById('div-translator');
            const errorDiv = document.getElementById('scan-error');

            errorDiv.classList.add('hidden');

            // Reset active classes
            btnSearch.classList.remove('border-emerald-400', 'text-emerald-400');
            btnSearch.classList.add('border-transparent', 'text-slate-400');
            btnScan.classList.remove('border-emerald-400', 'text-emerald-400');
            btnScan.classList.add('border-transparent', 'text-slate-400');
            btnTranslator.classList.remove('border-emerald-400', 'text-emerald-400');
            btnTranslator.classList.add('border-transparent', 'text-slate-400');

            formSearch.classList.add('hidden');
            divScan.classList.add('hidden');
            divTranslator.classList.add('hidden');

            if (tab === 'search') {
                btnSearch.classList.add('border-emerald-400', 'text-emerald-400');
                btnSearch.classList.remove('border-transparent', 'text-slate-400');
                formSearch.classList.remove('hidden');
            } else if (tab === 'scan') {
                btnScan.classList.add('border-emerald-400', 'text-emerald-400');
                btnScan.classList.remove('border-transparent', 'text-slate-400');
                divScan.classList.remove('hidden');
            } else if (tab === 'translator') {
                btnTranslator.classList.add('border-emerald-400', 'text-emerald-400');
                btnTranslator.classList.remove('border-transparent', 'text-slate-400');
                divTranslator.classList.remove('hidden');
            }
        }

        async function searchTranslators(e) {
            e.preventDefault();
            const query = document.getElementById('translator-input').value.trim();
            const errorDiv = document.getElementById('scan-error');
            const errorMsg = document.getElementById('scan-error-msg');
            const resultsDiv = document.getElementById('translator-results');
            const resultsList = document.getElementById('translator-results-list');

            errorDiv.classList.add('hidden');
            resultsDiv.classList.add('hidden');
            resultsList.innerHTML = '';

            if (!query) return;

            try {
                const response = await fetch('/search-translators?query=' + encodeURIComponent(query));
                const data = await response.json();

                if (data.success && data.translators) {
                    if (data.translators.length === 0) {
                        errorMsg.innerText = 'Penerjemah tidak ditemukan.';
                        errorDiv.classList.remove('hidden');
                    } else {
                        resultsDiv.classList.remove('hidden');
                        data.translators.forEach(t => {
                            const photoHtml = t.profile_picture 
                                ? `<img src="${t.profile_picture}" alt="${t.name}" class="w-full h-full object-cover">`
                                : `<i data-lucide="user" class="w-4 h-4 text-slate-400"></i>`;

                            resultsList.innerHTML += `
                                <a href="/verify-translator/${t.id}" class="flex items-center gap-3 p-3 bg-slate-950/60 border border-slate-800 rounded-xl hover:border-emerald-500/60 hover:bg-slate-900/60 transition group cursor-pointer text-left">
                                    <div class="w-9 h-9 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 font-bold overflow-hidden flex-shrink-0">
                                        ${photoHtml}
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-sm font-bold text-white group-hover:text-emerald-400 transition truncate">${t.name}</p>
                                        <p class="text-[10px] text-slate-450 truncate font-mono">No. Anggota: ${t.sk_number}</p>
                                    </div>
                                </a>
                            `;
                        });
                        lucide.createIcons();
                    }
                } else {
                    errorMsg.innerText = data.error || 'Gagal mencari penerjemah.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (err) {
                errorMsg.innerText = 'Terjadi kesalahan sistem saat menghubungi server.';
                errorDiv.classList.remove('hidden');
            }
        }

        async function handleQrUpload(input) {
            const file = input.files[0];
            if (!file) return;

            const errorDiv = document.getElementById('scan-error');
            const errorMsg = document.getElementById('scan-error-msg');
            const scanText = document.getElementById('scan-text');
            const scanLabel = document.getElementById('scan-label');
            const iconContainer = document.getElementById('scan-icon-container');

            errorDiv.classList.add('hidden');
            scanText.innerText = 'Membaca Berkas...';
            scanLabel.classList.add('opacity-50', 'pointer-events-none');
            iconContainer.innerHTML = '<div class="w-10 h-10 border-4 border-emerald-400 border-t-transparent rounded-full animate-spin mb-4"></div>';

            const reader = new FileReader();
            reader.onload = (event) => {
                const image = new Image();
                image.onload = () => {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    if (!context) {
                        showError("Gagal memproses gambar.");
                        return;
                    }
                    canvas.width = image.width;
                    canvas.height = image.height;
                    context.drawImage(image, 0, 0);
                    const imageData = context.getImageData(0, 0, image.width, image.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: 'dontInvert',
                    });

                    if (code && code.data) {
                        // Check if QR matches verification URL format /verify/XXXXXXXX
                        const match = code.data.match(/\/verify\/([A-Z0-9]{8})/i);
                        if (match && match[1]) {
                            window.location.href = '/verify/' + match[1];
                        } else {
                            const cleanData = code.data.trim();
                            if (cleanData.length === 8 && /^[A-Z0-9]+$/i.test(cleanData)) {
                                window.location.href = '/verify/' + cleanData.toUpperCase();
                            } else {
                                showError("Kode QR ini tidak dikenali sebagai URL verifikasi DocVerify atau ID Dokumen yang valid.");
                            }
                        }
                    } else {
                        showError("Tidak dapat menemukan kode QR yang terbaca. Coba pindai kembali dengan gambar yang lebih jelas.");
                    }
                };
                image.onerror = () => {
                    showError("Gagal memuat berkas gambar.");
                };
                image.src = event.target.result;
            };
            reader.readAsDataURL(file);

            function showError(msg) {
                errorMsg.innerText = msg;
                errorDiv.classList.remove('hidden');
                
                scanText.innerText = 'Ambil Foto atau Unggah QR';
                scanLabel.classList.remove('opacity-50', 'pointer-events-none');
                iconContainer.innerHTML = '<i data-lucide="camera" class="w-10 h-10 text-slate-500 group-hover:text-emerald-400 transition-colors duration-200 mb-4"></i>';
                lucide.createIcons();
                input.value = '';
            }
        }
    </script>
</body>
</html>
