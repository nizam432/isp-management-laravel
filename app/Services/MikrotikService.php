<?php

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

/**
 * MikrotikService
 * ─────────────────────────────────────────────────────────
 * সব MikroTik API operation এখানে।
 * Customer connect/disconnect, PPPoE user management,
 * Bandwidth (Queue) control, Active sessions।
 */
class MikrotikService
{
    private RouterOSAPI $api;
    private MikrotikRouter $router;

    public function __construct()
    {
        $this->api = new RouterOSAPI('', 8728);
    }

    // ══════════════════════════════════════════════════════
    // CONNECTION MANAGEMENT
    // ══════════════════════════════════════════════════════

    /**
     * Router এ connect করো
     */
    public function connectRouter(MikrotikRouter $router): self
    {
        $this->router = $router;
        $this->api    = new RouterOSAPI($router->ip_address, $router->api_port ?? 8728);
        $this->api->connect($router->username, $router->password);

        // last_seen আপডেট
        $router->update(['last_seen' => now()]);

        return $this;
    }

    /**
     * Disconnect করো
     */
    public function disconnect(): void
    {
        $this->api->disconnect();
    }

    /**
     * Router থেকে auto-connect করে callback চালাও, তারপর disconnect
     */
    public function withRouter(MikrotikRouter $router, callable $callback): mixed
    {
        try {
            $this->connectRouter($router);
            $result = $callback($this);
            return $result;
        } catch (\Exception $e) {
            Log::error("MikroTik [{$router->name}] Error: " . $e->getMessage());
            throw $e;
        } finally {
            $this->disconnect();
        }
    }

    // ══════════════════════════════════════════════════════
    // PPPoE USER MANAGEMENT
    // ══════════════════════════════════════════════════════

    /**
     * সব PPPoE user লিস্ট
     */
    public function getPPPoEUsers(): array
    {
        return $this->api->query([
            '/ppp/secret/print',
            '?service=pppoe',
        ]);
    }

    /**
     * একটি PPPoE user তৈরি করো
     */
    public function createPPPoEUser(array $params): bool
    {
        // $params = ['username', 'password', 'profile', 'local_address', 'remote_address', 'comment']
        $command = [
            '/ppp/secret/add',
            '=name='     . $params['username'],
            '=password=' . $params['password'],
            '=service=pppoe',
            '=profile='  . ($params['profile'] ?? 'default'),
        ];

        if (!empty($params['local_address'])) {
            $command[] = '=local-address=' . $params['local_address'];
        }
        if (!empty($params['remote_address'])) {
            $command[] = '=remote-address=' . $params['remote_address'];
        }
        if (!empty($params['comment'])) {
            $command[] = '=comment=' . $params['comment'];
        }

        $this->api->query($command);
        return true;
    }

    /**
     * PPPoE user আপডেট করো (profile/password পরিবর্তন)
     */
    public function updatePPPoEUser(string $username, array $params): bool
    {
        // প্রথমে .id বের করো
        $id = $this->getPPPoEUserId($username);
        if (!$id) throw new \Exception("PPPoE user '{$username}' not found.");

        $command = ['/ppp/secret/set', '=.id=' . $id];

        if (isset($params['password'])) $command[] = '=password=' . $params['password'];
        if (isset($params['profile']))  $command[] = '=profile='  . $params['profile'];
        if (isset($params['comment']))  $command[] = '=comment='  . $params['comment'];

        $this->api->query($command);
        return true;
    }

    /**
     * PPPoE user মুছে ফেলো
     */
    public function deletePPPoEUser(string $username): bool
    {
        $id = $this->getPPPoEUserId($username);
        if (!$id) return false;

        $this->api->query(['/ppp/secret/remove', '=.id=' . $id]);
        return true;
    }

    /**
     * Customer কে Disable করো (বিল বাকি / suspend)
     */
    public function disablePPPoEUser(string $username): bool
    {
        $id = $this->getPPPoEUserId($username);
        if (!$id) throw new \Exception("PPPoE user '{$username}' not found.");

        $this->api->query(['/ppp/secret/set', '=.id=' . $id, '=disabled=yes']);

        // Active session থাকলে kick করো
        $this->kickActiveSession($username);

        return true;
    }

    /**
     * Customer কে Enable করো (বিল পরিশোধ / reconnect)
     */
    public function enablePPPoEUser(string $username): bool
    {
        $id = $this->getPPPoEUserId($username);
        if (!$id) throw new \Exception("PPPoE user '{$username}' not found.");

        $this->api->query(['/ppp/secret/set', '=.id=' . $id, '=disabled=no']);
        return true;
    }

    /**
     * নাম দিয়ে একটি PPPoE user খোঁজো
     * Return করে user array অথবা null যদি না পাওয়া যায়
     */
    public function getPPPoEUserByName(string $username): ?array
    {
        $result = $this->api->query([
            '/ppp/secret/print',
            '?name=' . $username,
        ]);

        return $result[0] ?? null;
    }

