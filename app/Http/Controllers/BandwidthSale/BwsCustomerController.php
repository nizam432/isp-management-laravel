<?php

namespace App\Http\Controllers\BandwidthSale;

use App\Http\Controllers\Controller;
use App\Models\BandwidthSaleCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BwsCustomerController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────────
    public function index()
    {
        $customers = BandwidthSaleCustomer::withCount('invoices')
            ->orderBy('customer_name')
            ->paginate(request('per_page', 10));

        return view('bandwidth-sale.customers.index', compact('customers'));
    }

    // ── DATA (AJAX DataTable) ─────────────────────────────────────
    public function data()
    {
        $customers = BandwidthSaleCustomer::select([
            'id', 'customer_code', 'customer_name', 'contact_person',
            'email', 'mobile_number', 'balance_due',
            'pop_status', 'activity_status',
        ])->orderBy('customer_name')->get();

        return response()->json(['data' => $customers]);
    }

    // ── STORE ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:150',
            'mobile_number'  => 'required|string|max:20',
            'pop_status'     => 'required|in:active,inactive',
            'email'          => 'nullable|email|max:150',
            'username'       => 'nullable|string|max:100|unique:bandwidth_sale_customers,username',
        ]);

        $data = $request->except(['password', '_token']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->filled('vlan_info')) {
            $data['vlan_info'] = is_string($request->vlan_info)
                ? json_decode($request->vlan_info, true)
                : $request->vlan_info;
        }
        if ($request->filled('ip_addresses')) {
            $data['ip_addresses'] = is_string($request->ip_addresses)
                ? json_decode($request->ip_addresses, true)
                : $request->ip_addresses;
        }

        $data['created_by'] = auth()->id();

        $customer = BandwidthSaleCustomer::create($data);

        return response()->json([
            'success'  => true,
            'message'  => "Customer {$customer->customer_name} created.",
            'customer' => [
                'id'             => $customer->id,
                'customer_code'  => $customer->customer_code,
                'customer_name'  => $customer->customer_name,
                'contact_person' => $customer->contact_person,
                'email'          => $customer->email,
                'mobile_number'  => $customer->mobile_number,
                'balance_due'    => number_format($customer->balance_due, 2),
            ],
        ]);
    }

    // ── SHOW — returns JSON for AJAX edit, view for direct access ──
    public function show(Request $request, BandwidthSaleCustomer $customer)
    {
        // AJAX request → return JSON for edit modal
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'customer' => [
                    'id'              => $customer->id,
                    'customer_code'   => $customer->customer_code,
                    'customer_name'   => $customer->customer_name,
                    'contact_person'  => $customer->contact_person,
                    'email'           => $customer->email,
                    'mobile_number'   => $customer->mobile_number,
                    'phone_number'    => $customer->phone_number,
                    'pop_status'      => $customer->pop_status,
                    'reference_by'    => $customer->reference_by,
                    'address'         => $customer->address,
                    'facebook_url'    => $customer->facebook_url,
                    'skype_id'        => $customer->skype_id,
                    'website'         => $customer->website,
                    'remarks'         => $customer->remarks,
                    'attn_info'       => $customer->attn_info,
                    'bzr_dr_nas_id'   => $customer->bzr_dr_nas_id,
                    'activation_date' => $customer->activation_date?->format('Y-m-d'),
                    'pop_info'        => $customer->pop_info,
                    'vlan_info'       => $customer->vlan_info ?? [],
                    'ip_addresses'    => $customer->ip_addresses ?? [],
                    'username'        => $customer->username,
                    'activity_status' => $customer->activity_status,
                ],
            ]);
        }

        // Normal request → show page (modal)
        $customer->load(['createdBy']);
        $invoices      = $customer->invoices()->latest()->get();
        $payments      = $customer->payments()->with(['bwsInvoice', 'receivedBy'])->latest()->get();
        $totalPaid     = $payments->where('status', 'active')->sum('received_amount');
        $totalPayments = $payments->where('status', 'active')->count();

        return view('bandwidth-sale.customers.show', compact(
            'customer', 'invoices', 'payments', 'totalPaid', 'totalPayments'
        ));
    }

    // ── UPDATE ────────────────────────────────────────────────────
    public function update(Request $request, BandwidthSaleCustomer $customer)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:150',
            'mobile_number'  => 'required|string|max:20',
            'pop_status'     => 'required|in:active,inactive',
            'email'          => 'nullable|email|max:150',
            'username'       => 'nullable|string|max:100|unique:bandwidth_sale_customers,username,' . $customer->id,
        ]);

        $data = $request->except(['password', '_token', '_method']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->filled('vlan_info')) {
            $data['vlan_info'] = is_string($request->vlan_info)
                ? json_decode($request->vlan_info, true)
                : $request->vlan_info;
        }
        if ($request->filled('ip_addresses')) {
            $data['ip_addresses'] = is_string($request->ip_addresses)
                ? json_decode($request->ip_addresses, true)
                : $request->ip_addresses;
        }

        $customer->update($data);

        return response()->json([
            'success' => true,
            'message' => "Customer {$customer->customer_name} updated.",
        ]);
    }

    // ── DESTROY ───────────────────────────────────────────────────
    public function destroy(BandwidthSaleCustomer $customer)
    {
        if ($customer->hasPurchase()) {
            return response()->json([
                'success' => false,
                'message' => "'{$customer->customer_name}' এর invoice আছে — delete করা যাবে না।",
            ], 422);
        }

        $name = $customer->customer_name;
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => "Customer '{$name}' deleted.",
        ]);
    }
}
