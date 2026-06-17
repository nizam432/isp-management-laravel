<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacResellerTariffPackage extends Model
{
    protected $table = 'mac_reseller_tariff_packages';

    protected $fillable = [
        'tariff_id',
        'package_id',
        'server_name',
        'protocol',
        'profile',
        'rate',
        'validity_days',
        'min_activation_days',
    ];

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(MacResellerTariff::class, 'tariff_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MacResellerPackage::class, 'package_id');
    }
}
