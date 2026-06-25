<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_store_locations';

    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'phone',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function locationStocks()
    {
        return $this->hasMany(LocationStock::class, 'location_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'location_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'location_id');
    }

    public function consumptions()
    {
        return $this->hasMany(InternalConsumption::class, 'location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
