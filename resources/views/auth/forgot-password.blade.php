<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify - Lupa Password</title>
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
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden animate-fade-in">
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-[100px] opacity-10"></div>
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10 text-center">
        <div class="flex justify-center items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-2xl font-bold text-white tracking-tight">DocVerify</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
            Lupa Password?
        </h2>
        <p class="mt-2 text-center text-sm text-slate-400">
            Masukkan email Anda untuk menerima tautan pemulihan kata sandi.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10 px-4 sm:px-0">
        <div class="bg-slate-900/80 backdrop-blur-2xl py-8 px-6 shadow-2xl rounded-2xl border border-slate-800/80 sm:px-10">
            @if(session('status'))
                <div class="space-y-6 text-center">
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 rounded-full flex items-center justify-center mx-auto border border-emerald-500/20">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm text-slate-350 leading-relaxed">
                        {{ session('status') }}
                    </p>
                    <a
                        href="/login"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-400 hover:text-emerald-350 hover:underline transition"
                    >
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Kembali ke Halaman Masuk</span>
                    </a>
                </div>
            @else
                <form class="space-y-6" action="/forgot-password" method="POST">
                    @csrf
                    @if($errors->any())
                        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-sm text-center font-medium leading-snug flex items-center justify-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                            Alamat Email Terdaftar
                        </label>
                        <div class="mt-1">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                value="{{ old('email') }}"
                                class="appearance-none block w-full px-3.5 py-3 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-650 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="penerjemah@example.com"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            <span>Kirim Tautan Reset</span>
                        </button>

                        <div class="text-center">
                            <a
                                href="/login"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-450 hover:text-slate-200 transition"
                            >
                                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                                <span>Batal dan Kembali</span>
                            </a>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
