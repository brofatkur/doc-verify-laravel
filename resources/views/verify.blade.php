<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocVerify IPPTI - Verifikasi Resmi Dokumen</title>
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
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col items-center justify-between p-4 sm:p-8 selection:bg-emerald-500 selection:text-slate-950">

    @if(!$document)
        <!-- Document Not Found Container -->
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden text-center p-8 border border-gray-200 my-auto">
            <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i data-lucide="x-circle" class="w-10 h-10 text-rose-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Dokumen Tidak Ditemukan</h1>
            <p class="text-gray-500 mb-6 leading-relaxed text-sm">
                Kami tidak dapat memverifikasi dokumen ini. Kode QR mungkin tidak valid, telah dicabut, atau dokumen belum terdaftar di sistem kami.
            </p>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
                <p class="text-sm font-mono text-gray-500">ID Dokumen: {{ $documentId }}</p>
            </div>
            <a href="/" class="inline-flex items-center justify-center w-full px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-medium transition-colors duration-200">
                Kembali ke Beranda
            </a>
        </div>
    @else
        <!-- Header -->
        <div class="w-full max-w-lg mb-8 text-center pt-4">
            <div class="inline-flex items-center justify-center gap-3 mb-2">
                <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-10 w-auto rounded bg-white p-0.5 object-contain shadow-sm" />
                <span class="text-xl font-bold text-slate-900 tracking-tight">DocVerify</span>
            </div>
            <p class="text-xs text-slate-500 font-medium">Portal Verifikasi Resmi Terjemahan Tersumpah</p>
        </div>

        <!-- Verification Card -->
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden mb-8">
            <!-- Header Banner -->
            <div class="bg-gradient-to-br from-emerald-600 via-teal-700 to-emerald-800 p-8 text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-white/10 to-transparent"></div>
                
                <div class="relative z-10 space-y-3">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-md ring-4 ring-emerald-500/30">
                        <i data-lucide="check-circle-2" class="w-10 h-10 text-emerald-600"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-white tracking-widest">TERVERIFIKASI</h1>
                        <p class="text-xs text-emerald-100 font-semibold tracking-wider uppercase mt-0.5">Rekam Dokumen Resmi IPPTI</p>
                    </div>
                </div>
            </div>

            <!-- Details list -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row gap-4 border-b border-slate-100 pb-6">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">ID Dokumen</p>
                        <p class="text-lg font-mono font-bold text-slate-900">{{ $document->document_id }}</p>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">No. Registrasi</p>
                        <p class="text-base font-bold text-slate-800 font-mono">{{ $document->registration_number }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Terjemah</p>
                        <p class="text-base font-semibold text-slate-800">
                            {{ $document->document_date->translatedFormat('d F Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Status Verifikasi</p>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ $document->status }}
                            </span>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nama di Dokumen (Disamarkan)</p>
                        <p class="text-base font-mono font-bold text-slate-900 mt-1 bg-slate-50 border border-slate-200/80 p-3 rounded-xl select-none tracking-wide">
                            @php
                                $words = explode(' ', $document->client_name);
                                $masked = array_map(function($w) {
                                    return strlen($w) <= 1 ? $w : $w[0] . str_repeat('*', strlen($w) - 1);
                                }, $words);
                                echo implode(' ', $masked);
                            @endphp
                        </p>
                    </div>

                    <div class="sm:col-span-2 bg-slate-50 rounded-2xl p-4.5 border border-slate-200/60">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Pasangan Bahasa</p>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-800 bg-white px-3.5 py-1.5 rounded-lg border border-slate-200/80 shadow-sm">{{ $document->language_pair }}</span>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipe Dokumen</p>
                        <p class="text-base font-semibold text-slate-800 mt-1">{{ $document->document_type }}</p>
                    </div>

                    <!-- Sworn Translator Badge Box -->
                    <div class="sm:col-span-2 border-t border-slate-100 pt-6">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Penerjemah Tersumpah</p>
                        <div class="bg-slate-50/60 border border-slate-100 rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0 border border-emerald-100 shadow-sm overflow-hidden">
                                    @if($document->translator->profile_picture)
                                        <img src="{{ $document->translator->profile_picture }}" alt="{{ $document->translator->name }}" class="w-full h-full object-cover" />
                                    @else
                                        <i data-lucide="file-text" class="w-5 h-5 text-emerald-600"></i>
                                    @endif
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-base font-bold text-slate-900 truncate">{{ $document->translator->name }}</p>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5">No. Anggota: {{ $document->translator->sk_number }}</p>
                                </div>
                            </div>
                            @if($document->translator->language_services)
                                <div class="text-xs border-t border-slate-100/85 pt-3">
                                    <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Layanan Bahasa:</span>
                                    <p class="text-slate-700 font-semibold">{{ $document->translator->language_services }}</p>
                                </div>
                            @endif
                            @if($document->translator->bio)
                                <div class="text-xs border-t border-slate-100/85 pt-3">
                                    <span class="font-bold text-slate-400 uppercase tracking-wider block mb-1">Biografi:</span>
                                    <p class="text-slate-600 leading-relaxed italic text-justify">"{{ $document->translator->bio }}"</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secure Disclaimer box -->
            <div class="bg-amber-50/40 p-6 border-t border-slate-100 border-l-4 border-l-amber-500">
                <div class="flex items-start gap-3">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                    <p class="text-xs text-slate-600 leading-relaxed text-justify">
                        <strong class="text-slate-800 font-bold">Disclaimer Resmi:</strong> Sistem ini memverifikasi bahwa terjemahan dokumen ini telah resmi terdaftar oleh Penerjemah Tersumpah yang terasosiasi di atas. Harap pastikan fisik dokumen memiliki cap basah/segel pengaman yang sesuai untuk validitas hukum sepenuhnya.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Bottom Credit -->
    <div class="text-center text-[10px] text-slate-400 uppercase tracking-widest space-y-1 py-4">
        <p>&copy; {{ date('Y') }} DocVerify IPPTI. Seluruh Hak Cipta Dilindungi.</p>
        <p class="font-semibold text-slate-400">DIVERIFIKASI SECARA ELEKTRONIK & KRIPTOGRAFIS</p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
