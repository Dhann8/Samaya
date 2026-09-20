<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absen;
use App\Models\User;
use App\Services\WaNotificationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsenController extends Controller
{
    public function index(Request $request)
    {
        $query = Absen::with('user');

        $user = Auth::user();
        if ($user && $user->role === 'guru' && $user->kelas) {
            $query->where('kelas', $user->kelas);
        }

        // Search by User Name or NIS
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter per Minggu ke- (Minggu ke-1 s/d Minggu ke-5)
        if ($request->filled('minggu')) {
            $minggu = (int) $request->input('minggu');
            $startDay = ($minggu - 1) * 7 + 1;
            $endDay = min(31, $minggu * 7);
            $query->whereRaw('DAY(tanggal) BETWEEN ? AND ?', [$startDay, $endDay]);
        } elseif ($request->filled('hari')) {
            $query->where('hari', $request->input('hari'));
        }

        // Filter per Kelas
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->input('kelas'));
        }

        // Filter per Status
        if ($request->filled('status')) {
            $statusInput = $request->input('status');
            if (in_array(strtolower($statusInput), ['alfa', 'alpa'])) {
                $query->whereIn('status', ['Alpa', 'Alfa']);
            } else {
                $query->where('status', $statusInput);
            }
        }

        // Filter per Angkatan via User relation
        if ($request->filled('angkatan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('angkatan', $request->input('angkatan'));
            });
        }

        // Filter per Jurusan via User relation
        if ($request->filled('jurusan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jurusan', $request->input('jurusan'));
            });
        }
        
        // Filter per Tahun
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->input('tahun'));
        }
        
        // Filter per Bulan
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->input('bulan'));
        }

        $absens = $query->orderBy('tanggal', 'desc')->get();

        // Grouping by Hari and then by Kelas
        $groupedAbsens = $absens->groupBy(['hari', 'kelas']);

        // Data for dropdowns
        $haris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $kelases = User::whereNotNull('kelas')->where('role', '!=', 'admin')->distinct()->pluck('kelas')->sort();
        $angkatans = User::whereNotNull('angkatan')->where('role', '!=', 'admin')->distinct()->pluck('angkatan')->sort();
        $jurusans = User::whereNotNull('jurusan')->where('role', '!=', 'admin')->distinct()->pluck('jurusan')->sort();
        $users = User::where('role', '!=', 'admin')->orderBy('name', 'asc')->get();

        return view('absensi.index', compact(
            'groupedAbsens',
            'absens',
            'haris',
            'kelases',
            'angkatans',
            'jurusans',
            'users'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'hari' => 'required|string',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'keterangan' => 'nullable|string',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $validated['kelas'] = $user->kelas ?? 'Umum';

        $absen = Absen::create($validated);

        // Send WA Notification (Absen Masuk)
        WaNotificationService::sendMasuk($user, $absen);

        return redirect()->route('absensi.index')->with('success', 'Data Absensi berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $absen = Absen::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'hari' => 'required|string',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'keterangan' => 'nullable|string',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $validated['kelas'] = $user->kelas ?? 'Umum';

        $absen->update($validated);

        return redirect()->route('absensi.index')->with('success', 'Data Absensi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $absen = Absen::findOrFail($id);
        $absen->delete();

        return redirect()->route('absensi.index')->with('success', 'Data Absensi berhasil dihapus!');
    }

    /**
     * Generate a sort key for class names (e.g. X-RPL 1 < X-RPL 10 < XI-RPL 1 < XII-RPL 10)
     */
    private function getClassSortKey(?string $kelas): string
    {
        if (!$kelas) {
            return '999-ZZZ-99999';
        }

        $kelas = strtoupper(trim($kelas));

        // Match Roman numerals X, XI, XII at start
        $gradeValue = 99;
        if (preg_match('/^(XII|XI|X)\b/i', $kelas, $m)) {
            $roman = strtoupper($m[1]);
            if ($roman === 'X') $gradeValue = 10;
            elseif ($roman === 'XI') $gradeValue = 11;
            elseif ($roman === 'XII') $gradeValue = 12;
        } elseif (preg_match('/^(12|11|10)\b/', $kelas, $m)) {
            $gradeValue = (int) $m[1];
        }

        // Extract numbers at end of class string
        $numberValue = 0;
        if (preg_match('/(\d+)$/', $kelas, $m)) {
            $numberValue = (int) $m[1];
        }

        // Extract major / middle string
        $major = preg_replace('/^(XII|XI|X|12|11|10)[\s\-]?/i', '', $kelas);
        $major = preg_replace('/[\s\-]?\d+$/', '', $major);
        $major = trim($major);

        return sprintf('%03d-%s-%05d', $gradeValue, $major, $numberValue);
    }

    public function export(Request $request)
    {
        $query = Absen::with('user');

        // Filter kelas (bisa multi kelas dari modal)
        if ($request->filled('kelas_filter')) {
            $kelasFilter = (array) $request->input('kelas_filter');
            $query->whereIn('kelas', $kelasFilter);
        } elseif ($request->filled('kelas')) {
            $query->where('kelas', $request->input('kelas'));
        }

        // Filter rentang tanggal (dari_tahun-dari_bulan s/d sampai_tahun-sampai_bulan)
        if ($request->filled('dari_tahun') && $request->filled('dari_bulan')) {
            $dari = Carbon::createFromDate($request->dari_tahun, $request->dari_bulan, 1)->startOfMonth();
            if ($request->filled('sampai_tahun') && $request->filled('sampai_bulan')) {
                $sampai = Carbon::createFromDate($request->sampai_tahun, $request->sampai_bulan, 1)->endOfMonth();
            } else {
                $sampai = $dari->copy()->endOfMonth();
            }
            $query->whereBetween('tanggal', [$dari->format('Y-m-d'), $sampai->format('Y-m-d')]);
        } elseif ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->input('tahun'));
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal', $request->input('bulan'));
            }
        }

        if ($request->filled('minggu')) {
            $minggu = (int) $request->input('minggu');
            $startDay = ($minggu - 1) * 7 + 1;
            $endDay = min(31, $minggu * 7);
            $query->whereRaw('DAY(tanggal) BETWEEN ? AND ?', [$startDay, $endDay]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('jurusan')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('jurusan', $request->input('jurusan'));
            });
        }

        $hariOrder = [
            'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4,
            'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7
        ];

        $absens = $query->get()->sort(function ($a, $b) use ($hariOrder) {
            $classKeyA = $this->getClassSortKey($a->kelas);
            $classKeyB = $this->getClassSortKey($b->kelas);

            if ($classKeyA !== $classKeyB) {
                return strcmp($classKeyA, $classKeyB);
            }

            $hariA = $hariOrder[$a->hari] ?? 99;
            $hariB = $hariOrder[$b->hari] ?? 99;
            if ($hariA !== $hariB) {
                return $hariA <=> $hariB;
            }

            $nameA = strtolower($a->user->name ?? '');
            $nameB = strtolower($b->user->name ?? '');
            if ($nameA !== $nameB) {
                return strcmp($nameA, $nameB);
            }

            return strcmp($a->tanggal, $b->tanggal);
        })->values();

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Build filename from date range
        if ($request->filled('dari_bulan') && $request->filled('dari_tahun')) {
            $labelDari = ($bulanIndo[(int)$request->dari_bulan] ?? '') . '_' . $request->dari_tahun;
            if ($request->filled('sampai_bulan') && $request->filled('sampai_tahun')) {
                $labelSampai = ($bulanIndo[(int)$request->sampai_bulan] ?? '') . '_' . $request->sampai_tahun;
                $filename = "Laporan_Absen_{$labelDari}_sd_{$labelSampai}.xlsx";
            } else {
                $filename = "Laporan_Absen_{$labelDari}.xlsx";
            }
        } else {
            $firstAbsen = $absens->first();
            $monthNum = $firstAbsen ? (int) Carbon::parse($firstAbsen->tanggal)->format('n') : (int) Carbon::now()->format('n');
            $namaBulan = $bulanIndo[$monthNum] ?? 'Bulan';
            $filename = "Laporan_Absen_{$namaBulan}.xlsx";
        }

        // Group absens into weeks (1 Sheet per Week)
        $groupedByWeek = $absens->groupBy(function ($absen) {
            $carbon = Carbon::parse($absen->tanggal);
            $weekNum = (int) ceil($carbon->day / 7);
            $bulanIndo = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];
            $bl = $bulanIndo[$carbon->month] ?? '';
            return "Minggu {$weekNum} {$bl} {$carbon->year}";
        });

        if ($groupedByWeek->isEmpty()) {
            $groupedByWeek->put('Minggu 1', collect());
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $headers = ['No', 'Hari', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Angkatan', 'Jurusan', 'Status', 'Keterangan', 'Waktu Absen'];

        foreach ($groupedByWeek as $weekTitle => $itemsInWeek) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(substr($weekTitle, 0, 31)); // Excel limit 31 chars

            $rowIndex = 1;

            $groupedByClass = $itemsInWeek->groupBy('kelas');

            foreach ($groupedByClass as $kelas => $itemsInClass) {
                $sheet->setCellValue("A{$rowIndex}", "KELAS: " . strtoupper($kelas) . " — " . strtoupper($weekTitle));
                $sheet->mergeCells("A{$rowIndex}:K{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E293B'));
                $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex += 2;

                foreach ($headers as $colIndex => $headerText) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->setCellValue($colLetter . $rowIndex, $headerText);
                }

                $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($rowIndex)->setRowHeight(26);
                $rowIndex++;

                $uniqueDates = $itemsInClass->pluck('tanggal')->unique()->values()->toArray();
                $dateColorMap = [];
                foreach ($uniqueDates as $idx => $dateStr) {
                    $dateColorMap[$dateStr] = ($idx % 2 === 0) ? 'F1F5F9' : 'FFFFFF';
                }

                foreach ($itemsInClass as $itemIdx => $absen) {
                    $bgColor = $dateColorMap[$absen->tanggal] ?? 'FFFFFF';

                    $sheet->setCellValue("A{$rowIndex}", $itemIdx + 1);
                    $sheet->setCellValue("B{$rowIndex}", $absen->hari);
                    $sheet->setCellValue("C{$rowIndex}", Carbon::parse($absen->tanggal)->format('d/m/Y'));
                    $sheet->setCellValue("D{$rowIndex}", $absen->user->nis ?? '-');
                    $sheet->setCellValue("E{$rowIndex}", $absen->user->name ?? 'N/A');
                    $sheet->setCellValue("F{$rowIndex}", $absen->kelas);
                    $sheet->setCellValue("G{$rowIndex}", $absen->user->angkatan ?? '-');
                    $sheet->setCellValue("H{$rowIndex}", $absen->user->jurusan ?? '-');
                    $sheet->setCellValue("I{$rowIndex}", $absen->status);
                    $sheet->setCellValue("J{$rowIndex}", $absen->keterangan ?? '-');
                    $sheet->setCellValue("K{$rowIndex}", $absen->waktu_absen ?? '-');

                    $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->applyFromArray([
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                    ]);

                    $sheet->getStyle("A{$rowIndex}:D{$rowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$rowIndex}:I{$rowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("K{$rowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $statusColor = match ($absen->status) {
                        'Hadir' => '15803D', 'Izin' => '1D4ED8', 'Sakit' => 'B45309', 'Alpa' => 'B91C1C',
                        default => '334155'
                    };
                    $sheet->getStyle("I{$rowIndex}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($statusColor));
                    $sheet->getRowDimension($rowIndex)->setRowHeight(22);
                    $rowIndex++;
                }

                $rowIndex += 2;
            }

            foreach (range(1, 11) as $colIdx) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }
        }

        if ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->setActiveSheetIndex(0);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function qrIndex()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        
        // Indonesian Day Name
        $daysInIndonesian = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $hari = $daysInIndonesian[Carbon::now()->format('l')] ?? 'Senin';

        // Check if user has already checked in today
        $todayAbsen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        // QR token: 00:00-12:00 = MASUK, 12:00-00:00 = PULANG
        $jenisQr  = Carbon::now()->hour < 12 ? 'MASUK' : 'PULANG';
        $qrDataString = "SAMAYA-QR-{$today}-{$jenisQr}-" . substr(md5("SAMAYA-SECRET-{$today}-{$jenisQr}"), 0, 12);

        return view('absensi.qr', compact('user', 'today', 'hari', 'todayAbsen', 'qrDataString', 'jenisQr'));
    }



    public function qrScan(Request $request)
    {
        $user = Auth::user();
        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        if (!$user && $request->filled('nis')) {
            $user = User::where('nis', $request->input('nis'))->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User/Siswa tidak ditemukan!'
            ], 404);
        }

        if ($user->role !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi harian hanya dapat dilakukan oleh Siswa!'
            ], 403);
        }

        $today = Carbon::today()->format('Y-m-d');

        $request->validate([
            'qr_code'   => 'nullable|string',
            'latitude'  => 'nullable',
            'longitude' => 'nullable',
            'lokasi'    => 'nullable|string',
            'status'    => 'nullable|in:Hadir,Izin,Sakit,Alpa',
            'keterangan'=> 'nullable|string',
            'jenis'     => 'nullable|in:datang,pulang',
        ]);

        $jenis = $request->input('jenis', 'datang');

        $existingAbsen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if ($jenis === 'pulang') {
            if (!$existingAbsen || $existingAbsen->lokasi === 'Sistem Otomatis') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan absen datang hari ini!'
                ], 400);
            }

            if ($existingAbsen->waktu_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absen pulang hari ini!'
                ], 400);
            }

            $existingAbsen->update([
                'waktu_pulang' => Carbon::now()->format('H:i:s'),
            ]);

            // Kirim Notifikasi WhatsApp (Absen Pulang)
            WaNotificationService::sendPulang($user, $existingAbsen);

            return response()->json([
                'success' => true,
                'message' => 'Presensi Pulang Berhasil!',
                'data'    => $existingAbsen
            ], 200);
        }

        if ($existingAbsen && $existingAbsen->lokasi !== 'Sistem Otomatis') {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi datang hari ini!'
            ], 400);
        }

        $daysInIndonesian = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $hari = $daysInIndonesian[Carbon::now()->format('l')] ?? 'Senin';

        $jamBatasTerlambat = \App\Models\Setting::get('jam_batas_terlambat', '07:30');
        $jamBatasAlfa      = \App\Models\Setting::get('jam_batas_alfa', '08:00');
        $waktuNow          = Carbon::now()->format('H:i');

        $statusDefault     = 'Hadir';
        if ($waktuNow > $jamBatasAlfa) {
            $statusDefault     = 'Alpa';
            $keteranganDefault = "Alpa (Scan jam {$waktuNow} WIB - Melewati batas jam {$jamBatasAlfa})";
        } elseif ($waktuNow > $jamBatasTerlambat) {
            $statusDefault     = 'Hadir';
            $keteranganDefault = "Terlambat (Scan jam {$waktuNow} WIB)";
        } else {
            $statusDefault     = 'Hadir';
            $keteranganDefault = "Tepat Waktu (Scan jam {$waktuNow} WIB)";
        }

        $absenData = [
            'user_id'    => $user->id,
            'tanggal'    => $today,
            'hari'       => $hari,
            'kelas'      => $user->kelas ?? 'Umum',
            'status'     => $request->input('status', $statusDefault),
            'keterangan' => $request->input('keterangan', $keteranganDefault),
            'latitude'   => $request->input('latitude'),
            'longitude'  => $request->input('longitude'),
            'lokasi'     => $request->input('lokasi', 'Lokasi terdeteksi via GPS'),
            'waktu_absen'=> Carbon::now()->format('H:i:s'),
        ];

        // 3. Simpan / Update Kehadiran Datang
        if ($existingAbsen && $existingAbsen->lokasi === 'Sistem Otomatis') {
            $existingAbsen->update($absenData);
            $absen = $existingAbsen;
        } else {
            $absen = Absen::create($absenData);
        }

        // Kirim Notifikasi WhatsApp (Absen Masuk)
        WaNotificationService::sendMasuk($user, $absen);

        // 4. Kirim respon JSON ke Kodular
        return response()->json([
            'success' => true,
            'message' => 'Presensi Datang Berhasil!',
            'data'    => $absen
        ], 200);
    }

    /**
     * Display list of leave requests for admin approval.
     */
    public function persetujuanIndex(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'Pending');
        $query = Absen::with('user')->whereIn('status', ['Izin', 'Sakit']);

        if ($user->role === 'guru' && $user->kelas) {
            $query->where('kelas', $user->kelas);
        }

        if ($status !== 'semua') {
            $query->where('status_persetujuan', $status);
        }

        $pengajuans = $query->orderBy('created_at', 'desc')->paginate(15);

        $pendingQuery = Absen::whereIn('status', ['Izin', 'Sakit'])->where('status_persetujuan', 'Pending');
        $disetujuiQuery = Absen::whereIn('status', ['Izin', 'Sakit'])->where('status_persetujuan', 'Disetujui');
        $ditolakQuery = Absen::whereIn('status', ['Izin', 'Sakit'])->where('status_persetujuan', 'Ditolak');

        if ($user->role === 'guru' && $user->kelas) {
            $pendingQuery->where('kelas', $user->kelas);
            $disetujuiQuery->where('kelas', $user->kelas);
            $ditolakQuery->where('kelas', $user->kelas);
        }

        $totalPending = $pendingQuery->count();
        $totalDisetujui = $disetujuiQuery->count();
        $totalDitolak = $ditolakQuery->count();

        return view('absensi.persetujuan', compact('pengajuans', 'status', 'totalPending', 'totalDisetujui', 'totalDitolak'));
    }

    /**
     * Approve a permission request (ACC).
     */
    public function setujuiIzin($id)
    {
        $user = Auth::user();
        $absen = Absen::findOrFail($id);

        if ($user->role === 'guru' && $user->kelas && $absen->kelas !== $user->kelas) {
            return redirect()->back()->with('error', 'Anda hanya berhak memproses pengajuan izin untuk siswa di kelas ' . $user->kelas . '!');
        }

        $absen->update([
            'status_persetujuan' => 'Disetujui',
        ]);

        if ($absen->user) {
            WaNotificationService::sendMasuk($absen->user, $absen);
        }

        return redirect()->back()->with('success', 'Pengajuan ' . $absen->status . ' untuk ' . ($absen->user->name ?? 'Siswa') . ' BERHASIL DISETUJUI!');
    }

    /**
     * Reject a permission request.
     */
    public function tolakIzin($id)
    {
        $user = Auth::user();
        $absen = Absen::findOrFail($id);

        if ($user->role === 'guru' && $user->kelas && $absen->kelas !== $user->kelas) {
            return redirect()->back()->with('error', 'Anda hanya berhak memproses pengajuan izin untuk siswa di kelas ' . $user->kelas . '!');
        }

        $absen->update([
            'status_persetujuan' => 'Ditolak',
        ]);

        return redirect()->back()->with('error', 'Pengajuan ' . $absen->status . ' untuk ' . ($absen->user->name ?? 'Siswa') . ' TELAH DITOLAK.');
    }
}
