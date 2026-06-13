<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BwsInvoicePayment extends Model
{
    use SoftDeletes;

    protected $table = 'bws_invoice_payments';

    protected $fillable = [
        'payment_no', 'bws_invoice_id', 'bws_customer_id',
        'received_date', 'received_from', 'received_by',
        'payment_method', 'payable_amount', 'received_amount',
        'discount', 'receipt_transaction_no', 'remarks',
        'status', 'void_reason', 'income_id', 'created_by',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    // ── Boot: auto Income sync ────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        // After payment created → create Income
        static::created(function (self $payment) {
            $income = $payment->createIncomeRecord();
            if ($income) {
                $payment->updateQuietly(['income_id' => $income->id]);
            }
            // Recalc invoice due
            $payment->bwsInvoice->recalcDue();
        });

        // After payment updated (e.g. amount changed) → sync Income
        static::updated(function (self $payment) {
            if ($payment->income_id && $payment->wasChanged([
                'received_amount', 'payment_method',
                'received_date', 'discount', 'status',
            ])) {
                $income = Income::find($payment->income_id);
                if ($income) {
                    if ($payment->status === 'void') {
                        $income->update([
                            'status'      => 'void',
                            'void_reason' => $payment->void_reason ?? 'Payment voided',
                        ]);
                    } else {
                        $income->update([
                            'amount'         => $payment->received_amount,
                            'payment_method' => $payment->payment_method,
                            'income_date'    => $payment->received_date,
                            'description'    => $payment->remarks,
                        ]);
                    }
                }
            }
            $payment->bwsInvoice->recalcDue();
        });
    }

    // ── Create corresponding Income record ────────────────────────
    private function createIncomeRecord(): ?Income
    {
        // Find the Bandwidth Sale income category
        $category = IncomeCategory::where('slug', 'bandwidth-sale')->first();
        if (! $category) return null;

        $customer = $this->bwsCustomer;

        return Income::create([
            'income_no'      => Income::generateNumber(),
            'category_id'    => $category->id,
            'amount'         => $this->received_amount,
            'income_date'    => $this->received_date,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->receipt_transaction_no,
            'payer'          => $customer?->customer_name ?? $this->received_from,
            'reference_no'   => $this->payment_no,
            'description'    => "BWS Invoice: {$this->bwsInvoice->invoice_no} | " .
                                "Customer: {$customer?->customer_name}",
            'source_type'    => 'bandwidth_sale',
            'source_id'      => $this->id,
            'status'         => 'active',
            'created_by'     => $this->created_by,
        ]);
    }

    // ── Auto-number ───────────────────────────────────────────────
    public static function generateNumber(): string
    {
        $prefix = 'BWSP';
        $year   = date('Y');
        $last   = self::withTrashed()
            ->where('payment_no', 'like', $prefix . '-' . $year . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(payment_no, "-", -1) AS UNSIGNED) DESC')
            ->first();
        $seq = $last ? (intval(substr($last->payment_no, -4)) + 1) : 1;
        return $prefix . '-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Relations ─────────────────────────────────────────────────
    public function bwsInvoice()
    {
        return $this->belongsTo(BwsInvoice::class, 'bws_invoice_id');
    }

    public function bwsCustomer()
    {
        return $this->belongsTo(BandwidthSaleCustomer::class, 'bws_customer_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function income()
    {
        return $this->belongsTo(Income::class, 'income_id');
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isVoid(): bool   { return $this->status === 'void'; }
    public function isActive(): bool { return $this->status === 'active'; }

    /**
     * Void this payment and cascade to Income.
     */
    public function voidPayment(string $reason): void
    {
        $this->update([
            'status'      => 'void',
            'void_reason' => $reason,
        ]);
        // updated() boot event handles income void & recalcDue
    }
}
