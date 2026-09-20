<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
}