    /**
     * PPPoE user এর .id বের করো
     */
    private function getPPPoEUserId(string $username): ?string
    {
        $result = $this->api->query([
            '/ppp/secret/print',
            '?.id',
            '?name=' . $username,
        ]);

        return $result[0]['.id'] ?? null;
    }

    // ══════════════════════════════════════════════════════
    // ACTIVE SESSIONS
    // ══════════════════════════════════════════════════════

    /**
     * সব active PPPoE session
     */
    public function getActiveSessions(): array
    {
        return $this->api->query(['/ppp/active/print']);
    }

    /**
     * একজন customer এর active session
     */
    public function getCustomerSession(string $username): ?array
    {
        $sessions = $this->api->query([
            '/ppp/active/print',
            '?name=' . $username,
        ]);

        return $sessions[0] ?? null;
    }

    /**
     * Active session kick (force disconnect)
     */
    public function kickActiveSession(string $username): bool
    {
        $sessions = $this->api->query([
            '/ppp/active/print',
            '?name=' . $username,
        ]);

        foreach ($sessions as $session) {
            if (isset($session['.id'])) {
                $this->api->query(['/ppp/active/remove', '=.id=' . $session['.id']]);
            }
        }

        return true;
    }

    // ══════════════════════════════════════════════════════
    // BANDWIDTH CONTROL (Simple Queue)
    // ══════════════════════════════════════════════════════

    /**
     * Simple Queue তৈরি করো (IP-based bandwidth limit)
     */
    public function createQueue(array $params): bool
    {
        // $params = ['name', 'target', 'max_limit_down', 'max_limit_up', 'burst_limit', 'comment']
        $command = [
            '/queue/simple/add',
            '=name='      . $params['name'],
            '=target='    . $params['target'],  // IP/32 বা subnet
            '=max-limit=' . $this->formatSpeed($params['max_limit_up']) . '/' . $this->formatSpeed($params['max_limit_down']),
        ];

        if (!empty($params['burst_limit'])) {
            $command[] = '=burst-limit=' . $this->formatSpeed($params['burst_limit']) . '/' . $this->formatSpeed($params['burst_limit']);
            $command[] = '=burst-time=8/8';
            $command[] = '=burst-threshold=' . $this->formatSpeed(intval($params['max_limit_down'] * 0.8)) . '/' . $this->formatSpeed(intval($params['max_limit_up'] * 0.8));
        }

        if (!empty($params['comment'])) {
            $command[] = '=comment=' . $params['comment'];
        }

        $this->api->query($command);
        return true;
    }

    /**
     * Queue আপডেট করো (package পরিবর্তন)
     */
    public function updateQueue(string $name, array $params): bool
    {
        $id = $this->getQueueId($name);
        if (!$id) throw new \Exception("Queue '{$name}' not found.");

        $command = ['/queue/simple/set', '=.id=' . $id];

        if (isset($params['max_limit_down'], $params['max_limit_up'])) {
            $command[] = '=max-limit=' . $this->formatSpeed($params['max_limit_up']) . '/' . $this->formatSpeed($params['max_limit_down']);
        }
        if (isset($params['target'])) {
            $command[] = '=target=' . $params['target'];
        }

        $this->api->query($command);
        return true;
    }

    /**
     * Queue মুছে ফেলো
     */
    public function deleteQueue(string $name): bool
    {
        $id = $this->getQueueId($name);
        if (!$id) return false;

        $this->api->query(['/queue/simple/remove', '=.id=' . $id]);
        return true;
    }

    /**
     * Queue এর .id বের করো
     */
    private function getQueueId(string $name): ?string
    {
        $result = $this->api->query([
            '/queue/simple/print',
            '?name=' . $name,
        ]);
        return $result[0]['.id'] ?? null;
    }

    /**
     * সব Queue লিস্ট
     */
    public function getQueues(): array
    {
        return $this->api->query(['/queue/simple/print']);
    }

    // ══════════════════════════════════════════════════════
    // PPPoE PROFILES
    // ══════════════════════════════════════════════════════

    /**
     * সব PPPoE profile লিস্ট
     */
    public function getPPPoEProfiles(): array
    {
        return $this->api->query(['/ppp/profile/print']);
    }

