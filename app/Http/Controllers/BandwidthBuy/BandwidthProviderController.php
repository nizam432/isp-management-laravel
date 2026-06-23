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
        $providers = BandwidthProvider::withCount('purchases')->latest()->get();
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
            'phone_no'       => 'required|string|max:255',
            'document'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'address'        => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('bandwidth/providers', 'public');
        }

        $data['created_by'] = auth()->id();
        $provider = BandwidthProvider::create($data);
        $provider->loadCount('purchases');

        if ($request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Provider added successfully.',
                'provider' => $provider,
            ]);
        }

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
            'phone_no'       => 'required|string|max:255',
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
        $provider->refresh();
        $provider->loadCount('purchases');

        if ($request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Provider updated successfully.',
                'provider' => $provider,
            ]);
        }

        return redirect()->route('bandwidth-buy.provider.index')
            ->with('success', 'Provider updated successfully.');
    }

    public function destroy(Request $request, BandwidthProvider $provider)
    {
        if ($provider->purchases()->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete provider with existing purchases.',
                ], 422);
            }

            return redirect()->route('bandwidth-buy.provider.index')
                ->with('error', 'Cannot delete provider with existing purchases.');
        }

        if ($provider->document) {
            Storage::disk('public')->delete($provider->document);
        }

        $provider->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Provider deleted successfully.',
            ]);
        }

        return redirect()->route('bandwidth-buy.provider.index')
            ->with('success', 'Provider deleted successfully.');
    }
}
