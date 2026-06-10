<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OltType;
use Illuminate\Http\Request;

class OltTypeController extends Controller
{
    public function index()
    {
        return view('settings.olt-types.index');
    }

    public function data()
    {
        $types = OltType::withCount('olts')->latest()->get();

        $rows = $types->map(fn($type, $i) => [
            'DT_RowIndex' => $i + 1,
            'id'          => $type->id,
            'name'        => $type->name,
            'details'     => $type->details,
            'olts_count'  => $type->olts_count,
            'is_active'   => $type->is_active ? 1 : 0,
        ]);

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:olt_types,name',
            'details' => 'nullable|string',
        ]);

        $type = OltType::create([
            'name'      => strtoupper(trim($request->name)),
            'details'   => $request->details,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'OLT Type যোগ হয়েছে।', 'data' => $type]);
    }

    public function update(Request $request, OltType $oltType)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:olt_types,name,' . $oltType->id,
            'details' => 'nullable|string',
        ]);

        $oltType->update([
            'name'      => strtoupper(trim($request->name)),
            'details'   => $request->details,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'OLT Type আপডেট হয়েছে।']);
    }

    public function toggle(OltType $oltType)
    {
        $oltType->update(['is_active' => !$oltType->is_active]);
        $status = $oltType->is_active ? 'activated' : 'deactivated';
        return response()->json(['success' => true, 'message' => "OLT Type {$status}.", 'is_active' => $oltType->is_active]);
    }

    public function destroy(OltType $oltType)
    {
        if ($oltType->olts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'মুছা যাবে না — ' . $oltType->olts()->count() . 'টি OLT এই type ব্যবহার করছে।',
            ], 422);
        }

        $oltType->delete();
        return response()->json(['success' => true, 'message' => 'OLT Type মুছে ফেলা হয়েছে।']);
    }
}
