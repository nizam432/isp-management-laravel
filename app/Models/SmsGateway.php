<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $fillable = [
        'name', 'slug', 'is_active', 'config', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config'    => 'array', // JSON column
    ];

    public function logs()
    {
        return $this->hasMany(SmsLog::class, 'gateway', 'slug');
    }
}
