<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a paginated list of all packages.
     * Includes customer count per package.
     */
    public function index()
    {
        // Count how many customers are on each package
        $packages = Package::withCount('customers')->latest()->paginate(15);

        return view('packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        return view('packages.create');
    }

    /**
     * Store a newly created package in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'speed_download'   => 'required|integer|min:1',   // in Mbps
            'speed_upload'     => 'required|integer|min:1',   // in Mbps
            'price'            => 'required|numeric|min:0',   // monthly price in BDT
            'connection_fee'   => 'nullable|numeric|min:0',
            'type'             => 'required|in:home,business,student',
            'data_limit'       => 'nullable|integer|min:0',   // 0 means unlimited
            'mikrotik_profile' => 'nullable|string|max:100',  // MikroTik queue profile name
        ]);

        $package = Package::create($request->all());

        ActivityLog::log('Package created', 'Package', $package->id, null, $package->toArray());

        return redirect()->route('packages.index')
                         ->with('success', 'Package created successfully.');
    }

    /**
     * Display the specified package with its customer list.
     */
    public function show(Package $package)
    {
        $package->load('customers');

        return view('packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        return view('packages.edit', compact('package'));
    }

    /**
     * Update the specified package in the database.
     */
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'speed_download' => 'required|integer|min:1',
            'speed_upload'   => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'type'           => 'required|in:home,business,student',
        ]);

        // Save old values for activity log
        $old = $package->toArray();
        $package->update($request->all());

        ActivityLog::log('Package updated', 'Package', $package->id, $old, $package->toArray());

        return redirect()->route('packages.index')
                         ->with('success', 'Package updated successfully.');
    }

    /**
     * Delete the specified package.
     * Will not delete if customers are assigned to this package.
     */
    public function destroy(Package $package)
    {
        // Prevent deletion if customers exist on this package
        if ($package->customers()->count() > 0) {
            return back()->with('error', 'Cannot delete — customers are assigned to this package.');
        }

        ActivityLog::log('Package deleted', 'Package', $package->id, $package->toArray(), null);
        $package->delete();

        return redirect()->route('packages.index')
                         ->with('success', 'Package deleted successfully.');
    }

    /**
     * Toggle the active/inactive status of a package.
     */
    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        $status = $package->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Package {$status} successfully.");
    }
}
