<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IpPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id', 'pool_name', 'start_ip', 'end_ip', 'total_ip', 'used_ip',
    ];

    protected $casts = [
        'total_ip' => 'integer',
        'used_ip'  => 'integer',
    ];

    // Relations
    public function router()
    {
        return $this->belongsTo(MikrotikRouter::class, 'router_id');
    }

    // Accessor
    public function getAvailableIpAttribute()
    {
        return $this->total_ip - $this->used_ip;
    }

    public function getUsagePercentAttribute()
    {
        if ($this->total_ip == 0) return 0;
        return round(($this->used_ip / $this->total_ip) * 100, 1);
    }
}
