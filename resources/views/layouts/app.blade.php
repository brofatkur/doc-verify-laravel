<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'DocVerify IPPTI - Portal Dashboard')</title>

    <!-- PWA & Mobile App Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#059669">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DocVerify">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icon-512.png">

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
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 16px);
        }
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-900 selection:bg-emerald-500 selection:text-slate-950">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Desktop Sidebar -->
        <aside class="w-full md:w-64 bg-slate-900 text-white flex flex-col hidden md:flex">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <a href="https://ippti.or.id" target="_blank" title="Kunjungi Website Resmi IPPTI">
                        <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-8 w-auto rounded bg-white p-0.5 object-contain shadow-md hover:opacity-90 transition" />
                    </a>
                    <a href="/admin" class="text-xl font-bold text-white hover:underline">DocVerify</a>
                </div>
                <p class="text-slate-400 text-xs mt-2">Panel Dashboard</p>
            </div>

            <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
                <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin') || request()->is('admin/documents*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="file-text" class="w-5 h-5 {{ request()->is('admin') || request()->is('admin/documents*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span class="font-medium">Data Dokumen</span>
                </a>
                
                @if(Auth::check() && in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN']))
                    <a href="/admin/users" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/users*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="award" class="w-5 h-5 {{ request()->is('admin/users*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Manajemen User</span>
                    </a>
                    <a href="/admin/finance" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/finance*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="wallet" class="w-5 h-5 {{ request()->is('admin/finance*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Keuangan & Bagi Hasil</span>
                    </a>
                    <a href="/admin/vouchers" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/vouchers*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="ticket" class="w-5 h-5 {{ request()->is('admin/vouchers*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Voucher Diskon</span>
                    </a>
                    <a href="/admin/document-types" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/document-types*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="folder" class="w-5 h-5 {{ request()->is('admin/document-types*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Master Tipe Dokumen</span>
                    </a>
                    <a href="/admin/language-directions" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/language-directions*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="languages" class="w-5 h-5 {{ request()->is('admin/language-directions*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Master Arah Bahasa</span>
                    </a>
                    <a href="/admin/settings" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/settings*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="sliders" class="w-5 h-5 {{ request()->is('admin/settings*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Pengaturan Aplikasi</span>
                    </a>
                @endif

                @if(Auth::check() && Auth::user()->role === 'TRANSLATOR')
                    @if(Auth::user()->isReguler())
                        <a href="/admin/upgrade" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/upgrade*') ? 'bg-amber-600 text-white shadow-sm' : 'text-amber-400 hover:bg-slate-800 hover:text-amber-300' }}">
                            <i data-lucide="zap" class="w-5 h-5 fill-amber-400"></i>
                            <span class="font-bold">Upgrade Mode PRO</span>
                        </a>
                    @else
                        <a href="/admin/transactions" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/transactions*') ? 'bg-amber-600 text-white shadow-sm' : 'text-amber-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="receipt" class="w-5 h-5 text-amber-400"></i>
                            <span class="font-bold">Riwayat Pembelian & Topup</span>
                        </a>
                    @endif
                @endif

                @if(Auth::check() && Auth::user()->role === 'SUPERADMIN')
                    <a href="/admin/audit-logs" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/audit-logs*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="activity" class="w-5 h-5 {{ request()->is('admin/audit-logs*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Log Audit</span>
                    </a>
                @endif

                <a href="/admin/profile" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/profile*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="settings" class="w-5 h-5 {{ request()->is('admin/profile*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span class="font-medium">Profil & Layanan</span>
                </a>

                <a href="/PANDUAN_LENGKAP_DOCVERIFY_IPPTI.pdf" target="_blank" class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition text-slate-400 hover:bg-slate-800 hover:text-white mt-3 border border-slate-800">
                    <i data-lucide="file-text" class="w-5 h-5 text-amber-400"></i>
                    <span class="font-medium text-xs">Buku Panduan (.PDF)</span>
                </a>
            </nav>

            @auth
                <div class="px-6 py-4 border-t border-slate-800/40 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20 font-bold uppercase flex-shrink-0 overflow-hidden">
                        @if(Auth::user()->profile_picture)
                            <img src="{{ Auth::user()->profile_picture }}" alt="Profile" class="w-full h-full object-cover" />
                        @else
                            {{ substr(Auth::user()->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-semibold truncate text-slate-200">{{ Auth::user()->name }}</p>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="text-xs text-slate-400 truncate">
                                @if(Auth::user()->role === 'SUPERADMIN')
                                    Pengurus IPPTI (Super Admin)
                                @elseif(Auth::user()->role === 'ADMIN')
                                    Pengurus IPPTI (Admin)
                                @else
                                    Penerjemah
                                @endif
                            </span>
                            @if(Auth::user()->role === 'TRANSLATOR')
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black {{ Auth::user()->isPro() ? 'bg-amber-400/20 text-amber-300 border border-amber-400/30' : 'bg-slate-700 text-slate-300' }}">
                                    {{ strtoupper(Auth::user()->user_level ?? 'REGULER') }}
                                </span>
                            @endif
                        </div>
                        @if(Auth::user()->role === 'TRANSLATOR')
                            <div class="mt-1 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20" title="Saldo Poin Penggunaan Layanan">
                                <i data-lucide="coins" class="w-3 h-3 text-amber-400"></i>
                                <span>{{ number_format(Auth::user()->points ?? 0, 0, ',', '.') }} Poin</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endauth

            <div class="p-4 border-t border-slate-800/40">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-red-400 transition cursor-pointer">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span class="font-medium">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Topbar with App-like Header -->
        <header class="md:hidden bg-slate-900 text-white p-3.5 flex justify-between items-center sticky top-0 z-30 shadow-md border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    onclick="toggleMobileDrawer()"
                    class="p-2 -ml-1 text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 active:bg-slate-700 transition cursor-pointer"
                    aria-label="Buka Menu"
                >
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-2">
                    <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-6 w-auto rounded bg-white p-0.5 object-contain" />
                    <span class="text-base font-extrabold text-white tracking-tight">DocVerify</span>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                @if(Auth::check() && Auth::user()->role === 'TRANSLATOR')
                    <a href="/admin/upgrade" class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                        <i data-lucide="coins" class="w-3 h-3 text-amber-400"></i>
                        <span>{{ number_format(Auth::user()->points ?? 0, 0, ',', '.') }}</span>
                    </a>
                @elseif(Auth::check() && in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN']))
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase">
                        {{ Auth::user()->role }}
                    </span>
                @endif

                <button
                    type="button"
                    onclick="toggleMobileDrawer()"
                    class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-xs uppercase overflow-hidden"
                >
                    @if(Auth::check() && Auth::user()->profile_picture)
                        <img src="{{ Auth::user()->profile_picture }}" alt="Profile" class="w-full h-full object-cover" />
                    @else
                        {{ substr(Auth::check() ? Auth::user()->name : 'U', 0, 1) }}
                    @endif
                </button>
            </div>
        </header>

        <!-- Mobile Drawer Off-canvas Navigation (Slide from Left) -->
        <div id="mobile-drawer-backdrop" onclick="toggleMobileDrawer()" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 transition-opacity opacity-0 pointer-events-none md:hidden"></div>
        
        <aside id="mobile-drawer" class="fixed top-0 left-0 bottom-0 w-4/5 max-w-xs bg-slate-900 text-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col md:hidden shadow-2xl">
            <!-- Drawer Header -->
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-7 w-auto rounded bg-white p-0.5 object-contain" />
                    <div>
                        <h3 class="font-extrabold text-white text-base leading-tight">DocVerify</h3>
                        <p class="text-[11px] text-slate-400">Portal IPPTI Mobile</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="toggleMobileDrawer()"
                    class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition"
                    aria-label="Tutup Menu"
                >
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Drawer User Profile Info Card -->
            @auth
                <div class="p-4 mx-4 my-3 rounded-xl bg-slate-800/80 border border-slate-700/60 space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-sm uppercase overflow-hidden flex-shrink-0">
                            @if(Auth::user()->profile_picture)
                                <img src="{{ Auth::user()->profile_picture }}" alt="Profile" class="w-full h-full object-cover" />
                            @else
                                {{ substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="overflow-hidden flex-1">
                            <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">
                                @if(Auth::user()->role === 'SUPERADMIN')
                                    Pengurus (Super Admin)
                                @elseif(Auth::user()->role === 'ADMIN')
                                    Pengurus (Admin)
                                @else
                                    Penerjemah {{ Auth::user()->isPro() ? 'PRO' : 'Reguler' }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @if(Auth::user()->role === 'TRANSLATOR')
                        <div class="pt-2 border-t border-slate-700/50 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Saldo Poin:</span>
                            <span class="font-bold text-amber-300 font-mono">{{ number_format(Auth::user()->points ?? 0, 0, ',', '.') }} Poin</span>
                        </div>
                    @endif
                </div>
            @endauth

            <!-- Drawer Level-Based Navigation Links -->
            <nav class="flex-1 px-3 py-2 space-y-1.5 overflow-y-auto">
                <a href="/admin" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin') || request()->is('admin/documents*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i data-lucide="file-text" class="w-4 h-4 {{ request()->is('admin') || request()->is('admin/documents*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Data Dokumen</span>
                </a>

                @if(Auth::check() && in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN']))
                    <div class="pt-2 pb-1 px-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                        Menu Pengurus IPPTI
                    </div>
                    <a href="/admin/users" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/users*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i data-lucide="award" class="w-4 h-4 {{ request()->is('admin/users*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Manajemen User</span>
                    </a>
                    <a href="/admin/finance" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/finance*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i data-lucide="wallet" class="w-4 h-4 {{ request()->is('admin/finance*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Keuangan & Bagi Hasil</span>
                    </a>
                    <a href="/admin/vouchers" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/vouchers*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i data-lucide="ticket" class="w-4 h-4 {{ request()->is('admin/vouchers*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Voucher Diskon</span>
                    </a>
                    <a href="/admin/document-types" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/document-types*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i data-lucide="folder" class="w-4 h-4 {{ request()->is('admin/document-types*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Master Tipe Dokumen</span>
                    </a>
                    <a href="/admin/language-directions" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/language-directions*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i data-lucide="languages" class="w-4 h-4 {{ request()->is('admin/language-directions*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Master Arah Bahasa</span>
                    </a>
                    <a href="/admin/settings" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/settings*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i data-lucide="sliders" class="w-4 h-4 {{ request()->is('admin/settings*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Pengaturan Aplikasi</span>
                    </a>
                @endif

                @if(Auth::check() && Auth::user()->role === 'TRANSLATOR')
                    <div class="pt-2 pb-1 px-3 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                        Layanan Penerjemah
                    </div>
                    @if(Auth::user()->isReguler())
                        <a href="/admin/upgrade" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-bold {{ request()->is('admin/upgrade*') ? 'bg-amber-600 text-white' : 'text-amber-300 hover:bg-slate-800' }}">
                            <i data-lucide="zap" class="w-4 h-4 fill-amber-400"></i>
                            <span>Upgrade Mode PRO</span>
                        </a>
                    @else
                        <a href="/admin/transactions" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-bold {{ request()->is('admin/transactions*') ? 'bg-amber-600 text-white' : 'text-amber-300 hover:bg-slate-800' }}">
                            <i data-lucide="receipt" class="w-4 h-4 text-amber-400"></i>
                            <span>Riwayat Pembelian & Topup</span>
                        </a>
                    @endif
                @endif

                @if(Auth::check() && Auth::user()->role === 'SUPERADMIN')
                    <a href="/admin/audit-logs" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/audit-logs*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i data-lucide="activity" class="w-4 h-4 {{ request()->is('admin/audit-logs*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Log Audit Sistem</span>
                    </a>
                @endif

                <a href="/admin/profile" onclick="toggleMobileDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold {{ request()->is('admin/profile*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i data-lucide="settings" class="w-4 h-4 {{ request()->is('admin/profile*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Profil & Layanan</span>
                </a>

                <a href="/PANDUAN_LENGKAP_DOCVERIFY_IPPTI.pdf" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition text-sm font-semibold text-slate-300 hover:bg-slate-800 border border-slate-800/80 mt-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-amber-400"></i>
                    <span>Buku Panduan (.PDF)</span>
                </a>
            </nav>

            <!-- PWA Install Button inside Drawer -->
            <div class="p-3 border-t border-slate-800 space-y-2">
                <button
                    type="button"
                    id="btn-install-pwa-drawer"
                    onclick="triggerPWAInstall()"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md transition cursor-pointer"
                >
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Pasang Aplikasi di HP (PWA)</span>
                </button>

                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 px-3 py-2 rounded-xl text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 font-semibold text-xs transition cursor-pointer">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area with Desktop Top Header (REV-17) -->
        <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
            @auth
                <header class="hidden md:flex bg-white border-b border-slate-200 h-16 items-center justify-between px-8 z-10 flex-shrink-0">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 font-mono">
                        <span>PANEL DASHBOARD</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                        <span class="text-slate-600">
                            @if(request()->is('admin/users*')) MANAJEMEN USER @elseif(request()->is('admin/profile*')) PENGATURAN PROFIL @elseif(request()->is('admin/finance*')) LAPORAN KEUANGAN & BAGI HASIL @elseif(request()->is('admin/vouchers*')) VOUCHER DISKON @elseif(request()->is('admin/document-types*')) MASTER TIPE DOKUMEN @elseif(request()->is('admin/language-directions*')) MASTER ARAH BAHASA @elseif(request()->is('admin/upgrade*')) UPGRADE MODE PRO @elseif(request()->is('admin/transactions*')) RIWAYAT PEMBELIAN @else MANAJEMEN DOKUMEN @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            onclick="triggerPWAInstall()"
                            id="btn-install-pwa-desktop"
                            class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-xs font-bold transition cursor-pointer"
                        >
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Install App</span>
                        </button>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 font-bold uppercase overflow-hidden">
                                @if(Auth::user()->profile_picture)
                                    <img src="{{ Auth::user()->profile_picture }}" alt="Profile" class="w-full h-full object-cover" />
                                @else
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</span>
                        </div>
                        <form action="/logout" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-xs font-bold cursor-pointer transition">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </header>
            @endauth

            <!-- Main Content Body (with pb-24 for mobile dock bar safe area) -->
            <main class="flex-1 p-3.5 sm:p-6 md:p-8 overflow-y-auto bg-slate-50/50 pb-24 md:pb-8">
                @yield('content')
            </main>
        </div>

        <!-- Mobile Bottom Dock Navigation Bar (Always Visible on Mobile) -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 pb-safe shadow-2xl">
            <div class="grid {{ (Auth::check() && in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN'])) ? 'grid-cols-5' : 'grid-cols-4' }} items-center text-center py-2 px-1">
                <!-- Tab 1: Dokumen -->
                <a href="/admin" class="flex flex-col items-center gap-1 py-1 px-1 rounded-lg transition {{ request()->is('admin') || request()->is('admin/documents*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span class="text-[10px] tracking-tight truncate">Dokumen</span>
                </a>

                @if(Auth::check() && in_array(Auth::user()->role, ['SUPERADMIN', 'ADMIN']))
                    <!-- Tab 2 Admin: Keuangan -->
                    <a href="/admin/finance" class="flex flex-col items-center gap-1 py-1 px-1 rounded-lg transition {{ request()->is('admin/finance*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight truncate">Keuangan</span>
                    </a>
                    <!-- Tab 3 Admin: Users -->
                    <a href="/admin/users" class="flex flex-col items-center gap-1 py-1 px-1 rounded-lg transition {{ request()->is('admin/users*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                        <i data-lucide="award" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight truncate">User</span>
                    </a>
                    <!-- Tab 4 Admin: Vouchers -->
                    <a href="/admin/vouchers" class="flex flex-col items-center gap-1 py-1 px-1 rounded-lg transition {{ request()->is('admin/vouchers*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                        <i data-lucide="ticket" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight truncate">Voucher</span>
                    </a>
                @elseif(Auth::check() && Auth::user()->role === 'TRANSLATOR')
                    <!-- Tab 2 Translator: Topup / Upgrade -->
                    @if(Auth::user()->isReguler())
                        <a href="/admin/upgrade" class="flex flex-col items-center gap-1 py-1 px-1 rounded-lg transition {{ request()->is('admin/upgrade*') ? 'text-amber-400 font-bold' : 'text-amber-300/80 hover:text-amber-200' }}">
                            <i data-lucide="zap" class="w-5 h-5 fill-amber-400"></i>
                            <span class="text-[10px] tracking-tight truncate">Upgrade PRO</span>
                        </a>
                    @else
                        <a href="/admin/transactions" class="flex flex-col items-center gap-1 py-1 px-1 rounded-lg transition {{ request()->is('admin/transactions*') ? 'text-amber-400 font-bold' : 'text-amber-300/80 hover:text-amber-200' }}">
                            <i data-lucide="coins" class="w-5 h-5"></i>
                            <span class="text-[10px] tracking-tight truncate">Poin/Topup</span>
                        </a>
                    @endif
                    <!-- Tab 3 Translator: Profil -->
                    <a href="/admin/profile" class="flex flex-col items-center gap-1 py-1 px-1 rounded-lg transition {{ request()->is('admin/profile*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                        <span class="text-[10px] tracking-tight truncate">Profil</span>
                    </a>
                @endif

                <!-- Tab Last: Buka Menu Lengkap Drawer -->
                <button
                    type="button"
                    onclick="toggleMobileDrawer()"
                    class="flex flex-col items-center gap-1 py-1 px-1 text-slate-400 hover:text-white rounded-lg transition cursor-pointer"
                >
                    <i data-lucide="grid" class="w-5 h-5"></i>
                    <span class="text-[10px] tracking-tight truncate">Semua Menu</span>
                </button>
            </div>
        </nav>
    </div>

    <!-- PWA iOS Installation Helper Modal -->
    <div id="pwa-ios-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs z-50 flex items-end sm:items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-200 text-slate-900 space-y-4 animate-in fade-in slide-in-from-bottom duration-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <img src="/apple-touch-icon.png" alt="DocVerify" class="w-10 h-10 rounded-xl shadow-sm" />
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">Pasang di iPhone / iPad</h4>
                        <p class="text-[11px] text-slate-500">DocVerify IPPTI PWA</p>
                    </div>
                </div>
                <button type="button" onclick="closeIOSModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="space-y-3 text-xs text-slate-600 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                    <p>Ketuk tombol <strong>Share / Bagikan</strong> <i data-lucide="share" class="w-3.5 h-3.5 inline text-blue-600"></i> di bar bawah Safari.</p>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                    <p>Gulir ke bawah lalu pilih <strong>"Add to Home Screen"</strong> (Tambah ke Layar Utama).</p>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                    <p>Ketuk <strong>Add / Tambah</strong> di pojok kanan atas.</p>
                </div>
            </div>
            <button type="button" onclick="closeIOSModal()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs transition cursor-pointer">
                Mengerti
            </button>
        </div>
    </div>

    <!-- Scripts: Lucide Icons, Mobile Drawer Toggle & PWA Service Worker -->
    <script>
        // 1. Initialize Icons
        lucide.createIcons();

        // 2. Mobile Drawer Toggle
        function toggleMobileDrawer() {
            const drawer = document.getElementById('mobile-drawer');
            const backdrop = document.getElementById('mobile-drawer-backdrop');
            
            if (!drawer || !backdrop) return;

            const isOpen = !drawer.classList.contains('-translate-x-full');
            if (isOpen) {
                drawer.classList.add('-translate-x-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                document.body.style.overflow = '';
            } else {
                drawer.classList.remove('-translate-x-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                document.body.style.overflow = 'hidden';
            }
        }

        // 3. PWA Service Worker Registration & Installation Prompt Handler
        let deferredPrompt = null;

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('DocVerify PWA Service Worker Registered:', reg.scope))
                    .catch(err => console.log('DocVerify Service Worker Registration failed:', err));
            });
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const btnInstall = document.getElementById('btn-install-pwa-drawer');
            const btnDesk = document.getElementById('btn-install-pwa-desktop');
            if (btnInstall) btnInstall.classList.remove('hidden');
            if (btnDesk) btnDesk.classList.remove('hidden');
        });

        function triggerPWAInstall() {
            // Check if iOS Safari
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

            if (isIOS && !isStandalone) {
                document.getElementById('pwa-ios-modal').classList.remove('hidden');
                lucide.createIcons();
                return;
            }

            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted PWA installation');
                    }
                    deferredPrompt = null;
                });
            } else {
                alert('Aplikasi DocVerify IPPTI sudah terpasang atau browser Anda dapat menambahkannya melalui menu browser ("Add to Home screen").');
            }
        }

        function closeIOSModal() {
            document.getElementById('pwa-ios-modal').classList.add('hidden');
        }
    </script>
</body>
</html>

