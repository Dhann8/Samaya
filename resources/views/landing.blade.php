<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaMaya Mobile - Sistem Absensi Digital & Presensi QR Code Sekolah</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- AOS (Animate On Scroll) CSS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
                        brand: {
                            50: '#F0F6FF',
                            100: '#E0EDFF',
                            200: '#BAAEFF',
                            500: '#2563EB',
                            600: '#1E56A0',
                            700: '#16437E',
                            800: '#10325E',
                            900: '#0C2343',
                        }
                    },
                    boxShadow: {
                        'soft-blue': '0 12px 35px -8px rgba(30, 86, 160, 0.08), 0 4px 15px -3px rgba(0, 0, 0, 0.03)',
                        'hover-blue': '0 20px 40px -10px rgba(30, 86, 160, 0.15), 0 8px 20px -5px rgba(30, 86, 160, 0.08)',
                        'glow': '0 0 25px rgba(37, 99, 235, 0.25)',
                    }
                }
            }
        }
    </script>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #1E56A0 0%, #2563EB 50%, #0284C7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #1E56A0 0%, #1E40AF 100%);
        }
        .bg-pattern {
            background-image: radial-gradient(rgba(37, 99, 235, 0.06) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-white text-slate-800 font-sans antialiased selection:bg-blue-500 selection:text-white" x-data="{ mobileMenu: false }">

    <!-- NAVBAR HEADER -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-brand-600 flex items-center justify-center text-white shadow-md shadow-brand-600/30 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-qrcode text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-extrabold text-slate-900 tracking-tight">SaMaya</span>
                            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-100 text-brand-600 rounded-full">MOBILE</span>
                        </div>
                        <p class="text-[11px] font-semibold text-slate-400 -mt-1">Presensi Sekolah Modern</p>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-10 text-sm font-semibold text-slate-600">
                    <a href="#fitur" class="hover:text-brand-600 transition-colors">Fitur Utama</a>
                    <a href="#alur" class="hover:text-brand-600 transition-colors">Alur Kerja</a>
                    <a href="#instal" class="hover:text-brand-600 transition-colors">Cara Instal</a>
                    <a href="#spesifikasi" class="hover:text-brand-600 transition-colors">Spesifikasi APK</a>
                    <a href="#faq" class="hover:text-brand-600 transition-colors">FAQ</a>
                </nav>

                <!-- Action Buttons -->
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ route('download.apk') }}" class="px-6 py-3 rounded-xl font-bold text-xs text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-600/25 hover:shadow-lg hover:shadow-brand-600/30 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-download text-sm"></i> Unduh APK Android
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
                    <i class="fa-solid text-xl" :class="mobileMenu ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenu" x-transition class="md:hidden border-b border-slate-100 bg-white px-6 pt-2 pb-6 space-y-3">
            <a href="#fitur" @click="mobileMenu = false" class="block px-3 py-2 rounded-lg font-semibold text-slate-700 hover:bg-slate-50">Fitur Utama</a>
            <a href="#alur" @click="mobileMenu = false" class="block px-3 py-2 rounded-lg font-semibold text-slate-700 hover:bg-slate-50">Alur Kerja</a>
            <a href="#instal" @click="mobileMenu = false" class="block px-3 py-2 rounded-lg font-semibold text-slate-700 hover:bg-slate-50">Cara Instal</a>
            <a href="#spesifikasi" @click="mobileMenu = false" class="block px-3 py-2 rounded-lg font-semibold text-slate-700 hover:bg-slate-50">Spesifikasi APK</a>
            <a href="#faq" @click="mobileMenu = false" class="block px-3 py-2 rounded-lg font-semibold text-slate-700 hover:bg-slate-50">FAQ</a>
            <div class="pt-2 flex flex-col gap-2">
                <a href="{{ route('download.apk') }}" class="w-full text-center py-3 rounded-xl font-bold text-sm text-white bg-brand-600">
                    <i class="fa-solid fa-download mr-2"></i> Unduh APK Android (v1.0.4)
                </a>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative pt-12 pb-20 md:pt-24 md:pb-32 bg-pattern overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                
                <!-- Left Hero Content -->
                <div class="lg:col-span-7 space-y-8 text-center lg:text-left" data-aos="fade-right">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                        Sistem Presensi Sekolah <br class="hidden sm:inline">
                        <span class="gradient-text">Cerdas & Real-Time</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-3xl mx-auto lg:mx-0">
                        Solusi presensi digital terpadu untuk siswa dan guru wali kelas. Dilengkapi pemindaian <strong>QR Code instan</strong>, pengajuan surat izin/sakit berbasis kamera, approval wali kelas, dan notifikasi WhatsApp otomatis.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="{{ route('download.apk') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-extrabold text-base text-white bg-brand-600 hover:bg-brand-700 shadow-xl shadow-brand-600/30 hover:shadow-brand-600/40 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3 group">
                            <i class="fa-solid fa-file-arrow-down text-xl group-hover:bounce"></i>
                            <span>Unduh APK Android</span>
                            <span class="px-2.5 py-0.5 text-xs bg-white/20 rounded-md font-normal">185 MB</span>
                        </a>

                        <a href="#instal" class="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-base text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-soft-blue hover:shadow-hover-blue transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-question text-brand-500"></i>
                            <span>Panduan Instalasi</span>
                        </a>
                    </div>

                    <!-- Trust Metrics -->
                    <div class="pt-6 grid grid-cols-3 gap-6 border-t border-slate-200/60 max-w-xl mx-auto lg:mx-0">
                        <div class="text-center lg:text-left">
                            <p class="text-2xl font-extrabold text-brand-600">< 2 Detik</p>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Kecepatan Scan</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <p class="text-2xl font-extrabold text-brand-600">100% Digital</p>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Bebas Kertas</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <p class="text-2xl font-extrabold text-brand-600">Real-Time</p>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Notifikasi WhatsApp</p>
                        </div>
                    </div>
                </div>

                <!-- Right Phone Visual Mockup -->
                <div class="lg:col-span-5 flex justify-center" data-aos="fade-left">
                    <div class="relative w-full max-w-[340px] sm:max-w-[400px]">
                        
                        <!-- Decorative Glow Effect -->
                        <div class="absolute -inset-4 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-[45px] opacity-20 blur-2xl -z-10 animate-pulse"></div>

                        <!-- Phone Frame Card -->
                        <div class="bg-white rounded-[42px] p-5 shadow-2xl border-4 border-slate-900 shadow-brand-600/20">
                            
                            <!-- Phone Top Notch / Speaker -->
                            <div class="w-36 h-5 bg-slate-900 rounded-b-2xl mx-auto mb-4 flex items-center justify-center">
                                <div class="w-12 h-1.5 bg-slate-700 rounded-full"></div>
                            </div>

                            <!-- Phone Screen UI Content -->
                            <div class="bg-slate-50 rounded-[30px] p-5 space-y-4 text-left border border-slate-100">
                                
                                <!-- App Header Preview -->
                                <div class="bg-brand-600 text-white rounded-2xl p-4 shadow-md shadow-brand-600/30">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-sm">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-blue-100">Siswa • XI RPL 1</p>
                                                <p class="text-xs font-bold">Ahmad Ramdhani</p>
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-0.5 bg-emerald-500 text-white rounded-full text-[9px] font-extrabold">ONLINE</span>
                                    </div>
                                </div>

                                <!-- QR Code Scan Box Preview -->
                                <div class="bg-white rounded-2xl p-5 text-center border border-slate-200/80 shadow-soft-blue space-y-3">
                                    <p class="text-xs font-bold text-slate-700">Scan Presensi Hari Ini</p>
                                    <div class="w-32 h-32 mx-auto bg-blue-50 rounded-xl border-2 border-dashed border-brand-500 p-2 flex items-center justify-center relative">
                                        <i class="fa-solid fa-qrcode text-6xl text-brand-600"></i>
                                        <div class="absolute inset-x-2 top-1/2 h-0.5 bg-emerald-500 shadow-glow animate-bounce"></div>
                                    </div>
                                    <span class="inline-block px-3.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold rounded-lg">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Absen Masuk Berhasil
                                    </span>
                                </div>

                                <!-- Mini Permission Request Widget Preview -->
                                <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-soft-blue flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-notes-medical text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate">Pengajuan Surat Sakit</p>
                                        <p class="text-[10px] text-slate-500">Disetujui Wali Kelas</p>
                                    </div>
                                    <span class="px-2.5 py-1 bg-blue-50 text-brand-600 text-[10px] font-extrabold rounded-md">ACC</span>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- HIGHLIGHT STATS / FEATURES BRIEF -->
    <section class="py-14 bg-slate-50/70 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 text-center">
                
                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-13 h-13 mx-auto mb-3 rounded-2xl bg-blue-50 text-brand-600 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">QR Code Instant</h4>
                    <p class="text-xs text-slate-500 mt-1">Scan kilat tanpa antre</p>
                </div>

                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-13 h-13 mx-auto mb-3 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Bukti Surat Kamera</h4>
                    <p class="text-xs text-slate-500 mt-1">Foto bukti surat sakit</p>
                </div>

                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-13 h-13 mx-auto mb-3 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">ACC Wali Kelas</h4>
                    <p class="text-xs text-slate-500 mt-1">Approval khusus 1 kelas</p>
                </div>

                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-13 h-13 mx-auto mb-3 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <h4 class="text-base font-extrabold text-slate-900">Notifikasi WA</h4>
                    <p class="text-xs text-slate-500 mt-1">Info otomatis ke WA</p>
                </div>

            </div>
        </div>
    </section>

    <!-- FITUR UTAMA SECTION -->
    <section id="fitur" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-4xl mx-auto mb-16 space-y-4" data-aos="fade-up">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Semua Kebutuhan Absensi Sekolah dalam Satu Aplikasi
                </h2>
                <p class="text-slate-600 text-base sm:text-lg">
                    SaMaya Mobile dirancang khusus untuk memenuhi standar efisiensi presensi siswa modern dan kemudahan pengelolaan kelas oleh guru.
                </p>
            </div>

            <!-- Features Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
                
                <!-- Card 1 -->
                <div class="bg-white p-8 sm:p-9 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue hover:-translate-y-1 transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-brand-600 flex items-center justify-center text-2xl mb-6 shadow-sm">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Pemindaian QR Code Masuk & Pulang</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Siswa cukup mengarahkan kamera HP ke layar QR Code harian di sekolah untuk melakukan Absen Masuk dan Absen Pulang dengan akurasi jam yang tercatat presisi.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-8 sm:p-9 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue hover:-translate-y-1 transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mb-6 shadow-sm">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Pengajuan Izin & Sakit Digital</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Tidak perlu mengantar surat kertas ke sekolah. Siswa dapat mengajukan surat Izin atau Sakit lengkap dengan lampiran foto bukti langsung dari galeri atau kamera HP.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-8 sm:p-9 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue hover:-translate-y-1 transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-6 shadow-sm">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Dashboard Khusus Wali Kelas</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Guru Wali Kelas memiliki tampilan khusus untuk memantau presensi 1 kelas yang dikelola, melihat statistik bulanan, serta menyetujui atau menolak izin siswa.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="bg-white p-8 sm:p-9 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue hover:-translate-y-1 transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mb-6 shadow-sm">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Notifikasi WhatsApp Otomatis</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Sistem terintegrasi dengan bot WhatsApp untuk mengirimkan pesan konfirmasi kehadiran atau kabar izin langsung ke nomor HP siswa dan orang tua.
                    </p>
                </div>

                <!-- Card 5 -->
                <div class="bg-white p-8 sm:p-9 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue hover:-translate-y-1 transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl mb-6 shadow-sm">
                        <i class="fa-solid fa-address-card"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Manajemen Profil & Foto Instant</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Pengguna dapat mengganti foto profil pribadi secara langsung dari kamera atau galeri dengan pembaruan foto instant tanpa perlu keluar dari aplikasi.
                    </p>
                </div>

                <!-- Card 6 -->
                <div class="bg-white p-8 sm:p-9 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue hover:-translate-y-1 transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl mb-6 shadow-sm">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Auto Limit & Batas Jam Alfa</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Pencegahan kecurangan dengan aturan jam batas absensi. Scan yang dilakukan melewati batas jam tertentu otomatis akan dikategorikan sebagai Alfa secara transparan.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ALUR & CARA KERJA SECTION -->
    <section id="alur" class="py-24 bg-slate-50/70 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-4xl mx-auto mb-16 space-y-4" data-aos="fade-up">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Alur & Cara Kerja Aplikasi SaMaya
                </h2>
                <p class="text-slate-600 text-base sm:text-lg">
                    Proses sederhana dari pengunduhan file APK hingga tercatatnya absensi secara otomatis di sistem sekolah.
                </p>
            </div>

            <!-- Steps Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 relative">
                
                <!-- Step 1 -->
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all text-center relative z-10" data-aos="zoom-in" data-aos-delay="100">
                    <div class="w-13 h-13 rounded-full bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto mb-5 shadow-md shadow-brand-600/30">
                        1
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Unduh APK Android</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Klik tombol download di halaman ini untuk mengunduh berkas installer `SaMaya-Mobile.apk`.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all text-center relative z-10" data-aos="zoom-in" data-aos-delay="200">
                    <div class="w-13 h-13 rounded-full bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto mb-5 shadow-md shadow-brand-600/30">
                        2
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Login Akun</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Buka aplikasi dan masuk menggunakan NIS (untuk Siswa) atau Email resmi (untuk Guru Wali Kelas).
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all text-center relative z-10" data-aos="zoom-in" data-aos-delay="300">
                    <div class="w-13 h-13 rounded-full bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto mb-5 shadow-md shadow-brand-600/30">
                        3
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Scan QR / Ajukan Izin</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Pindai QR Code sekolah untuk absen harian, atau unggah foto bukti jika mengajukan Izin/Sakit.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-soft-blue hover:shadow-hover-blue transition-all text-center relative z-10" data-aos="zoom-in" data-aos-delay="400">
                    <div class="w-13 h-13 rounded-full bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto mb-5 shadow-md shadow-brand-600/30">
                        4
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Rekap & Verified</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Status kehadiran tersimpan di server dan notifikasi konfirmasi terkirim otomatis ke WhatsApp.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- PANDUAN CARA INSTAL APK SECTION -->
    <section id="instal" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                
                <!-- Left Title & Guide Info -->
                <div class="lg:col-span-5 space-y-6" data-aos="fade-right">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight">
                        Cara Menginstal Aplikasi APK di Smartphone Android
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg leading-relaxed">
                        Karena aplikasi dipasang secara langsung dari berkas APK resmi sekolah (di luar Google Play Store), Anda cukup mengizinkan akses pemasangan dari sumber tidak dikenal di HP Anda.
                    </p>
                </div>

                <!-- Right Steps Guide Accordion/List -->
                <div class="lg:col-span-7 space-y-4" data-aos="fade-left">
                    
                    <!-- Step Item 1 -->
                    <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-100 shadow-soft-blue flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 text-brand-600 font-bold flex items-center justify-center shrink-0 text-lg">
                            1
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">Unduh File APK SaMaya</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                                Tekan tombol <strong>"Unduh APK Android"</strong> pada bagian atas atau bawah halaman ini hingga berkas selesai terunduh ke folder Download HP Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Step Item 2 -->
                    <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-100 shadow-soft-blue flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 text-brand-600 font-bold flex items-center justify-center shrink-0 text-lg">
                            2
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">Buka File APK yang Terunduh</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                                Ketuk notifikasi hasil unduhan atau buka aplikasi <strong>File Manager / Berkas</strong> di HP Anda lalu pilih file <code>SaMaya-Mobile.apk</code>.
                            </p>
                        </div>
                    </div>

                    <!-- Step Item 3 -->
                    <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-100 shadow-soft-blue flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 text-brand-600 font-bold flex items-center justify-center shrink-0 text-lg">
                            3
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">Izinkan Pemasangan Sumber Tidak Dikenal</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                                Jika muncul pop-up keamanan Android, ketuk <strong>Pengaturan (Settings)</strong> lalu aktifkan opsi <strong>"Izinkan dari sumber ini"</strong> (Allow from this source).
                            </p>
                        </div>
                    </div>

                    <!-- Step Item 4 -->
                    <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-100 shadow-soft-blue flex items-start gap-4">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 text-brand-600 font-bold flex items-center justify-center shrink-0 text-lg">
                            4
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">Tekan "Instal" & Buka Aplikasi</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                                Tekan tombol <strong>Instal</strong>. Tunggu beberapa detik hingga proses selesai, lalu buka aplikasi SaMaya dan lakukan Login.
                            </p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- SPESIFIKASI BERKAS APK CARD SECTION -->
    <section id="spesifikasi" class="py-24 bg-slate-50/70 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-3xl p-8 sm:p-14 border border-slate-100 shadow-soft-blue relative overflow-hidden" data-aos="zoom-in">
                <div class="absolute top-0 right-0 w-80 h-80 bg-blue-100/50 rounded-full blur-3xl -z-0"></div>

                <div class="relative z-10 text-center max-w-3xl mx-auto space-y-6">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Informasi Berkas Paket Installer</h2>

                    <!-- Spec Table Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-left pt-2">
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="text-[11px] text-slate-400 font-semibold uppercase">Nama Berkas</p>
                            <p class="text-sm font-bold text-slate-800 mt-1 truncate">SaMaya-Mobile.apk</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="text-[11px] text-slate-400 font-semibold uppercase">Versi Rilis</p>
                            <p class="text-sm font-bold text-slate-800 mt-1">v1.0.4 Debug</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="text-[11px] text-slate-400 font-semibold uppercase">Ukuran File</p>
                            <p class="text-sm font-bold text-brand-600 mt-1">185 MB</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="text-[11px] text-slate-400 font-semibold uppercase">Min. Android</p>
                            <p class="text-sm font-bold text-slate-800 mt-1">Android 7.0+</p>
                        </div>
                    </div>

                    <!-- Direct Big Download CTA -->
                    <div class="pt-6">
                        <a href="{{ route('download.apk') }}" class="inline-flex items-center justify-center gap-3 px-12 py-5 rounded-2xl font-extrabold text-lg text-white bg-brand-600 hover:bg-brand-700 shadow-xl shadow-brand-600/30 hover:shadow-brand-600/40 hover:-translate-y-1 transition-all">
                            <i class="fa-solid fa-download text-xl"></i>
                            <span>Unduh SaMaya APK Sekarang</span>
                        </a>
                        <p class="text-xs text-slate-400 mt-3 font-semibold">Bebas Virus • Terverifikasi • Langsung Terhubung ke Server Sekolah</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- FAQ SECTION -->
    <section id="faq" class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center mb-16 space-y-4" data-aos="fade-up">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Pertanyaan yang Sering Diajukan (FAQ)
                </h2>
            </div>

            <!-- Accordions list -->
            <div class="space-y-4" x-data="{ active: 1 }">
                
                <!-- FAQ Item 1 -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-soft-blue overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                    <button @click="active = (active === 1 ? null : 1)" class="w-full p-6 text-left font-bold text-slate-900 flex items-center justify-between gap-4">
                        <span>Bagaimana cara mendapatkan NIS atau Akun Login aplikasi?</span>
                        <i class="fa-solid text-brand-600 transition-transform" :class="active === 1 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="active === 1" x-transition class="px-6 pb-6 text-sm text-slate-600 border-t border-slate-50 pt-4 leading-relaxed">
                        Akun login untuk Siswa menggunakan Nomor Induk Siswa (NIS) dan password default yang telah dibagikan oleh wali kelas atau bagian tata usaha sekolah. Bagi Guru Wali Kelas, login menggunakan Email terdaftar.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-soft-blue overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                    <button @click="active = (active === 2 ? null : 2)" class="w-full p-6 text-left font-bold text-slate-900 flex items-center justify-between gap-4">
                        <span>Apakah aplikasi ini aman dipasang di smartphone Android?</span>
                        <i class="fa-solid text-brand-600 transition-transform" :class="active === 2 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="active === 2" x-transition class="px-6 pb-6 text-sm text-slate-600 border-t border-slate-50 pt-4 leading-relaxed">
                        Sangat aman. Berkas APK disitus ini merupakan berkas resmi buatan tim sekolah SaMaya dan tidak mengandung iklan maupun kode berbahaya.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-soft-blue overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                    <button @click="active = (active === 3 ? null : 3)" class="w-full p-6 text-left font-bold text-slate-900 flex items-center justify-between gap-4">
                        <span>Apakah pengajuan Sakit harus melampirkan foto surat dokter?</span>
                        <i class="fa-solid text-brand-600 transition-transform" :class="active === 3 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="active === 3" x-transition class="px-6 pb-6 text-sm text-slate-600 border-t border-slate-50 pt-4 leading-relaxed">
                        Sangat disarankan untuk memfoto surat keterangan sakit agar pengajuan Anda dapat langsung disetujui (ACC) oleh Guru Wali Kelas di sistem.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-soft-blue overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                    <button @click="active = (active === 4 ? null : 4)" class="w-full p-6 text-left font-bold text-slate-900 flex items-center justify-between gap-4">
                        <span>Bagaimana jika foto profil belum langsung berubah saat ganti foto?</span>
                        <i class="fa-solid text-brand-600 transition-transform" :class="active === 4 ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div x-show="active === 4" x-transition class="px-6 pb-6 text-sm text-slate-600 border-t border-slate-50 pt-4 leading-relaxed">
                        Pada versi terbaru v1.0.4, foto profil akan langsung diperbarui secara otomatis begitu Anda memilih foto dari kamera/galeri dan menekan tombol "Simpan Perubahan".
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- DETAILED FOOTER SECTION -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-12 border-t border-slate-800 relative" data-aos="fade-up">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-12 pb-12 border-b border-slate-800">
                
                <!-- Column 1: Brand Info & Social Media -->
                <div class="lg:col-span-4 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-600/30">
                            <i class="fa-solid fa-qrcode text-xl"></i>
                        </div>
                        <div>
                            <span class="text-xl font-extrabold text-white tracking-tight">SaMaya</span>
                            <span class="ml-2 px-2 py-0.5 text-[10px] font-extrabold bg-blue-900 text-blue-300 rounded-full border border-blue-700/50">v1.0.4 Release</span>
                        </div>
                    </div>
                    
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Sistem Presensi Digital & Absensi QR Code Sekolah Modern. Solusi efisien presensi siswa, pengajuan izin/sakit online berbasis foto bukti, dan approval Wali Kelas secara real-time.
                    </p>

                    <!-- Server Status Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/60 text-xs text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-medium">Status Server: <strong>Online & Active</strong></span>
                    </div>

                    <!-- Social Icons -->
                    <div class="flex items-center gap-3 pt-2">
                        <a href="#" title="WhatsApp Call Center" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-emerald-600 text-slate-400 hover:text-white flex items-center justify-center transition-all">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </a>
                        <a href="#" title="Instagram Official" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-pink-600 text-slate-400 hover:text-white flex items-center justify-center transition-all">
                            <i class="fa-brands fa-instagram text-sm"></i>
                        </a>
                        <a href="#" title="YouTube Channel" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white flex items-center justify-center transition-all">
                            <i class="fa-brands fa-youtube text-sm"></i>
                        </a>
                        <a href="#" title="GitHub Repository" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-all">
                            <i class="fa-brands fa-github text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Column 2: Navigasi Halaman -->
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-sm font-extrabold text-white uppercase tracking-wider">Navigasi Halaman</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        <li><a href="#fitur" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i> Fitur Utama</a></li>
                        <li><a href="#alur" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i> Alur Kerja App</a></li>
                        <li><a href="#instal" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i> Panduan Instalasi</a></li>
                        <li><a href="#spesifikasi" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i> Spesifikasi APK</a></li>
                        <li><a href="#faq" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i> FAQ / Pertanyaan</a></li>
                    </ul>
                </div>

                <!-- Column 3: Layanan & Akses Portal -->
                <div class="lg:col-span-3 space-y-4">
                    <h4 class="text-sm font-extrabold text-white uppercase tracking-wider">Layanan & Portal</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        <li><a href="{{ route('download.apk') }}" class="hover:text-emerald-400 transition-colors flex items-center gap-2"><i class="fa-solid fa-download text-emerald-500"></i> Download APK Mobile (v1.0.4)</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-user-shield text-brand-500"></i> Portal Login Dashboard Web</a></li>
                        <li><a href="#fitur" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-qrcode text-brand-500"></i> Generator QR Code Presensi</a></li>
                        <li><a href="#fitur" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-file-check text-brand-500"></i> Approvals Izin Wali Kelas</a></li>
                        <li><a href="#spesifikasi" class="hover:text-brand-500 transition-colors flex items-center gap-2"><i class="fa-solid fa-shield-halved text-brand-500"></i> Panduan Keamanan APK</a></li>
                    </ul>
                </div>

                <!-- Column 4: Kontak & Dukungan Sekolah -->
                <div class="lg:col-span-3 space-y-4">
                    <h4 class="text-sm font-extrabold text-white uppercase tracking-wider">Hubungi Bantuan</h4>
                    <ul class="space-y-3 text-xs">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot text-brand-500 text-sm mt-0.5"></i>
                            <span class="text-slate-400">Jl. Pendidikan No. 45, Kompleks Sekolah Modern, Indonesia</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-clock text-brand-500 text-sm"></i>
                            <span class="text-slate-400">Senin - Jumat: 07:00 - 16:00 WIB</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-brand-500 text-sm"></i>
                            <a href="mailto:support@samaya.sch.id" class="text-slate-400 hover:text-white transition-colors">support@samaya.sch.id</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-brands fa-whatsapp text-emerald-500 text-sm"></i>
                            <a href="https://wa.me/6281234567890" target="_blank" class="text-slate-400 hover:text-white transition-colors font-bold text-emerald-400">+62 812-3456-7890 (WA Admin)</a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Footer Bar -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400 font-semibold">
                <p>© 2026 SaMaya System. Hak Cipta Dilindungi Undang-Undang.</p>

                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-white transition-colors">Syarat & Ketentuan</a>
                    <span class="text-slate-700">•</span>
                    <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                    <span class="text-slate-700">•</span>
                    <!-- Back to Top Button -->
                    <button @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-brand-600 text-white flex items-center justify-center transition-all" title="Kembali ke Atas">
                        <i class="fa-solid fa-arrow-up text-xs"></i>
                    </button>
                </div>
            </div>

        </div>
    </footer>

    <!-- AOS (Animate On Scroll) JS Initialization -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 80,
        });
    </script>

</body>
</html>
