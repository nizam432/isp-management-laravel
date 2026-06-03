<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    protected $table    = 'payroll_details';
    protected $fillable = ['payroll_id', 'salary_head_id', 'amount'];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function salaryHead()
    {
        return $this->belongsTo(SalaryHead::class);
    }
}