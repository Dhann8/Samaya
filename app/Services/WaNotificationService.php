<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\Absen;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class WaNotificationService
{
    /**
     * Send Attendance Check-in Notification (Absen Masuk)
     */
    public static function sendMasuk(User $user, Absen $absen): void
    {
        self::sendNotification($user, $absen, 'MASUK');
    }

    /**
     * Send Attendance Check-out Notification (Absen Pulang)
     */
    public static function sendPulang(User $user, Absen $absen): void
    {
        self::sendNotification($user, $absen, 'PULANG');
    }

    /**
     * Send Late Warning Notification
     */
    public static function sendWarningTelat(User $user, Absen $absen): void
    {
        self::sendNotification($user, $absen, 'WARNING_TELAT');
    }

    /**
     * Send Alfa Notification
     */
    public static function sendAlfa(User $user, Absen $absen): void
    {
        self::sendNotification($user, $absen, 'ALFA');
    }

    /**
     * Check unattended students and queue/send WhatsApp notifications
     * - If time >= jam_batas_terlambat and no attendance: send WA late warning
     * - If time >= jam_batas_alfa and no attendance: record Alfa & send WA notification
     */
    public static function checkAndSendUnattendedNotifications(): array
    {
        $enabled = Setting::get('wa_notification_enabled', '1');
        if ($enabled === '0' || $enabled === 'false') {
            return ['processed' => 0, 'warnings' => 0, 'alfas' => 0];
        }

        $jamBatasTerlambat = Setting::get('jam_batas_terlambat', '07:30');
        $jamBatasAlfa      = Setting::get('jam_batas_alfa', '08:00');

        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        $today = $now->toDateString();
        $todayDay = $now->locale('id')->isoFormat('dddd');

        $warningCount = 0;
        $alfaCount = 0;

        // Fetch all active students (role 'siswa')
        $students = User::where('role', 'siswa')->get();

        foreach ($students as $student) {
            $absen = Absen::where('user_id', $student->id)->where('tanggal', $today)->first();

            // 1. Check if current time >= jam_batas_alfa
            if ($currentTime >= $jamBatasAlfa) {
                if (!$absen) {
                    // Create Alfa attendance record automatically for today
                    $absen = Absen::create([
                        'user_id'     => $student->id,
                        'tanggal'     => $today,
                        'hari'        => $todayDay,
                        'kelas'       => $student->kelas ?? 'Umum',
                        'status'      => 'Alpa',
                        'keterangan'  => 'Otomatis Alfa oleh Sistem (Melewati jam batas alfa ' . $jamBatasAlfa . ')',
                        'waktu_absen' => $jamBatasAlfa . ':00',
                        'lokasi'      => 'Sistem Otomatis',
                    ]);

                    self::sendAlfa($student, $absen);
                    $alfaCount++;
                } elseif ($absen->lokasi === 'Sistem Otomatis' && $absen->status !== 'Alpa') {
                    // Automatically transition status from 'Belum Absen / Terlambat' to 'Alpa'
                    $absen->update([
                        'status'     => 'Alpa',
                        'keterangan' => 'Otomatis Alfa oleh Sistem (Melewati jam batas alfa ' . $jamBatasAlfa . ')',
                    ]);

                    $cacheKey = "wa_alfa_sent_{$student->id}_{$today}";
                    if (!Cache::has($cacheKey)) {
                        self::sendAlfa($student, $absen);
                        Cache::put($cacheKey, true, now()->endOfDay());
                        $alfaCount++;
                    }
                }
            } 
            // 2. Check if current time >= jam_batas_terlambat but < jam_batas_alfa
            elseif ($currentTime >= $jamBatasTerlambat) {
                if (!$absen) {
                    // Automatically create attendance record for late/unattended student
                    $absen = Absen::create([
                        'user_id'     => $student->id,
                        'tanggal'     => $today,
                        'hari'        => $todayDay,
                        'kelas'       => $student->kelas ?? 'Umum',
                        'status'      => 'Hadir',
                        'keterangan'  => 'Belum Absen / Terlambat (Melewati jam batas masuk ' . $jamBatasTerlambat . ')',
                        'waktu_absen' => $jamBatasTerlambat . ':00',
                        'lokasi'      => 'Sistem Otomatis',
                    ]);

                    $cacheKey = "wa_warning_telat_{$student->id}_{$today}";
                    if (!Cache::has($cacheKey)) {
                        self::sendWarningTelat($student, $absen);
                        Cache::put($cacheKey, true, now()->endOfDay());
                        $warningCount++;
                    }
                }
            }
        }

        return [
            'processed' => count($students),
            'warnings'  => $warningCount,
            'alfas'     => $alfaCount,
        ];
    }

    /**
     * Send Notification (Handles Masuk, Pulang, WARNING_TELAT, & ALFA)
     */
    private static function sendNotification(User $user, Absen $absen, string $type): void
    {
        if (!$user->no_hp) {
            Log::info("WaNotificationService: Skipped (no_hp empty for {$user->name})");
            return;
        }

        $enabled = Setting::get('wa_notification_enabled', '1');
        if ($enabled === '0' || $enabled === 'false') {
            return;
        }

        $gateway = Setting::get('wa_gateway_url', 'http://localhost:3000/api/send-wa');
        $token   = Setting::get('wa_api_token', '');
        $dateFormatted = Carbon::parse($absen->tanggal ?? now())->translatedFormat('d M Y');
        $waktu = ($type === 'PULANG') 
            ? ($absen->waktu_pulang ?? Carbon::now()->format('H:i:s')) 
            : ($absen->waktu_absen ?? Carbon::now()->format('H:i:s'));
        $timeFormatted = $waktu . ' WIB';

        $statusDisplay = $absen->keterangan ?? $absen->status ?? 'Hadir';

        $delay = (int) Setting::get('wa_delay_seconds', '2');
        if ($delay > 0) {
            sleep($delay);
        }

        $payload = [
            'phone'   => $user->no_hp,
            'type'    => $type,
            'name'    => $user->name,
            'kelas'   => $user->kelas ?? 'Umum',
            'date'    => $dateFormatted,
            'time'    => $timeFormatted,
            'status'  => $statusDisplay,
            'lokasi'  => $absen->lokasi ?? 'Lokasi GPS/Sekolah',
            'delay'   => $delay,
            'token'   => $token,
            'gateway' => $gateway,
        ];

        // 1. First Attempt: Direct fast HTTP call to local WA Node Server (port 3000)
        try {
            $targetUrl = str_contains($gateway, 'localhost:3000') || empty($gateway) 
                ? 'http://localhost:3000/api/send-wa' 
                : $gateway;

            $response = Http::timeout(4)->post($targetUrl, $payload);
            if ($response->successful()) {
                Log::info("WaNotificationService: WA Notification {$type} successfully sent to {$user->name} ({$user->no_hp})");
                return;
            }
        } catch (\Throwable $e) {
            Log::warning("WaNotificationService: Direct HTTP failed, falling back to background process. Error: " . $e->getMessage());
        }

        // 2. Fallback Attempt: Background Process execution via node script
        self::dispatchNodeScript([
            '--phone=' . $user->no_hp,
            '--type=' . $type,
            '--name=' . $user->name,
            '--kelas=' . ($user->kelas ?? 'Umum'),
            '--date=' . $dateFormatted,
            '--time=' . $timeFormatted,
            '--status=' . ($absen->status ?? 'Hadir'),
            '--lokasi=' . ($absen->lokasi ?? 'Lokasi GPS/Sekolah'),
            '--token=' . $token,
            '--gateway=' . $gateway,
        ]);
    }

    /**
     * Dispatch Node.js script in background as fallback
     */
    private static function dispatchNodeScript(array $args): void
    {
        try {
            $scriptPath = base_path('wa-service/send-cli.js');
            if (!file_exists($scriptPath)) {
                $scriptPath = base_path('scripts/send-wa.mjs');
            }

            $cmd = array_merge(['node', $scriptPath], $args);
            Process::timeout(10)->start($cmd);
        } catch (\Throwable $e) {
            Log::error("WaNotificationService Error: " . $e->getMessage());
        }
    }
}
