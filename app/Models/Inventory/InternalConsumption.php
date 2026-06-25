<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalConsumption extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_consumptions';

    protected $fillable = [
        'consumption_no',
        'consumption_date',
        'location_id',
        'purpose',          // যেকোনো text — Installation, Maintenance etc
        'reference_note',   // যেমন: Mirpur Zone, Block-A
        'total_amount',
        'status',           // enum: draft, confirmed, cancelled
        'is_void',
        'void_reason',
        'void_by',
        'void_at',
        'note',
        'created_by',
    ];

    protected $casts = [
        'consumption_date' => 'date',
        'total_amount'     => 'decimal:2',
        'is_void'          => 'boolean',
        'void_at'          => 'datetime',
    ];

    // ── Boot ──────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->consumption_no)) {
                $model->consumption_no = self::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────

    public function location()
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    public function items()
    {
        return $this->hasMany(InternalConsumptionItem::class, 'consumption_id');
    }

    public function voidBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'void_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed')->where('is_void', false);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed' && !$this->is_void;
    }

    public function isVoid(): bool
    {
        return $this->is_void;
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('consumption_no', 'like', 'CON-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(consumption_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->consumption_no, -4)) + 1) : 1;

        return 'CON-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
