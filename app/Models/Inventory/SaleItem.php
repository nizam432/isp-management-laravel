<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'purchase_price', // cost calculate এর জন্য
        'total_price',
        'profit',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'unit_price'     => 'decimal:2',
        'discount'       => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'total_price'    => 'decimal:2',
        'profit'         => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class, 'sale_item_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function getReturnedQuantityAttribute(): float
    {
        return (float) $this->returnItems()
            ->whereHas('saleReturn', fn($q) => $q->where('status', 'approved'))
            ->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): float
    {
        return (float) $this->quantity - $this->returned_quantity;
    }
}
