@extends('layout.app')

@section('title', 'QR Code Presensi Harian - SaMaya')
@section('header_title', 'QR Code Presensi Harian')

@section('content')
<div class="max-w-xl mx-auto space-y-6" x-data="qrAttendanceApp()">

    <!-- Main QR Code Display Card -->
    <div class="clean-card rounded-3xl p-8 flex flex-col items-center text-center relative overflow-hidden shadow-xl border border-slate-200">
        
        <!-- Header & Status Badge -->
        <div class="w-full flex flex-col items-center gap-2 pb-6 border-b border-slate-100">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider {{ $jenisQr === 'MASUK' ? 'text-blue-700 bg-blue-50 border border-blue-200' : 'text-amber-700 bg-amber-50 border border-amber-200' }}">
                @if($jenisQr === 'MASUK')
                    <span>QR Code Absen MASUK</span>
                @else
                    <span>QR Code Absen PULANG</span>
                @endif
            </div>

            <p class="text-xs font-semibold text-slate-500">
                {{ $hari }}, {{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}
            </p>
        </div>

        <!-- Centered QR Code Canvas -->
        <div class="py-8 flex flex-col items-center w-full">
            <div class="p-6 bg-white rounded-3xl border-2 border-dashed shadow-inner flex justify-center w-full max-w-sm {{ $jenisQr === 'MASUK' ? 'border-blue-300' : 'border-amber-300' }}">
                <div id="qrcode-canvas" class="flex items-center justify-center"></div>
            </div>

            <!-- Countdown Timer & Validity -->
            <div class="mt-6 flex flex-col items-center gap-2">
                <div class="inline-flex items-center text-xs font-bold px-4 py-1.5 rounded-full border shadow-sm {{ $jenisQr === 'MASUK' ? 'text-blue-700 bg-blue-50 border-blue-200' : 'text-amber-700 bg-amber-50 border-amber-200' }}">
                    <span>{{ $jenisQr === 'MASUK' ? 'Berlaku Dari Jam 00:00 – 12:00 WIB' : 'Berlaku Dari Jam 12:00 – 00:00 WIB' }}</span>
                </div>

                <div class="text-xs text-slate-400 font-medium flex items-center gap-2 mt-1">
                    <i class="fa-solid fa-arrows-rotate animate-spin text-slate-400"></i>
                    <span>Reset berikutnya dalam <strong class="font-mono text-slate-700 font-bold text-sm" x-text="countdownTimer">--:--:--</strong></span>
                </div>
            </div>
        </div>

        <!-- Footer Action (Download Button) -->
        <div class="w-full pt-4 border-t border-slate-100 flex items-center justify-center gap-3">
            <button type="button" @click="downloadQR()" class="py-3 px-6 rounded-2xl text-xs font-extrabold flex items-center justify-center gap-2 transition-all shadow-md {{ $jenisQr === 'MASUK' ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-600/25' : 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/25' }}">
                <i class="fa-solid fa-download text-sm"></i>
                <span>Unduh QR Code</span>
            </button>
        </div>

    </div>

</div>

<script>
function qrAttendanceApp() {
    return {
        countdownTimer: '00:00:00',
        nextRefreshLabel: '',
        qrToken: @json($qrDataString),
        jenisQr: @json($jenisQr),
        today: @json($today),

        init() {
            this.renderQRCode();
            this.startCountdown();
        },

        renderQRCode() {
            const container = document.getElementById("qrcode-canvas");
            if (container) {
                container.innerHTML = "";
                new QRCode(container, {
                    text: this.qrToken,
                    width: 240,
                    height: 240,
                    colorDark: this.jenisQr === 'MASUK' ? "#1e40af" : "#b45309",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        },

        downloadQR() {
            const container = document.getElementById('qrcode-canvas');
            const canvas = container ? container.querySelector('canvas') : null;
            if (canvas) {
                const url = canvas.toDataURL("image/png");
                const a = document.createElement('a');
                a.href = url;
                a.download = `QR_Absen_${this.jenisQr}_${this.today}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert('QR Code belum siap untuk diunduh.');
            }
        },

        startCountdown() {
            const updateTimer = () => {
                const now = new Date();
                const currentHour = now.getHours();

                const next = new Date(now);
                if (currentHour < 12) {
                    next.setHours(12, 0, 0, 0);
                    this.nextRefreshLabel = '12:00';
                } else {
                    next.setHours(24, 0, 0, 0);
                    this.nextRefreshLabel = '00:00';
                }

                const diff = next - now;
                if (diff <= 0) {
                    location.reload();
                    return;
                }

                const hours = Math.floor(diff / (1000 * 60 * 60)).toString().padStart(2, '0');
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
                const seconds = Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0');

                this.countdownTimer = `${hours}:${minutes}:${seconds}`;
            };

            updateTimer();
            setInterval(updateTimer, 1000);
        }
    }
}
</script>
@endsection
