<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\HR\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerHrDepartmentController extends Controller
{
    public function index()
    {
        $resellerId  = Auth::guard('mac_reseller')->id();
        $departments = Department::forReseller($resellerId)->orderBy('name')->get();

        return view('reseller.hr.department.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Department::create([
            'mac_reseller_id' => $resellerId,
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Department added successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $this->authorizeItem($department);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $department->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', $department->is_active),
        ]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function toggle(Department $department)
    {
        $this->authorizeItem($department);
        $department->update(['is_active' => !$department->is_active]);
        return response()->json(['success' => true]);
    }

    public function destroy(Department $department)
    {
        $this->authorizeItem($department);

        if ($department->employees()->exists()) {
            return back()->with('error', 'এই Department-এ Employee আছে, আগে সেগুলো সরান।');
        }

        $department->delete();
        return back()->with('success', 'Department deleted successfully.');
    }

    private function authorizeItem(Department $department): void
    {
        abort_unless($department->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);
    }
}
