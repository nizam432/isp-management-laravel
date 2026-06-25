<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocationStock extends Model
{
    use HasFactory;

    protected $table = 'inventory_location_stocks';

    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
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

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Stock বাড়ানো
     */
    public function increment_(float $qty): void
    {
        $this->increment('quantity', $qty);
    }

    /**
     * Stock কমানো
     */
    public function decrement_(float $qty): void
    {
        $this->decrement('quantity', $qty);
    }
}
