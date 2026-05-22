<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $fillable = [
        'name', 'slug', 'is_active', 'is_enabled', 'config', 'description',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_enabled' => 'boolean',
        'config'     => 'array',
    ];

    public function logs()
    {
        return $this->hasMany(SmsLog::class, 'gateway', 'slug');
    }

    // Super Admin এ enabled gateways
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
