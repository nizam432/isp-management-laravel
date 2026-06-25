<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorLedger extends Model
{
    use HasFactory;

    protected $table = 'inventory_vendor_ledger';

    protected $fillable = [
        'vendor_id',
        'date',
        'type',           // enum: purchase, payment, return, adjustment
        'reference_id',
        'debit',          // আমরা payment দিলাম (বাকি কমলো)
        'credit',         // purchase করলাম (বাকি বাড়লো)
        'balance',        // running balance
        'note',
        'created_by',
    ];

    protected $casts = [
        'date'    => 'date',
        'debit'   => 'decimal:2',
        'credit'  => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
