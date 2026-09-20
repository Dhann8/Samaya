@extends('layout.app')

@section('title', 'Login - SaMaya QR Presensi')
    
@section('content')
<div class="w-full max-w-md p-4">
    <div class="clean-card rounded-3xl p-8 shadow-2xl relative overflow-hidden bg-white border border-slate-100">
        <!-- Blue Accent Gradient Line top -->
        <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600"></div>

        <!-- Header -->
        <div class="text-center mb-8 relative z-10 pt-2">
            <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-blue-600/30 mb-4 text-white">
                <i class="fa-solid fa-qrcode text-2xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sa<span class="text-blue-600">Maya</span></h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Sistem Absensi QR Code & Pelacakan GPS</p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-5 relative z-10">
            @csrf

            <div>
                <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email / NIS</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-id-card text-sm"></i>
                    </span>
                    <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus
                        placeholder="Masukkan Email atau NIS"
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                @error('login')
                    <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" name="password" id="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-sm font-medium focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                @error('password')
                    <p class="text-xs text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600 hover:text-slate-900 font-medium">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all duration-200 flex items-center justify-center gap-2">
                <span>Masuk Sekarang</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400 font-medium bg-slate-50 p-3 rounded-xl border border-slate-100">
            Akun Demo Admin: <span class="text-blue-600 font-mono font-bold">admin@admin.com</span> / <span class="text-blue-600 font-mono font-bold">password</span>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
            <a href="{{ route('landing') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Halaman Landing Page
            </a>
            <a href="{{ route('download.apk') }}" class="text-emerald-600 hover:text-emerald-800 transition-colors">
                <i class="fa-solid fa-download mr-1"></i> Unduh APK Mobile
            </a>
        </div>
    </div>
</div>
@endsection