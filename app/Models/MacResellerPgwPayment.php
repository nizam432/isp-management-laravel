<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacResellerPgwPayment extends Model
{
    protected $table = 'mac_reseller_pgw_payments';

    protected $fillable = [
        'reseller_id',
        'client_code',
        'client_ip',
        'client_name',
        'package',
        'billing_status',
        'trx_id',
        'monthly_bill',
        'received',
        'money_receipt_no',
        'payment_gateway',
        'gateway_type',
        'transaction_status',
        'created_by',
        'received_by',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(MacReseller::class, 'reseller_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
