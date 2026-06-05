<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceTransaction extends Model
{
    protected $fillable = [
        'customer_id', 'type', 'amount', 'description',
        'payment_id', 'invoice_id', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
