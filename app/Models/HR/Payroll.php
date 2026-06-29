<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use App\Models\Expense;
use App\Models\User;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'basic_salary', 'gross_salary',
        'total_deduction', 'net_salary', 'payment_date',
        'payment_method', 'status', 'note', 'created_by',
        'expense_id', 'paid_amount', 'due_amount',
        'void_reason', 'void_date', 'void_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'void_date'    => 'datetime',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
        'net_salary'   => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(PayrollPayment::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function voidBy()
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isPaid(): bool     { return $this->status === 'paid'; }
    public function isPartial(): bool  { return $this->status === 'partial'; }
    public function isVoid(): bool     { return $this->status === 'void'; }
    public function isEditable(): bool { return in_array($this->status, ['pending', 'partial']); }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid'    => '<span class="badge badge-success">Paid</span>',
            'partial' => '<span class="badge badge-warning">Partial</span>',
            'pending' => '<span class="badge badge-secondary">Pending</span>',
            'void'    => '<span class="badge badge-danger">Void</span>',
            default   => '<span class="badge badge-light">' . $this->status . '</span>',
        };
    }

    // ── Recalculate paid/due ──────────────────────────────────────
    public function recalculate(): void
    {
        $totalPaid = $this->payments()->where('status', 'active')->sum('amount');
        $due       = max(0, (float) $this->net_salary - $totalPaid);

        $status = 'pending';
        if ($totalPaid >= (float) $this->net_salary) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        $this->update([
            'paid_amount' => $totalPaid,
            'due_amount'  => $due,
            'status'      => $status,
        ]);
    }
}
