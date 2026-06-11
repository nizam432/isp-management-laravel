<?php

namespace App\Models\BandwidthBuy;

use Illuminate\Database\Eloquent\Model;

class BandwidthPurchaseLine extends Model
{
    protected $table    = 'bandwidth_purchase_lines';
    protected $fillable = [
        'purchase_id',
        'service_id',
        'from_date',
        'to_date',
        'quantity_mb',
        'rate',
        'vat_percent',
        'line_total',
    ];

    protected $casts = [
        'from_date'   => 'date',
        'to_date'     => 'date',
        'quantity_mb' => 'decimal:2',
        'rate'        => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'line_total'  => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(BandwidthPurchase::class, 'purchase_id');
    }

    public function service()
    {
        return $this->belongsTo(BandwidthService::class, 'service_id');
    }

    public static function computeTotal(float $qty, float $rate, float $vat): float
    {
        $base = $qty * $rate;
        return round($base + ($base * $vat / 100), 2);
    }
}
