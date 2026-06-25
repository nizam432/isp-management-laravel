<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_purchase_returns';

    protected $fillable = [
        'return_no',
        'purchase_id',
        'vendor_id',
        'location_id',
        'return_date',
        'total_amount',
        'reason',
        'status',      // enum: draft, approved, cancelled
        'note',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'return_date'  => 'date',
        'total_amount' => 'decimal:2',
    ];

    // ── Boot ──────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->return_no)) {
                $model->return_no = self::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function location()
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'return_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('return_no', 'like', 'PRR-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(return_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->return_no, -4)) + 1) : 1;

        return 'PRR-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
