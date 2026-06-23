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

    public function create()
    {
        return view('bandwidth-buy.service.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:bandwidth_services,name',
            'description' => 'nullable|string',
        ]);

        $service = BandwidthService::create($request->only('name', 'description'));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Service added successfully.',
                'service' => $service,
            ]);
        }

        return redirect()->route('bandwidth-buy.service.index')
            ->with('success', 'Service added successfully.');
    }

    public function edit(BandwidthService $service)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'service' => $service,
            ]);
        }

        return view('bandwidth-buy.service.edit', compact('service'));
    }

    public function update(Request $request, BandwidthService $service)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:bandwidth_services,name,' . $service->id,
            'description' => 'nullable|string',
        ]);

        $service->update($request->only('name', 'description'));
        $service->refresh();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully.',
                'service' => $service,
            ]);
        }

        return redirect()->route('bandwidth-buy.service.index')
            ->with('success', 'Service updated successfully.');
    }
}
