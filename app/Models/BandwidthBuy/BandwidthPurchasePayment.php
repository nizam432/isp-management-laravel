<?php

namespace App\Models\BandwidthBuy;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Expense;

class BandwidthPurchasePayment extends Model
{
    protected $table    = 'bandwidth_purchase_payments';
    protected $fillable = [
        'purchase_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_no',
        'remarks',
        'expense_id',
        'status',
        'void_reason',
        'void_date',
        'void_by',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
        'void_date'    => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────
    public function purchase()
    {
        return $this->belongsTo(BandwidthPurchase::class, 'purchase_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}
