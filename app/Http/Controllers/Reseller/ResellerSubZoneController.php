<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerSubZone;
use App\Models\MacResellerZone;
use Illuminate\Http\Request;

class ResellerSubZoneController extends Controller
{
    public function index()
    {
        $macResellerId = auth('mac_reseller')->id();

        $subZones = MacResellerSubZone::forReseller($macResellerId)
            ->with('zone')
            ->orderBy('name')
            ->get();

        $zones = MacResellerZone::forReseller($macResellerId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('reseller.subzone.index', compact('subZones', 'zones'));
    }

    public function store(Request $request)
    {
        $macResellerId = auth('mac_reseller')->id();

        $data = $request->validate([
            'mac_reseller_zone_id' => 'required|exists:mac_reseller_zones,id',
            'name'                 => 'required|string|max:255',
            'details'              => 'nullable|string',
        ]);

        // make sure the chosen zone actually belongs to this reseller
        $zone = MacResellerZone::forReseller($macResellerId)->findOrFail($data['mac_reseller_zone_id']);

        MacResellerSubZone::create([
            'mac_reseller_id'      => $macResellerId,
            'mac_reseller_zone_id' => $zone->id,
            'name'                 => $data['name'],
            'details'              => $data['details'] ?? null,
            'is_active'            => true,
        ]);

        return back()->with('success', 'Sub zone added successfully.');
    }

    public function update(Request $request, MacResellerSubZone $subzone)
    {
        $this->authorizeSubZone($subzone);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'details'   => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $subzone->update([
            'name'      => $data['name'],
            'details'   => $data['details'] ?? null,
            'is_active' => $request->boolean('is_active', $subzone->is_active),
        ]);

        return back()->with('success', 'Sub zone updated successfully.');
    }

    public function toggle(MacResellerSubZone $subzone)
    {
        $this->authorizeSubZone($subzone);
        $subzone->update(['is_active' => !$subzone->is_active]);
        return response()->json(['success' => true, 'is_active' => $subzone->is_active]);
    }

    public function destroy(MacResellerSubZone $subzone)
    {
        $this->authorizeSubZone($subzone);
        $subzone->delete();
        return back()->with('success', 'Sub zone deleted successfully.');
    }

    private function authorizeSubZone(MacResellerSubZone $subzone): void
    {
        abort_unless($subzone->mac_reseller_id === auth('mac_reseller')->id(), 403);
    }
}
