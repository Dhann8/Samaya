<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absen;
use App\Services\WaNotificationService;
use Carbon\Carbon;

class ApiAbsenController extends Controller
{
    /**
     * Indonesian day name mapping.
     */
    private array $daysInIndonesian = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
    ];

    /**
     * Scan QR Code and record attendance (Absen Masuk).
     *
     * POST /api/absen/qr-scan
     * Headers: Authorization: Bearer {token}
     * Body: { "qr_data": "...", "latitude": "...", "longitude": "...", "lokasi": "..." }
     */
    public function qrScan(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi harian hanya dapat dilakukan oleh akun Siswa!',
            ], 403);
        }

        $today = Carbon::today()->format('Y-m-d');

        // Check if user already attended today (1x per day limit)
        $existingAbsen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if ($existingAbsen && $existingAbsen->lokasi !== 'Sistem Otomatis') {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi masuk hari ini!',
                'data'    => [
                    'tanggal'     => $existingAbsen->tanggal,
                    'hari'        => $existingAbsen->hari,
                    'status'      => $existingAbsen->status,
                    'waktu_absen' => $existingAbsen->waktu_absen,
                ],
            ], 409); // 409 Conflict
        }

        $request->validate([
            'qr_data'    => 'required|string',
            'latitude'   => 'nullable',
            'longitude'  => 'nullable',
            'lokasi'     => 'nullable|string',
            'status'     => 'nullable|in:Hadir,Izin,Sakit,Alpa',
            'keterangan' => 'nullable|string',
        ]);

        // Validate QR token — must match today's QR token (MASUK, PULANG, or DAILY)
        $scannedQr   = $request->input('qr_data');
        $tokenMasuk  = "SAMAYA-QR-{$today}-MASUK-" . substr(md5("SAMAYA-SECRET-{$today}-MASUK"), 0, 12);
        $tokenPulang = "SAMAYA-QR-{$today}-PULANG-" . substr(md5("SAMAYA-SECRET-{$today}-PULANG"), 0, 12);
        $tokenDaily  = "SAMAYA-DAILY-QR-{$today}-" . substr(md5("SAMAYA-DAILY-SECRET-{$today}"), 0, 12);

        $isValidQr = ($scannedQr === $tokenMasuk || $scannedQr === $tokenPulang || $scannedQr === $tokenDaily || str_starts_with($scannedQr, "SAMAYA-QR-{$today}") || str_starts_with($scannedQr, "SAMAYA-DAILY-QR-{$today}"));

        if (!$isValidQr) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa. Pastikan Anda memindai QR Code hari ini.',
            ], 422);
        }

        $hari = $this->daysInIndonesian[Carbon::now()->format('l')] ?? 'Senin';

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
            'user_id'     => $user->id,
            'tanggal'     => $today,
            'hari'        => $hari,
            'kelas'       => $user->kelas ?? 'Umum',
            'status'      => $request->input('status', $statusDefault),
            'keterangan'  => $request->input('keterangan', $keteranganDefault),
            'latitude'    => $request->input('latitude') !== null ? (string)$request->input('latitude') : null,
            'longitude'   => $request->input('longitude') !== null ? (string)$request->input('longitude') : null,
            'lokasi'      => $request->input('lokasi', 'Lokasi terdeteksi via GPS Mobile'),
            'waktu_absen' => Carbon::now()->format('H:i:s'),
        ];

        if ($existingAbsen && $existingAbsen->lokasi === 'Sistem Otomatis') {
            $existingAbsen->update($absenData);
            $absen = $existingAbsen;
        } else {
            $absen = Absen::create($absenData);
        }

        // Send WA Notification (Absen Masuk)
        WaNotificationService::sendMasuk($user, $absen);

        return response()->json([
            'success' => true,
            'message' => 'Presensi masuk berhasil dicatat!',
            'data'    => [
                'id'          => $absen->id,
                'tanggal'     => $absen->tanggal,
                'hari'        => $absen->hari,
                'kelas'       => $absen->kelas,
                'status'      => $absen->status,
                'waktu_absen' => $absen->waktu_absen,
                'lokasi'      => $absen->lokasi,
                'latitude'    => $absen->latitude,
                'longitude'   => $absen->longitude,
            ],
        ], 200);
    }

    /**
     * Record checkout time (Absen Pulang).
     *
     * POST /api/absen/pulang
     * Headers: Authorization: Bearer {token}
     * Body: { "latitude": "...", "longitude": "...", "lokasi": "..." }
     */
    public function absenPulang(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi harian hanya dapat dilakukan oleh akun Siswa!',
            ], 403);
        }

        $today = Carbon::today()->format('Y-m-d');

        // Cari data absen masuk hari ini
        $absen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if (!$absen) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absen masuk hari ini!',
            ], 400);
        }

        if ($absen->waktu_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen pulang hari ini!',
            ], 409);
        }

        // Validasi Token QR — cocokkan dengan token hari ini (PULANG, MASUK, atau DAILY)
        if ($request->has('qr_data')) {
            $scannedQr   = $request->input('qr_data');
            $tokenMasuk  = "SAMAYA-QR-{$today}-MASUK-" . substr(md5("SAMAYA-SECRET-{$today}-MASUK"), 0, 12);
            $tokenPulang = "SAMAYA-QR-{$today}-PULANG-" . substr(md5("SAMAYA-SECRET-{$today}-PULANG"), 0, 12);
            $tokenDaily  = "SAMAYA-DAILY-QR-{$today}-" . substr(md5("SAMAYA-DAILY-SECRET-{$today}"), 0, 12);

            $isValidQr = ($scannedQr === $tokenPulang || $scannedQr === $tokenMasuk || $scannedQr === $tokenDaily || str_starts_with($scannedQr, "SAMAYA-QR-{$today}") || str_starts_with($scannedQr, "SAMAYA-DAILY-QR-{$today}"));

            if (!$isValidQr) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid atau sudah kadaluarsa!',
                ], 422);
            }
        }

        // Update waktu pulang
        $absen->update([
            'waktu_pulang' => Carbon::now()->format('H:i:s'),
        ]);

        // Send WA Notification (Absen Pulang)
        WaNotificationService::sendPulang($user, $absen);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil dicatat! Selamat beristirahat.',
            'data'    => $absen,
        ], 200);
    }

    /**
     * Check if user has already attended today.
     *
     * GET /api/absen/status-hari-ini
     * Headers: Authorization: Bearer {token}
     */
    public function statusHariIni(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $todayAbsen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        $hari = $this->daysInIndonesian[Carbon::now()->format('l')] ?? 'Senin';

        if ($todayAbsen) {
            return response()->json([
                'success' => true,
                'message' => 'Data absensi hari ini ditemukan.',
                'data'    => [
                    'sudah_absen'  => true,
                    'tanggal'      => $todayAbsen->tanggal,
                    'hari'         => $hari,
                    'status'       => $todayAbsen->status,
                    'waktu_absen'  => $todayAbsen->waktu_absen,
                    'jam_pulang'   => $todayAbsen->jam_pulang,
                    'lokasi'       => $todayAbsen->lokasi,
                    'keterangan'   => $todayAbsen->keterangan,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Anda belum melakukan absensi hari ini.',
            'data'    => [
                'sudah_absen' => false,
                'tanggal'     => $today,
                'hari'        => $hari,
            ],
        ]);
    }

    /**
     * Get attendance history for authenticated user.
     *
     * GET /api/absen/riwayat?bulan=9&tahun=2026
     * Headers: Authorization: Bearer {token}
     */
    public function riwayat(Request $request)
    {
        $user = $request->user();

        $query = Absen::where('user_id', $user->id);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->input('tahun'));
        }

        $absens = $query->orderBy('tanggal', 'desc')->get();

        $totalHadir = $absens->where('status', 'Hadir')->count();
        $totalIzin  = $absens->where('status', 'Izin')->count();
        $totalSakit = $absens->where('status', 'Sakit')->count();
        $totalAlpa  = $absens->whereIn('status', ['Alpa', 'Alfa'])->count();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat absensi berhasil diambil.',
            'data'    => [
                'ringkasan' => [
                    'total' => $absens->count(),
                    'hadir' => $totalHadir,
                    'izin'  => $totalIzin,
                    'sakit' => $totalSakit,
                    'alpa'  => $totalAlpa,
                ],
                'riwayat'   => $absens->map(function ($absen) {
                    return [
                        'id'          => $absen->id,
                        'tanggal'     => $absen->tanggal,
                        'hari'        => $absen->hari,
                        'kelas'       => $absen->kelas,
                        'status'      => $absen->status,
                        'keterangan'  => $absen->keterangan,
                        'waktu_absen' => $absen->waktu_absen,
                        'jam_pulang'  => $absen->jam_pulang,
                        'lokasi'      => $absen->lokasi,
                        'latitude'    => $absen->latitude,
                        'longitude'   => $absen->longitude,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Get today's QR token for validation.
     *
     * GET /api/absen/qr-token
     * Headers: Authorization: Bearer {token}
     */
    public function qrToken(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $hari = $this->daysInIndonesian[Carbon::now()->format('l')] ?? 'Senin';
        $jenisQr  = Carbon::now()->hour < 12 ? 'MASUK' : 'PULANG';
        $qrDataString = "SAMAYA-QR-{$today}-{$jenisQr}-" . substr(md5("SAMAYA-SECRET-{$today}-{$jenisQr}"), 0, 12);

        return response()->json([
            'success' => true,
            'message' => 'QR token harian berhasil diambil.',
            'data'    => [
                'qr_token'       => $qrDataString,
                'tanggal'        => $today,
                'hari'           => $hari,
                'jenis'          => $jenisQr,
                'berlaku_sampai' => '23:59:59 WIB',
            ],
        ]);
    }

    /**
     * Get list of all students with today's attendance status.
     *
     * GET /api/siswa
     */
    public function siswaList(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $query = \App\Models\User::where('role', '!=', 'admin');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('kelas', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name', 'asc')->get();
        $todayAbsens = Absen::where('tanggal', $today)->get()->keyBy('user_id');

        $result = $students->map(function ($student) use ($todayAbsens) {
            $absen = $todayAbsens->get($student->id);
            return [
                'id'            => $student->id,
                'name'          => $student->name,
                'nis'           => $student->nis,
                'kelas'         => $student->kelas ?? 'XI RPL 1',
                'status_masuk'  => $absen ? ($absen->status ?? 'Hadir') : 'Belum',
                'status_pulang' => ($absen && $absen->waktu_pulang) ? 'Pulang' : 'Belum',
                'waktu_absen'   => $absen ? $absen->waktu_absen : null,
                'waktu_pulang'  => $absen ? $absen->waktu_pulang : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar siswa berhasil diambil.',
            'data'    => $result,
        ]);
    }

    /**
     * Get all attendance logs for a specific date (for Data Absensi screen).
     *
     * GET /api/absen/semua?tanggal=2026-08-27&type=masuk&search=Andi
     */
    public function absenSemua(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->format('Y-m-d'));
        $type    = strtolower($request->input('type', 'masuk')); // 'masuk' or 'pulang'
        $search  = $request->input('search');

        $query = Absen::with('user')->where('tanggal', $tanggal);

        if ($type === 'pulang') {
            $query->whereNotNull('waktu_pulang');
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $absens = $query->orderBy($type === 'pulang' ? 'waktu_pulang' : 'waktu_absen', 'asc')->get();

        $result = $absens->map(function ($item, $index) use ($type) {
            return [
                'no'     => $index + 1,
                'id'     => $item->id,
                'nama'   => $item->user->name ?? 'Siswa',
                'nis'    => $item->user->nis ?? '-',
                'kelas'  => $item->kelas ?? ($item->user->kelas ?? '-'),
                'status' => $item->status ?? 'Hadir',
                'waktu'  => $type === 'pulang' ? ($item->waktu_pulang ? substr($item->waktu_pulang, 0, 5) : '-') : ($item->waktu_absen ? substr($item->waktu_absen, 0, 5) : '-'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diambil.',
            'tanggal' => $tanggal,
            'type'    => $type,
            'data'    => $result,
        ]);
    }

    /**
     * Record attendance manually for a specific student (by student ID or NIS).
     *
     * POST /api/absen/manual
     */
    public function absenManual(Request $request)
    {
        $request->validate([
            'student_id' => 'required_without:nis',
            'nis'        => 'required_without:student_id',
            'type'       => 'required|in:masuk,pulang',
        ]);

        $today = Carbon::today()->format('Y-m-d');
        $type  = strtolower($request->input('type'));

        $user = null;
        if ($request->filled('student_id')) {
            $user = \App\Models\User::find($request->input('student_id'));
        } elseif ($request->filled('nis')) {
            $user = \App\Models\User::where('nis', $request->input('nis'))->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan!',
            ], 44);
        }

        $existingAbsen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        $hari = $this->daysInIndonesian[Carbon::now()->format('l')] ?? 'Senin';
        $waktuNow = Carbon::now()->format('H:i:s');
        $waktuDisplay = Carbon::now()->format('H:i');

        if ($type === 'masuk') {
            if ($existingAbsen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa ' . $user->name . ' sudah melakukan absen masuk hari ini!',
                    'data'    => [
                        'name'        => $user->name,
                        'kelas'       => $user->kelas ?? 'XI RPL 1',
                        'waktu'       => substr($existingAbsen->waktu_absen, 0, 5) . ' WIB',
                        'tanggal'     => Carbon::parse($today)->format('d-m-Y'),
                    ],
                ], 409);
            }

            $jamBatas = \App\Models\Setting::get('jam_batas_terlambat', '07:30');
            $keteranganDefault = $waktuDisplay > $jamBatas
                ? "Terlambat (Jam {$waktuDisplay} WIB)"
                : "Tepat Waktu (Jam {$waktuDisplay} WIB)";

            $absen = Absen::create([
                'user_id'     => $user->id,
                'tanggal'     => $today,
                'hari'        => $hari,
                'kelas'       => $user->kelas ?? 'XI RPL 1',
                'status'      => 'Hadir',
                'keterangan'  => $keteranganDefault,
                'lokasi'      => 'Aplikasi Mobile Absensi',
                'waktu_absen' => $waktuNow,
            ]);

            // WA Notif
            WaNotificationService::sendMasuk($user, $absen);

            return response()->json([
                'success' => true,
                'message' => 'Absen Berhasil!',
                'data'    => [
                    'name'    => $user->name,
                    'kelas'   => $user->kelas ?? 'XI RPL 1',
                    'waktu'   => $waktuDisplay . ' WIB',
                    'tanggal' => Carbon::parse($today)->format('d-m-Y'),
                ],
            ]);
        } else {
            // Absen Pulang
            if (!$existingAbsen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa ' . $user->name . ' belum melakukan absen masuk hari ini!',
                ], 400);
            }

            if ($existingAbsen->waktu_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa ' . $user->name . ' sudah melakukan absen pulang hari ini!',
                    'data'    => [
                        'name'    => $user->name,
                        'kelas'   => $user->kelas ?? 'XI RPL 1',
                        'waktu'   => substr($existingAbsen->waktu_pulang, 0, 5) . ' WIB',
                        'tanggal' => Carbon::parse($today)->format('d-m-Y'),
                    ],
                ], 409);
            }

            $existingAbsen->update([
                'waktu_pulang' => $waktuNow,
            ]);

            WaNotificationService::sendPulang($user, $existingAbsen);

            return response()->json([
                'success' => true,
                'message' => 'Absen Pulang Berhasil!',
                'data'    => [
                    'name'    => $user->name,
                    'kelas'   => $user->kelas ?? 'XI RPL 1',
                    'waktu'   => $waktuDisplay . ' WIB',
                    'tanggal' => Carbon::parse($today)->format('d-m-Y'),
                ],
            ]);
        }
    }

    /**
     * Submit permission or sick leave request (Pengajuan Izin / Sakit).
     *
     * POST /api/absen/pengajuan-izin
     * Headers: Authorization: Bearer {token}
     * Body: { "status": "Izin"|"Sakit", "keterangan": "...", "bukti": "base64...", "tanggal": "Y-m-d" }
     */
    public function pengajuanIzin(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'status'     => 'required|in:Izin,Sakit',
            'keterangan' => 'required|string',
            'tanggal'    => 'nullable|date',
            'bukti'      => 'nullable|string',
        ]);

        $tanggal = $request->input('tanggal', Carbon::today()->format('Y-m-d'));
        $existingAbsen = Absen::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($existingAbsen) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki catatan presensi / pengajuan izin pada tanggal ' . Carbon::parse($tanggal)->format('d-m-Y') . '!',
            ], 409);
        }

        $hari = $this->daysInIndonesian[Carbon::parse($tanggal)->format('l')] ?? 'Senin';

        $absen = Absen::create([
            'user_id'            => $user->id,
            'tanggal'            => $tanggal,
            'hari'               => $hari,
            'kelas'              => $user->kelas ?? 'XI RPL 1',
            'status'             => $request->input('status'),
            'keterangan'         => $request->input('keterangan'),
            'status_persetujuan' => 'Pending',
            'bukti'              => $request->input('bukti'),
            'waktu_absen'        => Carbon::now()->format('H:i:s'),
            'lokasi'             => 'Pengajuan via Mobile App',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan ' . $absen->status . ' berhasil dikirim! Menunggu persetujuan admin.',
            'data'    => [
                'id'                 => $absen->id,
                'tanggal'            => $absen->tanggal,
                'status'             => $absen->status,
                'keterangan'         => $absen->keterangan,
                'status_persetujuan' => $absen->status_persetujuan,
                'bukti'              => $absen->bukti ? true : false,
            ],
        ], 201);
    }

    /**
     * Get permission and sick leave submission history for current user.
     *
     * GET /api/absen/riwayat-izin
     * Headers: Authorization: Bearer {token}
     */
    public function riwayatIzin(Request $request)
    {
        $user = $request->user();

        $absens = Absen::where('user_id', $user->id)
            ->whereIn('status', ['Izin', 'Sakit'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pengajuan izin/sakit berhasil diambil.',
            'data'    => $absens->map(function ($absen) {
                return [
                    'id'                 => $absen->id,
                    'tanggal'            => $absen->tanggal,
                    'hari'               => $absen->hari,
                    'status'             => $absen->status,
                    'keterangan'         => $absen->keterangan,
                    'status_persetujuan' => $absen->status_persetujuan ?? 'Pending',
                    'bukti'              => $absen->bukti,
                    'waktu_absen'        => $absen->waktu_absen,
                    'created_at'         => $absen->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Teacher (Guru) Dashboard summary for assigned class.
     *
     * GET /api/guru/dashboard
     */
    public function guruDashboard(Request $request)
    {
        $user = $request->user();
        $kelas = $user->kelas ?? 'XI RPL 1';
        $today = Carbon::today()->format('Y-m-d');

        $totalSiswa = \App\Models\User::where('role', 'siswa')->where('kelas', $kelas)->count();
        $todayAbsens = Absen::where('tanggal', $today)->where('kelas', $kelas)->get();

        $hadir = $todayAbsens->where('status', 'Hadir')->count();
        $izin  = $todayAbsens->where('status', 'Izin')->count();
        $sakit = $todayAbsens->where('status', 'Sakit')->count();
        $alpa  = $todayAbsens->where('status', 'Alpa')->count();
        $belum = max(0, $totalSiswa - ($hadir + $izin + $sakit + $alpa));

        $pendingIzin = Absen::where('kelas', $kelas)
            ->whereIn('status', ['Izin', 'Sakit'])
            ->where('status_persetujuan', 'Pending')
            ->count();

        $recentAbsens = Absen::with('user')
            ->where('kelas', $kelas)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard guru berhasil diambil.',
            'data'    => [
                'kelas'         => $kelas,
                'total_siswa'   => $totalSiswa,
                'summary'       => [
                    'hadir'        => $hadir,
                    'izin'         => $izin,
                    'sakit'        => $sakit,
                    'alpa'         => $alpa,
                    'belum_absen'  => $belum,
                    'pending_izin' => $pendingIzin,
                ],
                'recent_absens' => $recentAbsens->map(function ($a) {
                    return [
                        'id'          => $a->id,
                        'name'        => $a->user->name ?? 'Siswa',
                        'status'      => $a->status,
                        'waktu_absen' => $a->waktu_absen,
                        'tanggal'     => $a->tanggal,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get list of students in teacher's assigned class with today's status.
     *
     * GET /api/guru/siswa
     */
    public function guruSiswaList(Request $request)
    {
        $user = $request->user();
        $kelas = $user->kelas ?? 'XI RPL 1';
        $today = Carbon::today()->format('Y-m-d');

        $query = \App\Models\User::where('role', 'siswa')->where('kelas', $kelas);
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name', 'asc')->get();
        $todayAbsens = Absen::where('tanggal', $today)->where('kelas', $kelas)->get()->keyBy('user_id');

        $result = $students->map(function ($student) use ($todayAbsens) {
            $absen = $todayAbsens->get($student->id);
            return [
                'id'            => $student->id,
                'name'          => $student->name,
                'nis'           => $student->nis,
                'kelas'         => $student->kelas,
                'status_masuk'  => $absen ? ($absen->status ?? 'Hadir') : 'Belum Absen',
                'waktu_absen'   => $absen ? $absen->waktu_absen : null,
                'keterangan'    => $absen ? $absen->keterangan : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar siswa kelas ' . $kelas . ' berhasil diambil.',
            'kelas'   => $kelas,
            'data'    => $result,
        ]);
    }

    /**
     * Get list of permission/sick requests for teacher's class.
     *
     * GET /api/guru/persetujuan-izin
     */
    public function guruPersetujuanIzin(Request $request)
    {
        $user = $request->user();
        $kelas = $user->kelas ?? 'XI RPL 1';

        $absens = Absen::with('user')
            ->where('kelas', $kelas)
            ->whereIn('status', ['Izin', 'Sakit'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengajuan izin kelas ' . $kelas . ' berhasil diambil.',
            'kelas'   => $kelas,
            'data'    => $absens->map(function ($a) {
                return [
                    'id'                 => $a->id,
                    'user_id'            => $a->user_id,
                    'name'               => $a->user->name ?? 'Siswa',
                    'nis'                => $a->user->nis ?? '-',
                    'kelas'              => $a->kelas,
                    'tanggal'            => $a->tanggal,
                    'status'             => $a->status,
                    'keterangan'         => $a->keterangan,
                    'status_persetujuan' => $a->status_persetujuan ?? 'Pending',
                    'bukti'              => $a->bukti,
                    'created_at'         => $a->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Approve (ACC) or Reject a permission request for teacher's class.
     *
     * POST /api/guru/persetujuan-izin/{id}/action
     * Body: { "action": "setujui"|"tolak" }
     */
    public function guruProsesIzin(Request $request, $id)
    {
        $user = $request->user();
        $kelas = $user->kelas ?? 'XI RPL 1';

        $absen = Absen::where('id', $id)->where('kelas', $kelas)->first();

        if (!$absen) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan atau bukan merupakan siswa kelas ' . $kelas . '!',
            ], 404);
        }

        $action = strtolower($request->input('action', 'setujui'));
        $newStatus = $action === 'setujui' ? 'Disetujui' : 'Ditolak';

        $absen->update(['status_persetujuan' => $newStatus]);

        if ($newStatus === 'Disetujui' && $absen->user) {
            WaNotificationService::sendMasuk($absen->user, $absen);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan ' . $absen->status . ' untuk ' . ($absen->user->name ?? 'Siswa') . ' berhasil di-' . $action . '!',
            'data'    => [
                'id'                 => $absen->id,
                'status_persetujuan' => $absen->status_persetujuan,
            ],
        ]);
    }
}