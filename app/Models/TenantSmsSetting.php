<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSmsSetting extends Model
{
    protected $fillable = [
        'tenant_id', 'gateway_slug', 'config', 'is_active',
    ];

    protected $casts = [
        'config'    => 'array',
        'is_active' => 'boolean',
    ];

    public function gateway()
    {
        return $this->belongsTo(SmsGateway::class, 'gateway_slug', 'slug');
    }
}
