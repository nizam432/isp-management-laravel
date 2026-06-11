<?php

namespace App\Http\Controllers\BandwidthBuy;

use App\Http\Controllers\Controller;
use App\Models\BandwidthBuy\BandwidthService;
use Illuminate\Http\Request;

class BandwidthServiceController extends Controller
{
    public function index()
    {
        $services = BandwidthService::latest()->get();
        return view('bandwidth-buy.service.index', compact('services'));
    }

    // ── Get single for edit modal (AJAX) ──────────────────────────────────────
    public function edit(BandwidthService $service)
    {
        return response()->json([
            'success' => true,
            'service' => [
                'id'          => $service->id,
                'name'        => $service->name,
                'description' => $service->description ?? '',
                'is_active'   => $service->is_active ? 1 : 0,
            ],
        ]);
    }

    // ── Store (AJAX) ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:bandwidth_services,name',
            'description' => 'nullable|string',
        ]);

        $service = BandwidthService::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service added successfully.',
            'service' => [
                'id'          => $service->id,
                'name'        => $service->name,
                'description' => $service->description ?? '—',
                'is_active'   => 1,
            ],
        ]);
    }

    // ── Update (AJAX) ─────────────────────────────────────────────────────────
    public function update(Request $request, BandwidthService $service)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:bandwidth_services,name,' . $service->id,
            'description' => 'nullable|string',
        ]);

        $service->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'service' => [
                'id'          => $service->id,
                'name'        => $service->name,
                'description' => $service->description ?? '—',
                'is_active'   => $service->is_active ? 1 : 0,
            ],
        ]);
    }
}
