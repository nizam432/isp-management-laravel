<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\Zone;
use App\Models\SubZone;
use Illuminate\Http\Request;

class BoxController extends Controller
{
    public function index()
    {
        $boxes = Box::with(['zone', 'subZone', 'createdBy'])->latest()->get();
        $zones = Zone::active()->orderBy('name')->get();

        return view('settings.box.index', compact('boxes', 'zones'));
    }

    /**
     * Load Sub Zones for a given Zone via AJAX (for cascading dropdown).
     * GET /settings/box/sub-zones?zone_id=1
     */
    public function getSubZones(Request $request)
    {
        $request->validate(['zone_id' => 'required|integer|exists:zones,id']);

        $subZones = SubZone::where('zone_id', $request->zone_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($subZones);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'zone_id'     => 'required|exists:zones,id',
            'sub_zone_id' => 'required|exists:sub_zones,id',
            'name'        => 'required|string|max:255',
            'details'     => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        Box::create($data);

        return response()->json(['success' => true, 'message' => 'Box added successfully.']);
    }

    public function edit(Box $box)
    {
        $box->load('subZone');
        return response()->json([
            'id'          => $box->id,
            'zone_id'     => $box->zone_id,
            'sub_zone_id' => $box->sub_zone_id,
            'name'        => $box->name,
            'details'     => $box->details,
        ]);
    }

    public function update(Request $request, Box $box)
    {
        $data = $request->validate([
            'zone_id'     => 'required|exists:zones,id',
            'sub_zone_id' => 'required|exists:sub_zones,id',
            'name'        => 'required|string|max:255',
            'details'     => 'nullable|string',
        ]);

        $box->update($data);

        return response()->json(['success' => true, 'message' => 'Box updated successfully.']);
    }

    public function destroy(Box $box)
    {
        $box->delete();
        return response()->json(['success' => true, 'message' => 'Box deleted.']);
    }

    public function toggle(Box $box)
    {
        $box->update(['is_active' => !$box->is_active]);
        return response()->json(['success' => true]);
    }
}
