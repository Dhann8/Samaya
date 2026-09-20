<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function downloadApk()
    {
        $file = public_path('downloads/SaMaya-Mobile.apk');
        if (!file_exists($file)) {
            $file = base_path('../Flutter/build/app/outputs/flutter-apk/app-debug.apk');
        }

        if (file_exists($file)) {
            return response()->download($file, 'SaMaya-Mobile.apk', [
                'Content-Type' => 'application/vnd.android.package-archive',
            ]);
        }

        return back()->with('error', 'File APK tidak ditemukan di server.');
    }

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $credentials['login'];
        $password = $credentials['password'];

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';
        
        $user = User::where($fieldType, $loginInput)->first();
        
        if ($user && $user->role !== 'admin') {
            return back()->withErrors([
                'login' => 'Akses ditolak. Web hanya untuk Admin.',
            ])->onlyInput('login');
        }

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, ' . Auth::user()->name);
        }

        return back()->withErrors([
            'login' => 'Kombinasi Email/NIS dan Password tidak sesuai.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    public function apiLogin(Request $request)
    {
        // 1. Validasi Input dari Kodular
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('login');
        $password = $request->input('password');

        // 2. Cek Login menggunakan Email atau NIS
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';
        $user = User::where($fieldType, $loginInput)->first();

        // 3. Verifikasi User dan Password
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kombinasi Email/NIS dan Password tidak sesuai.'
            ], 401);
        }

        // 4. Buat API Token (Membutuhkan Laravel Sanctum: $user->createToken())
        $user->tokens()->delete(); // Hapus token lama jika ada
        $token = $user->createToken('kodular-token')->plainTextToken;

        // 5. Kirim Respon JSON ke Kodular
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil!',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nis' => $user->nis,
                'kelas' => $user->kelas ?? 'Umum',
                'role' => $user->role
            ]
        ], 200);
    }
}