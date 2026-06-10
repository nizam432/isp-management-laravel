<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_no',
        'category_id',
        'amount',
        'expense_date',
        'payment_method',
        'transaction_id',
        'payee',
        'reference_no',
        'description',
        'receipt_path',
        'status',
        'reject_reason',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Boot — auto-generate expense_no on create
    // ──────────────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->expense_no)) {
                $model->expense_no = self::generateNumber();
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVoid($query)
    {
        return $query->where('status', 'void');
    }

    /** Active = approved + pending (counted in P&L) */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'pending']);
    }

    public function scopeByMonth($query, string $month)
    {
        [$year, $mon] = explode('-', $month);
        return $query->whereYear('expense_date', $year)
                     ->whereMonth('expense_date', $mon);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                     ->whereYear('expense_date', now()->year);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeByDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Formatted amount: ৳12,500.00
     */
    public function getFormattedAmountAttribute(): string
    {
        return '৳' . number_format($this->amount, 2);
    }

    /**
     * Status badge HTML — ready to echo in Blade.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge badge-success">Approved</span>',
            'pending'  => '<span class="badge badge-warning">Pending</span>',
            'rejected' => '<span class="badge badge-danger">Rejected</span>',
            'void'     => '<span class="badge badge-secondary">Void</span>',
            default    => '<span class="badge badge-light">' . $this->status . '</span>',
        };
    }

    /**
     * Receipt URL — null if no receipt uploaded.
     */
    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path
            ? asset('storage/' . $this->receipt_path)
            : null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Static helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate next expense number: EXP-2026-0001
     * Locked for concurrent requests — same pattern as Invoice::generateNumber().
     */
    public static function generateNumber(): string
    {
        $prefix = 'EXP';
        $year   = date('Y');

        $last = self::withTrashed()
            ->where('expense_no', 'like', $prefix . '-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(expense_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->expense_no, -4)) + 1) : 1;

        return $prefix . '-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Total expenses for a given month — used in P&L.
     *
     * @param  string $month  format: Y-m  (e.g. "2026-06")
     * @return float
     */
    public static function totalForMonth(string $month): float
    {
        [$year, $mon] = explode('-', $month);

        return (float) self::active()
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $mon)
            ->sum('amount');
    }

    /**
     * Grouped expense totals by category for a given month.
     * Returns: [ ['category_name' => ..., 'total' => ...], ... ]
     */
    public static function breakdownForMonth(string $month): \Illuminate\Support\Collection
    {
        [$year, $mon] = explode('-', $month);

        return self::active()
            ->with('category')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $mon)
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
