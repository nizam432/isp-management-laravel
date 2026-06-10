<?php

namespace App\Http\Controllers;

use App\Models\MikrotikRouter;
use App\Models\Customer;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use App\Jobs\MikrotikSyncJob;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MikrotikController extends Controller
{
    // ══════════════════════════════════════════════
    // পুরনো CRUD — Router & IP Pool Management
    // ══════════════════════════════════════════════

    /**
     * Display a list of all MikroTik routers.
     */
    public function index()
    {
        $routers = MikrotikRouter::withCount(['ipPools', 'customers'])->latest()->get();
        return view('mikrotik.index', compact('routers'));
    }
    /**
     * Store a newly added router in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'ip_address' => 'required|ip',
            'api_port'   => 'required|integer',
            'username'   => 'required|string|max:50',
            'password'   => 'required|string|max:100',
            'area'       => 'nullable|string|max:100',
        ]);

        $router = MikrotikRouter::create($request->all() + ['is_active' => false]);

        // ── Connection test ──
        try {
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->getRouterIdentity());
            $router->update(['is_active' => true, 'last_seen' => now()]);

            // ── Sync all packages to new router ──
            $this->syncAllPackagesToRouter($router);

            $message = 'Router added and connected successfully.';
        } catch (\Exception $e) {
            $message = 'Router added but connection failed: ' . $e->getMessage();
        }

        ActivityLog::log('Router added', 'MikrotikRouter', $router->id, null, $router->toArray());

        return back()->with(
            $router->is_active ? 'success' : 'warning',
            $message
        );
    }
    private function syncAllPackagesToRouter(MikrotikRouter $router): void
    {
        $packages = Package::whereNotNull('mikrotik_profile')
                           ->where('mikrotik_profile', '!=', '')
                           ->get();

        if ($packages->isEmpty()) return;

        $mikrotik = new MikrotikService();

        try {
            $mikrotik->withRouter($router, function($m) use ($packages, $router) {
                $existing = collect($m->getPPPoEProfiles())->pluck('name')->toArray();

                foreach ($packages as $package) {
                    if (in_array($package->mikrotik_profile, $existing)) {
                        Log::info("Profile '{$package->mikrotik_profile}' already exists on [{$router->name}] — skipped");
                        continue;
                    }

                    $m->createPPPoEProfile([
                        'name'          => $package->mikrotik_profile,
                        'upload_mbps'   => $package->speed_upload,
                        'download_mbps' => $package->speed_download,
                    ]);

                    Log::info("Profile '{$package->mikrotik_profile}' created on [{$router->name}]");
                }
            });
        } catch (\Exception $e) {
            Log::warning("syncAllPackagesToRouter [{$router->name}] failed: " . $e->getMessage());
        }
    }
    public function update(Request $request, MikrotikRouter $mikrotikRouter)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'ip_address' => 'required|ip',
            'api_port'   => 'required|integer',
            'username'   => 'required|string|max:50',
            'area'       => 'nullable|string|max:100',
        ]);

        $data = $request->except('password');

        // Password দিলেই শুধু update হবে
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $mikrotikRouter->update($data);

        // ── Connection test ──
        try {
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($mikrotikRouter, fn($m) => $m->getRouterIdentity());
            $mikrotikRouter->update(['is_active' => true, 'last_seen' => now()]);

            // ── Sync all packages to router ──
            $this->syncAllPackagesToRouter($mikrotikRouter);

            $message = 'Router updated and connected successfully.';
        } catch (\Exception $e) {
            $mikrotikRouter->update(['is_active' => false]);
            $message = 'Router updated but connection failed: ' . $e->getMessage();
        }

        return back()->with(
            $mikrotikRouter->is_active ? 'success' : 'warning',
            $message
        );
    }
    /**
     * Delete the specified router along with all its IP pools.
     */
    public function destroy(MikrotikRouter $mikrotikRouter)
    {
        if ($mikrotikRouter->customers()->count() > 0) {
            return back()->with('error', 'এই router এ ' . $mikrotikRouter->customers()->count() . ' জন customer আছে — delete করা যাবে না।');
        }

        $mikrotikRouter->ipPools()->delete();
        $mikrotikRouter->delete();

        return back()->with('success', 'Router deleted successfully.');
    }
    public function updatePool(Request $request, $pool)
    {
        $pool = \App\Models\IpPool::findOrFail($pool);
        $request->validate([
            'pool_name' => 'required|string|max:100',
            'start_ip'  => 'required|ip',
            'end_ip'    => 'required|ip',
        ]);
        $total = ip2long($request->end_ip) - ip2long($request->start_ip) + 1;
        $pool->update([
            'pool_name' => $request->pool_name,
            'start_ip'  => $request->start_ip,
            'end_ip'    => $request->end_ip,
            'total_ip'  => $total,
        ]);
        return back()->with('success', 'IP Pool updated successfully.');
        
    }

    public function destroyPool($pool)
    {
        $pool = \App\Models\IpPool::findOrFail($pool);
        $pool->delete();
        return back()->with('success', 'IP Pool deleted successfully.');
    }    
    /**
     * Add a new IP pool to the specified router.
     */
    public function addPool(Request $request, MikrotikRouter $mikrotikRouter)
    {
        $request->validate([
            'pool_name' => 'required|string|max:100',
            'start_ip'  => 'required|ip',
            'end_ip'    => 'required|ip',
        ]);

        // Total IP auto calculate
        $start = ip2long($request->start_ip);
        $end   = ip2long($request->end_ip);
        $total = $end - $start + 1;

        $mikrotikRouter->ipPools()->create([
            'pool_name' => $request->pool_name,
            'start_ip'  => $request->start_ip,
            'end_ip'    => $request->end_ip,
            'total_ip'  => $total,
        ]);

        return back()->with('success', 'IP Pool added successfully.');
    }

    // ══════════════════════════════════════════════
    // নতুন — Live API Methods (AJAX)
    // ══════════════════════════════════════════════

    /**
     * GET /mikrotik/{router}/status
     * Router live status — CPU, uptime, online count
     */
    public function routerStatus(MikrotikRouter $router): JsonResponse
    {
        try {
            $mikrotik = new MikrotikService();
            $data = $mikrotik->withRouter($router, function (MikrotikService $m) {
                return [
                    'resource'     => $m->getRouterResource(),
                    'online_count' => $m->getOnlineUserCount(),
                    'identity'     => $m->getRouterIdentity(),
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /mikrotik/{router}/pppoe-users
     * সব PPPoE user লিস্ট
     */
    public function pppoeUsers(MikrotikRouter $router): JsonResponse
    {
        try {
            $mikrotik = new MikrotikService();
            $users = $mikrotik->withRouter($router, fn($m) => $m->getPPPoEUsers());

            return response()->json(['success' => true, 'data' => $users, 'count' => count($users)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /mikrotik/{router}/active-sessions
     * সব active PPPoE session
     */
    public function activeSessions(MikrotikRouter $router): JsonResponse
    {
        try {
            $mikrotik = new MikrotikService();
            $sessions = $mikrotik->withRouter($router, fn($m) => $m->getActiveSessions());

            return response()->json(['success' => true, 'data' => $sessions, 'count' => count($sessions)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function activeSessionsPage()
    {
        $routers  = MikrotikRouter::where('is_active', 1)->get();
        $customers = Customer::pluck('name', 'pppoe_username'); // username → name map
        return view('mikrotik.active-sessions', compact('routers', 'customers'));
    }
    /**
     * GET /mikrotik/{router}/queues
     * Simple Queue লিস্ট
     */
    public function queues(MikrotikRouter $router): JsonResponse
    {
        try {
            $mikrotik = new MikrotikService();
            $queues = $mikrotik->withRouter($router, fn($m) => $m->getQueues());

            return response()->json(['success' => true, 'data' => $queues]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /mikrotik/{router}/profiles
     * PPPoE Profile লিস্ট
     */
    public function profiles(MikrotikRouter $router): JsonResponse
    {
        try {
            $mikrotik = new MikrotikService();
            $profiles = $mikrotik->withRouter($router, fn($m) => $m->getPPPoEProfiles());

            return response()->json(['success' => true, 'data' => $profiles]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════
    // Customer-level Operations
    // ══════════════════════════════════════════════

    /**
     * POST /customers/{customer}/mikrotik/provision
     * Customer কে MikroTik এ add করো
     */
    public function provisionCustomer(Customer $customer): JsonResponse
    {
        if (!$customer->pppoe_username || !$customer->pppoe_password) {
            return response()->json(['success' => false, 'message' => 'PPPoE credentials নেই।'], 422);
        }

        $router = MikrotikRouter::where('is_active', 1)->first();
        if (!$router) {
            return response()->json(['success' => false, 'message' => 'কোনো active router নেই।'], 404);
        }

        try {
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->provisionCustomer($customer));
            $customer->update(['mikrotik_status' => 'active']);

            return response()->json(['success' => true, 'message' => "{$customer->name} MikroTik এ add হয়েছে।"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /customers/{customer}/mikrotik/suspend
     * Customer suspend করো
     */
    public function suspendCustomer(Customer $customer): JsonResponse
    {
        try {
            $router   = $this->getCustomerRouter($customer);
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->suspendCustomer($customer));
            $customer->update(['mikrotik_status' => 'suspended']);

            return response()->json(['success' => true, 'message' => "{$customer->name} suspend হয়েছে।"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /customers/{customer}/mikrotik/restore
     * Customer restore করো
     */
    public function restoreCustomer(Customer $customer): JsonResponse
    {
        try {
            $router   = $this->getCustomerRouter($customer);
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->restoreCustomer($customer));
            $customer->update(['mikrotik_status' => 'active']);

            return response()->json(['success' => true, 'message' => "{$customer->name} restore হয়েছে।"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /customers/{customer}/mikrotik/kick
     * Active session force disconnect
     */
    public function kickCustomer(Customer $customer): JsonResponse
    {
        try {
            $router   = $this->getCustomerRouter($customer);
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->kickActiveSession($customer->pppoe_username));

            return response()->json(['success' => true, 'message' => "{$customer->name} disconnect হয়েছে।"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /customers/{customer}/mikrotik/change-package
     * Package পরিবর্তন
     */
    public function changePackage(Customer $customer): JsonResponse
    {
        try {
            $router   = $this->getCustomerRouter($customer);
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->changeCustomerPackage($customer));

            return response()->json(['success' => true, 'message' => 'Package পরিবর্তন হয়েছে।']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /customers/{customer}/mikrotik
     * MikroTik থেকে সম্পূর্ণ remove করো
     */
    public function removeCustomer(Customer $customer): JsonResponse
    {
        try {
            $router   = $this->getCustomerRouter($customer);
            $mikrotik = new MikrotikService();
            $mikrotik->withRouter($router, fn($m) => $m->removeCustomer($customer));
            $customer->update(['mikrotik_status' => 'removed']);

            return response()->json(['success' => true, 'message' => "{$customer->name} MikroTik থেকে remove হয়েছে।"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /customers/{customer}/mikrotik/session
     * Customer এর live session info
     *
     * Fix: !trap error gracefully handle করা হয়েছে।
     * !trap = PPPoE user router এ নেই (not provisioned)।
     * এই ক্ষেত্রে success=true, online=false, not_found=true return করবে।
     */
    public function customerSession(Customer $customer): JsonResponse
    {
        try {
            $router = $this->getCustomerRouter($customer);

            if (!$customer->pppoe_username) {
                return response()->json([
                    'success'   => true,
                    'online'    => false,
                    'session'   => null,
                    'not_found' => true,
                    'message'   => 'No PPPoE username set.',
                ]);
            }

            $mikrotik  = new MikrotikService();
            $session   = null;
            $notFound  = false;

            try {
                $session = $mikrotik->withRouter(
                    $router,
                    fn($m) => $m->getCustomerSession($customer->pppoe_username)
                );
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                // !trap বা no such item = router এ user নেই — treat as not provisioned
                if (str_contains($msg, '!trap') || str_contains($msg, 'no such item')) {
                    $notFound = true;
                } else {
                    throw $e; // real connection error — re-throw
                }
            }

            return response()->json([
                'success'   => true,
                'online'    => !is_null($session) && !$notFound,
                'session'   => $session,
                'not_found' => $notFound,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success'   => false,
                'online'    => false,
                'not_found' => false,
                'message'   => $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════
    // Bulk Operations
    // ══════════════════════════════════════════════

    /**
     * POST /mikrotik/bulk-suspend
     */
    public function bulkSuspend(Request $request): JsonResponse
    {
        $customerIds = $request->input('customer_ids', []);
        $count       = count($customerIds);

        MikrotikSyncJob::dispatch('suspend', $customerIds);

        return response()->json([
            'success' => true,
            'message' => "{$count} জন customer suspend queue এ add হয়েছে।",
        ]);
    }

    /**
     * POST /mikrotik/sync-all
     */
    public function syncAll(): JsonResponse
    {
        MikrotikSyncJob::dispatch('sync_all', []);

        return response()->json([
            'success' => true,
            'message' => 'Sync শুরু হয়েছে। Background এ চলছে।',
        ]);
    }

    // ══════════════════════════════════════════════
    // Helper
    // ══════════════════════════════════════════════

    private function getCustomerRouter(Customer $customer): MikrotikRouter
    {
        $router = MikrotikRouter::where('is_active', 1)->first();

        if (!$router) {
            throw new \Exception('কোনো active MikroTik router পাওয়া যায়নি।');
        }

        return $router;
    }
}