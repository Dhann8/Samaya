<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['nis' => '00001'],
            [
                'name' => 'Administrator',
                'email' => 'admin@admin.com',
                'kelas' => 'Staff',
                'angkatan' => '2024',
                'jurusan' => 'Kurikulum',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}

