<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ClientType;
use App\Models\Customer;
use App\Models\MacResellerSubZone;
use App\Models\MacResellerTariffPackage;
use App\Models\MacResellerZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResellerClientController extends Controller
{
    public function index()
    {
        $reseller = auth('mac_reseller')->user();

        $clients = Customer::forReseller($reseller->id)
            ->with(['resellerZone', 'resellerSubZone', 'clientType', 'resellerTariffPackage.package'])
            ->latest()
            ->paginate(25);

        return view('reseller.client.index', compact('clients'));
    }

    public function show(Customer $client)
    {
        $this->authorizeClient($client);
        return view('reseller.client.show', compact('client'));
    }

    public function create()
    {
        $reseller = auth('mac_reseller')->user();

        // Package lines from the reseller's own assigned Tariff (Server/Protocol/Profile come along)
        $packages = collect();
        if ($reseller->tariff_id) {
            $packages = MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)
                ->with('package')
                ->get();
        }

        $zones = MacResellerZone::forReseller($reseller->id)->active()->orderBy('name')->get(['id', 'name']);

        // all sub-zones for this reseller, sent along so the Sub Zone dropdown can be
        // populated client-side once a Zone is picked (same pattern as the Box page)
        $subZones = MacResellerSubZone::forReseller($reseller->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'mac_reseller_zone_id']);

        $clientTypes = ClientType::orderBy('name')->get(['id', 'name']);

        return view('reseller.client.create', compact('packages', 'zones', 'subZones', 'clientTypes'));
    }

    public function store(Request $request)
    {
        $reseller = auth('mac_reseller')->user();

        $data = $request->validate([
            'name'                            => 'required|string|max:255',
            'phone'                            => 'required|string|max:20',
            'email'                            => 'nullable|email',
            'nid_number'                       => 'nullable|string|max:50',
            'occupation'                       => 'nullable|string|max:255',
            'gender'                           => 'nullable|in:male,female,other',
            'address'                          => 'nullable|string',
            'photo'                            => 'nullable|image|max:2048',
            'nid_photo'                        => 'nullable|image|max:2048',

            'mac_reseller_tariff_package_id'   => 'required|exists:mac_reseller_tariff_packages,id',
            'client_type_id'                   => 'required|exists:client_types,id',
            'billing_status'                   => 'required|in:active,inactive,left,free',
            'mac_reseller_zone_id'             => 'nullable|exists:mac_reseller_zones,id',
            'mac_reseller_sub_zone_id'         => 'nullable|exists:mac_reseller_sub_zones,id',
            'monthly_bill_amount'              => 'nullable|numeric|min:0',
            'billing_date'                     => 'required|integer|min:1|max:28',
            'connection_date'                  => 'required|date',

            'pppoe_username'                   => 'nullable|string|max:255',
            'pppoe_password'                   => 'nullable|string|max:255',
            'ip_address'                       => 'nullable|string|max:45',
            'mac_address'                      => 'nullable|string|max:17',
            'status'                           => 'required|in:active,inactive,suspended,expired',
        ]);

        // make sure the chosen package line actually belongs to this reseller's own tariff
        $tariffPackage = MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)
            ->findOrFail($data['mac_reseller_tariff_package_id']);

        // ownership check for zone / sub-zone, if provided
        if (!empty($data['mac_reseller_zone_id'])) {
            MacResellerZone::forReseller($reseller->id)->findOrFail($data['mac_reseller_zone_id']);
        }
        if (!empty($data['mac_reseller_sub_zone_id'])) {
            MacResellerSubZone::forReseller($reseller->id)->findOrFail($data['mac_reseller_sub_zone_id']);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('customers/photos', 'public');
        }
        if ($request->hasFile('nid_photo')) {
            $data['nid_photo'] = $request->file('nid_photo')->store('customers/nid', 'public');
        }

        if (!empty($data['pppoe_password'])) {
            $data['pppoe_password'] = Hash::make($data['pppoe_password']);
        }

        $data['customer_code']    = Customer::generateCode();
        $data['mac_reseller_id']  = $reseller->id;
        $data['monthly_bill_amount'] = $data['monthly_bill_amount'] ?? $tariffPackage->rate;
        $data['created_by']       = null; // created by a reseller, not an internal User

        Customer::create($data);

        return redirect()->route('reseller.client.index')->with('success', 'Client added successfully.');
    }

    /** AJAX quick-add — used by the "+" button next to the Zone dropdown. */
    public function quickAddZone(Request $request)
    {
        $reseller = auth('mac_reseller')->user();

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:mac_reseller_zones,name,NULL,id,mac_reseller_id,' . $reseller->id,
        ]);

        $zone = MacResellerZone::create([
            'mac_reseller_id' => $reseller->id,
            'name'            => $data['name'],
            'is_active'       => true,
        ]);

        return response()->json(['success' => true, 'id' => $zone->id, 'name' => $zone->name]);
    }

    /** AJAX quick-add — used by the "+" button next to the Client Type dropdown. */
    public function quickAddClientType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:client_types,name',
        ]);

        $clientType = ClientType::create(['name' => $data['name']]);

        return response()->json(['success' => true, 'id' => $clientType->id, 'name' => $clientType->name]);
    }

    private function authorizeClient(Customer $client): void
    {
        abort_unless($client->mac_reseller_id === auth('mac_reseller')->id(), 403);
    }
}