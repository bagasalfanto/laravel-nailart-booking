<?php

namespace App\Support;

use App\Models\WebSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Reader untuk setting system-level (dari tabel web_settings).
 * Pakai cache supaya nggak hit DB tiap request.
 */
class SystemSetting
{
    private const CACHE_PREFIX = 'system_setting:';

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever(
            self::CACHE_PREFIX.$key,
            fn () => WebSetting::query()->where('key', $key)->value('value') ?? $default
        );
    }

    public static function set(string $key, ?string $value): void
    {
        WebSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_PREFIX.$key);
    }

    /**
     * Domain email untuk akun internal (admin/nailist), tanpa "@" di depan.
     */
    public static function emailDomain(): string
    {
        return self::get('email_domain', 'nailart.com') ?: 'nailart.com';
    }
}
