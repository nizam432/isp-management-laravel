<?php
// ═══════════════════════════════════════════════════
// app/Models/BwsInvoice.php
// ═══════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BwsInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bws_invoices';

    protected $fillable = [
        'invoice_no', 'bws_customer_id', 'billing_month', 'payment_due',
        'daily_basis', 'total_amount', 'vat_amount', 'discount',
        'grand_total', 'received_amount', 'due_amount', 'status',
        'notes', 'is_recurring', 'repeat_date',
        'recurring_start', 'recurring_end', 'created_by',
    ];

    protected $casts = [
        'daily_basis'     => 'boolean',
        'is_recurring'    => 'boolean',
        'payment_due'     => 'date',
        'recurring_start' => 'date',
        'recurring_end'   => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────────
    public function bwsCustomer()
    {
        return $this->belongsTo(BandwidthSaleCustomer::class, 'bws_customer_id');
    }

    public function items()
    {
        return $this->hasMany(BwsInvoiceItem::class, 'bws_invoice_id')
                    ->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->hasMany(BwsInvoicePayment::class, 'bws_invoice_id');
    }

    public function activePayments()
    {
        return $this->payments()->where('status', 'active');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Auto-number ───────────────────────────────────────────────
    public static function generateNumber(): string
    {
        $prefix = 'BWS';
        $year   = date('Y');
        $last   = self::withTrashed()
            ->where('invoice_no', 'like', $prefix . '-' . $year . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(invoice_no, "-", -1) AS UNSIGNED) DESC')
            ->first();
        $seq = $last ? (intval(substr($last->invoice_no, -4)) + 1) : 1;
        return $prefix . '-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isPaid(): bool   { return $this->status === 'paid'; }
    public function isUnpaid(): bool { return in_array($this->status, ['unpaid', 'overdue']); }

    /**
     * Recalculate received_amount & due_amount from active payments.
     * Call this after any payment insert/void.
     */
    public function recalcDue(): void
    {
        $received = $this->activePayments()->sum('received_amount');
        $discount = $this->activePayments()->sum('discount');
        $due      = max(0, $this->grand_total - $received - $discount);

        $status = match(true) {
            $due <= 0              => 'paid',
            $received > 0         => 'partial',
            now() > $this->payment_due => 'overdue',
            default                => 'unpaid',
        };

        $this->update([
            'received_amount' => $received,
            'due_amount'      => $due,
            'status'          => $status,
        ]);
    }
}
