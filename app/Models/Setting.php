<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * Cache memory static untuk optimasi performa bebas overhead query berulang.
     */
    protected static array $runtimeCache = [];

    /**
     * Ambil nilai setting berdasarkan key (dengan optimasi cache).
     */
    public static function get(string $key, $default = null)
    {
        if (isset(self::$runtimeCache[$key])) {
            return self::$runtimeCache[$key];
        }

        $value = Cache::remember('setting_' . $key, 3600, function () use ($key, $default) {
            $setting = self::where('setting_key', $key)->first();
            return $setting ? $setting->setting_value : $default;
        });

        self::$runtimeCache[$key] = $value ?? $default;
        return self::$runtimeCache[$key];
    }

    /**
     * Simpan atau perbarui nilai setting (otomatis menghapus cache).
     */
    public static function set(string $key, $value): self
    {
        $setting = self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => (string)$value]
        );

        Cache::forget('setting_' . $key);
        self::$runtimeCache[$key] = (string)$value;

        return $setting;
    }

    /**
     * Ambil seluruh setting sebagai key-value array.
     */
    public static function getAll(): array
    {
        return self::pluck('setting_value', 'setting_key')->toArray();
    }
}

// Global Helper Function for clean & effortless access across views, controllers & models
if (!function_exists('setting')) {
    function setting($key = null, $default = null) {
        if ($key === null) {
            return \App\Models\Setting::getAll();
        }
        return \App\Models\Setting::get($key, $default);
    }
}
