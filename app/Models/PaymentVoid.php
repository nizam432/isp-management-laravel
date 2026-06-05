<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentVoid extends Model
{
    protected $fillable = [
        'payment_id', 'voided_by', 'amount', 'reason', 'voided_at',
    ];

    protected $casts = [
        'voided_at' => 'datetime',
        'amount'    => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
