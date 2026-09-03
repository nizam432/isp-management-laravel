<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabasePool extends Model
{
    protected $table = 'database_pool';

    protected $fillable = [
        'database_name',
        'is_used',
        'tenant_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function scopeAvailable($query)
    {
        return $query->where('is_used', false);
    }
}
