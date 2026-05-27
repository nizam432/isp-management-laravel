<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ProtocolType;
use Illuminate\Http\Request;

class ProtocolTypeController extends Controller
{
    public function index()
    {
        return view('settings.protocol-types.index');
    }

    public function data()
    {
        $types = ProtocolType::withCount('customers')->latest()->get();

        $rows = $types->map(function ($type, $i) {
            return [
                'DT_RowIndex'    => $i + 1,
                'id'             => $type->id,
                'name'           => $type->name,
                'details'        => $type->details,
                'customers_count'=> $type->customers_count,
                'is_active'      => $type->is_active ? 1 : 0,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:protocol_types,name',
            'details' => 'nullable|string',
        ]);

        $type = ProtocolType::create([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Protocol Type added successfully.', 'data' => $type]);
    }

    public function update(Request $request, ProtocolType $protocolType)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:protocol_types,name,' . $protocolType->id,
            'details' => 'nullable|string',
        ]);

        $protocolType->update([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'Protocol Type updated successfully.']);
    }

    public function toggle(ProtocolType $protocolType)
    {
        $protocolType->update(['is_active' => !$protocolType->is_active]);
        $status = $protocolType->is_active ? 'activated' : 'deactivated';
        return response()->json(['success' => true, 'message' => "Protocol Type {$status}.", 'is_active' => $protocolType->is_active]);
    }

    public function destroy(ProtocolType $protocolType)
    {
        if ($protocolType->customers()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — ' . $protocolType->customers()->count() . ' customer(s) are using this type.',
            ], 422);
        }

        $protocolType->delete();
        return response()->json(['success' => true, 'message' => 'Protocol Type deleted.']);
    }
}
