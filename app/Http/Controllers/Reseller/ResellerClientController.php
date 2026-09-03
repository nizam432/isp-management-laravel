<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ClientType;
use App\Models\Customer;
use App\Models\MacResellerSubZone;
use App\Models\MacResellerTariffPackage;
use App\Models\MacResellerZone;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResellerClientController extends Controller
{
    public function index(Request $request)
    {
        $reseller   = Auth::guard('mac_reseller')->user();
        $resellerId = $reseller->id;

        $query = Customer::with(['resellerTariffPackage.package', 'resellerZone', 'resellerSubZone', 'clientType'])
            ->forReseller($resellerId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('customer_code', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('pppoe_username', 'like', "%{$s}%");
            });
        }

        $query->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
              ->when($request->filled('mac_reseller_tariff_package_id'), fn($q) => $q->where('mac_reseller_tariff_package_id', $request->mac_reseller_tariff_package_id))
              ->when($request->filled('client_type_id'), fn($q) => $q->where('client_type_id', $request->client_type_id))
              ->when($request->filled('mac_reseller_zone_id'), fn($q) => $q->where('mac_reseller_zone_id', $request->mac_reseller_zone_id))
              ->when($request->filled('mac_reseller_sub_zone_id'), fn($q) => $q->where('mac_reseller_sub_zone_id', $request->mac_reseller_sub_zone_id))
              ->when($request->filled('billing_date'), fn($q) => $q->where('billing_date', $request->billing_date));

        $perPage = (int) $request->input('per_page', 20);
        $clients = $query->latest()->paginate($perPage)->withQueryString();

        $stats = [
            'total'     => Customer::forReseller($resellerId)->count(),
            'active'    => Customer::forReseller($resellerId)->where('status', 'active')->count(),
            'suspended' => Customer::forReseller($resellerId)->where('status', 'suspended')->count(),
            'expired'   => Customer::forReseller($resellerId)->where('status', 'expired')->count(),
        ];

        // filter dropdown data — all scoped to this reseller
        $packages = $reseller->tariff_id
            ? MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)->with('package')->get()
            : collect();
        $zones       = MacResellerZone::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name']);
        $subZones    = MacResellerSubZone::forReseller($resellerId)->active()->orderBy('name')->get(['id', 'name', 'mac_reseller_zone_id']);
        $clientTypes = ClientType::orderBy('name')->get(['id', 'name']);

        return view('reseller.client.index', compact('clients', 'stats', 'packages', 'zones', 'subZones', 'clientTypes'));
    }

    public function show(Customer $client)
    {
        abort_unless(
            $client->mac_reseller_id === Auth::guard('mac_reseller')->id(),
            403,
            'You do not have access to this client.'
        );

        $client->load([
            'package', 'zone', 'subZone', 'connectionType', 'clientType', 'protocolType',
            'invoices', 'payments',
            // reseller-scoped equivalents (used for clients added via the reseller portal)
            'resellerZone', 'resellerSubZone', 'resellerTariffPackage.package',
        ]);

        return view('reseller.client.show', compact('client'));
    }

    public function updateStatus(Request $request, Customer $client)
    {
        abort_unless($client->mac_reseller_id === Auth::guard('mac_reseller')->id(), 403);

        $data = $request->validate([
            'status' => 'required|in:active,inactive,suspended,expired',
        ]);

        $client->update(['status' => $data['status']]);

        return back()->with('success', 'Status updated.');
    }

    public function create()
    {
        $reseller = Auth::guard('mac_reseller')->user();

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
        $reseller = Auth::guard('mac_reseller')->user();

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

        // Keep the RAW (unhashed) PPPoE password for the MikroTik provisioning
        // call below — Customer.pppoe_password is stored hashed in the DB, but
        // MikroTik needs the actual plaintext value to set on the router.
        $rawPppoePassword = $data['pppoe_password'] ?? null;

        if (!empty($data['pppoe_password'])) {
            $data['pppoe_password'] = Hash::make($data['pppoe_password']);
        }

        $data['customer_code']       = Customer::generateCode();
        $data['mac_reseller_id']     = $reseller->id;
        $data['monthly_bill_amount'] = $data['monthly_bill_amount'] ?? $tariffPackage->rate;
        $data['created_by']          = null; // created by a reseller, not an internal User

        $client = Customer::create($data);

        // ── Provision on MikroTik (best-effort — never blocks client creation) ──
        $mikrotikMessage = null;
        if ($client->pppoe_username && $rawPppoePassword) {
            $client->pppoe_password = $rawPppoePassword; // in-memory only, not saved
            $provisioned = (new MikrotikService())->provisionResellerCustomer($client);
            $client->update(['mikrotik_status' => $provisioned ? 'active' : 'failed']);
            $mikrotikMessage = $provisioned
                ? 'PPPoE user created on MikroTik successfully.'
                : 'Client saved, but MikroTik provisioning was skipped/failed — check the router/server name and try again from the client\'s edit page.';
        }

        return redirect()->route('reseller.client.index')
            ->with('success', 'Client added successfully.' . ($mikrotikMessage ? ' ' . $mikrotikMessage : ''));
    }

    public function edit(Customer $client)
    {
        $reseller = Auth::guard('mac_reseller')->user();
        abort_unless($client->mac_reseller_id === $reseller->id, 403);

        $packages = collect();
        if ($reseller->tariff_id) {
            $packages = MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)
                ->with('package')
                ->get();
        }

        $zones = MacResellerZone::forReseller($reseller->id)->active()->orderBy('name')->get(['id', 'name']);

        $subZones = MacResellerSubZone::forReseller($reseller->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'mac_reseller_zone_id']);

        $clientTypes = ClientType::orderBy('name')->get(['id', 'name']);

        return view('reseller.client.edit', compact('client', 'packages', 'zones', 'subZones', 'clientTypes'));
    }

    public function update(Request $request, Customer $client)
    {
        $reseller = Auth::guard('mac_reseller')->user();
        abort_unless($client->mac_reseller_id === $reseller->id, 403);

        $data = $request->validate([
            'name'                            => 'required|string|max:255',
            'phone'                            => 'required|string|max:20|unique:customers,phone,' . $client->id,
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
        MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)
            ->findOrFail($data['mac_reseller_tariff_package_id']);

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

        // keep existing password if a new one wasn't provided
        $rawPppoePassword = $data['pppoe_password'] ?? null;
        if (!empty($data['pppoe_password'])) {
            $data['pppoe_password'] = Hash::make($data['pppoe_password']);
        } else {
            unset($data['pppoe_password']);
        }

        $client->update($data);

        // ── Re-provision on MikroTik only when a new plaintext password was actually submitted ──
        $mikrotikMessage = null;
        if ($client->pppoe_username && $rawPppoePassword) {
            $client->pppoe_password = $rawPppoePassword; // in-memory only, not saved
            $provisioned = (new MikrotikService())->provisionResellerCustomer($client);
            $client->update(['mikrotik_status' => $provisioned ? 'active' : 'failed']);
            $mikrotikMessage = $provisioned
                ? 'PPPoE user updated on MikroTik successfully.'
                : 'Client updated, but MikroTik provisioning was skipped/failed — check the router/server name.';
        }

        return redirect()->route('reseller.client.show', $client)
            ->with('success', 'Client updated successfully.' . ($mikrotikMessage ? ' ' . $mikrotikMessage : ''));
    }

    /** AJAX — MikroTik Info modal: live account (PPP secret) + session info for this client. */
    public function mikrotikInfo(Customer $client)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($client->mac_reseller_id === $resellerId, 403);

        if (empty($client->pppoe_username)) {
            return response()->json(['success' => false, 'message' => 'This client has no PPPoE username set.']);
        }

        $router = $this->resolveRouter($client);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Could not determine which MikroTik router this client belongs to (check the Server Name on its Tariff Package).']);
        }

        try {
            $mikrotik = new MikrotikService();

            [$account, $session] = $mikrotik->withRouter($router, function ($m) use ($client) {
                return [
                    $m->getPPPoEUserByName($client->pppoe_username),
                    $m->getCustomerSession($client->pppoe_username),
                ];
            });

            if (!$account) {
                return response()->json(['success' => false, 'message' => "'{$client->pppoe_username}' was not found on {$router->name}."]);
            }

            return response()->json([
                'success' => true,
                'router'  => $router->name,
                'account' => [
                    'username' => $account['name']    ?? $client->pppoe_username,
                    'profile'  => $account['profile']  ?? '—',
                    'status'   => ($account['disabled'] ?? 'false') === 'true' ? 'Disabled' : 'Active',
                    'comment'  => $account['comment']  ?? '',
                ],
                'session' => $session ? [
                    'online'  => true,
                    'ip'      => $session['address']   ?? '—',
                    'mac'     => $session['caller-id']  ?? '—',
                    'uptime'  => $session['uptime']     ?? '—',
                ] : [
                    'online' => false,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error("Reseller MikroTik Info failed for client #{$client->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not connect to the router. Please try again.']);
        }
    }

    /** Suspend this client's PPPoE account on MikroTik (does not change billing status). */
    public function mikrotikSuspend(Customer $client)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($client->mac_reseller_id === $resellerId, 403);

        $router = $this->resolveRouter($client);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Could not determine which MikroTik router this client belongs to.']);
        }

        try {
            (new MikrotikService())->withRouter($router, fn($m) => $m->suspendCustomer($client));
            return response()->json(['success' => true, 'message' => 'Client suspended on MikroTik.']);
        } catch (\Exception $e) {
            \Log::error("Reseller MikroTik suspend failed for client #{$client->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to suspend on the router.']);
        }
    }

    /** Re-enable this client's PPPoE account on MikroTik. */
    public function mikrotikEnable(Customer $client)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($client->mac_reseller_id === $resellerId, 403);

        $router = $this->resolveRouter($client);
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'Could not determine which MikroTik router this client belongs to.']);
        }

        try {
            (new MikrotikService())->withRouter($router, fn($m) => $m->restoreCustomer($client));
            return response()->json(['success' => true, 'message' => 'Client re-enabled on MikroTik.']);
        } catch (\Exception $e) {
            \Log::error("Reseller MikroTik enable failed for client #{$client->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to enable on the router.']);
        }
    }

    /** Resolve which MikrotikRouter a reseller client belongs to, via its Tariff Package's server_name. */
    private function resolveRouter(Customer $client): ?\App\Models\MikrotikRouter
    {
        $client->loadMissing('resellerTariffPackage');
        $serverName = $client->resellerTariffPackage->server_name ?? null;
        if (!$serverName) return null;

        return \App\Models\MikrotikRouter::where('name', $serverName)->first();
    }

    /** AJAX quick-add — used by the "+" button next to the Zone dropdown. */
    public function quickAddZone(Request $request)
    {
        $reseller = Auth::guard('mac_reseller')->user();

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
}