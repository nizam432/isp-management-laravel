<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_product_categories';

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isDeletable(): bool
    {
        return $this->products()->count() === 0;
    }
}
