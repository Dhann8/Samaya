<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\Absen;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
     * Send Notification (Handles both Masuk & Pulang)
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
        
        $dateFormatted = Carbon::parse($absen->tanggal)->translatedFormat('d M Y');
        $waktu = ($type === 'MASUK') 
            ? ($absen->waktu_absen ?? Carbon::now()->format('H:i:s')) 
            : ($absen->waktu_pulang ?? Carbon::now()->format('H:i:s'));
        $timeFormatted = $waktu . ' WIB';

        $payload = [
            'phone'   => $user->no_hp,
            'type'    => $type,
            'name'    => $user->name,
            'kelas'   => $user->kelas ?? 'Umum',
            'date'    => $dateFormatted,
            'time'    => $timeFormatted,
            'status'  => $absen->status ?? 'Hadir',
            'lokasi'  => $absen->lokasi ?? 'Lokasi GPS/Sekolah',
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
