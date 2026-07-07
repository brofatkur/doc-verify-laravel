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
                <a href="/admin" class="flex items-center gap-3 text-xl font-bold text-white">
                    <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-8 w-auto rounded bg-white p-0.5 object-contain shadow-md" />
                    <span>DocVerify</span>
                </a>
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
            <a href="/admin" class="flex items-center gap-2 text-lg font-bold">
                <img src="/ippti-logo.jpg" alt="IPPTI Logo" class="h-6 w-auto rounded bg-white p-0.5 object-contain" />
                <span>DocVerify Panel</span>
            </a>
            
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

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
