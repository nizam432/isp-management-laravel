<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacResellerFundPayment extends Model
{
    protected $fillable = [
        'funding_id', 'amount', 'payment_method', 'received_by',
        'received_date', 'remarks', 'status', 'void_reason',
        'void_by', 'void_date', 'income_id',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'received_date' => 'date',
        'void_date'     => 'datetime',
    ];

    public function funding()
    {
        return $this->belongsTo(MacResellerFunding::class, 'funding_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function voidBy()
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function income()
    {
        return $this->belongsTo(Income::class, 'income_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVoid($query)
    {
        return $query->where('status', 'void');
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }
}
