@extends('layout.app')

@section('title', 'Dashboard Utama - SaMaya')
@section('header_title', 'Dashboard Utama')

@section('content')
<div class="space-y-8">
    
    <!-- Hero Banner Quick QR Link -->
    <div class="clean-card rounded-3xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 p-6 md:p-8 text-white shadow-xl shadow-blue-600/15 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div>
            <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider">Fitur Presensi Harian</span>
            <h2 class="text-2xl font-extrabold mt-2">Absensi QR Code Harian & Pelacakan GPS</h2>
            <p class="text-xs text-blue-100 mt-1 max-w-xl">Lakukan absen mandiri 1x per hari dengan verifikasi lokasi GPS otomatis.</p>
        </div>
        <a href="{{ route('absensi.qr') }}" class="py-3 px-6 rounded-2xl bg-white text-blue-700 hover:bg-blue-50 font-extrabold text-xs shadow-lg flex items-center gap-2 shrink-0 transition-all">
            <i class="fa-solid fa-qrcode text-base"></i> Buka Halaman QR Absen
        </a>
    </div>

    <!-- Top Summary Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users Card -->
        <div class="clean-card clean-card-hover p-6 rounded-3xl relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total User (Siswa)</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalUsers) }}</h3>
                    <p class="text-xs text-blue-600 mt-2 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-users text-xs"></i> Terdaftar di sistem
                    </p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 text-2xl shadow-sm">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
        </div>

        <!-- Total Absen Card -->
        <div class="clean-card clean-card-hover p-6 rounded-3xl relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Data Absen</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalAbsen) }}</h3>
                    <p class="text-xs text-emerald-600 mt-2 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-clipboard-check text-xs"></i> Total catatan presensi
                    </p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-2xl shadow-sm">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
        </div>

        <!-- Hadir Count Card -->
        <div class="clean-card clean-card-hover p-6 rounded-3xl relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tingkat Kehadiran</p>
                    <h3 class="text-3xl font-black text-slate-900 mt-2">{{ number_format($hadirCount) }}</h3>
                    <p class="text-xs text-emerald-600 mt-2 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-circle text-[8px]"></i> {{ $totalAbsen > 0 ? round(($hadirCount / $totalAbsen) * 100, 1) : 0 }}% Hadir
                    </p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600 text-2xl shadow-sm">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
        </div>

        <!-- Absensi Breakdown (Izin/Sakit/Alpa) -->
        <div class="clean-card clean-card-hover p-6 rounded-3xl relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Izin / Sakit / Alpa</p>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="flex items-center justify-between mt-3 text-xs font-bold">
                <span class="text-amber-600">Izin: {{ $izinCount }}</span>
                <span class="text-blue-600">Sakit: {{ $sakitCount }}</span>
                <span class="text-rose-600">Alpa: {{ $alpaCount }}</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden flex">
                <div class="bg-amber-500 h-full" style="width: {{ $totalAbsen > 0 ? ($izinCount / $totalAbsen) * 100 : 0 }}%"></div>
                <div class="bg-blue-500 h-full" style="width: {{ $totalAbsen > 0 ? ($sakitCount / $totalAbsen) * 100 : 0 }}%"></div>
                <div class="bg-rose-500 h-full" style="width: {{ $totalAbsen > 0 ? ($alpaCount / $totalAbsen) * 100 : 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Charts / Diagrams Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Status Absensi Chart (Doughnut) -->
        <div class="clean-card p-6 rounded-3xl flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Ringkasan Status Absensi</h3>
                    <p class="text-xs text-slate-500">Proporsi Kehadiran Siswa</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>
            <div class="relative flex items-center justify-center my-4 h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Chart Absensi per Hari (Grouped Bar Chart) -->
        <div class="clean-card p-6 rounded-3xl lg:col-span-2 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Statistik Absensi per Hari</h3>
                    <p class="text-xs text-slate-500">Distribusi Kehadiran Berdasarkan Hari</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-chart-column"></i>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="hariChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="clean-card rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Aktivitas Presensi Terbaru</h3>
                <p class="text-xs text-slate-500">5 Catatan Presensi Terakhir di Sistem</p>
            </div>
            <a href="{{ route('absensi.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                Lihat Semua <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-extrabold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-6">Nama Siswa</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Tanggal & Jam</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-6">Lokasi GPS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($recentAbsens as $absen)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3.5 px-6 font-bold text-slate-900">
                                {{ $absen->user->name ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-600">
                                {{ $absen->kelas }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $absen->hari }}, {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d M Y') }}
                                <span class="text-[11px] text-blue-600 font-mono block">{{ $absen->waktu_absen ?? \Carbon\Carbon::parse($absen->created_at)->format('H:i:s') }} WIB</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($absen->status == 'Hadir')
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">Hadir</span>
                                @elseif($absen->status == 'Izin')
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold">Izin</span>
                                @elseif($absen->status == 'Sakit')
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold">Sakit</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold">Alpa</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 truncate max-w-xs">
                                {{ $absen->lokasi ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 font-medium">Belum ada data presensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Doughnut Chart for Status
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
            datasets: [{
                data: [{{ $hadirCount }}, {{ $izinCount }}, {{ $sakitCount }}, {{ $alpaCount }}],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#475569',
                        font: { family: 'Plus Jakarta Sans', weight: 'bold' }
                    }
                }
            }
        }
    });

    // Bar Chart for Days
    const hariCtx = document.getElementById('hariChart').getContext('2d');
    const hariData = @json($absensByHari);
    const labels = Object.keys(hariData);
    const values = Object.values(hariData);

    new Chart(hariCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Presensi',
                data: values,
                backgroundColor: '#2563eb',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: 'bold' } },
                    grid: { display: false }
                },
                y: {
                    ticks: { color: '#64748b', precision: 0 },
                    grid: { color: '#f1f5f9' }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endsection
