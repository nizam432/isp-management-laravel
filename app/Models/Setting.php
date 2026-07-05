<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     *
     * Previously this method didn't accept a $group parameter at all, so calls
     * like Setting::set($key, $value, 'notification') from SettingController
     * silently dropped the group — PHP doesn't error on extra arguments to a
     * non-variadic function, it just ignores them. That meant `group` was never
     * written on updateOrCreate(), so any key that was ever created with the
     * wrong (or no) group stayed that way forever, no matter what group was
     * passed on later saves. This is why toggles like "Invoice Generated"
     * (saved once as group='general') never actually updated when re-saved
     * with group='notification' — the code intent was right, the value did
     * save, but under a group the settings page never reads from.
     */
    public static function set(string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget("setting_{$key}");
    }

    /**
     * Get all settings by group
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
                     ->pluck('value', 'key')
                     ->toArray();
    }
}
