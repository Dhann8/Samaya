<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'SaMaya - Sistem Absensi QR Code & Presensi')</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- QRCode JS library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        bluebrand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .clean-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(37, 99, 235, 0.05);
        }
        .clean-card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .clean-card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -4px rgba(37, 99, 235, 0.12);
            border-color: #bfdbfe;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .bg-blue-gradient {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
        }
        .bg-light-mesh {
            background-color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(219, 234, 254, 0.5) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(239, 246, 255, 0.8) 0px, transparent 50%);
        }
    </style>
</head>
<body class="h-full bg-light-mesh text-slate-800 flex flex-col font-sans antialiased selection:bg-blue-600 selection:text-white">

    @auth
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between z-20 shrink-0 shadow-sm">
            <div>
                <!-- Brand Header -->
                <div class="h-20 flex items-center px-6 border-b border-slate-100 gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25 text-white">
                        <i class="fa-solid fa-qrcode text-lg"></i>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-lg text-slate-900 tracking-wide leading-none">Sa<span class="text-blue-600">Maya</span></h1>
                        <span class="text-xs font-medium text-slate-500">Sistem Presensi QR</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1.5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 border border-blue-200/70 shadow-sm' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-chart-pie text-lg w-5 text-center {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                        <span>Dashboard Utama</span>
                    </a>

                    <a href="{{ route('absensi.qr') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('absensi.qr') ? 'bg-blue-50 text-blue-700 border border-blue-200/70 shadow-sm' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-qrcode text-lg w-5 text-center {{ request()->routeIs('absensi.qr') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                        <span class="flex-1">Absen QR Code</span>
                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-600 text-white rounded-full uppercase tracking-wider">Harian</span>
                    </a>

                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('absensi.index') ? 'bg-blue-50 text-blue-700 border border-blue-200/70 shadow-sm' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-clipboard-user text-lg w-5 text-center {{ request()->routeIs('absensi.index') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                        <span>Data Absensi</span>
                    </a>

                    <a href="{{ route('absensi.persetujuan') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('absensi.persetujuan') ? 'bg-blue-50 text-blue-700 border border-blue-200/70 shadow-sm' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-file-circle-check text-lg w-5 text-center {{ request()->routeIs('absensi.persetujuan') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                        <span class="flex-1">Persetujuan Izin</span>
                        @php
                            $pendingQuery = \App\Models\Absen::whereIn('status', ['Izin', 'Sakit'])->where('status_persetujuan', 'Pending');
                            if (Auth::user()->role === 'guru' && Auth::user()->kelas) {
                                $pendingQuery->where('kelas', Auth::user()->kelas);
                            }
                            $pendingCount = $pendingQuery->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-500 text-white rounded-full">{{ $pendingCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700 border border-blue-200/70 shadow-sm' : 'text-slate-600 hover:text-blue-600 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-users text-lg w-5 text-center {{ request()->routeIs('users.*') ? 'text-blue-600' : 'text-slate-400' }}"></i>
                        <span>Data Users</span>
                    </a>
                </nav>
            </div>

            <!-- User Profile & Quick Settings -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 border border-blue-200 flex items-center justify-center shrink-0 text-blue-600">
                            <i class="fa-solid fa-user-check text-sm"></i>
                        </div>
                        <div class="truncate">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs font-medium text-slate-500 truncate capitalize">
                                {{ Auth::user()->role === 'guru' ? 'Guru (' . (Auth::user()->kelas ?? 'Wali Kelas') . ')' : (Auth::user()->role ?? 'User') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('settings.index') }}" title="Pengaturan & Logout" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-blue-100 hover:text-blue-600 text-slate-500 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-gear text-xs"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden bg-transparent">
            <!-- Top Navbar -->
            <header class="h-20 border-b border-slate-200/70 bg-white/80 backdrop-blur-md px-8 flex items-center justify-between z-10">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                        @yield('header_title', 'Dashboard')
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center bg-blue-50/80 border border-blue-100 px-4 py-2 rounded-xl">
                        <div class="text-xs font-semibold text-slate-700">
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content Body -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-xl text-emerald-600"></i>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-exclamation text-xl text-rose-600"></i>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @else
        <!-- Guest View (Login) -->
        <main class="min-h-screen flex items-center justify-center p-4 bg-light-mesh">
            @yield('content')
        </main>
    @endauth

</body>
</html>