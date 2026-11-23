<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - Pendaftaran Anggota Baru IPPTI & Penerjemah Tersumpah</title>
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
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center py-10 sm:px-6 lg:px-8 relative overflow-x-hidden">
    
    <!-- Background Decorative Blurs -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-[120px] opacity-10 animate-blob pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-teal-500 rounded-full mix-blend-multiply filter blur-[120px] opacity-10 animate-blob animation-delay-2000 pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-2xl z-10 text-center px-4">
        <div class="flex justify-center items-center gap-3">
            <a href="/" class="flex items-center gap-3">
                <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-11 w-auto rounded-lg bg-white p-0.5 object-contain shadow-md" />
                <span class="text-2xl font-extrabold text-white tracking-tight">DocVerify IPPTI</span>
            </a>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-bold uppercase tracking-wider mt-4">
            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
            <span>Registrasi Keanggotaan & Portal Verifikasi</span>
        </div>
        <h2 class="mt-3 text-center text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            Pendaftaran Anggota Baru IPPTI
        </h2>
        <p class="mt-2 text-center text-xs sm:text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
            Pendaftar anggota IPPTI otomatis terdaftar sebagai pengguna sistem verifikasi dokumen <strong class="text-slate-200">DocVerify IPPTI</strong> (Penerjemah Tersumpah). Sudah punya akun?
            <a href="/login" class="font-bold text-emerald-400 hover:text-emerald-300 hover:underline transition-all duration-200">
                Masuk (Login) di sini
            </a>
        </p>
    </div>

    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-2xl z-10 px-4 sm:px-0">
        <div class="bg-slate-900/85 backdrop-blur-2xl py-8 px-5 shadow-2xl rounded-3xl border border-slate-800/90 sm:px-10 space-y-6">

            <!-- Alur Verifikasi Info Banner -->
            <div class="bg-blue-500/10 border border-blue-500/25 rounded-2xl p-4 flex items-start gap-3 text-left">
                <div class="p-2 bg-blue-500/20 text-blue-400 rounded-xl flex-shrink-0 mt-0.5">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div class="text-xs text-slate-300 space-y-1.5 leading-relaxed flex-1">
                    <p class="font-bold text-white text-sm">Mekanisme Aktivasi & Keanggotaan Tahunan IPPTI</p>
                    <p>
                        1. Isi formulir pendaftaran di bawah ini dengan data SK Menteri Hukum RI yang valid.<br>
                        2. Setelah mendaftar, Anda <strong class="text-emerald-400">dapat langsung login</strong> ke dashboard.<br>
                        3. Fitur <strong class="text-amber-300">Input Data Dokumen & QR Verifikasi</strong> akan otomatis aktif segera setelah Pengurus Admin IPPTI memverifikasi pendaftaran Anda (Masa aktif berlaku <strong>{{ (int)\App\Models\Setting::get('member_duration_years', 1) }} Tahun</strong> sejak Tanggal Aktivasi).
                    </p>
                    <div class="flex flex-wrap gap-3 pt-1 text-[11px]">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-950/70 border border-slate-800 text-emerald-400 font-bold">
                            Biaya Daftar Awal: Rp {{ number_format((float)($registrationFee ?? \App\Models\Setting::get('member_registration_fee', 250000)), 0, ',', '.') }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-slate-950/70 border border-slate-800 text-blue-400 font-bold">
                            Perpanjangan Tahunan: Rp {{ number_format((float)($renewalFee ?? \App\Models\Setting::get('member_renewal_fee', 150000)), 0, ',', '.') }} / Tahun
                        </span>
                    </div>
                </div>
            </div>

            <form id="form-register" class="space-y-6" action="/register" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                @csrf
                
                @if(session('already_registered') || (isset($errors) && $errors->has('sk_number') && str_contains($errors->first('sk_number'), 'sudah terdaftar')))
                    <div class="bg-amber-500/10 border border-amber-500/25 text-amber-300 p-4 rounded-xl text-xs text-center font-semibold leading-relaxed space-y-2">
                        <p class="text-sm font-bold text-amber-200">Anda sudah terdaftar, silahkan login</p>
                        <p class="text-slate-400 text-[11px]">Nomor anggota ini sudah memiliki akun aktif di DocVerify IPPTI.</p>
                        <div class="pt-1">
                            <a href="/login" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-xs transition shadow-sm">
                                <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                <span>Masuk ke Akun (Login)</span>
                            </a>
                        </div>
                    </div>
                @elseif($errors->any())
                    <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-xs text-left font-semibold leading-relaxed space-y-1">
                        @foreach($errors->all() as $err)
                            <div class="flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                                <span>{{ $err }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div id="js-error" class="hidden bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3.5 rounded-xl text-xs text-center font-semibold leading-relaxed"></div>
                <div id="js-success" class="hidden bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 p-3.5 rounded-xl text-xs text-left font-semibold leading-relaxed flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                    <span id="js-success-text"></span>
                </div>

                <!-- ========================================== -->
                <!-- BAGIAN 1: DATA PRIBADI & KEANGGOTAAN       -->
                <!-- ========================================== -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-800 pb-2.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-extrabold flex items-center justify-center">1</span>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Data Pribadi & Keanggotaan IPPTI</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Nama Lengkap & Gelar (Penerjemah Tersumpah) <span class="text-rose-400">*</span>
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="Contoh: Dr. Budi Santoso, S.S., M.Hum."
                            />
                        </div>

                        <div>
                            <label for="nik" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                NIK / Nomor KTP
                            </label>
                            <input
                                id="nik"
                                name="nik"
                                type="text"
                                value="{{ old('nik') }}"
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200 font-mono"
                                placeholder="16 digit NIK KTP"
                            />
                        </div>

                        <div>
                            <label for="whatsapp" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Nomor WhatsApp / HP Aktif <span class="text-rose-400">*</span>
                            </label>
                            <input
                                id="whatsapp"
                                name="whatsapp"
                                type="text"
                                value="{{ old('whatsapp') }}"
                                required
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="Contoh: 081234567890"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="sk_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Nomor Anggota IPPTI <span class="text-slate-500 font-normal normal-case">(Kosongkan jika mendaftar sebagai Anggota Baru)</span>
                            </label>
                            <div class="flex gap-2">
                                <input
                                    id="sk_number"
                                    name="sk_number"
                                    type="text"
                                    value="{{ old('sk_number') }}"
                                    class="appearance-none block flex-1 px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200 font-mono"
                                    placeholder="Kosongkan untuk Anggota Baru (atau ketik No. Anggota jika sudah punya)"
                                />
                                <button
                                    type="button"
                                    onclick="checkMember()"
                                    id="btn-check-member"
                                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer flex-shrink-0"
                                    title="Khusus bagi anggota lama yang ingin memuat data otomatis"
                                >
                                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                    <span id="btn-check-text">Cek No. Anggota</span>
                                </button>
                            </div>
                            <p class="mt-1.5 text-[11px] text-slate-400">
                                Bagi <strong class="text-emerald-400">Pendaftar Anggota Baru IPPTI</strong>, biarkan kolom ini kosong — Nomor Anggota resmi akan ditetapkan oleh Admin saat verifikasi.
                            </p>
                        </div>

                        <div>
                            <label for="city" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Kota / Provinsi Domisili
                            </label>
                            <input
                                id="city"
                                name="city"
                                type="text"
                                value="{{ old('city') }}"
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="Contoh: DKI Jakarta"
                            />
                        </div>

                        <div>
                            <label for="address" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Alamat Kantor / Domisili
                            </label>
                            <input
                                id="address"
                                name="address"
                                type="text"
                                value="{{ old('address') }}"
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="Alamat lengkap penerjemah"
                            />
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- BAGIAN 2: SERTIFIKASI SK KEMENKUM RI       -->
                <!-- ========================================== -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-slate-800 pb-2.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-extrabold flex items-center justify-center">2</span>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Data SK Penerjemah Tersumpah (Kemenkum RI)</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="no_sk_kemenkum" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Nomor SK Menteri Hukum RI <span class="text-rose-400">*</span>
                            </label>
                            <input
                                id="no_sk_kemenkum"
                                name="no_sk_kemenkum"
                                type="text"
                                value="{{ old('no_sk_kemenkum') }}"
                                required
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200 font-mono"
                                placeholder="Contoh: AHU-55.AH.03.07.2022"
                            />
                        </div>

                        <div>
                            <label for="tgl_sk" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Tanggal SK Menteri Hukum RI <span class="text-rose-400">*</span>
                            </label>
                            <input
                                id="tgl_sk"
                                name="tgl_sk"
                                type="text"
                                value="{{ old('tgl_sk') }}"
                                required
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="Contoh: 5 Oktober 2022"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="language_services" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Pasangan / Arah Bahasa Tersumpah <span class="text-rose-400">*</span>
                            </label>
                            <input
                                id="language_services"
                                name="language_services"
                                type="text"
                                value="{{ old('language_services') }}"
                                required
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="Contoh: Indonesia - Inggris, Inggris - Indonesia"
                            />
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <span class="text-[10px] text-slate-400 font-semibold self-center mr-1">Pilih Cepat:</span>
                                @foreach(['Indonesia - Inggris, Inggris - Indonesia', 'Indonesia - Mandarin, Mandarin - Indonesia', 'Indonesia - Jepang, Jepang - Indonesia', 'Indonesia - Arab, Arab - Indonesia', 'Indonesia - Belanda, Belanda - Indonesia', 'Indonesia - Jerman, Jerman - Indonesia', 'Indonesia - Prancis, Prancis - Indonesia'] as $presetLang)
                                    <button
                                        type="button"
                                        onclick="document.getElementById('language_services').value = '{{ $presetLang }}'"
                                        class="px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-emerald-600/30 border border-slate-700 hover:border-emerald-500/40 text-[10px] font-semibold text-slate-300 hover:text-emerald-300 transition cursor-pointer"
                                    >
                                        {{ explode(',', $presetLang)[0] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label for="profile_picture" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Pasfoto Resmi Penerjemah (JPG/PNG)
                            </label>
                            <input
                                id="profile_picture"
                                name="profile_picture"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-emerald-400 hover:file:bg-slate-700 border border-slate-800 rounded-xl bg-slate-950/60 p-1.5 cursor-pointer"
                            />
                            <p class="text-[10px] text-slate-500 mt-1">Ditampilkan pada Kartu Resmi Verifikasi Penerjemah (Maks 2MB).</p>
                        </div>

                        <div>
                            <label for="sk_document" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Unggah Salinan SK Kemenkum RI (PDF/JPG)
                            </label>
                            <input
                                id="sk_document"
                                name="sk_document"
                                type="file"
                                accept=".pdf,image/jpeg,image/png,image/jpg,image/webp"
                                class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-amber-400 hover:file:bg-slate-700 border border-slate-800 rounded-xl bg-slate-950/60 p-1.5 cursor-pointer"
                            />
                            <p class="text-[10px] text-slate-500 mt-1">Dokumen pendukung verifikasi keanggotaan oleh Admin (Maks 5MB).</p>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- BAGIAN 3: KREDENSIAL LOGIN AKUN            -->
                <!-- ========================================== -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-slate-800 pb-2.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-extrabold flex items-center justify-center">3</span>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Kredensial Login Sistem DocVerify IPPTI</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Alamat Email Aktif (Untuk Login) <span class="text-rose-400">*</span>
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                class="appearance-none block w-full px-3.5 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                placeholder="penerjemah@example.com"
                            />
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Password Akun <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    class="appearance-none block w-full pl-3.5 pr-10 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                    placeholder="Minimal 6 karakter"
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
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Konfirmasi Password <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    class="appearance-none block w-full pl-3.5 pr-10 py-2.5 border border-slate-800 rounded-xl bg-slate-950/60 placeholder-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 text-sm font-semibold transition-all duration-200"
                                    placeholder="Ulangi password"
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
                    </div>
                </div>

                <div class="pt-3">
                    <button
                        type="submit"
                        id="submit-btn"
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] transition-all duration-200 items-center gap-2 cursor-pointer"
                    >
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                        <span id="submit-text">Kirim Pendaftaran Anggota IPPTI & Buat Akun</span>
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
            const noSkInput = document.getElementById('no_sk_kemenkum');
            const tglSkInput = document.getElementById('tgl_sk');
            const langInput = document.getElementById('language_services');
            const submitText = document.getElementById('submit-text');

            if (!memberNo) {
                errorDiv.innerText = 'Masukkan Nomor Anggota IPPTI jika ingin mengecek data pra-impor, atau kosongkan jika Anda pendaftar anggota baru.';
                errorDiv.classList.remove('hidden');
                successDiv.classList.add('hidden');
                return;
            }

            btn.setAttribute('disabled', 'disabled');
            btnText.innerText = 'Mengecek...';
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch(`/api/check-member/${encodeURIComponent(memberNo)}`);
                const data = await response.json();

                if (data.already_registered) {
                    errorDiv.innerHTML = `
                        <div class="space-y-2 py-1">
                            <p class="text-sm font-bold text-amber-200">Anda sudah terdaftar, silahkan login</p>
                            <p class="text-slate-400 text-xs">Nomor anggota ini sudah memiliki akun aktif di DocVerify IPPTI.</p>
                            <div class="pt-1">
                                <a href="/login" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-xs transition shadow-sm">
                                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                    <span>Masuk ke Akun (Login)</span>
                                </a>
                            </div>
                        </div>
                    `;
                    errorDiv.className = "bg-amber-500/10 border border-amber-500/30 text-amber-300 p-4 rounded-xl text-xs text-center font-semibold leading-relaxed";
                    errorDiv.classList.remove('hidden');
                    lucide.createIcons();
                    return;
                }

                if (data.success && data.translator) {
                    nameInput.value = data.translator.name || '';
                    if (data.translator.email) emailInput.value = data.translator.email;
                    if (data.translator.whatsapp) whatsappInput.value = data.translator.whatsapp;
                    if (data.translator.no_sk_kemenkum) noSkInput.value = data.translator.no_sk_kemenkum;
                    if (data.translator.tgl_sk) tglSkInput.value = data.translator.tgl_sk;
                    if (data.translator.language_services) langInput.value = data.translator.language_services;
                    
                    successText.innerText = `Data Anggota Ditemukan! Profil "${data.translator.name}" berhasil dimuat otomatis. Silakan lengkapi data & password Anda.`;
                    successDiv.classList.remove('hidden');
                    submitText.innerText = 'Klaim Keanggotaan & Buat Akun';
                } else {
                    errorDiv.className = "bg-amber-500/10 border border-amber-500/20 text-amber-300 p-3.5 rounded-xl text-xs text-center font-semibold leading-relaxed";
                    errorDiv.innerText = 'Nomor Anggota belum terdaftar di database pra-impor. Anda tetap dapat melanjutkan pendaftaran sebagai Anggota Baru IPPTI dengan mengisi formulir di bawah ini.';
                    errorDiv.classList.remove('hidden');
                    submitText.innerText = 'Kirim Pendaftaran Anggota IPPTI & Buat Akun';
                }
            } catch (err) {
                errorDiv.innerText = 'Gagal memeriksa nomor anggota.';
                errorDiv.classList.remove('hidden');
            } finally {
                btn.removeAttribute('disabled');
                btnText.innerText = 'Cek No. Anggota';
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
