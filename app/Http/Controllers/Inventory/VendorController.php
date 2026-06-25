<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $vendors = Vendor::withCount('purchases')
                    ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                                                          ->orWhere('phone', 'like', '%' . $request->search . '%'))
                    ->when($request->status, fn($q) => $q->where('status', $request->status))
                    ->latest()
                    ->paginate(20);

        return view('inventory.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('inventory.vendors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'owner_name'      => 'nullable|string|max:255',
            'phone'           => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'area'            => 'nullable|string|max:255',
            'district'        => 'nullable|string|max:255',
            'vendor_type'     => 'required|in:supplier,manufacturer,both',
            'business_type'   => 'nullable|string|max:255',
            'trade_license'   => 'nullable|string|max:255',
            'tin_no'          => 'nullable|string|max:255',
            'bin_no'          => 'nullable|string|max:255',
            'bank_name'       => 'nullable|string|max:255',
            'bank_account'    => 'nullable|string|max:255',
            'bank_branch'     => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit'    => 'nullable|numeric|min:0',
            'note'            => 'nullable|string',
        ]);

        Vendor::create([
            ...$request->only([
                'name', 'owner_name', 'phone', 'alternate_phone', 'email',
                'address', 'area', 'district', 'vendor_type', 'business_type',
                'trade_license', 'tin_no', 'bin_no', 'bank_name',
                'bank_account', 'bank_branch', 'opening_balance',
                'credit_limit', 'note',
            ]),
            'status'     => 'active',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('inventory.vendors.index')
                         ->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load('contacts', 'documents', 'purchases');

        $ledger = $vendor->ledger()->orderBy('date')->get();

        return view('inventory.vendors.show', compact('vendor', 'ledger'));
    }

    public function edit(Vendor $vendor)
    {
        return view('inventory.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'owner_name'      => 'nullable|string|max:255',
            'phone'           => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'area'            => 'nullable|string|max:255',
            'district'        => 'nullable|string|max:255',
            'vendor_type'     => 'required|in:supplier,manufacturer,both',
            'business_type'   => 'nullable|string|max:255',
            'trade_license'   => 'nullable|string|max:255',
            'tin_no'          => 'nullable|string|max:255',
            'bin_no'          => 'nullable|string|max:255',
            'bank_name'       => 'nullable|string|max:255',
            'bank_account'    => 'nullable|string|max:255',
            'bank_branch'     => 'nullable|string|max:255',
            'credit_limit'    => 'nullable|numeric|min:0',
            'status'          => 'required|in:active,inactive,blacklisted',
            'note'            => 'nullable|string',
        ]);

        $vendor->update($request->only([
            'name', 'owner_name', 'phone', 'alternate_phone', 'email',
            'address', 'area', 'district', 'vendor_type', 'business_type',
            'trade_license', 'tin_no', 'bin_no', 'bank_name',
            'bank_account', 'bank_branch', 'credit_limit', 'status', 'note',
        ]));

        return redirect()->route('inventory.vendors.show', $vendor)
                         ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->purchases()->exists()) {
            return back()->with('error', 'This vendor has purchases and cannot be deleted.');
        }

        $vendor->delete();

        return redirect()->route('inventory.vendors.index')
                         ->with('success', 'Vendor deleted successfully.');
    }

    public function ledger(Vendor $vendor)
    {
        $ledger = $vendor->ledger()->orderBy('date')->paginate(30);

        return view('inventory.vendors.ledger', compact('vendor', 'ledger'));
    }
}
