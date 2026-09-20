<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Absen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'nis' => '00001',
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'kelas' => 'Staff',
            'angkatan' => '2024',
            'jurusan' => 'Kurikulum',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $studentsData = [
            // Angkatan 2024 (Kelas XII)
            ['nis' => '102401', 'name' => 'Ahmad Fauzi', 'email' => 'fauzi@school.id', 'kelas' => 'XII RPL 1', 'angkatan' => '2024', 'jurusan' => 'RPL'],
            ['nis' => '102402', 'name' => 'Budi Santoso', 'email' => 'budi@school.id', 'kelas' => 'XII RPL 1', 'angkatan' => '2024', 'jurusan' => 'RPL'],
            ['nis' => '102403', 'name' => 'Citra Dewi', 'email' => 'citra@school.id', 'kelas' => 'XII TKJ 1', 'angkatan' => '2024', 'jurusan' => 'TKJ'],
            ['nis' => '102404', 'name' => 'Dwi Cahyo', 'email' => 'dwi@school.id', 'kelas' => 'XII TKJ 1', 'angkatan' => '2024', 'jurusan' => 'TKJ'],
            ['nis' => '102405', 'name' => 'Eka Putri', 'email' => 'eka@school.id', 'kelas' => 'XII DKV 1', 'angkatan' => '2024', 'jurusan' => 'DKV'],

            // Angkatan 2025 (Kelas XI)
            ['nis' => '102501', 'name' => 'Fajar Pratama', 'email' => 'fajar@school.id', 'kelas' => 'XI RPL 1', 'angkatan' => '2025', 'jurusan' => 'RPL'],
            ['nis' => '102502', 'name' => 'Gita Gutawa', 'email' => 'gita@school.id', 'kelas' => 'XI RPL 1', 'angkatan' => '2025', 'jurusan' => 'RPL'],
            ['nis' => '102503', 'name' => 'Hadi Wijaya', 'email' => 'hadi@school.id', 'kelas' => 'XI TKJ 1', 'angkatan' => '2025', 'jurusan' => 'TKJ'],
            ['nis' => '102504', 'name' => 'Indah Sari', 'email' => 'indah@school.id', 'kelas' => 'XI DKV 1', 'angkatan' => '2025', 'jurusan' => 'DKV'],
            ['nis' => '102505', 'name' => 'Joko Susilo', 'email' => 'joko@school.id', 'kelas' => 'XI DKV 1', 'angkatan' => '2025', 'jurusan' => 'DKV'],

            // Angkatan 2026 (Kelas X)
            ['nis' => '102601', 'name' => 'Kiki Amelia', 'email' => 'kiki@school.id', 'kelas' => 'X RPL 1', 'angkatan' => '2026', 'jurusan' => 'RPL'],
            ['nis' => '102602', 'name' => 'Luki Hermawan', 'email' => 'luki@school.id', 'kelas' => 'X RPL 1', 'angkatan' => '2026', 'jurusan' => 'RPL'],
            ['nis' => '102603', 'name' => 'Maya Rahma', 'email' => 'maya@school.id', 'kelas' => 'X TKJ 1', 'angkatan' => '2026', 'jurusan' => 'TKJ'],
            ['nis' => '102604', 'name' => 'Naufal Rizky', 'email' => 'naufal@school.id', 'kelas' => 'X TKJ 1', 'angkatan' => '2026', 'jurusan' => 'TKJ'],
            ['nis' => '102605', 'name' => 'Olivia Zalianty', 'email' => 'olivia@school.id', 'kelas' => 'X DKV 1', 'angkatan' => '2026', 'jurusan' => 'DKV'],
        ];

        $createdStudents = [];
        foreach ($studentsData as $student) {
            $createdStudents[] = User::create([
                'nis' => $student['nis'],
                'name' => $student['name'],
                'email' => $student['email'],
                'kelas' => $student['kelas'],
                'angkatan' => $student['angkatan'],
                'jurusan' => $student['jurusan'],
                'role' => 'siswa',
                'password' => Hash::make('password'),
            ]);
        }

        // Generate Attendance Data over past 5 days (Senin to Jumat)
        $days = [
            ['hari' => 'Senin', 'subDays' => 4],
            ['hari' => 'Selasa', 'subDays' => 3],
            ['hari' => 'Rabu', 'subDays' => 2],
            ['hari' => 'Kamis', 'subDays' => 1],
            ['hari' => 'Jumat', 'subDays' => 0],
        ];

        $statuses = ['Hadir', 'Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit', 'Alpa'];
        $keteranganMap = [
            'Hadir' => 'Tepat waktu',
            'Izin' => 'Acara keluarga',
            'Sakit' => 'Demam & flu',
            'Alpa' => 'Tanpa keterangan',
        ];

        foreach ($days as $index => $dayData) {
            $date = Carbon::now()->subDays($dayData['subDays'])->format('Y-m-d');
            foreach ($createdStudents as $student) {
                // Vary status deterministically or semi-randomly
                $status = $statuses[($student->id + $index) % count($statuses)];
                Absen::create([
                    'user_id' => $student->id,
                    'tanggal' => $date,
                    'hari' => $dayData['hari'],
                    'kelas' => $student->kelas,
                    'status' => $status,
                    'keterangan' => $keteranganMap[$status],
                ]);
            }
        }
    }
}
