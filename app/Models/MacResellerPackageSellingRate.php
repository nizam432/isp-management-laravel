<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacResellerPackageSellingRate extends Model
{
    protected $fillable = [
        'mac_reseller_id',
        'mac_reseller_tariff_package_id',
        'selling_rate',
    ];

    protected $casts = [
        'selling_rate' => 'decimal:2',
    ];

    public function reseller()
    {
        return $this->belongsTo(MacReseller::class, 'mac_reseller_id');
    }

    public function tariffPackage()
    {
        return $this->belongsTo(MacResellerTariffPackage::class, 'mac_reseller_tariff_package_id');
    }
}
