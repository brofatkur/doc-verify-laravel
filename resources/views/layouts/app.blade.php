<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DocVerify IPPTI - Portal Dashboard')</title>
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
    </style>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-900 selection:bg-emerald-500 selection:text-slate-950">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar -->
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

            <nav class="flex-1 px-4 py-4 space-y-2">
                <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin') || request()->is('admin/documents*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="file-text" class="w-5 h-5 {{ request()->is('admin') || request()->is('admin/documents*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span class="font-medium">Data Dokumen</span>
                </a>
                
                @if(Auth::check() && (Auth::user()->role === 'SUPERADMIN' || Auth::user()->role === 'ADMIN'))
                    <a href="/admin/users" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/users*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="award" class="w-5 h-5 {{ request()->is('admin/users*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Manajemen User</span>
                    </a>
                    <a href="/admin/document-types" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/document-types*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="folder" class="w-5 h-5 {{ request()->is('admin/document-types*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Master Tipe Dokumen</span>
                    </a>
                    <a href="/admin/language-directions" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->is('admin/language-directions*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="languages" class="w-5 h-5 {{ request()->is('admin/language-directions*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span class="font-medium">Master Arah Bahasa</span>
                    </a>
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
                        <p class="text-xs text-slate-400 truncate">
                            @if(Auth::user()->role === 'SUPERADMIN')
                                Pengurus IPPTI (Super Admin)
                            @elseif(Auth::user()->role === 'ADMIN')
                                Pengurus IPPTI (Admin)
                            @else
                                Penerjemah
                            @endif
                        </p>
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

        <!-- Mobile Topbar -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <a href="https://ippti.or.id" target="_blank" title="Kunjungi Website Resmi IPPTI">
                    <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-6 w-auto rounded bg-white p-0.5 object-contain hover:opacity-90 transition" />
                </a>
                <a href="/admin" class="text-lg font-bold text-white hover:underline">DocVerify Panel</a>
            </div>
            
            <div class="flex items-center gap-3">
                @if(Auth::check() && (Auth::user()->role === 'SUPERADMIN' || Auth::user()->role === 'ADMIN'))
                    <a href="/admin/users" class="text-slate-350 p-1 {{ request()->is('admin/users*') ? 'text-emerald-400' : '' }}" title="Manajemen User">
                        <i data-lucide="award" class="w-5 h-5"></i>
                    </a>
                @endif
                <a href="/admin/profile" class="text-slate-350 p-1 {{ request()->is('admin/profile*') ? 'text-emerald-400' : '' }}" title="Profil">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                </a>
                <form action="/logout" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-400 p-2 cursor-pointer">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Content Area with Desktop Top Header (REV-17) -->
        <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
            @auth
                <header class="hidden md:flex bg-white border-b border-slate-200 h-16 items-center justify-between px-8 z-10 flex-shrink-0">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 font-mono">
                        <span>PANEL DASHBOARD</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                        <span class="text-slate-600">
                            @if(request()->is('admin/users*')) MANAJEMEN USER @elseif(request()->is('admin/profile*')) PENGATURAN PROFIL @elseif(request()->is('admin/document-types*')) MASTER TIPE DOKUMEN @elseif(request()->is('admin/language-directions*')) MASTER ARAH BAHASA @else MANAJEMEN DOKUMEN @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-4">
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

            <!-- Main Content Body -->
            <main class="flex-1 p-4 md:p-8 overflow-y-auto bg-slate-50/50">
                @yield('content')
            </main>
        </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
