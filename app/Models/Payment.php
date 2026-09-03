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
        'reseller_employee_id', // ── who at the RESELLER'S own staff collected this (used only for reseller-created payments) ──
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'send_sms'               => 'boolean',
        'set_next_billing_date'  => 'boolean',
        'payment_date'           => 'date',
        'paid_at'                => 'datetime',
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
        // Was: belongsTo(User::class, 'received_by') — but the "Received By"
        // dropdown (PaymentController::collectPage()) is populated from
        // Employee (staff members who physically receive cash, not all of
        // whom have a system login/User account). Fixed to match.
        return $this->belongsTo(\App\Models\HR\Employee::class, 'received_by');
    }

    // ── Used only when this payment was collected via the Reseller portal ──
    public function receivedByReseller()
    {
        return $this->belongsTo(\App\Models\ResellerEmployee::class, 'reseller_employee_id');
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