<?php

namespace App\Models\BandwidthBuy;

use Illuminate\Database\Eloquent\Model;

class BandwidthService extends Model
{
    protected $table    = 'bandwidth_services';
    protected $fillable = ['name', 'description', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function lines()
    {
        return $this->hasMany(BandwidthPurchaseLine::class, 'service_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
