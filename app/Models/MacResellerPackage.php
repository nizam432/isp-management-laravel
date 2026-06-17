<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacResellerPackage extends Model
{
    use SoftDeletes;

    protected $table = 'mac_reseller_packages';

    protected $fillable = [
        'name',
        'bandwidth_mb',
        'details',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tariffPackages(): HasMany
    {
        return $this->hasMany(MacResellerTariffPackage::class, 'package_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
