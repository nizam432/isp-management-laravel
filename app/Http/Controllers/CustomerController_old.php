<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Agent;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a paginated list of customers.
     * Supports filtering by search keyword, status, and area.
     */
    public function index(Request $request)
    {
        $customers = Customer::with(['package', 'agent'])
            // Search by name, phone, or customer code
            ->when($request->search, fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('customer_code', 'like', "%{$request->search}%"))
            // Filter by status: active / inactive / suspended / expired
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            // Filter by area
            ->when($request->area, fn($q) => $q->where('area', $request->area))
            ->latest()
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        // Only show active packages and agents
        $packages = Package::active()->get();
        $agents   = Agent::active()->get();

        return view('customers.create', compact('packages', 'agents'));
    }

    /**
     * Store a newly created customer in the database.
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
        ]);

        $data = $request->all();

        // Auto-generate unique customer code e.g. ISP-0001
        $data['customer_code'] = Customer::generateCode();

        // Track which user created this record
        $data['created_by'] = auth()->id();

        // Handle profile photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('customers/photos', 'public');
        }

        // Handle NID photo upload
        if ($request->hasFile('nid_photo')) {
            $data['nid_photo'] = $request->file('nid_photo')->store('customers/nid', 'public');
        }

        $customer = Customer::create($data);

        ActivityLog::log('Customer created', 'Customer', $customer->id, null, $customer->toArray());

        return redirect()->route('customers.show', $customer)
                         ->with('success', 'Customer added successfully.');
    }

    /**
     * Display the specified customer with related data.
     */
    public function show(Customer $customer)
    {
        // Eager load all related data
        $customer->load(['package', 'agent', 'invoices', 'payments', 'tickets']);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        $packages = Package::active()->get();
        $agents   = Agent::active()->get();

        return view('customers.edit', compact('customer', 'packages', 'agents'));
    }

    /**
     * Update the specified customer in the database.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            // Exclude current customer's own phone from unique check
            'phone'        => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'package_id'   => 'required|exists:packages,id',
            'billing_date' => 'required|integer|min:1|max:28',
            'status'       => 'required|in:active,inactive,suspended,expired',
        ]);

        // Save old values for activity log
        $old  = $customer->toArray();
        $data = $request->all();

        // Replace photo if a new one is uploaded
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('customers/photos', 'public');
        }

        if ($request->hasFile('nid_photo')) {
            $data['nid_photo'] = $request->file('nid_photo')->store('customers/nid', 'public');
        }

        $customer->update($data);

        ActivityLog::log('Customer updated', 'Customer', $customer->id, $old, $customer->toArray());

        return redirect()->route('customers.show', $customer)
                         ->with('success', 'Customer updated successfully.');
    }

    /**
     * Soft delete the specified customer.
     */
    public function destroy(Customer $customer)
    {
        ActivityLog::log('Customer deleted', 'Customer', $customer->id, $customer->toArray(), null);

        // Uses SoftDeletes — record is not permanently removed
        $customer->delete();

        return redirect()->route('customers.index')
                         ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Update the connection status of a customer.
     */
    public function updateStatus(Request $request, Customer $customer)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended,expired',
        ]);

        $old = $customer->status;
        $customer->update(['status' => $request->status]);

        ActivityLog::log(
            "Customer status changed: {$old} -> {$request->status}",
            'Customer',
            $customer->id
        );

        return back()->with('success', 'Status updated successfully.');
    }
}
