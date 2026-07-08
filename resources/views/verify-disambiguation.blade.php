<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disambiguasi Verifikasi Dokumen - DocVerify IPPTI</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: "Plus Jakarta Sans", sans-serif;
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-blue-950 min-h-screen flex flex-col justify-between selection:bg-blue-500/20 selection:text-blue-950">

    <!-- Header -->
    <header class="py-6 px-6 md:px-12 border-b border-slate-200 bg-white/80 backdrop-blur-xl flex items-center justify-between z-10">
        <a href="/" class="flex items-center gap-3">
            <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-9 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
            <span class="text-xl font-bold tracking-tight text-blue-950">DocVerify</span>
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-4xl mx-auto w-full p-6 sm:p-12 space-y-8">
        <div class="text-center space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-100 text-amber-800 text-xs font-semibold uppercase tracking-wide">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                Beberapa Dokumen Ditemukan
            </div>
            <h1 class="text-3xl font-extrabold text-blue-950 tracking-tight leading-tight">
                Pilih Dokumen Penerjemah
            </h1>
            <p class="text-slate-500 text-sm max-w-md mx-auto">
                Nomor registrasi <span class="font-mono font-bold text-slate-800">"{{ $regNumber }}"</span> terdaftar pada beberapa dokumen terjemahan. Silakan pilih dokumen yang sesuai berdasarkan nama penerjemah di bawah ini.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($documents as $doc)
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-md hover:shadow-lg hover:border-blue-400 transition-all duration-300 flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <!-- Translator info card style -->
                        <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200 overflow-hidden flex-shrink-0">
                                @if($doc->translator->profile_picture)
                                    <img src="{{ $doc->translator->profile_picture }}" alt="Profile" class="w-full h-full object-cover" />
                                @else
                                    <i data-lucide="user" class="w-6 h-6"></i>
                                @endif
                            </div>
                            <div class="overflow-hidden">
                                <h3 class="font-bold text-slate-950 text-sm truncate">{{ $doc->translator->name }}</h3>
                                <p class="text-xs text-slate-400 font-mono mt-0.5">No. Anggota: {{ $doc->translator->sk_number }}</p>
                            </div>
                        </div>

                        <!-- Document properties -->
                        <div class="space-y-2.5 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-medium">Tipe Dokumen:</span>
                                <span class="font-semibold text-slate-800">{{ $doc->document_type }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-medium">Pasangan Bahasa:</span>
                                <span class="font-bold text-blue-900">{{ $doc->language_pair }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-medium">Nama di Dokumen:</span>
                                <span class="font-mono text-slate-700">
                                    @php
                                        $words = explode(' ', $doc->client_name);
                                        $masked = array_map(function($w) {
                                            return strlen($w) <= 1 ? $w : $w[0] . str_repeat('*', strlen($w) - 1);
                                        }, $words);
                                        echo implode(' ', $masked);
                                    @endphp
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-medium">Tanggal:</span>
                                <span class="font-medium text-slate-500">{{ $doc->document_date ? $doc->document_date->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="/verify/{{ $doc->document_id }}" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-blue-950 hover:bg-blue-900 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                        <span>Pilih & Verifikasi Dokumen</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="text-center pt-4">
            <a href="/" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-850 hover:underline">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Pencarian</span>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 border-t border-slate-200 text-center text-xs text-slate-400 bg-white">
        &copy; {{ date('Y') }} IPPTI (Ikatan Penerjemah Tersumpah Indonesia). All rights reserved.
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
