<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'hari',
        'kelas',
        'status',
        'keterangan',
        'latitude',
        'longitude',
        'lokasi',
        'waktu_absen',
        'waktu_pulang',
        'status_persetujuan',
        'bukti',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
