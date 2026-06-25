<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_products';

    protected $fillable = [
        'category_id',
        'name',
        'model',
        'unit',           // enum: pcs, meter, roll, box
        'meter_per_roll', // শুধু roll type এর জন্য
        'stock_quantity',
        'low_stock_alert',
        'purchase_price', // last purchase price — auto fill এর জন্য
        'sell_price',
        'created_by',
    ];

    protected $casts = [
        'stock_quantity'  => 'decimal:2',
        'purchase_price'  => 'decimal:2',
        'sell_price'      => 'decimal:2',
        'low_stock_alert' => 'integer',
        'meter_per_roll'  => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function locationStocks()
    {
        return $this->hasMany(LocationStock::class, 'product_id');
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'product_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'product_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    public function consumptionItems()
    {
        return $this->hasMany(InternalConsumptionItem::class, 'product_id');
    }

    public function deviceAssignments()
    {
        return $this->hasMany(ClientDeviceAssignment::class, 'product_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_alert');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->low_stock_alert;
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isDeletable(): bool
    {
        return $this->stockTransactions()->count() === 0;
    }

    /**
     * নির্দিষ্ট location এ stock কত আছে
     */
    public function stockAtLocation(int $locationId): float
    {
        $locationStock = $this->locationStocks()
            ->where('location_id', $locationId)
            ->first();

        return $locationStock ? (float) $locationStock->quantity : 0;
    }
}
