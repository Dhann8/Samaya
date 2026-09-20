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
        $totalAbsen = (clone $absenQuery)->count();

        // Status counts
        $hadirCount = (clone $absenQuery)->where('status', 'Hadir')->count();
        $izinCount  = (clone $absenQuery)->where('status', 'Izin')->count();
        $sakitCount = (clone $absenQuery)->where('status', 'Sakit')->count();
        $alpaCount  = (clone $absenQuery)->where('status', 'Alpa')->count();

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
