<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransaction extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_transactions';

    protected $fillable = [
        'product_id',
        'location_id',
        'from_location_id', // transfer এর জন্য
        'to_location_id',   // transfer এর জন্য
        'type',             // enum: in, out
        'reason',           // enum: purchase, sale, consumption, transfer, return, damage, adjustment
        'reference_type',   // যেমন: purchase, sale, consumption
        'reference_id',     // purchase_id / sale_id etc
        'quantity',
        'note',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function location()
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'to_location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }
}
