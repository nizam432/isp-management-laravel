<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerZone;
use Illuminate\Http\Request;

class ResellerZoneController extends Controller
{
    public function index()
    {
        $macResellerId = auth('mac_reseller')->id();

        $zones = MacResellerZone::forReseller($macResellerId)
            ->withCount('subZones')
            ->orderBy('name')
            ->get();

        return view('reseller.zone.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'name'    => 'required|string|max:255|unique:mac_reseller_zones,name,NULL,id,mac_reseller_id,' . $macResellerId,
            'details' => 'nullable|string',
        ]);

        MacResellerZone::create([
            'mac_reseller_id' => $macResellerId,
            'name'            => $data['name'],
            'details'         => $data['details'] ?? null,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Zone added successfully.');
    }

    public function update(Request $request, MacResellerZone $zone)
    {
        $this->authorizeZone($zone);

        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'name'      => 'required|string|max:255|unique:mac_reseller_zones,name,' . $zone->id . ',id,mac_reseller_id,' . $macResellerId,
            'details'   => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $zone->update([
            'name'      => $data['name'],
            'details'   => $data['details'] ?? null,
            'is_active' => $request->boolean('is_active', $zone->is_active),
        ]);

        return back()->with('success', 'Zone updated successfully.');
    }

    public function toggle(MacResellerZone $zone)
    {
        $this->authorizeZone($zone);
        $zone->update(['is_active' => !$zone->is_active]);
        return response()->json(['success' => true, 'is_active' => $zone->is_active]);
    }

    public function destroy(MacResellerZone $zone)
    {
   
        $this->authorizeZone($zone);

        if ($zone->subZones()->exists()) {
            return back()->with('error', 'Cannot delete a zone that still has sub-zones. Delete its sub-zones first.');
        }

        $zone->delete();
        return back()->with('success', 'Zone deleted successfully.');
    }

    /** Make sure a reseller can only touch their own zone. */
    private function authorizeZone(MacResellerZone $zone): void
    {
        abort_unless($zone->mac_reseller_id === auth('mac_reseller')->id(), 403);
    }
}
