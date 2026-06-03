<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'payment_type',
        'installment_amount',
        'total_installments',
        'paid_installments',
        'remaining_amount',
        'advance_date',
        'deduct_month',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'advance_date'       => 'date',
        'amount'             => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'remaining_amount'   => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function isInstallment(): bool
    {
        return $this->payment_type === 'installment';
    }

    public function getNextDeductionAmount(): float
    {
        if ($this->payment_type === 'one_time') {
            return $this->remaining_amount;
        }
        return min($this->installment_amount, $this->remaining_amount);
    }

    public function deduct(float $amount): void
    {
        $this->increment('paid_installments');
        $this->decrement('remaining_amount', $amount);

        if ($this->remaining_amount <= 0) {
            $this->update(['status' => 'deducted']);
        }
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForMonth($query, string $month)
    {
        return $query->where('deduct_month', $month);
    }
}