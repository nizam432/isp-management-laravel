<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_vendors';

    protected $fillable = [
        'vendor_no',
        'name',
        'owner_name',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'area',
        'district',
        'vendor_type',    // enum: supplier, manufacturer, both
        'business_type',
        'trade_license',
        'tin_no',
        'bin_no',
        'bank_name',
        'bank_account',
        'bank_branch',
        'opening_balance',
        'credit_limit',
        'status',         // enum: active, inactive, blacklisted
        'note',
        'created_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit'    => 'decimal:2',
    ];

    // ── Boot ──────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->vendor_no)) {
                $model->vendor_no = self::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────

    public function contacts()
    {
        return $this->hasMany(VendorContact::class, 'vendor_id');
    }

    public function primaryContact()
    {
        return $this->hasOne(VendorContact::class, 'vendor_id')->where('is_primary', true);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class, 'vendor_id');
    }

    public function ledger()
    {
        return $this->hasMany(VendorLedger::class, 'vendor_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'vendor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithDue($query)
    {
        return $query->whereHas('ledger', function ($q) {
            $q->havingRaw('SUM(credit - debit) > 0');
        });
    }

    // ── Accessors ─────────────────────────────────────────────────

    /**
     * মোট বাকি (vendor এর কাছে আমাদের দেনা)
     */
    public function getTotalDueAttribute(): float
    {
        $credit = $this->ledger()->sum('credit');
        $debit  = $this->ledger()->sum('debit');
        return (float) ($credit - $debit);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = self::withTrashed()
            ->orderByRaw('CAST(SUBSTRING_INDEX(vendor_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->vendor_no, strrpos($last->vendor_no, '-') + 1)) + 1) : 1;

        return 'VEN-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
