<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerMikrotikClientController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $clients = Customer::with('router')
            ->forReseller($resellerId)
            ->whereNotNull('pppoe_username')
            ->latest()
            ->paginate(50);

        // Group by router so we fetch active sessions once per router, not per client.
        $routerGroups = $clients->getCollection()->groupBy('router_id');
        $activeMap    = []; // username => ['ip', 'mac', 'uptime', 'protocol']

        foreach ($routerGroups as $routerId => $groupClients) {
            $router = $groupClients->first()->router;
            if (!$router) continue;

            try {
                $mikrotik = new MikrotikService();
                $mikrotik->withRouter($router, function ($m) use (&$activeMap) {
                    foreach ($m->getActiveSessions() as $conn) {
                        $activeMap[$conn['name'] ?? ''] = [
                            'ip'       => $conn['address'] ?? null,
                            'mac'      => $conn['caller-id'] ?? null,
                            'uptime'   => $conn['uptime'] ?? null,
                            'protocol' => 'pppoe',
                        ];
                    }
                    foreach ($m->getActiveHotspotSessions() as $conn) {
                        $activeMap[$conn['user'] ?? ''] = [
                            'ip'       => $conn['address'] ?? null,
                            'mac'      => $conn['mac-address'] ?? null,
                            'uptime'   => $conn['uptime'] ?? null,
                            'protocol' => 'hotspot',
                        ];
                    }
                });
            } catch (\Exception $e) {
                // Unreachable routers are silently skipped; remaining clients show as offline.
                continue;
            }
        }

        $clients->getCollection()->transform(function ($client) use ($activeMap) {
            $live = $activeMap[$client->pppoe_username] ?? null;
            $client->live_status   = $live ? 'online' : 'offline';
            $client->live_ip       = $live['ip'] ?? $client->ip_address;
            $client->live_mac      = $live['mac'] ?? $client->mac_address;
            $client->live_uptime   = $live['uptime'] ?? null;
            $client->live_protocol = $live['protocol'] ?? $client->protocolType?->name;
            return $client;
        });

        $onlineCount = $clients->getCollection()->where('live_status', 'online')->count();

        return view('reseller.mikrotik-client.index', [
            'clients'      => $clients,
            'onlineCount'  => $onlineCount,
            'offlineCount' => $clients->total() - $onlineCount,
        ]);
    }

    /** Disconnect a client session (PPPoE or Hotspot) after verifying reseller ownership. */
    public function disconnect(Request $request, Customer $client)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($client->mac_reseller_id === $resellerId, 403);

        $request->validate([
            'protocol' => 'required|in:pppoe,hotspot',
        ]);

        if (!$client->router || !$client->pppoe_username) {
            return response()->json(['success' => false, 'message' => 'No router/username assigned to this client.'], 422);
        }

        try {
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($client->router, function ($m) use ($request, $client) {
                return $request->protocol === 'pppoe'
                    ? $m->kickActiveSession($client->pppoe_username)
                    : $m->kickActiveHotspotSession($client->pppoe_username);
            });

            return response()->json(['success' => true, 'message' => "{$client->name} disconnected successfully."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
