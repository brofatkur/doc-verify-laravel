<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify - Inisialisasi Super Admin</title>
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
<body class="bg-slate-50 text-blue-950 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    
    <!-- Background Decorative Blurs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full mix-blend-multiply filter blur-[100px] animate-blob"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-500/10 rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob animation-delay-2000"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10 text-center">
        <div class="flex justify-center items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-2xl font-bold text-blue-950 tracking-tight">DocVerify</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-blue-950">
            Setup Website Pertama Kali
        </h2>
        <p class="mt-2 text-center text-sm text-slate-500">
            Buat akun Super Admin utama untuk mengelola aplikasi verifikasi.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10 px-4 sm:px-0">
        <div class="bg-white border border-slate-200/80 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
            <form class="space-y-6" action="/install" method="POST">
                @csrf
                
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 p-3.5 rounded-xl text-sm text-center font-medium leading-snug">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-rose-50 border border-rose-100 text-rose-700 p-3.5 rounded-xl text-sm text-center font-medium leading-snug">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                        Nama Lengkap Super Admin
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        class="appearance-none block w-full px-3.5 py-3 border border-slate-200 rounded-xl bg-slate-50 placeholder-slate-400 text-blue-950 focus:outline-none focus:ring-2 focus:ring-blue-950/20 focus:border-blue-950 text-sm font-semibold transition-all duration-200"
                        placeholder="Contoh: Administrator Pusat"
                    />
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                        Alamat Email Resmi
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        class="appearance-none block w-full px-3.5 py-3 border border-slate-200 rounded-xl bg-slate-50 placeholder-slate-400 text-blue-950 focus:outline-none focus:ring-2 focus:ring-blue-950/20 focus:border-blue-950 text-sm font-semibold transition-all duration-200"
                        placeholder="superadmin@domain.com"
                    />
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                        Password Baru
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="appearance-none block w-full pl-3.5 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 placeholder-slate-400 text-blue-950 focus:outline-none focus:ring-2 focus:ring-blue-950/20 focus:border-blue-950 text-sm font-semibold transition-all duration-200"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('password')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                        >
                            <i data-lucide="eye" id="password-eye-icon" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                        Konfirmasi Password
                    </label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        class="appearance-none block w-full px-3.5 py-3 border border-slate-200 rounded-xl bg-slate-50 placeholder-slate-400 text-blue-950 focus:outline-none focus:ring-2 focus:ring-blue-950/20 focus:border-blue-950 text-sm font-semibold transition-all duration-200"
                        placeholder="••••••••"
                    />
                </div>

                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-blue-950 hover:bg-blue-900 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Instal Akun Super Admin</span>
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
    </script>
</body>
</html>
