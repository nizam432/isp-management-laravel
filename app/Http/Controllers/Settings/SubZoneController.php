<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SubZone;
use App\Models\Zone;
use Illuminate\Http\Request;

class SubZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::active()->get();
        return view('settings.subzones.index', compact('zones'));
    }

    /**
     * GET /settings/sub-zones/data  — DataTables AJAX (object format)
     */
    public function data()
    {
        $subZones = SubZone::with('zone')->latest()->get();

        $rows = $subZones->map(function ($sz, $i) {
            return [
                'DT_RowIndex' => $i + 1,
                'id'          => $sz->id,
                'zone_id'     => $sz->zone_id,
                'zone_name'   => $sz->zone->name ?? '—',
                'name'        => $sz->name,
                'details'     => $sz->details,
                'is_active'   => $sz->is_active ? 1 : 0,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'name'    => 'required|string|max:100',
            'details' => 'nullable|string',
        ]);

        $subZone = SubZone::create([
            'zone_id'   => $request->zone_id,
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sub Zone added successfully.',
            'data'    => $subZone,
        ]);
    }

    public function update(Request $request, SubZone $subZone)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'name'    => 'required|string|max:100',
            'details' => 'nullable|string',
        ]);

        $subZone->update([
            'zone_id'   => $request->zone_id,
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'Sub Zone updated successfully.']);
    }

    public function destroy(SubZone $subZone)
    {
        $subZone->delete();
        return response()->json(['success' => true, 'message' => 'Sub Zone deleted.']);
    }
}