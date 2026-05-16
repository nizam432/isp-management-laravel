<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'unit', 'stock_quantity', 'min_stock', 'unit_price',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'min_stock'      => 'integer',
        'unit_price'     => 'decimal:2',
    ];

    // Relations
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    // Accessor
    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity <= $this->min_stock;
    }
}