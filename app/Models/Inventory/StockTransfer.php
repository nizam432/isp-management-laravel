<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_stock_transfers';

    protected $fillable = [
        'transfer_no',
        'from_location_id',
        'to_location_id',
        'transfer_date',
        'status',   // enum: draft, confirmed, cancelled
        'note',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    // ── Boot ──────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->transfer_no)) {
                $model->transfer_no = self::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────

    public function fromLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(StoreLocation::class, 'to_location_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('transfer_no', 'like', 'TRF-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(transfer_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->transfer_no, -4)) + 1) : 1;

        return 'TRF-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
