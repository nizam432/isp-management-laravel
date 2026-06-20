<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResellerConfigurationController extends Controller
{
    public function index()
    {
        $reseller = Auth::guard('mac_reseller')->user();
        return view('reseller.configuration.index', compact('reseller'));
    }

    public function update(Request $request)
    {
        $reseller = Auth::guard('mac_reseller')->user();

        $data = $request->validate([
            'contact_person' => 'required|string|max:255',
            'email'          => 'nullable|email',
            'mobile'         => 'required|string|max:20',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'required|string',
            'logo'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('mac-reseller/logos', 'public');
        }

        $reseller->update($data);

        return redirect()->route('reseller.configuration.index')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $reseller = Auth::guard('mac_reseller')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $reseller->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $reseller->update(['password' => Hash::make($request->password)]);

        return redirect()->route('reseller.configuration.index')
            ->with('success', 'Password updated successfully.');
    }
}
