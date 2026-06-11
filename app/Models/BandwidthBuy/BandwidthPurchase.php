<?php

namespace App\Models\BandwidthBuy;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BandwidthPurchase extends Model
{
    protected $table    = 'bandwidth_purchases';
    protected $fillable = [
        'invoice_no',
        'provider_id',
        'billing_date',
        'document',
        'sub_total',
        'paid',
        'due',
        'bank_account',
        'created_by',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'sub_total'    => 'decimal:2',
        'paid'         => 'decimal:2',
        'due'          => 'decimal:2',
    ];

    public function provider()
    {
        return $this->belongsTo(BandwidthProvider::class, 'provider_id');
    }

    public function lines()
    {
        return $this->hasMany(BandwidthPurchaseLine::class, 'purchase_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document ? asset('storage/' . $this->document) : null;
    }

    public function recalculateDue(): void
    {
        $this->due = max(0, (float) $this->sub_total - (float) $this->paid);
        $this->save();
    }
}
