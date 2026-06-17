<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerTariff;
use App\Models\MacResellerTariffPackage;
use App\Models\MacResellerPackage;
use Illuminate\Http\Request;

class MacResellerTariffController extends Controller
{
    public function index()
    {
        $tariffs  = MacResellerTariff::with(['packages.package', 'createdBy'])->latest()->get();
        $packages = MacResellerPackage::where('is_active', true)->orderBy('name')->get();
        return view('mac-reseller.tariff.index', compact('tariffs', 'packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tariff_type'  => 'required|in:custom,date_to_date',
            'name'         => 'required|string|max:255',
            'lines'        => 'required|array|min:1',
            'lines.*.package_id'           => 'required|exists:mac_reseller_packages,id',
            'lines.*.server_name'          => 'nullable|string',
            'lines.*.protocol'             => 'nullable|string',
            'lines.*.profile'              => 'nullable|string',
            'lines.*.rate'                 => 'required|numeric|min:0',
            'lines.*.validity_days'        => 'required|integer|min:1',
            'lines.*.min_activation_days'  => 'required|integer|min:1',
        ]);

        $tariff = MacResellerTariff::create([
            'tariff_type' => $request->tariff_type,
            'name'        => $request->name,
            'created_by'  => auth()->id(),
        ]);

        foreach ($request->lines as $line) {
            $tariff->packages()->create($line);
        }

        return response()->json(['success' => true, 'message' => 'Tariff added successfully.']);
    }

    public function show(MacResellerTariff $tariff)
    {
        return response()->json($tariff->load('packages.package'));
    }

    public function update(Request $request, MacResellerTariff $tariff)
    {
        $request->validate([
            'tariff_type'  => 'required|in:custom,date_to_date',
            'name'         => 'required|string|max:255',
            'lines'        => 'required|array|min:1',
        ]);

        $tariff->update([
            'tariff_type' => $request->tariff_type,
            'name'        => $request->name,
        ]);

        $tariff->packages()->delete();
        foreach ($request->lines as $line) {
            $tariff->packages()->create($line);
        }

        return response()->json(['success' => true, 'message' => 'Tariff updated successfully.']);
    }

    public function destroy(MacResellerTariff $tariff)
    {
        $tariff->delete();
        return response()->json(['success' => true, 'message' => 'Tariff deleted.']);
    }

    public function toggle(MacResellerTariff $tariff)
    {
        $tariff->update(['is_active' => !$tariff->is_active]);
        return response()->json(['success' => true]);
    }

    public function syncMikrotik(MacResellerTariff $tariff)
    {
        // Mikrotik sync logic এখানে আসবে
        return response()->json(['success' => true, 'message' => 'Synced with Mikrotik.']);
    }
}
