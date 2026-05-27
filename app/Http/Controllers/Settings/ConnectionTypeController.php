<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ConnectionType;
use Illuminate\Http\Request;

class ConnectionTypeController extends Controller
{
    public function index()
    {
        return view('settings.connection-types.index');
    }

    public function data()
    {
        $types = ConnectionType::withCount('customers')->latest()->get();

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
            'name'    => 'required|string|max:100|unique:connection_types,name',
            'details' => 'nullable|string',
        ]);

        $type = ConnectionType::create([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Connection Type added successfully.', 'data' => $type]);
    }

    public function update(Request $request, ConnectionType $connectionType)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:connection_types,name,' . $connectionType->id,
            'details' => 'nullable|string',
        ]);

        $connectionType->update([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'Connection Type updated successfully.']);
    }

    public function toggle(ConnectionType $connectionType)
    {
        $connectionType->update(['is_active' => !$connectionType->is_active]);
        $status = $connectionType->is_active ? 'activated' : 'deactivated';
        return response()->json(['success' => true, 'message' => "Connection Type {$status}.", 'is_active' => $connectionType->is_active]);
    }

    public function destroy(ConnectionType $connectionType)
    {
        if ($connectionType->customers()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — ' . $connectionType->customers()->count() . ' customer(s) are using this type.',
            ], 422);
        }

        $connectionType->delete();
        return response()->json(['success' => true, 'message' => 'Connection Type deleted.']);
    }
}
