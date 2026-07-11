<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify - Daftar Akun Penerjemah</title>
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
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    
    <!-- Background Decorative Blurs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-teal-500 rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob animation-delay-2000"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10 text-center">
        <div class="flex justify-center items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-2xl font-bold text-white tracking-tight">DocVerify</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
            Daftar Akun Penerjemah
        </h2>
        <p class="mt-2 text-center text-sm text-slate-400">
            Atau 
            <a href="/login" class="font-semibold text-emerald-400 hover:text-emerald-350 hover:underline transition-all duration-200">
                masuk ke profil Anda yang sudah ada
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10 px-4 sm:px-0">
        <div class="bg-slate-900/80 backdrop-blur-2xl py-8 px-6 shadow-2xl rounded-2xl border border-slate-800/80 sm:px-10">
            <form id="form-register" class="space-y-5" action="/register" method="POST" onsubmit="return validateForm()">
                @csrf
                
                @if($errors->any())
                    <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-xs text-center font-semibold leading-relaxed">
                        {{ $errors->first() }}
                    </div>
                @endif
                <div id="js-error" class="hidden bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-xs text-center font-semibold leading-relaxed"></div>
                <div id="js-success" class="hidden bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 p-3.5 rounded-xl text-xs text-center font-semibold leading-relaxed flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                    <span id="js-success-text"></span>
                </div>

                <div>
                    <label for="sk_number" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Nomor Anggota IPPTI
                    </label>
                    <div class="mt-1 flex gap-2">
                        <input
                            id="sk_number"
                            name="sk_number"
                            type="text"
                            value="{{ old('sk_number') }}"
                            required
                            class="appearance-none block flex-1 px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="Contoh: 25004"
                        />
                        <button
                            type="button"
                            onclick="checkMember()"
                            id="btn-check-member"
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-750 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                        >
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            <span id="btn-check-text">Cek</span>
                        </button>
                    </div>
                    <p class="mt-1.5 text-[10px] text-slate-500 flex items-center gap-1">
                        <i data-lucide="info" class="w-3 h-3 text-slate-500"></i>
                        <span>Masukkan nomor anggota untuk memuat data profil otomatis.</span>
                    </p>
                </div>

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Nama Lengkap (Penerjemah Tersumpah)
                    </label>
                    <div class="mt-1">
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="Nama Lengkap & Gelar Akademis"
                        />
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Alamat Email
                    </label>
                    <div class="mt-1">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="penerjemah@example.com"
                        />
                    </div>
                </div>

                <div>
                    <label for="whatsapp" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Nomor WhatsApp
                    </label>
                    <div class="mt-1">
                        <input
                            id="whatsapp"
                            name="whatsapp"
                            type="text"
                            value="{{ old('whatsapp') }}"
                            class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="Contoh: 081234567890"
                        />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Password Baru
                    </label>
                    <div class="mt-1 relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="appearance-none block w-full pl-3.5 pr-10 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('password')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-200 cursor-pointer transition-colors duration-200"
                            tabindex="-1"
                        >
                            <i data-lucide="eye" id="password-eye-icon" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Konfirmasi Password Baru
                    </label>
                    <div class="mt-1 relative">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            class="appearance-none block w-full pl-3.5 pr-10 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('password_confirmation')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-200 cursor-pointer transition-colors duration-200"
                            tabindex="-1"
                        >
                            <i data-lucide="eye" id="password_confirmation-eye-icon" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        id="submit-btn"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span id="submit-text">Daftar Akun Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        async function checkMember() {
            const memberNo = document.getElementById('sk_number').value.trim();
            const btnText = document.getElementById('btn-check-text');
            const btn = document.getElementById('btn-check-member');
            const errorDiv = document.getElementById('js-error');
            const successDiv = document.getElementById('js-success');
            const successText = document.getElementById('js-success-text');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const whatsappInput = document.getElementById('whatsapp');
            const submitText = document.getElementById('submit-text');

            if (!memberNo) {
                errorDiv.innerText = 'Silakan masukkan Nomor Anggota terlebih dahulu.';
                errorDiv.classList.remove('hidden');
                successDiv.classList.add('hidden');
                return;
            }

            btn.setAttribute('disabled', 'disabled');
            btnText.innerText = 'Memproses...';
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch(`/api/check-member/${memberNo}`);
                const data = await response.json();

                if (data.success && data.translator) {
                    nameInput.value = data.translator.name;
                    nameInput.setAttribute('readonly', 'readonly');
                    nameInput.className = "appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200 opacity-70 cursor-not-allowed bg-slate-900 border-emerald-500/30";
                    
                    emailInput.value = data.translator.email || '';
                    whatsappInput.value = data.translator.whatsapp || '';
                    
                    successText.innerText = `Data Terintegrasi! Profil "${data.translator.name}" berhasil dimuat otomatis. Silakan lengkapi email & password Anda.`;
                    successDiv.classList.remove('hidden');
                    submitText.innerText = 'Klaim & Daftar Akun';
                } else {
                    errorDiv.innerText = data.error || 'Nomor Anggota tidak ditemukan dalam database pra-impor.';
                    errorDiv.classList.remove('hidden');
                    nameInput.removeAttribute('readonly');
                    nameInput.className = "appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200";
                    submitText.innerText = 'Daftar Akun Baru';
                }
            } catch (err) {
                errorDiv.innerText = 'Gagal memverifikasi nomor anggota.';
                errorDiv.classList.remove('hidden');
            } finally {
                btn.removeAttribute('disabled');
                btnText.innerText = 'Cek';
            }
        }

        function validateForm() {
            const pass = document.getElementById('password').value;
            const confirmPass = document.getElementById('password_confirmation').value;
            const name = document.getElementById('name').value.trim();
            const errorDiv = document.getElementById('js-error');

            if (!name) {
                errorDiv.innerText = 'Nama lengkap tidak boleh kosong.';
                errorDiv.classList.remove('hidden');
                return false;
            }

            if (pass !== confirmPass) {
                errorDiv.innerText = 'Password dan konfirmasi password tidak sama.';
                errorDiv.classList.remove('hidden');
                return false;
            }
            errorDiv.classList.add('hidden');
            return true;
        }
    </script>
</body>
</html>
