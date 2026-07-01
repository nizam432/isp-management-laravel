<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $clients = Customer::with(['router', 'package'])
            ->forReseller($resellerId)
            ->whereNotNull('pppoe_username')
            ->get();

        $routerGroups = $clients->groupBy('router_id');
        $activeMap    = [];

        foreach ($routerGroups as $routerId => $groupClients) {
            $router = $groupClients->first()->router;
            if (!$router) continue;

            try {
                $mikrotik = new MikrotikService();
                $mikrotik->withRouter($router, function ($m) use (&$activeMap) {
                    foreach ($m->getActiveSessions() as $conn) {
                        $activeMap[$conn['name'] ?? ''] = [
                            'ip' => $conn['address'] ?? null, 'uptime' => $conn['uptime'] ?? null, 'protocol' => 'pppoe',
                        ];
                    }
                    foreach ($m->getActiveHotspotSessions() as $conn) {
                        $activeMap[$conn['user'] ?? ''] = [
                            'ip' => $conn['address'] ?? null, 'uptime' => $conn['uptime'] ?? null, 'protocol' => 'hotspot',
                        ];
                    }
                });
            } catch (\Exception $e) {
                continue;
            }
        }

        $clients = $clients->map(function ($client) use ($activeMap) {
            $live = $activeMap[$client->pppoe_username] ?? null;
            $client->live_status = $live ? 'online' : 'offline';
            $client->live_ip     = $live['ip'] ?? null;
            $client->live_uptime = $live['uptime'] ?? null;
            return $client;
        });

        $onlineClients  = $clients->where('live_status', 'online')->values();
        $offlineClients = $clients->where('live_status', 'offline')->values();

        $packageBreakdown = $onlineClients->groupBy(fn($c) => $c->package?->name ?? 'Unknown')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        $stats = [
            'total'   => $clients->count(),
            'online'  => $onlineClients->count(),
            'offline' => $offlineClients->count(),
        ];

        return view('reseller.monitoring.index', compact('stats', 'onlineClients', 'offlineClients', 'packageBreakdown'));
    }
}
