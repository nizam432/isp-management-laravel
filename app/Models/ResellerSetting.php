<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ResellerSetting extends Model
{
    protected $fillable = ['mac_reseller_id', 'key', 'value', 'group'];

    /** Get a single setting value for a reseller. */
    public static function get(int $macResellerId, string $key, $default = null)
    {
        $cacheKey = "reseller_setting_{$macResellerId}_{$key}";

        return Cache::rememberForever($cacheKey, function () use ($macResellerId, $key, $default) {
            $row = static::where('mac_reseller_id', $macResellerId)->where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    /** Get all settings in a group for a reseller, as a [key => value] array. */
    public static function getGroup(int $macResellerId, string $group): array
    {
        return static::where('mac_reseller_id', $macResellerId)
            ->where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /** Set (create or update) a single setting for a reseller. */
    public static function set(int $macResellerId, string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['mac_reseller_id' => $macResellerId, 'key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("reseller_setting_{$macResellerId}_{$key}");
    }
}
