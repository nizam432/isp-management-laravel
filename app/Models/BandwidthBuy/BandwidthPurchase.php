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

    // ── Relations ─────────────────────────────────────────────────
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

    public function payments()
    {
        return $this->hasMany(BandwidthPurchasePayment::class, 'purchase_id');
    }

    // ── Accessors ─────────────────────────────────────────────────
    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document ? asset('storage/' . $this->document) : null;
    }

    // ── Status Helpers ────────────────────────────────────────────
    public function isPaid(): bool
    {
        return (float) $this->due <= 0 && (float) $this->paid > 0;
    }

    public function isPartial(): bool
    {
        return (float) $this->paid > 0 && (float) $this->due > 0;
    }

    public function isDue(): bool
    {
        return (float) $this->paid <= 0;
    }

    public function isEditable(): bool
    {
        return $this->isDue();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isPaid())    return 'Paid';
        if ($this->isPartial()) return 'Partial';
        return 'Due';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->isPaid())    return '<span class="badge badge-success">Paid</span>';
        if ($this->isPartial()) return '<span class="badge badge-warning">Partial</span>';
        return '<span class="badge badge-danger">Due</span>';
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function recalculateDue(): void
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->paid = $totalPaid;
        $this->due  = max(0, (float) $this->sub_total - $totalPaid);
        $this->save();
    }
}

