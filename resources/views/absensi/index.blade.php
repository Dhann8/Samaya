@extends('layout.app')

@section('title', 'Data Absensi - SaMaya')
@section('header_title', 'Kelola Data Absensi Siswa')

@section('content')
<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    showExportModal: false,
    activeDay: '{{ request('hari', array_keys($groupedAbsens->toArray())[0] ?? 'Senin') }}',
    editAbsen: { id: '', user_id: '', tanggal: '', hari: '', status: 'Hadir', keterangan: '' }
}" class="space-y-6">

    <!-- Action Bar & Filter Toolbar -->
    <div class="clean-card p-6 rounded-3xl space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Daftar Data Absensi</h3>
                <p class="text-xs text-slate-500">Dikelompokkan berdasarkan <b>Hari</b> & <b>Kelas</b>. Termasuk data pelacakan lokasi GPS.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('absensi.qr') }}" class="py-2.5 px-4 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-qrcode text-sm"></i>
                    <span>Halaman Absen QR</span>
                </a>

                <button @click="showExportModal = true" class="py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-file-excel text-sm text-emerald-600"></i>
                    <span>Unduh Excel</span>
                </button>

                <button @click="showCreateModal = true" class="py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-blue-600/25 transition-all">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Tambah Absen Manual</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <!-- Filter & Search Controls -->
        <form id="absensi-search-form" method="GET" action="{{ route('absensi.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-3">
            <div class="relative lg:col-span-2">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="search-input-absensi" name="search" value="{{ request('search') }}" placeholder="Cari Nama / NIS Siswa..."
                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                    autocomplete="off">
            </div>

            <div>
                <select name="tahun" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <select name="bulan" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Bulan</option>
                    @php
                        $bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                    @endphp
                    @foreach($bulanIndo as $num => $nama)
                        <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="minggu" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Minggu</option>
                    <option value="1" {{ request('minggu') == '1' ? 'selected' : '' }}>Minggu ke-1</option>
                    <option value="2" {{ request('minggu') == '2' ? 'selected' : '' }}>Minggu ke-2</option>
                    <option value="3" {{ request('minggu') == '3' ? 'selected' : '' }}>Minggu ke-3</option>
                    <option value="4" {{ request('minggu') == '4' ? 'selected' : '' }}>Minggu ke-4</option>
                    <option value="5" {{ request('minggu') == '5' ? 'selected' : '' }}>Minggu ke-5</option>
                </select>
            </div>

            <div>
                <select name="kelas" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach($kelases as $k)
                        <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="Alpa" {{ request('status') == 'Alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="jurusan" onchange="this.form.submit()" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusans as $j)
                        <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>

                @if(request()->anyFilled(['search', 'minggu', 'kelas', 'status', 'jurusan', 'tahun', 'bulan']))
                    <a href="{{ route('absensi.index') }}" title="Reset Filter" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
    <!-- Modal Filter Export Excel -->
    <div x-show="showExportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-file-excel text-emerald-600"></i> Filter Ekspor Excel
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih kelas dan rentang waktu data yang ingin diekspor.</p>
                </div>
                <button @click="showExportModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('absensi.export') }}" method="GET" class="space-y-5">

                {{-- Pilih Kelas --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-school text-blue-500 mr-1"></i> Pilih Kelas
                        <span class="text-slate-400 font-normal normal-case">(kosongkan = semua kelas)</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-4 bg-slate-50 rounded-2xl border border-slate-200 max-h-48 overflow-y-auto">
                        @foreach($kelases as $k)
                            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-xl hover:bg-white border border-transparent hover:border-slate-200 transition-all">
                                <input type="checkbox" name="kelas_filter[]" value="{{ $k }}"
                                    class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                                <span class="text-xs font-semibold text-slate-700">{{ $k }}</span>
                            </label>
                        @endforeach
                        @if($kelases->isEmpty())
                            <p class="text-xs text-slate-400 col-span-3 text-center py-2">Belum ada data kelas</p>
                        @endif
                    </div>
                </div>

                {{-- Rentang Waktu --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-calendar-range text-blue-500 mr-1"></i> Rentang Waktu
                    </label>
                    @php
                        $bulanList = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Dari --}}
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 space-y-3">
                            <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Dari</span>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Bulan</label>
                                    <select name="dari_bulan" class="w-full py-2 px-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                                        <option value="">-- Pilih --</option>
                                        @foreach($bulanList as $num => $nama)
                                            <option value="{{ $num }}">{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Tahun</label>
                                    <select name="dari_tahun" class="w-full py-2 px-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                                        <option value="">-- Pilih --</option>
                                        @for($y = date('Y'); $y >= 2023; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- Sampai --}}
                        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 space-y-3">
                            <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Sampai</span>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Bulan</label>
                                    <select name="sampai_bulan" class="w-full py-2 px-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                                        <option value="">-- Pilih --</option>
                                        @foreach($bulanList as $num => $nama)
                                            <option value="{{ $num }}">{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Tahun</label>
                                    <select name="sampai_tahun" class="w-full py-2 px-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 font-semibold focus:outline-none focus:border-blue-500">
                                        <option value="">-- Pilih --</option>
                                        @for($y = date('Y'); $y >= 2023; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2"><i class="fa-solid fa-circle-info"></i> Jika tidak memilih rentang waktu, semua data akan diekspor.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showExportModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center gap-2">
                        <i class="fa-solid fa-download"></i> Unduh Excel Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="absensi-content-container">
    <!-- Grouped View Navigation (Days Tabs) -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto custom-scrollbar">
        @foreach($haris as $h)
            @php
                $countForDay = isset($groupedAbsens[$h]) ? $groupedAbsens[$h]->flatten(1)->count() : 0;
            @endphp
            <button @click="activeDay = '{{ $h }}'"
                :class="activeDay === '{{ $h }}' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all whitespace-nowrap">
                <span>{{ $h }}</span>
                <span :class="activeDay === '{{ $h }}' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'" class="px-2 py-0.5 rounded-full text-[10px]">
                    {{ $countForDay }}
                </span>
            </button>
        @endforeach
    </div>

    <!-- Tab Content by Day -->
    @foreach($haris as $h)
        <div x-show="activeDay === '{{ $h }}'" class="space-y-6">
            @if(isset($groupedAbsens[$h]) && count($groupedAbsens[$h]) > 0)
                @foreach($groupedAbsens[$h] as $kelasName => $itemsInKelas)
                    <div class="clean-card rounded-3xl overflow-hidden">
                        <!-- Group Header (Kelas) -->
                        <div class="bg-gradient-to-r from-blue-50 to-white px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-sm">Kelas {{ $kelasName }}</h4>
                                <span class="text-xs text-slate-500">Hari {{ $h }} — {{ count($itemsInKelas) }} Siswa Presensi</span>
                            </div>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold border border-blue-200">
                                {{ count($itemsInKelas) }} Presensi
                            </span>
                        </div>

                        <!-- Data Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse table-fixed">
                                <colgroup>
                                    <col style="width: 22%;">
                                    <col style="width: 16%;">
                                    <col style="width: 18%;">
                                    <col style="width: 10%;">
                                    <col style="width: 16%;">
                                    <col style="width: 10%;">
                                    <col style="width: 8%;">
                                </colgroup>
                                <thead>
                                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-extrabold uppercase text-slate-500 tracking-wider">
                                        <th class="py-3.5 pl-6 pr-4 align-middle text-left whitespace-nowrap">Siswa & NIS</th>
                                        <th class="py-3.5 px-4 align-middle text-left whitespace-nowrap">Tanggal & Jam</th>
                                        <th class="py-3.5 px-4 align-middle text-left whitespace-nowrap">Lokasi GPS Terdaftar</th>
                                        <th class="py-3.5 px-4 align-middle text-left whitespace-nowrap">Status</th>
                                        <th class="py-3.5 px-4 align-middle text-left whitespace-nowrap">Keterangan</th>
                                        <th class="py-3.5 px-4 align-middle text-left whitespace-nowrap">Waktu Pulang</th>
                                        <th class="py-3.5 pl-4 pr-6 align-middle text-center whitespace-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-xs">
                                    @foreach($itemsInKelas as $absen)
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            <td class="py-4 pl-6 pr-4 align-middle min-w-0">
                                                <div class="truncate">
                                                    <span class="font-bold text-slate-900 block truncate" title="{{ $absen->user->name ?? 'N/A' }}">{{ $absen->user->name ?? 'N/A' }}</span>
                                                    <span class="text-[11px] text-slate-400 font-mono block truncate">NIS: {{ $absen->user->nis ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 align-middle min-w-0">
                                                <div class="font-semibold text-slate-800 truncate">
                                                    {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d M Y') }}
                                                </div>
                                                <div class="text-[11px] text-blue-600 font-mono font-bold mt-0.5 truncate">
                                                    <i class="fa-solid fa-clock"></i> {{ $absen->waktu_absen ?? \Carbon\Carbon::parse($absen->created_at)->format('H:i:s') }} WIB
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 align-middle min-w-0">
                                                @if($absen->lokasi)
                                                    <div class="text-xs font-semibold text-slate-800 truncate" title="{{ $absen->lokasi }}">
                                                        <i class="fa-solid fa-location-dot text-rose-500 mr-1"></i> {{ $absen->lokasi }}
                                                    </div>
                                                    @if($absen->latitude && $absen->longitude)
                                                        <a href="https://maps.google.com/?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:underline mt-0.5 truncate">
                                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Maps Pin ({{ $absen->latitude }}, {{ $absen->longitude }})
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-slate-400 italic text-[11px]">Tidak ada data lokasi</span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-4 align-middle text-left whitespace-nowrap">
                                                @if($absen->status == 'Hadir')
                                                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold inline-block">
                                                        Hadir
                                                    </span>
                                                @elseif($absen->status == 'Izin')
                                                    <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold inline-block">
                                                        Izin
                                                    </span>
                                                @elseif($absen->status == 'Sakit')
                                                    <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold inline-block">
                                                        Sakit
                                                    </span>
                                                @else
                                                    <span class="px-3 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold inline-block">
                                                        Alpa
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-4 align-middle text-slate-600 min-w-0 truncate">
                                                {{ $absen->keterangan ?? '-' }}
                                            </td>
                                            <td class="py-4 px-4 align-middle whitespace-nowrap">
                                                @if($absen->waktu_pulang)
                                                    <div class="text-[11px] text-amber-600 font-mono font-bold">
                                                        <i class="fa-solid fa-clock"></i> {{ $absen->waktu_pulang }} WIB
                                                    </div>
                                                @else
                                                    <span class="text-slate-300 text-xs">—</span>
                                                @endif
                                            </td>
                                            <td class="py-4 pl-4 pr-6 align-middle text-center whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button @click="
                                                        editAbsen = {
                                                            id: '{{ $absen->id }}',
                                                            user_id: '{{ $absen->user_id }}',
                                                            tanggal: '{{ $absen->tanggal }}',
                                                            hari: '{{ $absen->hari }}',
                                                            status: '{{ $absen->status }}',
                                                            keterangan: '{{ $absen->keterangan }}'
                                                        };
                                                        showEditModal = true;
                                                    " class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition-colors">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>

                                                    <form action="{{ route('absensi.destroy', $absen->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="clean-card rounded-3xl p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-clipboard-question"></i>
                    </div>
                    <h4 class="font-extrabold text-slate-800 text-base">Belum Ada Data Absensi Hari {{ $h }}</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Tidak ada data presensi yang ditemukan untuk hari {{ $h }} sesuai filter yang Anda pilih.
                    </p>
                </div>
            @endif
        </div>
    @endforeach

    <!-- Modal Tambah Absen -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base">Tambah Data Absensi Siswa</h3>
                <button @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('absensi.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Siswa</label>
                    <select name="user_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} (NIS: {{ $u->nis ?? '-' }} - Kelas: {{ $u->kelas ?? 'Umum' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hari</label>
                        <select name="hari" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                            @foreach($haris as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Presensi</label>
                    <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Catatan opsional..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Absen -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base">Edit Data Absensi</h3>
                <button @click="showEditModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="'/absensi/' + editAbsen.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Siswa</label>
                    <select name="user_id" x-model="editAbsen.user_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} (NIS: {{ $u->nis ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" x-model="editAbsen.tanggal" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hari</label>
                        <select name="hari" x-model="editAbsen.hari" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                            @foreach($haris as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Presensi</label>
                    <select name="status" x-model="editAbsen.status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:border-blue-500">
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan</label>
                    <textarea name="keterangan" x-model="editAbsen.keterangan" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-blue-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20">
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>{{-- end #absensi-content-container --}}

</div>

<script>
(function () {
    const input  = document.getElementById('search-input-absensi');
    const form   = document.getElementById('absensi-search-form');
    const target = document.getElementById('absensi-content-container');
    if (!input || !form || !target) return;

    let timer;
    let controller;

    function setLoading(on) {
        target.style.opacity = on ? '0.5' : '1';
        target.style.pointerEvents = on ? 'none' : '';
        target.style.transition = 'opacity 0.15s';
    }

    async function doSearch() {
        if (controller) controller.abort();
        controller = new AbortController();

        const params = new URLSearchParams(new FormData(form));
        const url    = form.action + '?' + params.toString();

        setLoading(true);
        try {
            const res  = await fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await res.text();

            const doc  = new DOMParser().parseFromString(html, 'text/html');
            const next = doc.getElementById('absensi-content-container');
            if (next) {
                target.innerHTML = next.innerHTML;
            }

            history.pushState(null, '', url);
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        } finally {
            setLoading(false);
            input.focus();
        }
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(doSearch, 380);
    });
})();
</script>
@endsection
