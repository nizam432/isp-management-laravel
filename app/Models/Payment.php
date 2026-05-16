<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'customer_id', 'amount', 'method',
        'transaction_id', 'paid_at', 'received_by', 'remarks',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    // Relations
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function commission()
    {
        return $this->hasOne(AgentCommission::class);
    }

    // Scopes
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('paid_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('paid_at', now()->month)
                     ->whereYear('paid_at', now()->year);
    }
}
