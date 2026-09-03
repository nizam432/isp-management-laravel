<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerHrEmployeeController extends Controller
{
    public function index()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $employees = Employee::forReseller($resellerId)
            ->with(['department', 'position'])
            ->latest()
            ->paginate(25);

        return view('reseller.hr.employee.index', compact('employees'));
    }

    public function create()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $departments = Department::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name']);
        $positions   = Position::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name', 'department_id']);

        return view('reseller.hr.employee.create', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'department_id'       => 'required|exists:departments,id',
            'position_id'         => 'required|exists:positions,id',
            'name'                => 'required|string|max:255',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email',
            'nid_number'           => 'nullable|string|max:50',
            'photo'                => 'nullable|image|max:2048',
            'join_date'            => 'required|date',
            'status'               => 'required|in:active,inactive',
            'present_address'      => 'nullable|string',
            'permanent_address'    => 'nullable|string',
            'basic_salary'         => 'required|numeric|min:0',
            'salary_date'          => 'nullable|integer|min:1|max:28',
            'emergency_name'       => 'nullable|string|max:255',
            'emergency_phone'      => 'nullable|string|max:20',
            'emergency_relation'   => 'nullable|string|max:100',
            'bank_name'            => 'nullable|string|max:255',
            'account_number'       => 'nullable|string|max:100',
            'branch_name'          => 'nullable|string|max:255',
        ]);

        // ownership checks
        Department::forReseller($resellerId)->findOrFail($data['department_id']);
        Position::forReseller($resellerId)->where('department_id', $data['department_id'])->findOrFail($data['position_id']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('hr/employees', 'public');
        }

        $data['mac_reseller_id'] = $resellerId;
        $data['employee_code']   = Employee::generateCode();
        $data['created_by']      = null; // created by reseller, not an internal admin User

        Employee::create($data);

        return redirect()->route('reseller.hr.employee.index')->with('success', 'Employee added successfully.');
    }
}
