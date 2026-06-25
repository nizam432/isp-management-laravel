<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Vendor;
use App\Models\Inventory\VendorContact;
use Illuminate\Http\Request;

class VendorContactController extends Controller
{
    public function store(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone'       => 'required|string|max:20',
            'email'       => 'nullable|email|max:255',
            'is_primary'  => 'boolean',
        ]);

        // অন্য contact থেকে primary সরিয়ে নাও
        if ($request->is_primary) {
            $vendor->contacts()->update(['is_primary' => false]);
        }

        $vendor->contacts()->create($request->only([
            'name', 'designation', 'phone', 'email', 'is_primary',
        ]));

        return back()->with('success', 'Contact added successfully.');
    }

    public function update(Request $request, Vendor $vendor, VendorContact $contact)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone'       => 'required|string|max:20',
            'email'       => 'nullable|email|max:255',
            'is_primary'  => 'boolean',
        ]);

        if ($request->is_primary) {
            $vendor->contacts()->where('id', '!=', $contact->id)->update(['is_primary' => false]);
        }

        $contact->update($request->only(['name', 'designation', 'phone', 'email', 'is_primary']));

        return back()->with('success', 'Contact updated successfully.');
    }

    public function destroy(Vendor $vendor, VendorContact $contact)
    {
        $contact->delete();

        return back()->with('success', 'Contact deleted successfully.');
    }
}
