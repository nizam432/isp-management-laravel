<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\SubZone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * GET /settings/zones
     */
    public function index()
    {
        return view('settings.zones.index');
    }

    /**
     * GET /settings/zones/data  (DataTables AJAX)
     * Object format — DataTables columns use data: 'name' style
     */
    public function data()
    {
        $zones = Zone::withCount('subZones')->latest()->get();

        $rows = $zones->map(function ($zone, $i) {
            return [
                'DT_RowIndex'    => $i + 1,
                'id'             => $zone->id,
                'name'           => $zone->name,
                'details'        => $zone->details,
                'sub_zones_count'=> $zone->sub_zones_count,
                'is_active'      => $zone->is_active ? 1 : 0,
            ];
        });

        // Total SubZone count for stat box
        $totalSubZones = SubZone::count();

        return response()->json([
            'data'           => $rows,
            'total_subzones' => $totalSubZones,
        ]);
    }

    /**
     * POST /settings/zones
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:zones,name',
            'details' => 'nullable|string',
        ]);

        $zone = Zone::create([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Zone সফলভাবে যোগ হয়েছে।',
            'data'    => $zone,
        ]);
    }

    /**
     * PUT /settings/zones/{zone}
     */
    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:zones,name,' . $zone->id,
            'details' => 'nullable|string',
        ]);

        $zone->update([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'Zone আপডেট হয়েছে।']);
    }

    /**
     * DELETE /settings/zones/{zone}
     */
    public function destroy(Zone $zone)
    {
        if ($zone->subZones()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'এই Zone এ ' . $zone->subZones()->count() . 'টি SubZone আছে। আগে SubZone মুছুন।',
            ], 422);
        }

        $zone->delete();

        return response()->json(['success' => true, 'message' => 'Zone মুছে ফেলা হয়েছে।']);
    }
}