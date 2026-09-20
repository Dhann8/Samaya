<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nis',
        'name',
        'email',
        'no_hp',
        'foto_profil',
        'kelas',
        'angkatan',
        'jurusan',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];  

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function absens()
    {
        return $this->hasMany(Absen::class);
    }
}
