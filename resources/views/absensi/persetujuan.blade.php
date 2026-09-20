@extends('layout.app')

@section('title', 'Persetujuan Izin & Sakit - SaMaya')
@section('header_title', 'Persetujuan Izin & Sakit Siswa')

@section('content')
<div x-data="{
    showImageModal: false,
    modalImageSrc: '',
    modalStudentName: '',
    openModal(src, name) {
        this.modalImageSrc = src;
        this.modalStudentName = name;
        this.showImageModal = true;
    }
}" class="space-y-6">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-bold flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Statistic Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <a href="{{ route('absensi.persetujuan', ['status' => 'Pending']) }}" class="clean-card p-5 rounded-3xl flex items-center gap-4 transition-all hover:scale-[1.01] border-l-4 border-l-amber-500">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 text-xl font-bold">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500">Menunggu ACC (Pending)</p>
                <h4 class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $totalPending }}</h4>
            </div>
        </a>

        <a href="{{ route('absensi.persetujuan', ['status' => 'Disetujui']) }}" class="clean-card p-5 rounded-3xl flex items-center gap-4 transition-all hover:scale-[1.01] border-l-4 border-l-emerald-500">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-xl font-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500">Telah Disetujui</p>
                <h4 class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $totalDisetujui }}</h4>
            </div>
        </a>

        <a href="{{ route('absensi.persetujuan', ['status' => 'Ditolak']) }}" class="clean-card p-5 rounded-3xl flex items-center gap-4 transition-all hover:scale-[1.01] border-l-4 border-l-rose-500">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-xl font-bold">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500">Ditolak</p>
                <h4 class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $totalDitolak }}</h4>
            </div>
        </a>
    </div>

    <!-- Main Content Card -->
    <div class="clean-card p-6 rounded-3xl space-y-6">
        <!-- Header Filter Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Daftar Pengajuan Izin & Sakit</h3>
                <p class="text-xs text-slate-500">Pilih <span class="text-emerald-600 font-bold">Setujui (ACC)</span> atau <span class="text-rose-600 font-bold">Tolak</span> untuk memperbarui status presensi siswa.</p>
            </div>
            <!-- Filter Tabs -->
            <div class="flex items-center bg-slate-100 p-1 rounded-2xl gap-1 text-xs font-bold">
                <a href="{{ route('absensi.persetujuan', ['status' => 'Pending']) }}" class="px-3.5 py-2 rounded-xl transition-all {{ $status === 'Pending' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                    Pending ({{ $totalPending }})
                </a>
                <a href="{{ route('absensi.persetujuan', ['status' => 'Disetujui']) }}" class="px-3.5 py-2 rounded-xl transition-all {{ $status === 'Disetujui' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                    Disetujui
                </a>
                <a href="{{ route('absensi.persetujuan', ['status' => 'Ditolak']) }}" class="px-3.5 py-2 rounded-xl transition-all {{ $status === 'Ditolak' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                    Ditolak
                </a>
                <a href="{{ route('absensi.persetujuan', ['status' => 'semua']) }}" class="px-3.5 py-2 rounded-xl transition-all {{ $status === 'semua' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                    Semua
                </a>
            </div>
        </div>

        <!-- Table List -->
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-xs uppercase font-extrabold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama Siswa</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Tipe</th>
                        <th class="px-5 py-3.5">Alasan / Keterangan</th>
                        <th class="px-5 py-3.5">Bukti (Surat Sakit/Foto)</th>
                        <th class="px-5 py-3.5">Status ACC</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pengajuans as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Siswa Info -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($item->user->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 leading-snug">{{ $item->user->name ?? 'Siswa' }}</p>
                                        <p class="text-xs text-slate-400 font-mono">NIS: {{ $item->user->nis ?? '-' }} • {{ $item->kelas ?? 'XI RPL 1' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Tanggal -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-slate-800 text-xs">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</p>
                                <p class="text-[11px] text-slate-400">{{ $item->hari }} • {{ substr($item->waktu_absen, 0, 5) }} WIB</p>
                            </td>

                            <!-- Tipe -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($item->status === 'Sakit')
                                    <span class="px-3 py-1 text-xs font-extrabold bg-rose-100 text-rose-700 rounded-full border border-rose-200">
                                        <i class="fa-solid fa-hospital-user mr-1"></i> Sakit
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-extrabold bg-amber-100 text-amber-800 rounded-full border border-amber-200">
                                        <i class="fa-solid fa-envelope-open-text mr-1"></i> Izin
                                    </span>
                                @endif
                            </td>

                            <!-- Keterangan -->
                            <td class="px-5 py-4 max-w-xs">
                                <p class="text-xs text-slate-700 line-clamp-2 leading-relaxed">{{ $item->keterangan ?? '-' }}</p>
                            </td>

                            <!-- Bukti -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($item->bukti)
                                    @php
                                        $imageSrc = str_starts_with($item->bukti, 'data:image') || str_starts_with($item->bukti, 'http') 
                                            ? $item->bukti 
                                            : 'data:image/jpeg;base64,' . $item->bukti;
                                    @endphp
                                    <button @click="openModal('{{ $imageSrc }}', '{{ addslashes($item->user->name ?? 'Siswa') }}')" 
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold transition-all">
                                        <i class="fa-solid fa-image text-sm"></i>
                                        <span>Lihat Bukti</span>
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tanpa Lampiran</span>
                                @endif
                            </td>

                            <!-- Status Persetujuan -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($item->status_persetujuan === 'Disetujui')
                                    <span class="px-3 py-1 text-xs font-extrabold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-300 flex items-center gap-1.5 w-fit">
                                        <i class="fa-solid fa-circle-check"></i> Disetujui
                                    </span>
                                @elseif($item->status_persetujuan === 'Ditolak')
                                    <span class="px-3 py-1 text-xs font-extrabold bg-rose-100 text-rose-800 rounded-full border border-rose-300 flex items-center gap-1.5 w-fit">
                                        <i class="fa-solid fa-circle-xmark"></i> Ditolak
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-extrabold bg-amber-100 text-amber-800 rounded-full border border-amber-300 flex items-center gap-1.5 w-fit animate-pulse">
                                        <i class="fa-solid fa-clock"></i> Pending (Perlu ACC)
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($item->status_persetujuan !== 'Disetujui')
                                        <form action="{{ route('absensi.persetujuan.setujui', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan izin/sakit ini?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1 shadow-sm transition-all">
                                                <i class="fa-solid fa-check"></i> ACC
                                            </button>
                                        </form>
                                    @endif

                                    @if($item->status_persetujuan !== 'Ditolak')
                                        <form action="{{ route('absensi.persetujuan.tolak', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK pengajuan izin/sakit ini?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold flex items-center gap-1 shadow-sm transition-all">
                                                <i class="fa-solid fa-xmark"></i> Tolak
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-file-circle-check text-4xl mb-3 text-slate-300"></i>
                                <p class="font-bold text-sm">Tidak ada data pengajuan {{ $status === 'semua' ? '' : strtolower($status) }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $pengajuans->appends(['status' => $status])->links() }}
        </div>
    </div>

    <!-- Image Evidence Modal -->
    <div x-show="showImageModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="showImageModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-4 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-medical text-blue-600 text-lg"></i>
                    <h3 class="font-extrabold text-slate-900 text-base">Bukti Surat / Foto Lampiran</h3>
                </div>
                <button @click="showImageModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <p class="text-xs text-slate-500">Pemohon: <b class="text-slate-800" x-text="modalStudentName"></b></p>

            <div class="bg-slate-900 rounded-2xl overflow-hidden max-h-[70vh] flex items-center justify-center p-2">
                <img :src="modalImageSrc" alt="Bukti Surat Sakit / Izin" class="max-h-[65vh] w-auto object-contain rounded-xl" />
            </div>

            <div class="flex justify-end">
                <button @click="showImageModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs rounded-xl">
                    Tutup Preview
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
