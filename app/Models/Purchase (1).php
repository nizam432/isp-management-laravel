<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_purchases';

    protected $fillable = [
        'purchase_no',
        'vendor_id',
        'location_id',
        'purchase_date',
        'invoice_no',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'due_amount',
        'refund_due',
        'payment_status', // enum: unpaid, partial, paid
        'status',         // enum: draft, received, cancelled
        'note',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'due_amount'    => 'decimal:2',
        'refund_due'    => 'decimal:2',
    ];

    // ── Boot ──────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->purchase_no)) {
                $model->purchase_no = self::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────

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
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id');
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class, 'purchase_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeWithDue($query)
    {
        return $query->where('due_amount', '>', 0);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Payment হয়ে গেলে অথবা কোনো Return হলে purchase lock হয়ে যাবে।
     */
    public function isLocked(): bool
    {
        return (float) $this->paid_amount > 0
            || $this->returns()->where('status', '!=', 'cancelled')->exists();
    }

    public function isEditable(): bool
    {
        return ! $this->isLocked() && ! $this->isCancelled();
    }

    public function canDelete(): bool
    {
        return ! $this->isLocked() && ! $this->isCancelled();
    }

    public function canCancel(): bool
    {
        return ! $this->isLocked();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'received'  => '<span class="badge badge-success">Received</span>',
            'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
            default     => '<span class="badge badge-light">' . $this->status . '</span>',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'    => '<span class="badge badge-success">Paid</span>',
            'partial' => '<span class="badge badge-warning">Partial</span>',
            'unpaid'  => '<span class="badge badge-danger">Unpaid</span>',
            default   => '<span class="badge badge-light">' . $this->payment_status . '</span>',
        };
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $last = self::withTrashed()
            ->where('purchase_no', 'like', 'PUR-' . $year . '-%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING_INDEX(purchase_no, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $seq = $last ? (intval(substr($last->purchase_no, -4)) + 1) : 1;

        return 'PUR-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
