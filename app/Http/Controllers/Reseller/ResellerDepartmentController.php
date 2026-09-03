<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerDepartment;
use Illuminate\Http\Request;

class ResellerDepartmentController extends Controller
{
    public function index()
    {
        $macResellerId = auth('mac_reseller')->id();
        $items = MacResellerDepartment::forReseller($macResellerId)->orderBy('name')->get();

        return view('reseller.lookup.index', [
            'items'        => $items,
            'entityLabel'  => 'Department',
            'entityPlural' => 'Departments',
            'routeBase'    => 'reseller.configuration.department',
        ]);
    }

    public function store(Request $request)
    {
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'name'    => 'required|string|max:255|unique:mac_reseller_departments,name,NULL,id,mac_reseller_id,' . $macResellerId,
            'details' => 'nullable|string',
        ]);

        MacResellerDepartment::create([
            'mac_reseller_id' => $macResellerId,
            'name'            => $data['name'],
            'details'         => $data['details'] ?? null,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Department added successfully.');
    }

    public function update(Request $request, MacResellerDepartment $department)
    {
        $this->authorizeItem($department);
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'name'      => 'required|string|max:255|unique:mac_reseller_departments,name,' . $department->id . ',id,mac_reseller_id,' . $macResellerId,
            'details'   => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $department->update([
            'name'      => $data['name'],
            'details'   => $data['details'] ?? null,
            'is_active' => $request->boolean('is_active', $department->is_active),
        ]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function toggle(MacResellerDepartment $department)
    {
        $this->authorizeItem($department);
        $department->update(['is_active' => !$department->is_active]);
        return response()->json(['success' => true, 'is_active' => $department->is_active]);
    }

    public function destroy(MacResellerDepartment $department)
    {
        $this->authorizeItem($department);
        $department->delete();
        return back()->with('success', 'Department deleted successfully.');
    }

    private function authorizeItem(MacResellerDepartment $department): void
    {
        abort_unless($department->mac_reseller_id === auth('mac_reseller')->id(), 403);
    }
}
