<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeEducation extends Model
{
    protected $fillable = ['employee_id', 'degree', 'institution', 'passing_year'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}