<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalePayment extends Model
{
    use HasFactory;

    protected $table = 'inventory_sale_payments';

    protected $fillable = [
        'sale_id',
        'amount',
        'payment_date',
        'payment_method', // enum: cash, bank, mobile_banking, bkash, nagad
        'reference_no',
        'note',
        'is_void',
        'void_reason',
        'void_by',
        'void_at',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'is_void'      => 'boolean',
        'void_at'      => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function voidBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'void_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_void', false);
    }

    public function scopeVoid($query)
    {
        return $query->where('is_void', true);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isVoid(): bool
    {
        return $this->is_void;
    }
}
