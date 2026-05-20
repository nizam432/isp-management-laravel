<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Agent;
use App\Models\ActivityLog;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with(['package', 'agent'])
            ->when($request->search, fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('customer_code', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->area, fn($q) => $q->where('area', $request->area))
            ->latest()
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $packages = Package::active()->get();
        $agents   = Agent::active()->get();
        return view('customers.create', compact('packages', 'agents'));
    }

    /**
     * Store করার পর automatically MikroTik এ PPPoE user তৈরি হবে।
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'phone'           => 'required|string|max:20|unique:customers',
            'package_id'      => 'required|exists:packages,id',
            'address'         => 'nullable|string',
            'area'            => 'nullable|string|max:100',
            'billing_date'    => 'required|integer|min:1|max:28',
            'connection_date' => 'nullable|date',
            'status'          => 'required|in:active,inactive,suspended,expired',
            'pppoe_username'  => 'nullable|string|max:50|unique:customers',
            'pppoe_password'  => 'nullable|string|max:50',
        ]);

        $data = $request->all();
        $data['customer_code'] = Customer::generateCode();
        $data['created_by']    = auth()->id();

        // PPPoE username না দিলে auto-generate
        if (empty($data['pppoe_username'])) {
            $data['pppoe_username'] = 'isp_' . strtolower(preg_replace('/\s+/', '', $request->name)) . '_' . rand(100, 999);
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

        // ── Auto-Provision MikroTik ──────────────────
        if ($customer->status === 'active') {
            $this->provisionToMikrotik($customer);
        }

        ActivityLog::log('Customer created', 'Customer', $customer->id, null, $customer->toArray());

        return redirect()->route('customers.show', $customer)
                         ->with('success', 'Customer added successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['package', 'agent', 'invoices', 'payments', 'tickets']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $packages = Package::active()->get();
        $agents   = Agent::active()->get();
        return view('customers.edit', compact('customer', 'packages', 'agents'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'package_id'   => 'required|exists:packages,id',
            'billing_date' => 'required|integer|min:1|max:28',
            'status'       => 'required|in:active,inactive,suspended,expired',
        ]);

        $old        = $customer->toArray();
        $oldPackage = $customer->package_id;
        $data       = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('customers/photos', 'public');
        }
        if ($request->hasFile('nid_photo')) {
            $data['nid_photo'] = $request->file('nid_photo')->store('customers/nid', 'public');
        }

        $customer->update($data);

        // Package পরিবর্তন হলে MikroTik এ update করো
        if ($oldPackage !== $customer->package_id && $customer->mikrotik_status === 'active') {
            try {
                $router = MikrotikRouter::where('is_active', 1)->first();
                if ($router) {
                    $mikrotik = new MikrotikService();
                    $mikrotik->withRouter($router, fn($m) => $m->changeCustomerPackage($customer));
                }
            } catch (\Exception $e) {
                Log::warning("MikroTik package update failed: " . $e->getMessage());
            }
        }

        ActivityLog::log('Customer updated', 'Customer', $customer->id, $old, $customer->toArray());

        return redirect()->route('customers.show', $customer)
                         ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        // MikroTik থেকেও remove করো
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if ($router && $customer->mikrotik_status === 'active') {
                $mikrotik = new MikrotikService();
                $mikrotik->withRouter($router, fn($m) => $m->removeCustomer($customer));
            }
        } catch (\Exception $e) {
            Log::warning("MikroTik remove failed: " . $e->getMessage());
        }

        ActivityLog::log('Customer deleted', 'Customer', $customer->id, $customer->toArray(), null);
        $customer->delete();

        return redirect()->route('customers.index')
                         ->with('success', 'Customer deleted successfully.');
    }

    public function updateStatus(Request $request, Customer $customer)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended,expired',
        ]);

        $old = $customer->status;
        $customer->update(['status' => $request->status]);

        // Status পরিবর্তনে MikroTik sync
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if ($router) {
                $mikrotik = new MikrotikService();
                match ($request->status) {
                    'active'    => $mikrotik->withRouter($router, fn($m) => $m->restoreCustomer($customer)),
                    'suspended' => $mikrotik->withRouter($router, fn($m) => $m->suspendCustomer($customer)),
                    default     => null,
                };
            }
        } catch (\Exception $e) {
            Log::warning("MikroTik status sync failed: " . $e->getMessage());
        }

        ActivityLog::log(
            "Customer status changed: {$old} -> {$request->status}",
            'Customer',
            $customer->id
        );

        return back()->with('success', 'Status updated successfully.');
    }

    // ══════════════════════════════════════════════
    // Private Helper
    // ══════════════════════════════════════════════

    private function provisionToMikrotik(Customer $customer): void
    {
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if (!$router) return;

            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->provisionCustomer($customer));
            $customer->update(['mikrotik_status' => 'active']);

            Log::info("Auto-provisioned customer {$customer->customer_code} on MikroTik.");
        } catch (\Exception $e) {
            // MikroTik fail হলেও customer save হবে — শুধু log করো
            Log::warning("MikroTik auto-provision failed for {$customer->customer_code}: " . $e->getMessage());
            $customer->update(['mikrotik_status' => 'pending']);
        }
    }
}