<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayTransaction extends Model
{
    protected $table    = 'payment_gateway_transactions';
    protected $fillable = [
        'txn_ref', 'tenant_id', 'customer_id', 'invoice_id', 'gateway',
        'amount', 'currency', 'gateway_txn_id', 'status',
        'gateway_response', 'payer_ip', 'paid_at',
    ];
    protected $casts = [
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
        'amount'           => 'float',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function invoice()  { return $this->belongsTo(Invoice::class);  }

    public function isSuccess(): bool { return $this->status === 'success'; }
    public function isPending(): bool { return $this->status === 'pending'; }

    public static function generateRef(): string
    {
        $date = now()->format('Ymd');
        $seq  = static::whereDate('created_at', today())->count() + 1;
        return 'PGT-' . $date . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
