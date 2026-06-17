<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacResellerTariff extends Model
{
    use SoftDeletes;

    protected $table = 'mac_reseller_tariffs';

    protected $fillable = [
        'tariff_type',
        'name',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(MacResellerTariffPackage::class, 'tariff_id');
    }

    public function resellers(): HasMany
    {
        return $this->hasMany(MacReseller::class, 'tariff_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
