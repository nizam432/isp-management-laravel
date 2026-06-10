<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltUser extends Model
{
    protected $fillable = [
        'olt_id', 'customer_id', 'mac_address', 'onu_mac_address',
        'ip_address', 'olt_port', 'optical_power', 'onu_status',
        'description', 'distance', 'last_deregister_time',
        'deregister_reason', 'last_synced_at', 'previous_snapshot',
    ];

    protected $casts = [
        'previous_snapshot'    => 'array',
        'last_deregister_time' => 'datetime',
        'last_synced_at'       => 'datetime',
        'optical_power'        => 'float',
    ];

    // ── Relationships ─────────────────────────────
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // ── Scopes ────────────────────────────────────
    public function scopeOnline($query)
    {
        return $query->where('onu_status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('onu_status', 'offline');
    }

    /**
     * dBm 24+ মানে optical_power > -24 (weak signal)
     */
    public function scopeWeakSignal($query)
    {
        return $query->whereNotNull('optical_power')
                     ->where('optical_power', '>', -24);
    }

    // ── Accessors ─────────────────────────────────
    public function getSignalStatusAttribute(): string
    {
        if (is_null($this->optical_power)) return 'unknown';
        if ($this->optical_power >= -20)   return 'excellent';
        if ($this->optical_power >= -24)   return 'good';
        if ($this->optical_power >= -27)   return 'weak';
        return 'very_weak';
    }

    public function getSignalBadgeAttribute(): string
    {
        return match ($this->signal_status) {
            'excellent' => '<span class="badge badge-success">Excellent</span>',
            'good'      => '<span class="badge badge-info">Good</span>',
            'weak'      => '<span class="badge badge-warning">Weak</span>',
            'very_weak' => '<span class="badge badge-danger">Very Weak</span>',
            default     => '<span class="badge badge-secondary">-</span>',
        };
    }
}
