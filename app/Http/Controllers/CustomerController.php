<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Agent;
use App\Models\Zone;
use App\Models\SubZone;
use App\Models\ConnectionType;
use App\Models\ClientType;
use App\Models\ProtocolType;
use App\Models\MikrotikRouter;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    // =========================================================================
    // Customer List
    // =========================================================================

    public function index(Request $request)
    {
        $customers = Customer::with(['package', 'agent', 'zone', 'subZone', 'router', 'clientType', 'connectionType', 'protocolType'])
            ->when($request->search, fn($q) => $q
                ->where('name',           'like', "%{$request->search}%")
                ->orWhere('phone',         'like', "%{$request->search}%")
                ->orWhere('customer_code', 'like', "%{$request->search}%"))
            ->when($request->status,             fn($q) => $q->where('status',             $request->status))
            ->when($request->package_id,         fn($q) => $q->where('package_id',         $request->package_id))
            ->when($request->billing_date,       fn($q) => $q->where('billing_date',       $request->billing_date))
            ->when($request->router_id,          fn($q) => $q->where('router_id',          $request->router_id))
            ->when($request->client_type_id,     fn($q) => $q->where('client_type_id',     $request->client_type_id))
            ->when($request->zone_id,            fn($q) => $q->where('zone_id',            $request->zone_id))
            ->when($request->sub_zone_id,        fn($q) => $q->where('sub_zone_id',        $request->sub_zone_id))
            ->when($request->protocol_type_id,   fn($q) => $q->where('protocol_type_id',   $request->protocol_type_id))
            ->when($request->connection_type_id, fn($q) => $q->where('connection_type_id', $request->connection_type_id))
            ->when($request->agent_id,           fn($q) => $q->where('agent_id',           $request->agent_id))
            ->latest()
            ->paginate(20);

        $totalCustomers     = Customer::count();
        $activeCustomers    = Customer::where('status', 'active')->count();
        $suspendedCustomers = Customer::where('status', 'suspended')->count();
        $expiredCustomers   = Customer::where('status', 'expired')->count();

        $packages        = Package::active()->get();
        $routers         = \App\Models\MikrotikRouter::where('is_active', 1)->get();
        $clientTypes     = \App\Models\ClientType::active()->get();
        $zones           = \App\Models\Zone::active()->get();
        $subZones        = \App\Models\SubZone::active()->get();
        $protocolTypes   = \App\Models\ProtocolType::active()->get();
        $connectionTypes = \App\Models\ConnectionType::active()->get();
        $agents          = \App\Models\Agent::active()->get();
        $smsTemplates    = \App\Models\SmsTemplate::active()->get();

        return view('customers.index', compact(
            'customers',
            'totalCustomers', 'activeCustomers', 'suspendedCustomers', 'expiredCustomers',
            'packages', 'routers', 'clientTypes', 'zones', 'subZones',
            'protocolTypes', 'connectionTypes', 'agents', 'smsTemplates'
        ));
    }

    // =========================================================================
    // Create Customer
    // =========================================================================

    public function create()
    {
        $packages        = Package::active()->get();
        $agents          = Agent::active()->get();
        $zones           = Zone::active()->get();
        $connectionTypes = ConnectionType::active()->get();
        $clientTypes     = ClientType::active()->get();
        $protocolTypes   = ProtocolType::active()->get();
        $routers         = MikrotikRouter::active()->get();

        return view('customers.create', compact(
            'packages', 'agents', 'zones',
            'connectionTypes', 'clientTypes', 'protocolTypes', 'routers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Personal
            'name'               => 'required|string|max:100',
            'phone'              => 'required|string|max:20|unique:customers',
            'email'              => 'nullable|email|max:150',
            'address'            => 'nullable|string',
            'occupation'         => 'nullable|string|max:100',
            'gender'             => 'nullable|in:male,female,other',
            'nid_number'         => 'nullable|string|max:30',
            'photo'              => 'nullable|image|max:2048',
            'nid_photo'          => 'nullable|image|max:2048',
            // Service
            'package_id'         => 'required|exists:packages,id',
            'client_type_id'     => 'required|exists:client_types,id',
            'zone_id'            => 'nullable|exists:zones,id',
            'sub_zone_id'        => 'nullable|exists:sub_zones,id',
            'connection_type_id' => 'nullable|exists:connection_types,id',
            'billing_status'     => 'required|in:active,inactive,left,free',
            'billing_date'       => 'required|integer|min:1|max:28',
            'connection_date'    => 'required|date',
            'monthly_bill_amount'=> 'nullable|numeric|min:0',
            // Network
            'router_id'          => 'nullable|exists:mikrotik_routers,id',
            'protocol_type_id'   => 'nullable|exists:protocol_types,id',
            'pppoe_username'     => 'nullable|string|max:50|unique:customers',
            'pppoe_password'     => 'nullable|string|max:100',
            'ip_address'         => 'nullable|string|max:20',
            'mac_address'        => 'nullable|string|max:20',
            'status'             => 'required|in:active,inactive,suspended,expired',
        ]);

        $data                  = $request->except(['photo', 'nid_photo', '_token']);
        $data['customer_code'] = Customer::generateCode();
        $data['created_by']    = auth()->id();
        $data['portal_password'] = Hash::make($request->phone);

        // Monthly bill — default to package price
        if (empty($data['monthly_bill_amount'])) {
            $package = Package::find($request->package_id);
            $data['monthly_bill_amount'] = $package?->price ?? 0;
        }

        // Auto PPPoE if not provided
        if (empty($data['pppoe_username'])) {
            $data['pppoe_username'] = 'isp_'
                . strtolower(preg_replace('/\s+/', '', $request->name))
                . '_' . rand(100, 999);
        }
        if (empty($data['pppoe_password'])) {
            $data['pppoe_password'] = 'pass' . rand(10000, 99999);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('customers/photos', 'public');
        }
        if ($request->hasFile('nid_photo')) {
            $data['nid_photo'] = $request->file('nid_photo')->store('customers/nid', 'public');
        }

        $customer = Customer::create($data);

        if ($customer->status === 'active') {
            $this->provisionToMikrotik($customer);
        }

        ActivityLog::log('Customer created', 'Customer', $customer->id, null, $customer->toArray());

        return redirect()->route('customers.show', $customer)
                         ->with('success', 'Customer added successfully.');
    }

    // =========================================================================
    // Show Customer
    // =========================================================================

    public function show(Customer $customer)
    {
        $customer->load([
            'package', 'agent', 'zone', 'subZone',
            'connectionType', 'clientType', 'protocolType',
            'router', 'invoices', 'payments', 'tickets',
        ]);
        return view('customers.show', compact('customer'));
    }

    // =========================================================================
    // Edit Customer
    // =========================================================================

    public function edit(Customer $customer)
    {
        $packages        = Package::active()->get();
        $agents          = Agent::active()->get();
        $zones           = Zone::active()->get();
        $subZones        = SubZone::where('zone_id', $customer->zone_id)->active()->get();
        $connectionTypes = ConnectionType::active()->get();
        $clientTypes     = ClientType::active()->get();
        $protocolTypes   = ProtocolType::active()->get();
        $routers         = MikrotikRouter::active()->get();

        return view('customers.edit', compact(
            'customer', 'packages', 'agents', 'zones', 'subZones',
            'connectionTypes', 'clientTypes', 'protocolTypes', 'routers'
        ));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'               => 'required|string|max:100',
            'phone'              => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email'              => 'nullable|email|max:150',
            'address'            => 'nullable|string',
            'occupation'         => 'nullable|string|max:100',
            'gender'             => 'nullable|in:male,female,other',
            'nid_number'         => 'nullable|string|max:30',
            'package_id'         => 'required|exists:packages,id',
            'client_type_id'     => 'required|exists:client_types,id',
            'zone_id'            => 'nullable|exists:zones,id',
            'sub_zone_id'        => 'nullable|exists:sub_zones,id',
            'connection_type_id' => 'nullable|exists:connection_types,id',
            'billing_status'     => 'required|in:active,inactive,left,free',
            'billing_date'       => 'required|integer|min:1|max:28',
            'connection_date'    => 'required|date',
            'monthly_bill_amount'=> 'nullable|numeric|min:0',
            'router_id'          => 'nullable|exists:mikrotik_routers,id',
            'protocol_type_id'   => 'nullable|exists:protocol_types,id',
            'status'             => 'required|in:active,inactive,suspended,expired',
        ]);

        $old         = $customer->toArray();
        $oldPackage  = $customer->package_id;
        $oldMkStatus = $customer->mikrotik_status;
        $newStatus   = $request->input('status', $customer->status);
        $data        = $request->except(['photo', 'nid_photo', '_token', '_method']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('customers/photos', 'public');
        }
        if ($request->hasFile('nid_photo')) {
            $data['nid_photo'] = $request->file('nid_photo')->store('customers/nid', 'public');
        }

        $customer->update($data);
        $customer->refresh();

        // MikroTik sync
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if ($router) {
                $mikrotik = new MikrotikService();

                match ($newStatus) {
                    'active' => $mikrotik->withRouter($router, function ($m) use ($customer) {
                        $exists = collect($m->getPPPoEUsers())->firstWhere('name', $customer->pppoe_username);
                        $exists ? $m->restoreCustomer($customer) : $m->provisionCustomer($customer);
                    }),
                    'suspended', 'expired', 'inactive' =>
                        $mikrotik->withRouter($router, fn($m) => $m->suspendCustomer($customer)),
                    default => null,
                };

                // Router থেকে actual status check করে mikrotik_status set করো
                try {
                    $mikrotik->withRouter($router, function ($m) use ($customer) {
                        $user = $m->getPPPoEUserByName($customer->pppoe_username);

                        if (!$user) {
                            $customer->update(['mikrotik_status' => 'removed']);
                        } elseif (isset($user['disabled']) && $user['disabled'] === 'true') {
                            $customer->update(['mikrotik_status' => 'suspended']);
                        } else {
                            $customer->update(['mikrotik_status' => 'active']);
                        }
                    });
                } catch (\Exception $e) {
                    // Check fail হলে assumed status দিয়ে রাখো
                    $customer->update(['mikrotik_status' => match ($newStatus) {
                        'active'                           => 'active',
                        'suspended', 'expired', 'inactive' => 'suspended',
                        default                            => $oldMkStatus,
                    }]);
                }

                if ($oldPackage !== $customer->package_id && $newStatus === 'active') {
                    $mikrotik->withRouter($router, fn($m) => $m->changeCustomerPackage($customer));
                }
            }
        } catch (\Exception $e) {
            Log::warning('MikroTik sync failed: ' . $e->getMessage());
        }

        ActivityLog::log('Customer updated', 'Customer', $customer->id, $old, $customer->toArray());

        return redirect()->route('customers.show', $customer)
                         ->with('success', 'Customer updated successfully.');
    }

    // =========================================================================
    // Status Update
    // =========================================================================

    public function updateStatus(Request $request, Customer $customer)
    {
        $request->validate(['status' => 'required|in:active,inactive,suspended,expired']);
        
        $customer->update(['status' => $request->status]);
        return back()->with('success', 'Status updated.');
    }

    // =========================================================================
    // Delete Customer
    // =========================================================================

    public function destroy(Customer $customer)
    {
       /*  try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if ($router) {
                $mikrotik = new MikrotikService();
                $mikrotik->withRouter($router, fn($m) => $m->removeCustomer($customer));
            }
        } catch (\Exception $e) {
            Log::warning('MikroTik remove failed: ' . $e->getMessage());
        }

        ActivityLog::log('Customer deleted', 'Customer', $customer->id, $customer->toArray(), null);
        $customer->delete();

        return redirect()->route('customers.index')
                         ->with('success', 'Customer deleted successfully.'); */
    }

    // =========================================================================
    // AJAX Helpers
    // =========================================================================

    // Zone select করলে SubZone load
    public function getSubZones(Request $request)
    {
        $subZones = SubZone::where('zone_id', $request->zone_id)
                           ->where('is_active', true)
                           ->get(['id', 'name']);
        return response()->json($subZones);
    }

    // Package select করলে price load
    public function getPackagePrice(Request $request)
    {
        $package = Package::find($request->package_id);
        return response()->json(['price' => $package?->price ?? 0]);
    }

    // ── Modal AJAX: Zone quick add ────────────────────────
    public function quickAddZone(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:zones,name']);
        $zone = Zone::create(['name' => $request->name, 'is_active' => true]);
        return response()->json(['success' => true, 'id' => $zone->id, 'name' => $zone->name]);
    }

    // ── Modal AJAX: ConnectionType quick add ──────────────
    public function quickAddConnectionType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:connection_types,name']);
        $ct = ConnectionType::create(['name' => $request->name, 'is_active' => true]);
        return response()->json(['success' => true, 'id' => $ct->id, 'name' => $ct->name]);
    }

    // ── Modal AJAX: ClientType quick add ─────────────────
    public function quickAddClientType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:client_types,name']);
        $ct = ClientType::create(['name' => $request->name, 'is_active' => true]);
        return response()->json(['success' => true, 'id' => $ct->id, 'name' => $ct->name]);
    }

    // ── Modal AJAX: ProtocolType quick add ───────────────
    public function quickAddProtocolType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:protocol_types,name']);
        $pt = ProtocolType::create(['name' => $request->name, 'is_active' => true]);
        return response()->json(['success' => true, 'id' => $pt->id, 'name' => $pt->name]);
    }

    // =========================================================================
    // MikroTik Provision
    // =========================================================================

    protected function provisionToMikrotik(Customer $customer)
    {
        try {
            $router = $customer->router ?? MikrotikRouter::active()->first();
            if (!$router) return;
            $service = new MikrotikService($router);
            $service->addPppoeUser(
                $customer->pppoe_username,
                $customer->pppoe_password,
                $customer->package->mikrotik_profile ?? 'default'
            );
        } catch (\Exception $e) {
            Log::error('MikroTik provision failed: ' . $e->getMessage());
        }
    }

public function mikrotikInfo(Customer $customer)
{
    try {
        $router = $customer->router ?? MikrotikRouter::where('is_active', 1)->first();

        if (!$router) {
            return response()->json(['success' => false, 'message' => 'No router found.']);
        }

        $mikrotik = new MikrotikService();

        $account = $mikrotik->withRouter($router, function($m) use ($customer) {
            $users = $m->getPPPoEUsers();
            return collect($users)->firstWhere('name', $customer->pppoe_username);
        });

        $session = null;
        try {
            $session = $mikrotik->withRouter($router, fn($m) => $m->getCustomerSession($customer->pppoe_username));
        } catch (\Exception $e) {
            // session না পেলেও চলবে
        }

        return response()->json([
            'success' => true,
            'account' => $account ?? [],
            'session' => $session ?? [],
            'router'  => $router->name,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}  

}