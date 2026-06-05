<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'customer_id','paid_at', 'amount', 'method',
        'transaction_id', 'remarks', 'status', 'received_by',
        'receive_from', 'send_sms', 'set_next_billing_date', 'payment_date',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'send_sms'               => 'boolean',
        'set_next_billing_date'  => 'boolean',
        'payment_date'           => 'date',
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

    public function voidLog()
    {
        return $this->hasOne(PaymentVoid::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVoided($query)
    {
        return $query->where('status', 'void');
    }
    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date', now()->year);
    }

    // Helpers
    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
