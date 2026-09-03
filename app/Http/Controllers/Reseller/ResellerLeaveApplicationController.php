<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\LeaveApplication;
use App\Models\HR\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ResellerLeaveApplicationController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $leaves = LeaveApplication::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))
            ->with(['employee', 'leaveType'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'pending'  => LeaveApplication::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', 'pending')->count(),
            'approved' => LeaveApplication::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', 'approved')->count(),
            'rejected' => LeaveApplication::whereHas('employee', fn($q) => $q->where('mac_reseller_id', $resellerId))->where('status', 'rejected')->count(),
        ];

        return view('reseller.hr.leave-application.index', compact('leaves', 'stats'));
    }

    public function create()
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $employees  = Employee::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name']);
        $leaveTypes = LeaveType::forReseller($resellerId)->active()->orderBy('name')->get();

        return view('reseller.hr.leave-application.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'nullable|string',
        ]);

        $employee  = Employee::forReseller($resellerId)->findOrFail($data['employee_id']);
        $leaveType = LeaveType::forReseller($resellerId)->findOrFail($data['leave_type_id']);

        $days = Carbon::parse($data['from_date'])->diffInDays(Carbon::parse($data['to_date'])) + 1;

        LeaveApplication::create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $leaveType->id,
            'from_date'     => $data['from_date'],
            'to_date'       => $data['to_date'],
            'days'          => $days,
            'reason'        => $data['reason'] ?? null,
            'status'        => 'pending',
        ]);

        return redirect()->route('reseller.hr.leave-application.index')->with('success', 'Leave application submitted successfully.');
    }

    public function approve(LeaveApplication $leave)
    {
        $this->authorizeItem($leave);
        $leave->update(['status' => 'approved']);
        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, LeaveApplication $leave)
    {
        $this->authorizeItem($leave);

        $data = $request->validate(['note' => 'nullable|string|max:255']);

        $leave->update(['status' => 'rejected', 'note' => $data['note'] ?? $leave->note]);

        return back()->with('success', 'Leave rejected.');
    }

    /** AJAX — used/remaining leave balance for an employee+type in the current year. */
    public function balance(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
        ]);

        $employee  = Employee::forReseller($resellerId)->findOrFail($request->employee_id);
        $leaveType = LeaveType::forReseller($resellerId)->findOrFail($request->leave_type_id);

        $usedDays = LeaveApplication::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereYear('from_date', now()->year)
            ->sum('days');

        return response()->json([
            'total_days'     => $leaveType->days_per_year,
            'used_days'      => (int) $usedDays,
            'remaining_days' => max(0, $leaveType->days_per_year - $usedDays),
        ]);
    }

    private function authorizeItem(LeaveApplication $leave): void
    {
        abort_unless($leave->employee && $leave->employee->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}