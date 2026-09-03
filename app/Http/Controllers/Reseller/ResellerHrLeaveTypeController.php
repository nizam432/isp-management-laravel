<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerHrLeaveTypeController extends Controller
{
    public function index()
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        $leaveTypes = LeaveType::forReseller($resellerId)->orderBy('name')->get();

        return view('reseller.hr.leave-type.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'days_per_year' => 'required|integer|min:0',
        ]);

        LeaveType::create([
            'mac_reseller_id' => $resellerId,
            'name'            => $data['name'],
            'days_per_year'   => $data['days_per_year'],
            'is_active'       => true,
        ]);

        return back()->with('success', 'Leave Type added successfully.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $this->authorizeItem($leaveType);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'days_per_year' => 'required|integer|min:0',
            'is_active'     => 'nullable|boolean',
        ]);

        $leaveType->update([
            'name'          => $data['name'],
            'days_per_year' => $data['days_per_year'],
            'is_active'     => $request->boolean('is_active', $leaveType->is_active),
        ]);

        return back()->with('success', 'Leave Type updated successfully.');
    }

    public function toggle(LeaveType $leaveType)
    {
        $this->authorizeItem($leaveType);
        $leaveType->update(['is_active' => !$leaveType->is_active]);
        return response()->json(['success' => true]);
    }

    public function destroy(LeaveType $leaveType)
    {
        $this->authorizeItem($leaveType);
        $leaveType->delete();
        return back()->with('success', 'Leave Type deleted successfully.');
    }

    private function authorizeItem(LeaveType $leaveType): void
    {
        abort_unless($leaveType->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}