<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Absen;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        \App\Services\WaNotificationService::checkAndSendUnattendedNotifications();

        $user = auth()->user();
        $isGuru = $user && $user->role === 'guru' && !empty($user->kelas);
        $teacherKelas = $isGuru ? $user->kelas : null;

        $userQuery = User::where('role', 'siswa');
        $absenQuery = Absen::query();

        if ($isGuru) {
            $userQuery->where('kelas', $teacherKelas);
            $absenQuery->where('kelas', $teacherKelas);
        }

        $totalUsers = $userQuery->count();

        // Status counts in 1 single grouped query instead of 5 separate queries
        $statusGroup = (clone $absenQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $hadirCount = $statusGroup->get('Hadir', 0);
        $izinCount  = $statusGroup->get('Izin', 0);
        $sakitCount = $statusGroup->get('Sakit', 0);
        $alpaCount  = $statusGroup->get('Alpa', 0);
        $totalAbsen = $statusGroup->sum();

        // Jurusan user distribution
        $jurusanDist = (clone $userQuery)
            ->select('jurusan', DB::raw('count(*) as total'))
            ->whereNotNull('jurusan')
            ->groupBy('jurusan')
            ->get();

        // Hari attendance distribution
        $hariDist = (clone $absenQuery)
            ->select('hari', 'status', DB::raw('count(*) as total'))
            ->groupBy('hari', 'status')
            ->get()
            ->groupBy('hari');

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $attendanceByDay = [];
        $absensByHari = [];
        foreach ($days as $day) {
            $group = $hariDist->get($day, collect());
            $attendanceByDay[$day] = [
                'Hadir' => $group->where('status', 'Hadir')->first()->total ?? 0,
                'Izin' => $group->where('status', 'Izin')->first()->total ?? 0,
                'Sakit' => $group->where('status', 'Sakit')->first()->total ?? 0,
                'Alpa' => $group->where('status', 'Alpa')->first()->total ?? 0,
            ];
            $absensByHari[$day] = $group->sum('total');
        }

        // Recent Activity
        $recentAbsens = (clone $absenQuery)->with('user')->orderBy('created_at', 'desc')->take(5)->get();

        return view('dashboard.index', compact(
            'totalUsers',
            'totalAbsen',
            'hadirCount',
            'izinCount',
            'sakitCount',
            'alpaCount',
            'jurusanDist',
            'attendanceByDay',
            'absensByHari',
            'recentAbsens'
        ));
    }
}
