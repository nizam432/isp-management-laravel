<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Olt extends Model
{
    protected $fillable = [
        'ip_address', 'community', 'olt_type_id',
        'web_ip', 'web_username', 'web_password',
        'is_active', 'last_synced_at', 'created_by',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────
    public function oltType(): BelongsTo
    {
        return $this->belongsTo(OltType::class);
    }

    public function oltUsers(): HasMany
    {
        return $this->hasMany(OltUser::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
