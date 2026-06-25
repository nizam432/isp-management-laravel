<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StoreLocation;
use Illuminate\Http\Request;

class StoreLocationController extends Controller
{
    public function index()
    {
        $locations = StoreLocation::withCount('purchases', 'sales')
                        ->latest()
                        ->paginate(20);

        return view('inventory.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('inventory.locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255|unique:inventory_store_locations,name',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
        ]);

        StoreLocation::create([
            ...$request->only(['name', 'address', 'contact_person', 'phone']),
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('inventory.locations.index')
                         ->with('success', 'Location created successfully.');
    }

    public function edit(StoreLocation $location)
    {
        return view('inventory.locations.edit', compact('location'));
    }

    public function update(Request $request, StoreLocation $location)
    {
        $request->validate([
            'name'           => 'required|string|max:255|unique:inventory_store_locations,name,' . $location->id,
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'is_active'      => 'boolean',
        ]);

        $location->update($request->only(['name', 'address', 'contact_person', 'phone', 'is_active']));

        return redirect()->route('inventory.locations.index')
                         ->with('success', 'Location updated successfully.');
    }

    public function destroy(StoreLocation $location)
    {
        // Location এ কোনো stock বা transaction থাকলে delete করা যাবে না
        if ($location->locationStocks()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'This location has stock and cannot be deleted.');
        }

        $location->delete();

        return redirect()->route('inventory.locations.index')
                         ->with('success', 'Location deleted successfully.');
    }

    // Active/Inactive toggle
    public function toggle(StoreLocation $location)
    {
        $location->update(['is_active' => !$location->is_active]);

        return back()->with('success', 'Location status updated.');
    }
}
