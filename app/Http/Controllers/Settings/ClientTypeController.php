<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ClientType;
use Illuminate\Http\Request;

class ClientTypeController extends Controller
{
    public function index()
    {
        return view('settings.client-types.index');
    }

    public function data()
    {
        $types = ClientType::withCount('customers')->latest()->get();

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
            'name'    => 'required|string|max:100|unique:client_types,name',
            'details' => 'nullable|string',
        ]);

        $type = ClientType::create([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Client Type added successfully.', 'data' => $type]);
    }

    public function update(Request $request, ClientType $clientType)
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:client_types,name,' . $clientType->id,
            'details' => 'nullable|string',
        ]);

        $clientType->update([
            'name'      => $request->name,
            'details'   => $request->details,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'Client Type updated successfully.']);
    }

    public function toggle(ClientType $clientType)
    {
        $clientType->update(['is_active' => !$clientType->is_active]);
        $status = $clientType->is_active ? 'activated' : 'deactivated';
        return response()->json(['success' => true, 'message' => "Client Type {$status}.", 'is_active' => $clientType->is_active]);
    }

    public function destroy(ClientType $clientType)
    {
        if ($clientType->customers()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — ' . $clientType->customers()->count() . ' customer(s) are using this type.',
            ], 422);
        }

        $clientType->delete();
        return response()->json(['success' => true, 'message' => 'Client Type deleted.']);
    }
}
