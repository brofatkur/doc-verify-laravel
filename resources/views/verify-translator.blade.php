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
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between relative overflow-hidden py-8 px-4 sm:px-6 lg:px-8">
    <!-- Background blur blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-emerald-500/5 rounded-full filter blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[45vw] h-[45vw] bg-blue-500/5 rounded-full filter blur-[120px]"></div>

    <!-- Header -->
    <header class="max-w-4xl w-full mx-auto flex items-center justify-between pb-6 border-b border-slate-900 mb-8 z-10">
        <a href="/" class="flex items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-8 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-lg font-bold tracking-tight text-white">DocVerify</span>
        </a>
        <a href="/" class="text-xs font-semibold text-slate-400 hover:text-emerald-400 transition-colors">
            Kembali ke Beranda
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col items-center justify-center z-10 max-w-4xl mx-auto w-full">
        <div class="w-full max-w-4xl bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl relative z-10">
            <!-- Header / Language Selector -->
            <div class="px-6 py-4 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-500 animate-pulse"></i>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest font-mono">
                        IPPTI Official Registry Card
                    </span>
                </div>
                <div class="flex bg-slate-900 p-0.5 rounded-lg border border-slate-800">
                    <button
                        id="lang-id-btn"
                        onclick="setLanguage('id')"
                        class="px-3 py-1 rounded-md text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-emerald-600 text-white shadow-md"
                    >
                        INDONESIA
                    </button>
                    <button
                        id="lang-en-btn"
                        onclick="setLanguage('en')"
                        class="px-3 py-1 rounded-md text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200"
                    >
                        ENGLISH
                    </button>
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
                                <span>No. Anggota</span>
                            </span>
                            <p class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                {{ $translator->sk_number }} <span class="text-slate-500 mx-2">—</span> {{ $translator->name }}
                            </p>
                        </div>

                        <!-- Statement Body -->
                        <div id="statement-body" class="text-slate-300 text-sm font-medium leading-relaxed pt-2 text-left space-y-3">
                            <!-- Handled dynamically by setLanguage script below on load -->
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
                                <i data-lucide="user" class="w-16 h-16 text-slate-655"></i>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2">Foto Resmi</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="max-w-4xl w-full mx-auto text-center text-[10px] text-slate-650 pt-8 border-t border-slate-900 mt-12">
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

        function setLanguage(lang) {
            const btnId = document.getElementById('lang-id-btn');
            const btnEn = document.getElementById('lang-en-btn');
            const title = document.getElementById('result-title');
            const badge = document.getElementById('result-badge');
            const body = document.getElementById('statement-body');
            const footer = document.getElementById('statement-footer');

            if (lang === 'id') {
                btnId.className = "px-3 py-1 rounded-md text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-emerald-600 text-white shadow-md";
                btnEn.className = "px-3 py-1 rounded-md text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200";
                
                title.innerText = "Hasil Verifikasi Penerjemah Tersumpah";
                badge.innerText = "Aktif & Terdaftar";
                
                let skFullHtml = skLengkap ? `
                    <div class="col-span-2 border-t border-slate-850 pt-2 mt-1">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Keterangan SK Lengkap</p>
                        <p class="text-slate-300 mt-1 font-medium italic">${skLengkap}</p>
                    </div>
                ` : '';

                body.innerHTML = `
                    <p>
                        Benar bahwa penerjemah tersumpah atas nama <strong class="text-white font-bold">${name}</strong> terdaftar resmi sebagai anggota IPPTI dan merupakan penerjemah tersumpah di bawah <strong class="text-white font-bold">Kementerian Hukum dan HAM</strong> sesuai SK nomor <strong class="text-white font-bold">${noSkKemenkum}</strong> yang ditetapkan pada tanggal <strong class="text-white font-bold">${tglSk}</strong>.
                    </p>
                    <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 border border-slate-800 rounded-2xl text-xs mt-3">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Arah Bahasa</p>
                            <p class="text-white font-semibold mt-1">${languages}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Masa Aktif Registrasi</p>
                            <p class="text-emerald-400 font-bold mt-1">${masaAktif}</p>
                        </div>
                        ${skFullHtml}
                    </div>
                `;
                footer.innerHTML = `Kementerian Hukum dan Hak Asasi Manusia Republik Indonesia &copy; Copyright ${currentYear}`;
            } else {
                btnEn.className = "px-3 py-1 rounded-md text-[10px] font-extrabold tracking-wider transition cursor-pointer bg-emerald-600 text-white shadow-md";
                btnId.className = "px-3 py-1 rounded-md text-[10px] font-extrabold tracking-wider transition cursor-pointer text-slate-400 hover:text-slate-200";
                
                title.innerText = "Sworn Translator Verification Result";
                badge.innerText = "Active & Registered";
                
                let skFullHtml = skLengkap ? `
                    <div class="col-span-2 border-t border-slate-850 pt-2 mt-1">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Decree Full Statement</p>
                        <p class="text-slate-300 mt-1 font-medium italic">${skLengkap}</p>
                    </div>
                ` : '';

                const valMasaAktif = masaAktif === 'Seumur Hidup' ? 'Lifetime' : masaAktif;

                body.innerHTML = `
                    <p>
                        It is verified that the sworn translator named <strong class="text-white font-bold">${name}</strong> is officially registered as a member of IPPTI and is a sworn translator certified under the <strong class="text-white font-bold">Ministry of Law and Human Rights</strong> of the Republic of Indonesia pursuant to decree number <strong class="text-white font-bold">${noSkKemenkum}</strong> issued on <strong class="text-white font-bold">${tglSk}</strong>.
                    </p>
                    <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 border border-slate-800 rounded-2xl text-xs mt-3">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Language Pairing</p>
                            <p class="text-white font-semibold mt-1">${languages}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Registration Validity</p>
                            <p class="text-emerald-400 font-bold mt-1">${valMasaAktif}</p>
                        </div>
                        ${skFullHtml}
                    </div>
                `;
                footer.innerHTML = `Ministry of Law and Human Rights of the Republic of Indonesia &copy; Copyright ${currentYear}`;
            }
        }

        // Run setLanguage on load
        setLanguage('id');
    </script>
</body>
</html>
