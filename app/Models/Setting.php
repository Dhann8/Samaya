<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as key-value array.
     */
    public static function getAll(): array
    {
        $defaults = [
            'jam_masuk_mulai' => '00:00',
            'jam_masuk_akhir' => '12:00',
            'jam_batas_terlambat' => '07:30',
            'jam_batas_alfa' => '08:00',
            'jam_pulang_mulai' => '12:00',
            'jam_pulang_akhir' => '00:00',
            'qr_refresh_mode' => 'daily', // daily, 5_min, 15_min, 30_min
            'qr_refresh_interval' => '1',
            'sekolah_nama' => 'SaMaya School',
        ];

        $settings = static::pluck('value', 'key')->toArray();

        return array_merge($defaults, $settings);
    }
}
