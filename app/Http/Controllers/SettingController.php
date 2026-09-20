<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAll();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'jam_batas_terlambat' => 'required|string',
            'jam_batas_alfa' => 'required|string',
            'jam_masuk_mulai' => 'required|string',
            'jam_masuk_akhir' => 'required|string',
            'jam_pulang_mulai' => 'required|string',
            'jam_pulang_akhir' => 'required|string',
            'qr_refresh_mode' => 'required|string|in:daily,5_min,15_min,30_min,hourly',
            'sekolah_nama' => 'nullable|string|max:255',
            'wa_notification_enabled' => 'nullable|string',
            'wa_gateway_url' => 'nullable|string',
            'wa_api_token' => 'nullable|string',
            'wa_delay_seconds' => 'nullable|numeric|min:0|max:60',
            'apk_download_url' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan aplikasi berhasil diperbarui!');
    }
}
