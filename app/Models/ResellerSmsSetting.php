<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerSmsSetting extends Model
{
    protected $fillable = ['mac_reseller_id', 'gateway_slug', 'config', 'is_active'];

    protected $casts = [
        'config'    => 'array',
        'is_active' => 'boolean',
    ];
}
