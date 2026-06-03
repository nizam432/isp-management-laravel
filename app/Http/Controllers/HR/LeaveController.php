<?php


namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller; 

use App\Models\HR\Employee;
use App\Models\HR\LeaveType;
use App\Models\HR\LeaveApplication;
use App\Models\HR\LeaveBalance;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $leaves = LeaveApplication::with(['employee', 'leaveType'])
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
            ->latest()
            ->paginate(20);

        $employees = Employee::active()->get();
        return view('hr.leave.index', compact('leaves', 'employees'));
    }

    public function create()
    {
        $employees  = Employee::active()->get();
        $leaveTypes = LeaveType::active()->get();
        return view('hr.leave.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'nullable|string',
        ]);

        $days = \Carbon\Carbon::parse($request->from_date)
                              ->diffInDays(\Carbon\Carbon::parse($request->to_date)) + 1;

        LeaveApplication::create([
            'employee_id'   => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'from_date'     => $request->from_date,
            'to_date'       => $request->to_date,
            'days'          => $days,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        return redirect()->route('leave.index')
                         ->with('success', 'Leave application submitted.');
    }

    public function approve(LeaveApplication $leave)
    {
        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // Update leave balance
        LeaveBalance::updateOrCreate(
            ['employee_id' => $leave->employee_id, 'leave_type_id' => $leave->leave_type_id, 'year' => now()->year],
            ['total_days' => $leave->leaveType->days_per_year]
        );

        $balance = LeaveBalance::where('employee_id', $leave->employee_id)
                               ->where('leave_type_id', $leave->leave_type_id)
                               ->where('year', now()->year)
                               ->first();

        if ($balance) {
            $balance->increment('used_days', $leave->days);
            $balance->update(['remaining_days' => $balance->total_days - $balance->used_days]);
        }

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, LeaveApplication $leave)
    {
        $leave->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'note'        => $request->note,
        ]);
        return back()->with('success', 'Leave rejected.');
    }

    // Leave Types CRUD
    public function types()
    {
        $types = LeaveType::latest()->get();
        return view('hr.leave.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100', 'days_per_year' => 'required|integer|min:0']);
        LeaveType::create($request->only('name', 'days_per_year') + ['is_active' => true]);
        return back()->with('success', 'Leave type added.');
    }

    public function updateType(Request $request, LeaveType $type)
    {
        $type->update($request->only('name', 'days_per_year'));
        return back()->with('success', 'Leave type updated.');
    }

    public function destroyType(LeaveType $type)
    {
        $type->delete();
        return back()->with('success', 'Leave type deleted.');
    }
}