    /**
     * PPPoE profile তৈরি করো
     */
    public function createPPPoEProfile(array $params): bool
    {
        // $params = ['name', 'rate_limit', 'local_address', 'remote_address', 'dns_server', 'only_one']
        $command = [
            '/ppp/profile/add',
            '=name='       . $params['name'],
            '=rate-limit=' . $this->formatSpeed($params['upload_mbps']) . '/' . $this->formatSpeed($params['download_mbps']),
        ];

        if (!empty($params['local_address']))  $command[] = '=local-address='  . $params['local_address'];
        if (!empty($params['remote_address'])) $command[] = '=remote-address=' . $params['remote_address'];
        if (!empty($params['dns_server']))     $command[] = '=dns-server='     . $params['dns_server'];
        if (isset($params['only_one']))        $command[] = '=only-one='       . ($params['only_one'] ? 'yes' : 'no');

        $this->api->query($command);
        return true;
    }

    // ══════════════════════════════════════════════════════
    // IP POOL MANAGEMENT
    // ══════════════════════════════════════════════════════

    /**
     * IP Pool লিস্ট
     */
    public function getIPPools(): array
    {
        return $this->api->query(['/ip/pool/print']);
    }

    /**
     * IP Pool তৈরি করো
     */
    public function createIPPool(string $name, string $ranges): bool
    {
        $this->api->query([
            '/ip/pool/add',
            '=name='   . $name,
            '=ranges=' . $ranges,  // e.g. "192.168.1.2-192.168.1.254"
        ]);
        return true;
    }

    // ══════════════════════════════════════════════════════
    // ROUTER STATUS & MONITORING
    // ══════════════════════════════════════════════════════

    /**
     * Router resource info (CPU, RAM, uptime)
     */
    public function getRouterResource(): array
    {
        $result = $this->api->query(['/system/resource/print']);
        return $result[0] ?? [];
    }

    /**
     * Interface লিস্ট
     */
    public function getInterfaces(): array
    {
        return $this->api->query(['/interface/print']);
    }

    /**
     * Router identity (hostname)
     */
    public function getRouterIdentity(): string
    {
        $result = $this->api->query(['/system/identity/print']);
        return $result[0]['name'] ?? 'Unknown';
    }

    /**
     * Online user count
     */
    public function getOnlineUserCount(): int
    {
        return count($this->getActiveSessions());
    }

    // ══════════════════════════════════════════════════════
    // CUSTOMER-LEVEL OPERATIONS
    // (Customer model থেকে directly ব্যবহার করো)
    // ══════════════════════════════════════════════════════

    /**
     * Customer কে MikroTik এ সম্পূর্ণ provision করো
     * (PPPoE user + Queue তৈরি)
     */
    public function provisionCustomer(Customer $customer): bool
    {
        $package = $customer->package;

        // 1. PPPoE Secret তৈরি
        $this->createPPPoEUser([
            'username' => $customer->pppoe_username,
            'password' => $customer->pppoe_password,
            'profile'  => $package->mikrotik_profile ?? 'default',
            'comment'  => "ISP-{$customer->customer_code} | {$customer->name}",
        ]);

        // 2. Simple Queue তৈরি (যদি static IP থাকে)
        if ($customer->ip_address) {
            $this->createQueue([
                'name'          => "ISP-{$customer->customer_code}",
                'target'        => $customer->ip_address . '/32',
                'max_limit_down'=> $package->speed_download,
                'max_limit_up'  => $package->speed_upload,
                'comment'       => $customer->name,
            ]);
        }

        return true;
    }

    /**
     * Customer suspend (বিল বাকি)
     */
    public function suspendCustomer(Customer $customer): bool
    {
        $this->disablePPPoEUser($customer->pppoe_username);
        return true;
    }

    /**
     * Customer restore (বিল দিয়েছে)
     */
    public function restoreCustomer(Customer $customer): bool
    {
        $this->enablePPPoEUser($customer->pppoe_username);
        return true;
    }

    /**
     * Customer সম্পূর্ণ মুছে ফেলো
     */
    public function removeCustomer(Customer $customer): bool
    {
        $this->deletePPPoEUser($customer->pppoe_username);
        if ($customer->ip_address) {
            $this->deleteQueue("ISP-{$customer->customer_code}");
        }
        return true;
    }

    /**
     * Customer এর package upgrade/downgrade
     */
    public function changeCustomerPackage(Customer $customer): bool
    {
        $package = $customer->package;

        $this->updatePPPoEUser($customer->pppoe_username, [
            'profile' => $package->mikrotik_profile ?? 'default',
        ]);

        if ($customer->ip_address) {
            $this->updateQueue("ISP-{$customer->customer_code}", [
                'max_limit_down' => $package->speed_download,
                'max_limit_up'   => $package->speed_upload,
            ]);
        }

        return true;
    }

    // ══════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════

    /**
     * Mbps → RouterOS format (e.g. 10M, 1G, 512k)
     */
    private function formatSpeed(int $mbps): string
    {
        if ($mbps >= 1000) return ($mbps / 1000) . 'G';
        if ($mbps >= 1)    return $mbps . 'M';
        return ($mbps * 1000) . 'k';
    }
}
