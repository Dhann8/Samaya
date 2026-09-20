<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiAbsenController;

// Public Routes
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/password/request-token', [ApiAuthController::class, 'requestResetToken']);
Route::post('/password/reset-email', [ApiAuthController::class, 'requestResetToken']);
Route::post('/password/verify-token', [ApiAuthController::class, 'verifyResetToken']);
Route::post('/password/reset-with-token', [ApiAuthController::class, 'resetWithToken']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profil
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/profil', [ApiAuthController::class, 'profil']);
    Route::post('/profil/update', [ApiAuthController::class, 'updateProfil']);
    Route::post('/profil/ganti-password', [ApiAuthController::class, 'gantiPassword']);

    // Absensi
    Route::post('/absen/qr-scan', [ApiAbsenController::class, 'qrScan']);
    Route::post('/absen/pulang', [ApiAbsenController::class, 'absenPulang']);
    Route::get('/absen/status-hari-ini', [ApiAbsenController::class, 'statusHariIni']);
    Route::get('/absen/riwayat', [ApiAbsenController::class, 'riwayat']);
    Route::get('/absen/qr-token', [ApiAbsenController::class, 'qrToken']);

    // Student List & Attendance Management (Flutter App)
    Route::get('/siswa', [ApiAbsenController::class, 'siswaList']);
    Route::get('/absen/semua', [ApiAbsenController::class, 'absenSemua']);
    Route::post('/absen/manual', [ApiAbsenController::class, 'absenManual']);
    // Pengajuan Izin & Sakit (Manual with approval & evidence)
    Route::post('/absen/pengajuan-izin', [ApiAbsenController::class, 'pengajuanIzin']);
    Route::get('/absen/riwayat-izin', [ApiAbsenController::class, 'riwayatIzin']);

    // Teacher (Guru / Wali Kelas) Endpoints
    Route::get('/guru/dashboard', [ApiAbsenController::class, 'guruDashboard']);
    Route::get('/guru/siswa', [ApiAbsenController::class, 'guruSiswaList']);
    Route::get('/guru/persetujuan-izin', [ApiAbsenController::class, 'guruPersetujuanIzin']);
    Route::post('/guru/persetujuan-izin/{id}/action', [ApiAbsenController::class, 'guruProsesIzin']);
});
