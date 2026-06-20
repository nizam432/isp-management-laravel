<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacResellerTariff;
use App\Models\MacResellerTariffPackage;
use App\Models\MacResellerPackage;
use App\Models\MikrotikRouter;
use Illuminate\Http\Request;

class MacResellerTariffController extends Controller
{
    public function index()
    {
        $tariffs  = MacResellerTariff::with(['packages.package', 'createdBy'])->latest()->get();
        $packages = MacResellerPackage::where('is_active', true)->orderBy('name')->get();
        $routers  = MikrotikRouter::active()->orderBy('name')->get();
        return view('mac-reseller.tariff.index', compact('tariffs', 'packages', 'routers'));
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
            'lines.*.rate'                 => 'required|numeric|min:1',
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
            'lines'        => 'sometimes|array',
        ]);

        $tariff->update([
            'tariff_type' => $request->tariff_type,
            'name'        => $request->name,
        ]);

        // নতুন package lines যোগ করা হলে (existing lines অপরিবর্তিত থাকবে,
        // কারণ delete/edit আলাদা endpoint দিয়ে হ্যান্ডেল হয়)
        if ($request->has('lines')) {
            foreach ($request->lines as $line) {
                // এড়িয়ে যাও যদি ইতিমধ্যেই id দিয়ে existing line হয়
                if (!empty($line['id'])) continue;

                $tariff->packages()->create([
                    'package_id'           => $line['package_id'],
                    'server_name'          => $line['server_name'] ?? null,
                    'protocol'             => $line['protocol'] ?? null,
                    'profile'              => $line['profile'] ?? null,
                    'rate'                 => $line['rate'],
                    'validity_days'        => $line['validity_days'],
                    'min_activation_days'  => $line['min_activation_days'],
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Tariff updated successfully.']);
    }

    /**
     * একটা নির্দিষ্ট Package Line ইনলাইন এডিট করো (শুধু Rate, Validity, Min Activation Days)।
     * Package/Server/Profile পরিবর্তন হয় না — তাই client এর কোনো ক্ষতি হয় না।
     */
    public function updateLine(Request $request, MacResellerTariffPackage $line)
    {
        $data = $request->validate([
            'rate'                 => 'required|numeric|min:1',
            'validity_days'        => 'required|integer|min:1',
            'min_activation_days'  => 'required|integer|min:1',
        ]);

        $line->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Package line updated successfully.',
            'line'    => $line->fresh('package'),
        ]);
    }

    /**
     * একটা নির্দিষ্ট Package Line ডিলিট করার আগে চেক করো —
     * এই Tariff কোনো MAC Reseller ব্যবহার করছে কিনা।
     * করলে ব্লক করো এবং কোন কোন Reseller ব্যবহার করছে তা দেখাও।
     */
    public function destroyLine(MacResellerTariffPackage $line)
    {
        $tariff = $line->tariff;

        $resellers = $tariff->resellers()->pluck('business_name');

        if ($resellers->isNotEmpty()) {
            return response()->json([
                'success'   => false,
                'blocked'   => true,
                'message'   => 'This package/profile/speed is currently in use.',
                'resellers' => $resellers,
            ], 422);
        }

        $line->delete();

        return response()->json(['success' => true, 'message' => 'Package line removed successfully.']);
    }

    public function destroy(MacResellerTariff $tariff)
    {
        // কোনো MAC Reseller এই tariff ব্যবহার করলে delete করা যাবে না
        $resellers = $tariff->resellers()->pluck('business_name');
        if ($resellers->isNotEmpty()) {
            return response()->json([
                'success'   => false,
                'message'   => "This tariff is assigned to: {$resellers->join(', ')}. Please change their tariff before deleting.",
                'resellers' => $resellers,
            ], 422);
        }

        // আগে child tariff packages delete করো, তারপর tariff delete
        $tariff->packages()->delete();
        $tariff->delete();
        return response()->json(['success' => true, 'message' => 'Tariff deleted successfully.']);
    }

    public function toggle(MacResellerTariff $tariff)
    {
        $tariff->update(['is_active' => !$tariff->is_active]);
        return response()->json(['success' => true]);
    }

    public function syncMikrotik(MacResellerTariff $tariff)
    {
        return response()->json(['success' => true, 'message' => 'Synced with Mikrotik.']);
    }
}
