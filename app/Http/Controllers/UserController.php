<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->where('role', '!=', 'admin');

        $currentUser = auth()->user();
        if ($currentUser && $currentUser->role === 'guru' && $currentUser->kelas) {
            $query->where('kelas', $currentUser->kelas);
        }

        // Search by Name or NIS
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter per kelas
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->input('kelas'));
        }

        // Filter per jurusan
        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->input('jurusan'));
        }

        $users = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();

        // Get unique options for filter dropdowns
        $kelases = User::whereNotNull('kelas')->where('role', '!=', 'admin')->distinct()->pluck('kelas')->sort();
        $jurusans = User::whereNotNull('jurusan')->where('role', '!=', 'admin')->distinct()->pluck('jurusan')->sort();

        return view('users.index', compact('users', 'kelases', 'jurusans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'nullable|string|unique:users,nis',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'no_hp' => 'nullable|string',
            'kelas' => 'nullable|string',
            'jurusan' => 'nullable|string',
            'role' => 'nullable|in:siswa,guru,admin',
            'password' => 'required|string|min:6',
            'angkatan' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = $request->input('role', 'siswa');

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Data User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nis' => 'nullable|string|unique:users,nis,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'no_hp' => 'nullable|string',
            'kelas' => 'nullable|string',
            'jurusan' => 'nullable|string',
            'role' => 'nullable|in:siswa,guru,admin',
            'password' => 'nullable|string|min:6',
            'angkatan' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Data User berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Data User berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $query = User::query()->where('role', '!=', 'admin');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->input('kelas'));
        }
        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->input('jurusan'));
        }

        $users = $query->orderBy('name', 'asc')->get();

        $filename = 'data_users_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            // Output BOM for Excel UTF-8 recognition
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['No', 'NIS', 'Nama Lengkap', 'Email', 'Kelas', 'Jurusan']);

            foreach ($users as $index => $user) {
                fputcsv($file, [
                    $index + 1,
                    $user->nis,
                    $user->name,
                    $user->email,
                    $user->kelas,
                    $user->jurusan,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
