<?php

namespace App\Http\Controllers;

use App\Models\MikrotikRouter;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class MikrotikController extends Controller
{
    /**
     * Display a list of all MikroTik routers.
     * Includes the count of IP pools per router.
     */
    public function index()
    {
        $routers = MikrotikRouter::withCount('ipPools')->latest()->get();

        return view('mikrotik.index', compact('routers'));
    }

    /**
     * Store a newly added router in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'ip_address' => 'required|ip',         // must be a valid IP address
            'api_port'   => 'required|integer',    // MikroTik API port, default 8728
            'username'   => 'required|string|max:50',
            'password'   => 'required|string|max:100',
            'area'       => 'nullable|string|max:100', // which area this router covers
        ]);

        $router = MikrotikRouter::create($request->all());

        ActivityLog::log('Router added', 'MikrotikRouter', $router->id, null, $router->toArray());

        return back()->with('success', 'Router added successfully.');
    }

    /**
     * Update the specified router's connection details.
     */
    public function update(Request $request, MikrotikRouter $mikrotikRouter)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'ip_address' => 'required|ip',
            'api_port'   => 'required|integer',
            'username'   => 'required|string|max:50',
            'area'       => 'nullable|string|max:100',
        ]);

        $mikrotikRouter->update($request->all());

        return back()->with('success', 'Router updated successfully.');
    }

    /**
     * Delete the specified router along with all its IP pools.
     */
    public function destroy(MikrotikRouter $mikrotikRouter)
    {
        // Remove associated IP pools first
        $mikrotikRouter->ipPools()->delete();
        $mikrotikRouter->delete();

        return back()->with('success', 'Router deleted successfully.');
    }

    /**
     * Add a new IP pool to the specified router.
     * e.g. pool from 192.168.1.1 to 192.168.1.254
     */
    public function addPool(Request $request, MikrotikRouter $mikrotikRouter)
    {
        $request->validate([
            'pool_name' => 'required|string|max:100',
            'start_ip'  => 'required|ip',         // starting IP of the range
            'end_ip'    => 'required|ip',         // ending IP of the range
            'total_ip'  => 'required|integer|min:1',
        ]);

        // Create IP pool linked to this router
        $mikrotikRouter->ipPools()->create($request->all());

        return back()->with('success', 'IP Pool added successfully.');
    }
}
