<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BandwidthProviderController extends Controller
{
    public function index(Request $request)
    {
        $providers = BandwidthProvider::latest()->get();
        return view('bandwidth-buy.provider.index', compact('providers'));
    }

    public function create()
    {
        return view('bandwidth-buy.provider.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'   => 'required|string|max:150',
            'contact_person' => 'required|string|max:100',
            'email'          => 'required|email|max:150',
            'phone_no'       => ['required', 'digits:11'],
            'document'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'address'        => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('bandwidth/providers', 'public');
        }

        $data['created_by'] = auth()->id();
        BandwidthProvider::create($data);

        return redirect()->route('bandwidth-buy.provider.index')
            ->with('success', 'Provider added successfully.');
    }

    public function edit(BandwidthProvider $provider)
    {
        return view('bandwidth-buy.provider.edit', compact('provider'));
    }

    public function update(Request $request, BandwidthProvider $provider)
    {
        $data = $request->validate([
            'company_name'   => 'required|string|max:150',
            'contact_person' => 'required|string|max:100',
            'email'          => 'required|email|max:150',
            'phone_no'       => ['required', 'digits:11'],
            'document'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'address'        => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('document')) {
            if ($provider->document) {
                Storage::disk('public')->delete($provider->document);
            }
            $data['document'] = $request->file('document')->store('bandwidth/providers', 'public');
        }

        $provider->update($data);

        return redirect()->route('bandwidth-buy.provider.index')
            ->with('success', 'Provider updated successfully.');
    }
}
