<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AbsenController;
use App\Http\Controllers\SettingController;

// Public Landing Page & APK Download
Route::get('/', [AuthController::class, 'landing'])->name('landing');
Route::get('/download-apk', [AuthController::class, 'downloadApk'])->name('download.apk');

// Auth Routes
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users CRUD & Export
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

    // Absensi CRUD & Export
    Route::get('/absensi', [AbsenController::class, 'index'])->name('absensi.index');
    Route::post('/absensi', [AbsenController::class, 'store'])->name('absensi.store');
    Route::put('/absensi/{id}', [AbsenController::class, 'update'])->name('absensi.update');
    Route::delete('/absensi/{id}', [AbsenController::class, 'destroy'])->name('absensi.destroy');
    Route::get('/absensi/export', [AbsenController::class, 'export'])->name('absensi.export');

    // QR Code Absensi & Scanning
    Route::get('/absen-qr', [AbsenController::class, 'qrIndex'])->name('absensi.qr');
    Route::post('/absen-qr/scan', [AbsenController::class, 'qrScan'])->name('absensi.qr.scan');

    // Persetujuan Izin & Sakit
    Route::get('/absensi/persetujuan', [AbsenController::class, 'persetujuanIndex'])->name('absensi.persetujuan');
    Route::post('/absensi/persetujuan/{id}/setujui', [AbsenController::class, 'setujuiIzin'])->name('absensi.persetujuan.setujui');
    Route::post('/absensi/persetujuan/{id}/tolak', [AbsenController::class, 'tolakIzin'])->name('absensi.persetujuan.tolak');

    // Settings Routes
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});