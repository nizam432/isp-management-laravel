<?php

namespace App\Services\Olt;

use App\Models\Olt;
use App\Models\OltUser;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

/**
 * OLT Sync Service
 * Strategy: SNMP চেষ্টা → fail হলে → Web Scraping
 */
class OltSyncService
{
    // ══════════════════════════════════════════
    // PUBLIC: একটা OLT sync করো
    // ══════════════════════════════════════════

    public function sync(Olt $olt): array
    {
        $method = 'none';
        $onuList = [];

        // ── Step 1: SNMP try ──────────────────
        try {
            $snmp = new VsolSnmpService($olt);

            if ($snmp->isAvailable()) {
                $onuList = $snmp->fetchAllOnu();
                $method  = 'snmp';
                Log::info("OLT [{$olt->ip_address}] synced via SNMP: " . count($onuList) . " ONUs");
            }
        } catch (\Exception $e) {
            Log::warning("OLT [{$olt->ip_address}] SNMP failed, trying web scraping: " . $e->getMessage());
        }

        // ── Step 2: SNMP fail হলে Web Scraping ──
        if (empty($onuList) && $olt->web_ip && $olt->web_password) {
            try {
                $scraper = new VsolWebScraper($olt);
                $onuList = $scraper->fetchAllOnu();
                $method  = 'web_scraping';
                Log::info("OLT [{$olt->ip_address}] synced via Web Scraping: " . count($onuList) . " ONUs");
            } catch (\Exception $e) {
                Log::error("OLT [{$olt->ip_address}] Web Scraping failed: " . $e->getMessage());
                throw new \RuntimeException("OLT sync failed — SNMP এবং Web Scraping দুটোই fail হয়েছে।");
            }
        }

        if (empty($onuList)) {
            throw new \RuntimeException("OLT তে কোনো ONU পাওয়া যায়নি বা credentials সঠিক নয়।");
        }

        // ── Step 3: Database এ save ──────────
        $saved = $this->saveOltUsers($olt, $onuList);

        // ── Step 4: OLT timestamp update ─────
        $olt->update(['last_synced_at' => now()]);

        return [
            'method'  => $method,
            'total'   => count($onuList),
            'saved'   => $saved['saved'],
            'updated' => $saved['updated'],
        ];
    }

    // ══════════════════════════════════════════
    // PRIVATE: OLT Users save/update করো
    // ══════════════════════════════════════════

    private function saveOltUsers(Olt $olt, array $onuList): array
    {
        $saved   = 0;
        $updated = 0;

        // MAC → customer_id map (performance এর জন্য একবারে সব load)
        $customerMap = Customer::whereNotNull('mac_address')
            ->pluck('id', 'mac_address')
            ->mapWithKeys(fn($id, $mac) => [strtoupper($mac) => $id])
            ->toArray();

        foreach ($onuList as $onu) {
            $mac = $onu['mac'] ?? null;
            if (! $mac) continue;

            // MAC দিয়ে customer খোঁজো
            $customerId = $customerMap[strtoupper($mac)] ?? null;

            $data = [
                'olt_id'               => $olt->id,
                'mac_address'          => $mac,
                'olt_port'             => $onu['olt_port'] ?? null,
                'onu_status'           => $onu['status'] ?? 'unknown',
                'optical_power'        => $onu['rx_power'] ?? null,
                'distance'             => $onu['distance'] ?? null,
                'last_deregister_time' => $onu['last_deregister_time'] ?? null,
                'deregister_reason'    => $onu['deregister_reason'] ?? null,
                'last_synced_at'       => now(),
                'customer_id'          => $customerId,
            ];

            $existing = OltUser::where('olt_id', $olt->id)
                               ->where('mac_address', $mac)
                               ->first();

            if ($existing) {
                $existing->update(array_merge($data, [
                    'previous_snapshot' => $existing->only([
                        'onu_status', 'optical_power', 'distance'
                    ]),
                ]));
                $updated++;
            } else {
                OltUser::create($data);
                $saved++;
            }
        }

        return ['saved' => $saved, 'updated' => $updated];
    }
}
