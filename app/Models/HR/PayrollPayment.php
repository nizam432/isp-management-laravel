<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Expense;

class PayrollPayment extends Model
{
    protected $table    = 'payroll_payments';
    protected $fillable = [
        'payroll_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_no',
        'note',
        'expense_id',
        'status',
        'void_reason',
        'void_date',
        'void_by',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
        'void_date'    => 'datetime',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidBy()
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function isVoid(): bool   { return $this->status === 'void'; }
    public function isActive(): bool { return $this->status === 'active'; }
}

