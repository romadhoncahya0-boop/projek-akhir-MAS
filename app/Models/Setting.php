<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan sekolah yang dapat diubah admin (kontak, jam layanan, SLA, pengumuman).
 * Nilai bawaan dipakai bila belum pernah diubah.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'school_name' => 'MAS',
        'school_address' => 'Loket Tata Usaha, Gedung Utama',
        'contact_email' => 'tu@sekolah.sch.id',
        'contact_phone' => '',
        'service_days' => 'Senin–Jumat',
        'service_hours' => '08.00–15.30 WIB',
        'sla_days' => '3',
        'announcement' => '',
    ];

    public static function get(string $key): ?string
    {
        $all = app('mas.settings');

        return $all[$key] ?? self::DEFAULTS[$key] ?? null;
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                static::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }
        app()->forgetInstance('mas.settings');
    }
}
