<?php

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

    // ── Relations ──────────────────────────────────────────────
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

    // ── Auto-number ────────────────────────────────────────────
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

    // ── Helpers ────────────────────────────────────────────────
    public function isPaid(): bool   { return $this->status === 'paid'; }
    public function isUnpaid(): bool { return in_array($this->status, ['unpaid', 'overdue']); }

    /**
     * Recalculate received_amount & due_amount from active payments.
     *
     * Logic:
     *  - grand_total = invoice total after invoice-level discount (already stored)
     *  - received    = sum of payment received_amount
     *  - pay_discount= sum of payment-level discount (additional off on payment)
     *  - due         = grand_total - received - pay_discount
     *
     * Invoice-level discount is already baked into grand_total.
     * Payment-level discount is an additional discount given at collection time.
     */
    public function recalcDue(): void
    {
        $received   = (float) $this->activePayments()->sum('received_amount');
        $payDiscount= (float) $this->activePayments()->sum('discount');

        // due = what's left after received cash + payment-level discounts
        $due = max(0, $this->grand_total - $received - $payDiscount);

        $status = match(true) {
            $due <= 0                                          => 'paid',
            $received > 0 || $payDiscount > 0                 => 'partial',
            $this->payment_due && now() > $this->payment_due  => 'overdue',
            default                                            => 'unpaid',
        };

        $this->update([
            'received_amount' => $received,
            'due_amount'      => $due,
            'status'          => $status,
        ]);
    }
}
