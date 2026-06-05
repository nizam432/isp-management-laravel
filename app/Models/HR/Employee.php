<?php

namespace App\Models\HR;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Employee extends Model
{
    protected $fillable = [
        'employee_code', 'user_id', 'department_id', 'position_id',
        'name', 'phone', 'email', 'nid_number', 'photo',
        'join_date', 'status', 'leaving_date', 'leaving_reason', 'leaving_note',
        'present_address', 'permanent_address',
        'basic_salary', 'salary_date',
        'emergency_name', 'emergency_phone', 'emergency_relation',
        'bank_name', 'account_number', 'branch_name',
        'created_by',
    ];

    protected $casts = [
        'join_date'    => 'date',
        'leaving_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function advances()
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    public function leaves()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function generateCode(): string
    {
        do {
            $code = 'EMP-' . str_pad(rand(1, 99999), 4, '0', STR_PAD_LEFT);
        } while (static::where('employee_code', $code)->exists());
        return $code;
    }
}