<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacResellerFunding extends Model
{
    use SoftDeletes;

    protected $table = 'mac_reseller_fundings';

    protected $fillable = [
        'invoice_number',
        'reseller_id',
        'fund_amount',
        'payment',
        'processing_fee',
        'vat',
        'apply_vat',
        'discount',
        'due_amount',
        'funding_date',
        'fund_given_by',
        'received_date',
        'received_by',
        'remarks',
        'transaction_status',
        'restrict_online',
    ];

    protected $casts = [
        'apply_vat'      => 'boolean',
        'restrict_online'=> 'boolean',
        'funding_date'   => 'date',
        'received_date'  => 'date',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(MacReseller::class, 'reseller_id');
    }

    public function fundGivenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fund_given_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = now()->format('ymd');
        $last   = static::where('invoice_number', 'like', $prefix . '%')->count();
        return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT) . 'INV';
    }
}
