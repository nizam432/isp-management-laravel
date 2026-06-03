<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'basic_salary', 'gross_salary',
        'total_deduction', 'net_salary', 'payment_date',
        'payment_method', 'status', 'note', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }
}