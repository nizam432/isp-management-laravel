<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'type', 'quantity', 'reference', 'customer_id', 'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relations
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Auto update stock after save
    protected static function booted()
    {
        static::created(function ($transaction) {
            $item = InventoryItem::find($transaction->item_id);
            if ($transaction->type === 'in') {
                $item->increment('stock_quantity', $transaction->quantity);
            } else {
                $item->decrement('stock_quantity', $transaction->quantity);
            }
        });
    }
}
