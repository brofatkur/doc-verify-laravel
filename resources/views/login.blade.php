<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify - Masuk ke Akun Anda</title>
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
            <a href="https://ippti.or.id" target="_blank" title="Kunjungi Website Resmi IPPTI">
                <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-md hover:opacity-90 transition" />
            </a>
            <a href="/" class="text-2xl font-bold text-white tracking-tight hover:underline">DocVerify</a>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
            Masuk ke Akun Anda
        </h2>
        <p class="mt-2 text-center text-sm text-slate-400">
            Atau{' '}
            <a href="/register" class="font-semibold text-emerald-400 hover:text-emerald-350 hover:underline transition-all duration-200">
                daftar profil penerjemah baru
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10 px-4 sm:px-0">
        <div class="bg-slate-900/80 backdrop-blur-2xl py-8 px-6 shadow-2xl rounded-2xl border border-slate-800/80 sm:px-10">
            <form class="space-y-6" action="/login" method="POST">
                @csrf
                
                @if($errors->any())
                    <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-sm text-center font-medium leading-snug">
                        {{ $errors->first() }}
                        @if(Illuminate\Support\Str::contains(strtolower($errors->first()), ['salah', 'password', 'invalid']))
                            <a href="/forgot-password" class="text-emerald-400 hover:text-emerald-350 hover:underline font-bold transition ml-1">Lupa Password?</a>
                        @endif
                    </div>
                @endif

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
                            class="appearance-none block w-full px-3.5 py-3 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="penerjemah@example.com"
                        />
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Password
                        </label>
                        <a href="/forgot-password" class="text-xs font-semibold text-emerald-400 hover:text-emerald-350 hover:underline transition">
                            Lupa Password?
                        </a>
                    </div>
                    <div class="mt-1 relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="appearance-none block w-full pl-3.5 pr-10 py-3 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('password')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-200 cursor-pointer transition-colors duration-200"
                        >
                            <i data-lucide="eye" id="password-eye-icon" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>Masuk</span>
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
