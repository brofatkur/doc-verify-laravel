<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - Verifikasi Keamanan 2FA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: "Plus Jakarta Sans", sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10 text-center">
        <div class="flex justify-center items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-2xl font-extrabold text-white tracking-tight">DocVerify IPPTI</span>
        </div>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase tracking-wider mt-5">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>Verifikasi Dua Langkah (2FA)</span>
        </div>

        <h2 class="mt-3 text-2xl font-extrabold text-white">
            Masukkan Kode Keamanan 6-Digit
        </h2>
        <p class="mt-2 text-xs sm:text-sm text-slate-400 leading-relaxed">
            @if(($method ?? 'otp') === 'totp')
                Buka aplikasi <strong class="text-white">Google Authenticator</strong> / Authy di ponsel Anda dan masukkan 6-digit kode verifikasi untuk akun <strong class="text-emerald-400">{{ $maskedEmail ?? '' }}</strong>.
            @else
                Kami telah mengirimkan 6-digit Kode OTP ke Email (<strong class="text-emerald-400">{{ $maskedEmail ?? '' }}</strong>)
                @if(!empty($maskedWhatsapp))
                    &amp; WhatsApp (<strong class="text-emerald-400">{{ $maskedWhatsapp }}</strong>)
                @endif
                yang terdaftar pada akun Anda.
            @endif
        </p>
    </div>

    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-md z-10">
        <div class="bg-slate-900/85 backdrop-blur-2xl py-8 px-6 shadow-2xl rounded-2xl border border-slate-800/80 sm:px-10 space-y-6">
            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 p-3.5 rounded-xl text-xs font-bold text-center">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3.5 rounded-xl text-xs font-bold text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="/login/2fa" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="code" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">
                        Kode OTP / Google Authenticator (6 Digit)
                    </label>
                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="000000"
                        class="block w-full px-4 py-3.5 border border-slate-700 rounded-xl bg-slate-950/80 text-white text-center text-2xl font-black tracking-[0.5em] font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 transition"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-lg text-sm font-extrabold text-white bg-emerald-600 hover:bg-emerald-500 transition cursor-pointer"
                >
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Verifikasi &amp; Masuk</span>
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <form action="/login/2fa/resend" method="POST">
                    @csrf
                    <button type="submit" class="text-emerald-400 hover:text-emerald-300 font-bold hover:underline flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span>Kirim Kode OTP via Email &amp; WhatsApp</span>
                    </button>
                </form>

                <a href="/login" class="text-slate-400 hover:text-slate-200 font-semibold hover:underline">
                    Kembali ke Login
                </a>
            </div>
        </div>

        <footer class="mt-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} IPPTI (Ikatan Penerjemah dan Pengalih Bahasa Tersumpah di Indonesia).
        </footer>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
