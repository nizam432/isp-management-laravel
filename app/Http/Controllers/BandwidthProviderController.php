<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BandwidthProviderController extends Controller
{
    // ── Index (view only) ─────────────────────────────────────────────────────
    public function index()
    {
        $providers = BandwidthProvider::latest()->get();
        return view('bandwidth-buy.provider.index', compact('providers'));
    }

    // ── Store (AJAX) ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'company_name'   => 'required|string|max:150',
            'contact_person' => 'required|string|max:100',
            'email'          => 'required|email|max:150',
            'phone_no'       => ['required', 'digits:11'],
            'document'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'address'        => 'nullable|string|max:255',
        ]);

        $data = $request->only('company_name', 'contact_person', 'email', 'phone_no', 'address');
        $data['created_by'] = auth()->id();

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('bandwidth/providers', 'public');
        }

        $provider = BandwidthProvider::create($data);

        return response()->json([
            'success'  => true,
            'message'  => 'Provider added successfully.',
            'provider' => [
                'id'             => $provider->id,
                'company_name'   => $provider->company_name,
                'contact_person' => $provider->contact_person,
                'email'          => $provider->email,
                'phone_no'       => $provider->phone_no,
                'address'        => $provider->address ?? '—',
                'document_url'   => $provider->document
                                        ? asset('storage/' . $provider->document)
                                        : null,
            ],
        ]);
    }

    // ── Get single provider for edit modal (AJAX) ─────────────────────────────
    public function edit(BandwidthProvider $provider)
    {
        return response()->json([
            'success'  => true,
            'provider' => [
                'id'             => $provider->id,
                'company_name'   => $provider->company_name,
                'contact_person' => $provider->contact_person,
                'email'          => $provider->email,
                'phone_no'       => $provider->phone_no,
                'address'        => $provider->address ?? '',
                'document_url'   => $provider->document
                                        ? asset('storage/' . $provider->document)
                                        : null,
            ],
        ]);
    }

    // ── Update (AJAX) ─────────────────────────────────────────────────────────
    public function update(Request $request, BandwidthProvider $provider)
    {
        $request->validate([
            'company_name'   => 'required|string|max:150',
            'contact_person' => 'required|string|max:100',
            'email'          => 'required|email|max:150',
            'phone_no'       => ['required', 'digits:11'],
            'document'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'address'        => 'nullable|string|max:255',
        ]);

        $data = $request->only('company_name', 'contact_person', 'email', 'phone_no', 'address');

        if ($request->hasFile('document')) {
            if ($provider->document) {
                Storage::disk('public')->delete($provider->document);
            }
            $data['document'] = $request->file('document')->store('bandwidth/providers', 'public');
        }

        $provider->update($data);

        return response()->json([
            'success'  => true,
            'message'  => 'Provider updated successfully.',
            'provider' => [
                'id'             => $provider->id,
                'company_name'   => $provider->company_name,
                'contact_person' => $provider->contact_person,
                'email'          => $provider->email,
                'phone_no'       => $provider->phone_no,
                'address'        => $provider->address ?? '—',
                'document_url'   => $provider->document
                                        ? asset('storage/' . $provider->document)
                                        : null,
            ],
        ]);
    }
}
