<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InternalConsumptionItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_consumption_items';

    protected $fillable = [
        'consumption_id',
        'product_id',
        'unit',
        'quantity',
        'unit_price',  // manually enter, auto fill from last purchase price
        'total_price',
        'note',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function consumption()
    {
        return $this->belongsTo(InternalConsumption::class, 'consumption_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
