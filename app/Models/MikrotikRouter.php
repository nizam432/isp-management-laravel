<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MikrotikRouter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'ip_address', 'api_port', 'username',
        'password', 'area', 'is_active', 'last_seen',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
        'api_port'  => 'integer',
    ];

    // Relations
    public function ipPools()
    {
        return $this->hasMany(IpPool::class, 'router_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
