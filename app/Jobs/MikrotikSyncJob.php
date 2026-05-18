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
use Illuminate\Support\Facades\Log;

/**
 * MikrotikSyncJob
 * ──────────────────────────────────────────────────────────
 * Background queue job — bulk operations এ ব্যবহার হয়।
 * php artisan queue:work দিয়ে চালাতে হবে।
 *
 * Actions: suspend | restore | provision | sync_all | remove
 */
class MikrotikSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300; // 5 minutes

    public function __construct(
        private string $action,
        private array  $customerIds,
        private ?int   $routerId = null,
    ) {}

    public function handle(MikrotikService $mikrotik): void
    {
        $router = $this->routerId
            ? MikrotikRouter::find($this->routerId)
            : MikrotikRouter::active()->first();

        if (!$router) {
            Log::error('MikrotikSyncJob: কোনো active router নেই।');
            return;
        }

        $customers = $this->action === 'sync_all'
            ? Customer::where('status', 'active')->get()
            : Customer::whereIn('id', $this->customerIds)->get();

        Log::info("MikrotikSyncJob [{$this->action}] শুরু। Customers: " . $customers->count());

        $mikrotik->withRouter($router, function (MikrotikService $m) use ($customers) {
            foreach ($customers as $customer) {
                try {
                    match ($this->action) {
                        'suspend'    => $this->suspendOne($m, $customer),
                        'restore'    => $this->restoreOne($m, $customer),
                        'provision'  => $m->provisionCustomer($customer),
                        'remove'     => $m->removeCustomer($customer),
                        'sync_all'   => $this->syncOne($m, $customer),
                        default      => throw new \InvalidArgumentException("Unknown action: {$this->action}"),
                    };
                } catch (\Exception $e) {
                    Log::warning("MikrotikSyncJob [{$this->action}] failed for {$customer->customer_code}: " . $e->getMessage());
                }
            }
        });

        Log::info("MikrotikSyncJob [{$this->action}] শেষ।");
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

    /**
     * Sync: PPPoE secret না থাকলে তৈরি করো, থাকলে স্কিপ
     */
    private function syncOne(MikrotikService $m, Customer $customer): void
    {
        $existingUsers = $m->getPPPoEUsers();
        $existingNames = array_column($existingUsers, 'name');

        if (!in_array($customer->pppoe_username, $existingNames)) {
            $m->provisionCustomer($customer);
            $customer->update(['mikrotik_status' => 'active']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("MikrotikSyncJob [{$this->action}] permanently failed: " . $exception->getMessage());
    }
}
