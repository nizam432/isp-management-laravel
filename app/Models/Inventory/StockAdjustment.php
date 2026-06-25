<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_adjustments';

    protected $fillable = [
        'adjustment_no',
        'product_id',
        'location_id',
        'adjustment_date',
        'type',     // enum: add, subtract
        'quantity',
        'reason',
        'note',
        'created_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'quantity'        => 'decimal:2',
    ];

    // ── Boot ──────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->adjustment_no)) {
                $model->adjustment_no = self::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function location()
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('adjustment_no', 'like', 'ADJ-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(adjustment_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->adjustment_no, -4)) + 1) : 1;

        return 'ADJ-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
