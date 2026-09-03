<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerBox;
use App\Models\MacResellerZone;
use App\Models\MacResellerSubZone;
use Illuminate\Http\Request;

class ResellerBoxController extends Controller
{
    public function index()
    {
        $macResellerId = auth('mac_reseller')->id();

        $boxes = MacResellerBox::forReseller($macResellerId)
            ->with(['zone', 'subZone'])
            ->orderBy('name')
            ->get();

        $zones = MacResellerZone::forReseller($macResellerId)->active()->orderBy('name')->get(['id', 'name']);

        // all sub-zones for this reseller, grouped by zone_id — used to populate the
        // dependent "Sub Zone" dropdown client-side once a Zone is picked
        $subZones = MacResellerSubZone::forReseller($macResellerId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'mac_reseller_zone_id']);

        return view('reseller.box.index', compact('boxes', 'zones', 'subZones'));
    }

    public function store(Request $request)
    {
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'mac_reseller_zone_id'     => 'required|exists:mac_reseller_zones,id',
            'mac_reseller_sub_zone_id' => 'required|exists:mac_reseller_sub_zones,id',
            'name'    => 'required|string|max:255|unique:mac_reseller_boxes,name,NULL,id,mac_reseller_id,' . $macResellerId,
            'details' => 'nullable|string',
        ]);

        // ownership checks — zone and sub-zone must belong to this reseller,
        // and the sub-zone must actually belong to the chosen zone
        $zone = MacResellerZone::forReseller($macResellerId)->findOrFail($data['mac_reseller_zone_id']);
        $subZone = MacResellerSubZone::forReseller($macResellerId)
            ->where('mac_reseller_zone_id', $zone->id)
            ->findOrFail($data['mac_reseller_sub_zone_id']);

        MacResellerBox::create([
            'mac_reseller_id'          => $macResellerId,
            'mac_reseller_zone_id'     => $zone->id,
            'mac_reseller_sub_zone_id' => $subZone->id,
            'name'                     => $data['name'],
            'details'                  => $data['details'] ?? null,
            'is_active'                => true,
        ]);

        return back()->with('success', 'Box added successfully.');
    }

    public function update(Request $request, MacResellerBox $box)
    {
        $this->authorizeItem($box);
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'mac_reseller_zone_id'     => 'required|exists:mac_reseller_zones,id',
            'mac_reseller_sub_zone_id' => 'required|exists:mac_reseller_sub_zones,id',
            'name'      => 'required|string|max:255|unique:mac_reseller_boxes,name,' . $box->id . ',id,mac_reseller_id,' . $macResellerId,
            'details'   => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $zone = MacResellerZone::forReseller($macResellerId)->findOrFail($data['mac_reseller_zone_id']);
        $subZone = MacResellerSubZone::forReseller($macResellerId)
            ->where('mac_reseller_zone_id', $zone->id)
            ->findOrFail($data['mac_reseller_sub_zone_id']);

        $box->update([
            'mac_reseller_zone_id'     => $zone->id,
            'mac_reseller_sub_zone_id' => $subZone->id,
            'name'                     => $data['name'],
            'details'                  => $data['details'] ?? null,
            'is_active'                => $request->boolean('is_active', $box->is_active),
        ]);

        return back()->with('success', 'Box updated successfully.');
    }

    public function toggle(MacResellerBox $box)
    {
        $this->authorizeItem($box);
        $box->update(['is_active' => !$box->is_active]);
        return response()->json(['success' => true, 'is_active' => $box->is_active]);
    }

    public function destroy(MacResellerBox $box)
    {
        $this->authorizeItem($box);
        $box->delete();
        return back()->with('success', 'Box deleted successfully.');
    }

    private function authorizeItem(MacResellerBox $box): void
    {
        abort_unless($box->mac_reseller_id === auth('mac_reseller')->id(), 403);
    }
}
