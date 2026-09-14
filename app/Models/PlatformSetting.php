<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    /**
     * The single entry point for every "configurable" business rule in the spec
     * (exam passing score, cohort defaults, certificate requirements, ...).
     * Never hard-code these values in controllers/actions — always read them here.
     */
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $cacheKey = "platform_settings.{$group}.{$key}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($group, $key, $default) {
            $setting = static::query()->where('group', $group)->where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        static::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'type' => $type]
        );

        Cache::forget("platform_settings.{$group}.{$key}");
    }
}
