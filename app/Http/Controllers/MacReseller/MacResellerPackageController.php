<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerPackage;
use Illuminate\Http\Request;

class MacResellerPackageController extends Controller
{
    public function index()
    {
        $packages = MacResellerPackage::latest()->get();
        return view('mac-reseller.package.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'bandwidth_mb' => 'required|integer|min:1',
            'details'      => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        MacResellerPackage::create($data);

        return response()->json(['success' => true, 'message' => 'Package added successfully.']);
    }

    public function edit(MacResellerPackage $package)
    {
        return response()->json($package);
    }

    public function update(Request $request, MacResellerPackage $package)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'bandwidth_mb' => 'required|integer|min:1',
            'details'      => 'nullable|string',
        ]);

        $package->update($data);

        return response()->json(['success' => true, 'message' => 'Package updated successfully.']);
    }

    public function destroy(MacResellerPackage $package)
    {
        $tariffCount = $package->tariffPackages()->count();
        if ($tariffCount > 0) {
            $tariffNames = $package->tariffPackages()
                ->with('tariff')
                ->get()
                ->pluck('tariff.name')
                ->filter()
                ->unique()
                ->join(', ');
            return response()->json([
                'success' => false,
                'message' => "This package is used in {$tariffCount} tariff line(s): {$tariffNames}. Please remove it from those tariffs first.",
            ], 422);
        }

        $package->delete();
        return response()->json(['success' => true, 'message' => 'Package deleted successfully.']);
    }
}
