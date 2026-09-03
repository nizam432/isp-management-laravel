<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MacResellerZone extends Model
{
    use HasFactory;

    protected $fillable = ['mac_reseller_id', 'name', 'details', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────
    public function reseller()
    {
        return $this->belongsTo(MacReseller::class, 'mac_reseller_id');
    }

    public function subZones()
    {
        return $this->hasMany(MacResellerSubZone::class, 'mac_reseller_zone_id');
    }

    public function activeSubZones()
    {
        return $this->hasMany(MacResellerSubZone::class, 'mac_reseller_zone_id')->where('is_active', true);
    }

    // ── Scopes ────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForReseller($query, $macResellerId)
    {
        return $query->where('mac_reseller_id', $macResellerId);
    }
}
