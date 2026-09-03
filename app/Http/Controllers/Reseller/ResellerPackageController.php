<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerPackageSellingRate;
use App\Models\MacResellerTariffPackage;
use Illuminate\Http\Request;

class ResellerPackageController extends Controller
{
    /**
     * List the package lines that belong to this reseller's assigned Tariff.
     * Everything (Server, Protocol, Profile, Buying Rate) comes from Admin's
     * Tariff setup and is read-only here — only Selling Rate can be edited,
     * and it's stored per-reseller so resellers sharing the same Tariff can
     * each set their own price.
     */
    public function index()
    {
        $reseller = auth('mac_reseller')->user();

        $packages = collect();

        if ($reseller->tariff_id) {
            $packages = MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)
                ->with('package') // package name (e.g. "50 Mbps")
                ->get();

            // attach this reseller's own selling rate (if set) to each row
            $sellingRates = MacResellerPackageSellingRate::where('mac_reseller_id', $reseller->id)
                ->whereIn('mac_reseller_tariff_package_id', $packages->pluck('id'))
                ->pluck('selling_rate', 'mac_reseller_tariff_package_id');

            $packages->each(function ($pkg) use ($sellingRates) {
                $pkg->my_selling_rate = $sellingRates->get($pkg->id);
            });
        }

        return view('reseller.package.index', compact('packages'));
    }

    /**
     * The only thing a reseller is allowed to change: their own selling
     * price for one package line. Server/Protocol/Profile/BuyingRate stay
     * untouched — those belong to Admin's Tariff configuration.
     */
    public function updateSellingRate(Request $request, MacResellerTariffPackage $package)
    {
        $reseller = auth('mac_reseller')->user();

        // make sure this package line actually belongs to the reseller's own tariff
        abort_unless($package->tariff_id === $reseller->tariff_id, 403);

        $data = $request->validate([
            'selling_rate' => 'required|numeric|min:0',
        ]);

        MacResellerPackageSellingRate::updateOrCreate(
            [
                'mac_reseller_id'                => $reseller->id,
                'mac_reseller_tariff_package_id' => $package->id,
            ],
            [
                'selling_rate' => $data['selling_rate'],
            ]
        );

        return back()->with('success', 'Selling price updated successfully.');
    }
}
