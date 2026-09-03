<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerDesignation;
use Illuminate\Http\Request;

class ResellerDesignationController extends Controller
{
    public function index()
    {
        $macResellerId = auth('mac_reseller')->id();
        $items = MacResellerDesignation::forReseller($macResellerId)->orderBy('name')->get();

        return view('reseller.lookup.index', [
            'items'        => $items,
            'entityLabel'  => 'Designation',
            'entityPlural' => 'Designations',
            'routeBase'    => 'reseller.configuration.designation',
        ]);
    }

    public function store(Request $request)
    {
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'name'    => 'required|string|max:255|unique:mac_reseller_designations,name,NULL,id,mac_reseller_id,' . $macResellerId,
            'details' => 'nullable|string',
        ]);

        MacResellerDesignation::create([
            'mac_reseller_id' => $macResellerId,
            'name'            => $data['name'],
            'details'         => $data['details'] ?? null,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Designation added successfully.');
    }

    public function update(Request $request, MacResellerDesignation $designation)
    {
        $this->authorizeItem($designation);
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'name'      => 'required|string|max:255|unique:mac_reseller_designations,name,' . $designation->id . ',id,mac_reseller_id,' . $macResellerId,
            'details'   => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $designation->update([
            'name'      => $data['name'],
            'details'   => $data['details'] ?? null,
            'is_active' => $request->boolean('is_active', $designation->is_active),
        ]);

        return back()->with('success', 'Designation updated successfully.');
    }

    public function toggle(MacResellerDesignation $designation)
    {
        $this->authorizeItem($designation);
        $designation->update(['is_active' => !$designation->is_active]);
        return response()->json(['success' => true, 'is_active' => $designation->is_active]);
    }

    public function destroy(MacResellerDesignation $designation)
    {
        $this->authorizeItem($designation);
        $designation->delete();
        return back()->with('success', 'Designation deleted successfully.');
    }

    private function authorizeItem(MacResellerDesignation $designation): void
    {
        abort_unless($designation->mac_reseller_id === auth('mac_reseller')->id(), 403);
    }
}
