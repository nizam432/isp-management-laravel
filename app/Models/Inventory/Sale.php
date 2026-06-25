<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_sales';

    protected $fillable = [
        'sale_no',
        'invoice_no',
        'client_id',      // FK → existing customers table
        'walk_in_name',   // client না হলে
        'location_id',
        'sale_date',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status', // enum: unpaid, partial, paid
        'sale_type',      // enum: cash, credit
        'status',         // enum: draft, confirmed, cancelled
        'note',
        'created_by',
    ];

    protected $casts = [
        'sale_date'    => 'date',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
    ];

    // ── Boot ──────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->sale_no)) {
                $model->sale_no = self::generateSaleNumber();
            }
            if (empty($model->invoice_no)) {
                $model->invoice_no = self::generateInvoiceNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────

    /**
     * Existing Customer model এ connect
     */
    public function client()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'client_id');
    }

    public function location()
    {
        return $this->belongsTo(StoreLocation::class, 'location_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class, 'sale_id');
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class, 'sale_id');
    }

    public function clientLedger()
    {
        return $this->hasMany(ClientLedger::class, 'reference_id')
                    ->where('type', 'sale');
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
        return $query->where('status', 'confirmed');
    }

    public function scopeWithDue($query)
    {
        return $query->where('due_amount', '>', 0);
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

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Confirmed sale cancel করা যাবে না — return করতে হবে
    public function canCancel(): bool
    {
        return $this->isDraft();
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->client?->name ?? $this->walk_in_name ?? 'Walk-in Customer';
    }

    public static function generateSaleNumber(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('sale_no', 'like', 'SAL-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(sale_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->sale_no, -4)) + 1) : 1;

        return 'SAL-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('invoice_no', 'like', 'INV-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(invoice_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->invoice_no, -4)) + 1) : 1;

        return 'INV-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
