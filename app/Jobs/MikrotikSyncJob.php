<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * MikrotikSyncJob
 * ──────────────────────────────────────────────────────────
 * Background queue job — used for bulk MikroTik operations.
 * Run with: php artisan queue:work
 *
 * Actions: suspend | restore | provision | sync_all | remove
 */
class MikrotikSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 600; // 10 minutes for large sync

    public function __construct(
        private string $action,
        private array  $customerIds,
        private ?int   $routerId = null,
    ) {}

    public function handle(MikrotikService $mikrotik): void
    {
        if ($this->action === 'sync_all') {
            $this->handleSyncAll($mikrotik);
            return;
        }

        // Other bulk actions (suspend, restore, provision, remove)
        $router = $this->routerId
            ? MikrotikRouter::find($this->routerId)
            : MikrotikRouter::where('is_active', 1)->first();

        if (!$router) {
            Log::error('MikrotikSyncJob: No active router found.');
            return;
        }

        $customers = Customer::whereIn('id', $this->customerIds)->get();
        $total     = $customers->count();

        Log::info("MikrotikSyncJob [{$this->action}] started. Customers: {$total}");

        $done = 0;
        $mikrotik->withRouter($router, function (MikrotikService $m) use ($customers, $total, &$done) {
            foreach ($customers as $customer) {
                try {
                    match ($this->action) {
                        'suspend'   => $this->suspendOne($m, $customer),
                        'restore'   => $this->restoreOne($m, $customer),
                        'provision' => $m->provisionCustomer($customer),
                        'remove'    => $m->removeCustomer($customer),
                        default     => throw new \InvalidArgumentException("Unknown action: {$this->action}"),
                    };
                } catch (\Exception $e) {
                    Log::warning("MikrotikSyncJob [{$this->action}] failed for {$customer->customer_code}: " . $e->getMessage());
                }

                $done++;
                if ($done % 50 === 0) {
                    Cache::put('mikrotik_sync_status', [
                        'status'  => 'running',
                        'total'   => $total,
                        'done'    => $done,
                        'message' => "Processing {$done}/{$total}...",
                    ], 3600);
                }
            }
        });

        Log::info("MikrotikSyncJob [{$this->action}] completed.");
    }

    // ──────────────────────────────────────────────────────
    // sync_all: Support multiple routers
    // Groups customers by router_id, then syncs each router separately
    // ──────────────────────────────────────────────────────
    private function handleSyncAll(MikrotikService $mikrotik): void
    {
        // Get all active routers
        $routers = MikrotikRouter::where('is_active', 1)->get();

        if ($routers->isEmpty()) {
            Log::error('MikrotikSyncJob: No active routers found.');
            Cache::put('mikrotik_sync_status', [
                'status'  => 'failed',
                'message' => 'No active routers found.',
                'done_at' => now()->toDateTimeString(),
            ], 3600);
            return;
        }

        // Get all customers with a pppoe_username
        $allCustomers = Customer::whereNotNull('pppoe_username')->get();
        $total        = $allCustomers->count();

        Log::info("MikrotikSyncJob [sync_all] started. Customers: {$total}, Routers: {$routers->count()}");

        Cache::put('mikrotik_sync_status', [
            'status'  => 'running',
            'total'   => $total,
            'done'    => 0,
            'message' => "Starting sync across {$routers->count()} router(s)...",
        ], 3600);

        $globalStats = ['active' => 0, 'pending' => 0, 'ip_updated' => 0];
        $globalDone  = 0;

        foreach ($routers as $router) {
            // Customers assigned to this router
            // If customer has no router_id, assign to first router as fallback
            $routerCustomers = $allCustomers->filter(function ($c) use ($router, $routers) {
                if ($c->router_id) {
                    return $c->router_id === $router->id;
                }
                // No router_id — assign to first router only
                return $router->id === $routers->first()->id;
            });

            if ($routerCustomers->isEmpty()) {
                Log::info("MikrotikSyncJob: Router [{$router->name}] — no customers assigned, skipping.");
                continue;
            }

            Log::info("MikrotikSyncJob: Syncing router [{$router->name}] — {$routerCustomers->count()} customers.");

            try {
                $mikrotik->withRouter($router, function (MikrotikService $m) use (
                    $routerCustomers, $router, $total, &$globalStats, &$globalDone
                ) {
                    $this->syncRouter($m, $router, $routerCustomers, $total, $globalStats, $globalDone);
                });
            } catch (\Exception $e) {
                Log::error("MikrotikSyncJob: Router [{$router->name}] failed: " . $e->getMessage());
                // Continue with next router
            }
        }

        // Final result
        Cache::put('mikrotik_sync_status', [
            'status'     => 'completed',
            'total'      => $total,
            'done'       => $globalDone,
            'active'     => $globalStats['active'],
            'pending'    => $globalStats['pending'],
            'ip_updated' => $globalStats['ip_updated'],
            'message'    => "Sync complete! Active: {$globalStats['active']}, Pending: {$globalStats['pending']}, IPs updated: {$globalStats['ip_updated']}",
            'done_at'    => now()->toDateTimeString(),
        ], 3600);

        Log::info("MikrotikSyncJob [sync_all] completed. Active: {$globalStats['active']}, Pending: {$globalStats['pending']}");
    }

    // ──────────────────────────────────────────────────────
    // Sync customers for a single router
    // ──────────────────────────────────────────────────────
    private function syncRouter(
        MikrotikService $m,
        MikrotikRouter $router,
        $customers,
        int $total,
        array &$stats,
        int &$done
    ): void {
        // Single API call to get all PPPoE users and active sessions
        $routerUsers    = collect($m->getPPPoEUsers())->keyBy('name');
        $activeSessions = collect($m->getActiveSessions())->keyBy('name');

        $customers->chunk(200)->each(function ($chunk) use ($routerUsers, $activeSessions, $router, &$stats, &$done, $total) {
            foreach ($chunk as $customer) {
                $onRouter   = $routerUsers->has($customer->pppoe_username);
                $session    = $activeSessions->get($customer->pppoe_username);
                $updateData = [];

                if ($onRouter) {
                    $updateData['mikrotik_status'] = 'active';
                    $stats['active']++;

                    // Sync IP address from live session
                    if ($session && !empty($session['address'])) {
                        $updateData['ip_address'] = $session['address'];
                        $stats['ip_updated']++;
                    }
                } else {
                    $updateData['mikrotik_status'] = 'pending';
                    $stats['pending']++;
                }

                $customer->update($updateData);
                $done++;
            }

            // Update progress cache
            Cache::put('mikrotik_sync_status', [
                'status'  => 'running',
                'total'   => $total,
                'done'    => $done,
                'message' => "Processing {$done}/{$total} (Router: {$router->name})...",
            ], 3600);
        });
    }

    private function suspendOne(MikrotikService $m, Customer $customer): void
    {
        $m->suspendCustomer($customer);
        $customer->update(['mikrotik_status' => 'suspended']);
    }

    private function restoreOne(MikrotikService $m, Customer $customer): void
    {
        $m->restoreCustomer($customer);
        $customer->update(['mikrotik_status' => 'active']);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("MikrotikSyncJob [{$this->action}] permanently failed: " . $exception->getMessage());
        Cache::put('mikrotik_sync_status', [
            'status'  => 'failed',
            'message' => $exception->getMessage(),
            'done_at' => now()->toDateTimeString(),
        ], 3600);
    }
}
