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
        'status', 'void_reason', 'void_date', 'void_by',
        'source_type', 'source_id', 'source_invoice_id', 'created_by',
    ];

    // source_type constants
    const SOURCE_DIRECT         = 'manual';
    const SOURCE_MONTHLY_BILL   = 'monthly_bill';
    const SOURCE_BANDWIDTH_SALE = 'bandwidth_sale';
    const SOURCE_PRODUCT_SALE   = 'product_sale';
    const SOURCE_PRODUCT_RETURN = 'product_return';
    const SOURCE_CONNECTION_FEE = 'connection_fee';

    protected $casts = [
        'amount'      => 'decimal:2',
        'income_date' => 'date',
        'void_date'   => 'datetime',
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

    public function isDirectSource(): bool
    {
        return in_array($this->source_type, [null, '', self::SOURCE_DIRECT]);
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            self::SOURCE_MONTHLY_BILL   => 'Monthly Bill',
            self::SOURCE_BANDWIDTH_SALE => 'Bandwidth Sale',
            self::SOURCE_PRODUCT_SALE   => 'Product Sale',
            self::SOURCE_PRODUCT_RETURN => 'Product Return',
            self::SOURCE_CONNECTION_FEE => 'Connection Fee',
            default                     => 'Manual',
        };
    }

    public function getSourceUrlAttribute(): ?string
    {
        if ($this->isDirectSource() || ! $this->source_id) {
            return null;
        }

        return match ($this->source_type) {
            self::SOURCE_MONTHLY_BILL   => route('invoices.show', $this->source_invoice_id ?? $this->source_id),
            self::SOURCE_BANDWIDTH_SALE => route('bandwidth-sale.invoices.show',
                $this->source_invoice_id ?? $this->source_id
            ),
            self::SOURCE_PRODUCT_SALE   => route('inventory.sales.show', $this->source_invoice_id ?? $this->source_id),
            self::SOURCE_PRODUCT_RETURN => route('inventory.sale-returns.show', $this->source_id),
            self::SOURCE_CONNECTION_FEE => route('connection-fee.show', $this->source_id),
            default                     => null,
        };
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
