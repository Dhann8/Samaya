<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    /**
     * Login via API — returns Sanctum token.
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('login');
        $password = $request->input('password');

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';
        $user = User::where($fieldType, $loginInput)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Kombinasi Email/NIS dan Password tidak sesuai.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('kodular-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil! Selamat datang, ' . $user->name,
            'token'   => $token,
            'data'    => [
                'user' => [
                    'id'          => $user->id,
                    'nis'         => $user->nis,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'no_hp'       => $user->no_hp,
                    'foto_profil' => $user->foto_profil,
                    'kelas'       => $user->kelas,
                    'angkatan'    => $user->angkatan,
                    'jurusan'     => $user->jurusan,
                    'role'        => $user->role,
                ],
            ],
        ]);
    }

    /**
     * Logout — revoke current token.
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Token telah dihapus.',
        ]);
    }

    /**
     * Get authenticated user profile.
     * GET /api/profil
     */
    public function profil(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data' => [
                'id' => $user->id,
                'nis' => $user->nis,
                'name' => $user->name,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'foto_profil' => $user->foto_profil,
                'kelas' => $user->kelas,
                'angkatan' => $user->angkatan,
                'jurusan' => $user->jurusan,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Update user profile (Email, No. HP, Name, Foto Profil).
     * POST /api/profil/update
     */
    public function updateProfil(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'        => 'nullable|string|max:255',
            'email'       => 'nullable|email|unique:users,email,' . $user->id,
            'no_hp'       => 'nullable|string|max:20',
            'foto_profil' => 'nullable|string',
        ]);

        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }
        if ($request->filled('email')) {
            $user->email = $request->input('email');
        }
        if ($request->filled('no_hp')) {
            $user->no_hp = $request->input('no_hp');
        }
        if ($request->filled('foto_profil')) {
            $user->foto_profil = $request->input('foto_profil');
        }

        try {
            $user->save();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil akun berhasil diperbarui!',
            'data' => [
                'id'          => $user->id,
                'nis'         => $user->nis,
                'name'        => $user->name,
                'email'       => $user->email,
                'no_hp'       => $user->no_hp,
                'foto_profil' => $user->foto_profil,
                'kelas'       => $user->kelas,
                'role'        => $user->role,
            ],
        ]);
    }

    /**
     * Change Password.
     * POST /api/profil/ganti-password
     */
    public function gantiPassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama Anda tidak sesuai!',
            ], 400);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui! Silakan gunakan password baru untuk login.',
        ]);
    }

    /**
     * Request 6-digit OTP Token for Password Reset & Send Email.
     * POST /api/password/request-token
     */
    public function requestResetToken(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Email or NIS
        ]);

        $loginInput = $request->input('login');
        $fieldType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';

        $user = User::where($fieldType, $loginInput)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dengan Email/NIS tersebut tidak ditemukan di database.',
            ], 404);
        }

        // Generate 6-digit OTP Token
        $otpToken = (string) rand(100000, 999999);
        $user->remember_token = $otpToken;
        $user->save();

        // Send Email with OTP Token
        $emailSent = false;
        $emailError = '';

        if (!empty($user->email)) {
            try {
                $recipientEmail = $user->email;
                $userName = $user->name;

                $htmlContent = "
                <div style='font-family: Arial, sans-serif; max-width: 540px; margin: 0 auto; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; background-color: #ffffff;'>
                    <div style='background-color: #1E56A0; padding: 24px; text-align: center; color: #ffffff;'>
                        <h2 style='margin: 0; font-size: 20px;'>Absensi Siswa SMK</h2>
                        <p style='margin: 4px 0 0 0; font-size: 13px; opacity: 0.9;'>Permintaan Reset Password Akun</p>
                    </div>
                    <div style='padding: 28px;'>
                        <p style='font-size: 15px; color: #1E293B; margin-top: 0;'>Halo <strong>{$userName}</strong>,</p>
                        <p style='font-size: 14px; color: #475569; line-height: 1.5;'>Anda telah mengajukan permintaan untuk meriset password akun aplikasi Absensi Siswa. Masukkan <strong>Kode Token Reset 6-Digit</strong> di bawah ini pada aplikasi:</p>
                        
                        <div style='background-color: #F1F5F9; border: 2px dashed #1E56A0; border-radius: 10px; padding: 18px; text-align: center; margin: 24px 0;'>
                            <span style='font-size: 12px; color: #64748B; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;'>Kode Token Reset Anda</span><br/>
                            <span style='font-size: 32px; font-weight: bold; color: #1E56A0; letter-spacing: 6px;'>{$otpToken}</span>
                        </div>

                        <p style='font-size: 13px; color: #64748B; line-height: 1.4;'><i>Catatan: Kode token ini bersifat rahasia dan hanya berlaku untuk 1 kali penggunaan. Jangan bagikan kode ini kepada siapapun.</i></p>
                        <hr style='border: none; border-top: 1px solid #E2E8F0; margin: 24px 0;'/>
                        <p style='font-size: 12px; color: #94A3B8; text-align: center; margin: 0;'>Jika Anda tidak merasa meminta reset password, abaikan pesan email ini.</p>
                    </div>
                </div>
                ";

                Mail::html($htmlContent, function ($message) use ($recipientEmail, $userName, $otpToken) {
                    $message->to($recipientEmail, $userName)
                            ->subject("[{$otpToken}] Kode Token Reset Password Akun Absensi Siswa");
                });

                $emailSent = true;
            } catch (\Exception $e) {
                $emailSent = false;
                $emailError = $e->getMessage();
            }
        }

        return response()->json([
            'success'    => true,
            'message'    => "Kode Token Reset 6-Digit telah resmi dikirim ke email {$user->email}! Silakan buka email Anda untuk melihat kode token.",
            'email'      => $user->email,
            'nis'        => $user->nis,
            'email_sent' => $emailSent,
        ]);
    }

    /**
     * Verify 6-digit OTP Token.
     * POST /api/password/verify-token
     */
    public function verifyResetToken(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'token' => 'required|string',
        ]);

        $loginInput = $request->input('login');
        $inputToken = trim($request->input('token'));
        $fieldType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';

        $user = User::where($fieldType, $loginInput)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dengan Email/NIS tersebut tidak ditemukan.',
            ], 404);
        }

        if (empty($user->remember_token) || trim($user->remember_token) !== $inputToken) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Token Reset tidak valid atau salah! Silakan periksa kembali email Anda.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode Token valid! Silakan masukkan password baru Anda.',
        ]);
    }

    /**
     * Reset Password using 6-digit OTP Token.
     * POST /api/password/reset-with-token
     */
    public function resetWithToken(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'token'    => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $loginInput  = $request->input('login');
        $inputToken  = trim($request->input('token'));
        $newPassword = $request->input('password');

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nis';
        $user = User::where($fieldType, $loginInput)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dengan Email/NIS tersebut tidak ditemukan.',
            ], 404);
        }

        if (empty($user->remember_token) || trim($user->remember_token) !== $inputToken) {
            return response()->json([
                'success' => false,
                'message' => 'Kode Token Reset tidak valid atau sudah tidak berlaku!',
            ], 400);
        }

        $user->password = Hash::make($newPassword);
        $user->remember_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password baru Anda berhasil disimpan! Silakan login dengan password baru Anda.',
        ]);
    }
}
