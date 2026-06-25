<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_purchase_items';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_item_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * কতটুকু return করা হয়েছে
     */
    public function getReturnedQuantityAttribute(): float
    {
        return (float) $this->returnItems()
            ->whereHas('purchaseReturn', fn($q) => $q->where('status', 'approved'))
            ->sum('quantity');
    }

    /**
     * কতটুকু return করা যাবে
     */
    public function getReturnableQuantityAttribute(): float
    {
        return (float) $this->quantity - $this->returned_quantity;
    }
}
