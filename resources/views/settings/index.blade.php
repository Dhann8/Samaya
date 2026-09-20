@extends('layout.app')

@section('title', 'Pengaturan Aplikasi - SaMaya')
@section('header_title', 'Pengaturan Presensi & Sistem')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Page Title Header Card -->
    <div class="clean-card p-6 rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-extrabold text-slate-900">Pengaturan & Konfigurasi Server</h3>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-clock"></i>
                <span>WIB (Asia/Jakarta)</span>
            </span>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card 1: Batas Waktu Keterlambatan -->
        <div class="clean-card p-6 rounded-3xl space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Batas Waktu Keterlambatan</h4>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Batas Jam Terlambat (WIB)</label>
                    <input type="time" name="jam_batas_terlambat" value="{{ $settings['jam_batas_terlambat'] ?? '07:30' }}" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-amber-700 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Batas Jam Alfa (WIB)</label>
                    <input type="time" name="jam_batas_alfa" value="{{ $settings['jam_batas_alfa'] ?? '08:00' }}" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-rose-700 focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Instansi / Sekolah</label>
                    <input type="text" name="sekolah_nama" value="{{ $settings['sekolah_nama'] ?? 'SaMaya School' }}" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Card 2: Pengaturan Refresh QR Code -->
        <div class="clean-card p-6 rounded-3xl space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Refresh & Keamanan QR Code</h4>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Frekuensi Refresh QR Code</label>
                    <select name="qr_refresh_mode" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                        <option value="daily" {{ ($settings['qr_refresh_mode'] ?? '') == 'daily' ? 'selected' : '' }}>Setiap Hari (Rekomendasi Harian)</option>
                        <option value="5_min" {{ ($settings['qr_refresh_mode'] ?? '') == '5_min' ? 'selected' : '' }}>Setiap 5 Menit (Dinamis)</option>
                        <option value="15_min" {{ ($settings['qr_refresh_mode'] ?? '') == '15_min' ? 'selected' : '' }}>Setiap 15 Menit</option>
                        <option value="30_min" {{ ($settings['qr_refresh_mode'] ?? '') == '30_min' ? 'selected' : '' }}>Setiap 30 Menit</option>
                        <option value="hourly" {{ ($settings['qr_refresh_mode'] ?? '') == 'hourly' ? 'selected' : '' }}>Setiap Jam</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 3: Jam Operasional QR Masuk & Pulang -->
        <div class="clean-card p-6 rounded-3xl space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center font-bold text-base">
                    <i class="fa-solid fa-business-time"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Jam Operasional QR Masuk & Pulang</h4>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                <!-- Session Masuk -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-blue-700 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i> Sesi QR Masuk
                        </span>
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-md">Pagi</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase">Jam Mulai</label>
                            <input type="time" name="jam_masuk_mulai" value="{{ $settings['jam_masuk_mulai'] ?? '00:00' }}" required
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase">Jam Selesai</label>
                            <input type="time" name="jam_masuk_akhir" value="{{ $settings['jam_masuk_akhir'] ?? '12:00' }}" required
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                        </div>
                    </div>
                </div>

                <!-- Session Pulang -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-amber-700 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-right-from-bracket"></i> Sesi QR Pulang
                        </span>
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-md">Siang/Sore</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase">Jam Mulai</label>
                            <input type="time" name="jam_pulang_mulai" value="{{ $settings['jam_pulang_mulai'] ?? '12:00' }}" required
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase">Jam Selesai</label>
                            <input type="time" name="jam_pulang_akhir" value="{{ $settings['jam_pulang_akhir'] ?? '00:00' }}" required
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Notifikasi WhatsApp via Node.js -->
        <div class="clean-card p-6 rounded-3xl space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center font-bold text-base">
                    <i class="fa-brands fa-whatsapp text-xl"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Notifikasi WhatsApp Harian</h4>
                </div>
            </div>

            <!-- Live WhatsApp Login QR Code Component -->
            <div x-data="{
                connected: false,
                user: '',
                qr: '',
                loading: true,
                statusText: 'Memeriksa status koneksi WhatsApp...',
                async checkStatus() {
                    try {
                        const res = await fetch('http://localhost:3000/api/qr-status');
                        const data = await res.json();
                        this.connected = data.connected;
                        this.user = data.user || '';
                        this.qr = data.qr || '';
                        this.statusText = data.status || '';
                    } catch (e) {
                        this.connected = false;
                        this.qr = '';
                        this.statusText = 'Server WhatsApp belum aktif.';
                    } finally {
                        this.loading = false;
                    }
                },
                async resetSession() {
                    if (!confirm('Apakah Anda yakin ingin me-logout & me-reset sesi WhatsApp ini?')) return;
                    try {
                        await fetch('http://localhost:3000/api/logout', { method: 'POST' });
                        this.checkStatus();
                    } catch (e) {
                        alert('Gagal me-reset sesi WA.');
                    }
                },
                init() {
                    this.checkStatus();
                    setInterval(() => this.checkStatus(), 3000);
                }
            }" class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                
                <div class="flex items-center justify-between">
                    <h5 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-emerald-600 text-sm"></i> QR Code Login Bot WhatsApp Mandiri (Baileys)
                    </h5>
                    <button type="button" @click="checkStatus()" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-arrows-rotate"></i> Refresh Status
                    </button>
                </div>

                <!-- Status Terhubung -->
                <template x-if="connected">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div>
                                <h6 class="font-extrabold text-emerald-900 text-sm">Bot WhatsApp Terhubung & Siap Digunakan!</h6>
                                <p class="text-xs text-emerald-700 mt-0.5">Nomor Terdaftar: <strong class="font-mono text-emerald-900" x-text="user"></strong></p>
                            </div>
                        </div>
                        <button type="button" @click="resetSession()" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold transition-all shadow-sm flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout / Reset Sesi WA</span>
                        </button>
                    </div>
                </template>

                <!-- Status Menunggu Scan QR Code -->
                <template x-if="!connected && qr">
                    <div class="flex flex-col items-center justify-center text-center p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                            <img :src="qr" alt="Scan QR Code WhatsApp" class="w-56 h-56 rounded-xl shadow-md border border-slate-100">
                        </div>
                        <div>
                            <h6 class="font-extrabold text-slate-900 text-sm flex items-center justify-center gap-2">
                                <i class="fa-solid fa-mobile-screen-button text-blue-600"></i> Scan QR Code Ini di HP Anda
                            </h6>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto leading-relaxed">
                                Buka aplikasi <b>WhatsApp di HP</b> &gt; Ketuk Menu Titik Tiga / Pengaturan &gt; <b>Perangkat Tertaut</b> &gt; <b>Tautkan Perangkat</b>.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- Status Server Offline / Menghubungkan -->
                <template x-if="!connected && !qr">
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-exclamation text-amber-600 text-base"></i>
                            <span class="font-semibold" x-text="statusText"></span>
                        </div>
                        <button type="button" @click="checkStatus()" class="px-3 py-1.5 bg-amber-200 hover:bg-amber-300 text-amber-900 rounded-lg font-bold text-xs whitespace-nowrap transition-colors">
                            Coba Lagi
                        </button>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Status Notifikasi WhatsApp</label>
                    <select name="wa_notification_enabled" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                        <option value="1" {{ ($settings['wa_notification_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Aktif (Kirim Notifikasi WA)</option>
                        <option value="0" {{ ($settings['wa_notification_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Nonaktifkan Notifikasi WA</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">WA Gateway API Token / Key (Opsional)</label>
                    <input type="text" name="wa_api_token" value="{{ $settings['wa_api_token'] ?? '' }}" placeholder="Kosongkan jika pakai Bot Baileys Mandiri di atas"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">WA Gateway API Endpoint URL</label>
                <input type="text" name="wa_gateway_url" value="{{ $settings['wa_gateway_url'] ?? 'http://localhost:3000/api/send-wa' }}" required
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
            <button type="submit" class="py-3 px-8 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 flex items-center gap-2 transition-all">
                <span>Simpan Seluruh Pengaturan</span>
            </button>
        </div>
    </form>

    <!-- Card 4: Sesi Akun Administrator & Logout -->
    <div class="clean-card p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 text-xl font-bold">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-slate-900 text-base">{{ Auth::user()->name }}</h4>
                    <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <div>
                <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                    @csrf
                    <button type="submit" class="py-3 px-6 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold flex items-center gap-2 shadow-md shadow-rose-600/20 hover:shadow-rose-600/30 transition-all">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                        <span>Keluar / Logout Akun</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
