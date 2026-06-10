<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'income_no', 'category_id', 'amount', 'income_date',
        'payment_method', 'transaction_id', 'customer_id',
        'payer', 'reference_no', 'description', 'receipt_path',
        'status', 'void_reason', 'created_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'income_date' => 'date',
    ];

    // ── Boot ──────────────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->income_no)) {
                $model->income_no = self::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────
    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVoid($query)
    {
        return $query->where('status', 'void');
    }

    public function scopeByMonth($query, string $month)
    {
        [$year, $mon] = explode('-', $month);
        return $query->whereYear('income_date', $year)
                     ->whereMonth('income_date', $mon);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('income_date', now()->month)
                     ->whereYear('income_date', now()->year);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('income_date', today());
    }

    public function scopeByDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('income_date', [$from, $to]);
    }

    // ── Accessors ─────────────────────────────────────────────────
    public function getFormattedAmountAttribute(): string
    {
        return 'BDT ' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => '<span class="badge badge-success">Active</span>',
            'void'   => '<span class="badge badge-secondary">Void</span>',
            default  => '<span class="badge badge-light">' . $this->status . '</span>',
        };
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path
            ? asset('storage/' . $this->receipt_path)
            : null;
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ── Static Helpers ────────────────────────────────────────────

    /**
     * Generate next income number: INC-2026-0001
     */
    public static function generateNumber(): string
    {
        $prefix = 'INC';
        $year   = date('Y');

        $last = self::withTrashed()
            ->where('income_no', 'like', $prefix . '-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(income_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->income_no, -4)) + 1) : 1;

        return $prefix . '-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Total manual income for a given month (Y-m).
     * Used in P&L report alongside Monthly Bill (payments).
     */
    public static function totalForMonth(string $month): float
    {
        [$year, $mon] = explode('-', $month);
        return (float) self::active()
            ->whereYear('income_date', $year)
            ->whereMonth('income_date', $mon)
            ->sum('amount');
    }

    /**
     * Grouped totals by category for a given month.
     * Returns collection: [['category' => ..., 'total' => ..., 'count' => ...], ...]
     */
    public static function breakdownForMonth(string $month): \Illuminate\Support\Collection
    {
        [$year, $mon] = explode('-', $month);

        return self::active()
            ->with('category')
            ->whereYear('income_date', $year)
            ->whereMonth('income_date', $mon)
            ->get()
            ->groupBy('category_id')
            ->map(function ($items) {
                return [
                    'category' => $items->first()->category,
                    'total'    => $items->sum('amount'),
                    'count'    => $items->count(),
                ];
            })
            ->values();
    }
}
