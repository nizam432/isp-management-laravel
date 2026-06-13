<?php

namespace App\Services\Olt;

use App\Models\Olt;
use Illuminate\Support\Facades\Log;

/**
 * VSOL SNMP Service
 * UDP Port 161 open থাকলে এটা কাজ করবে
 */
class VsolSnmpService
{
    private string $ip;
    private string $community;

    // VSOL V1600D8 ONU List OID
    // এই OID গুলো VSOL এর standard MIB থেকে নেওয়া
    private const OID_ONU_MAC_LIST    = '1.3.6.1.4.1.34592.1.3.3.1.2';   // ONU MAC address
    private const OID_ONU_STATUS      = '1.3.6.1.4.1.34592.1.3.3.1.13';  // ONU status (1=online, 2=offline)
    private const OID_ONU_RX_POWER    = '1.3.6.1.4.1.34592.1.3.6.1.3';   // RX Power (dBm * 100)
    private const OID_ONU_DISTANCE    = '1.3.6.1.4.1.34592.1.3.3.1.21';  // Distance (meters)

    public function __construct(Olt $olt)
    {
        // IP:Port হলে শুধু IP নাও
        $ip          = $olt->ip_address;
        $this->ip    = str_contains($ip, ':') ? explode(':', $ip)[0] : $ip;
        $this->community = $olt->community ?? 'public';
    }

    // ══════════════════════════════════════════
    // PUBLIC: SNMP available কিনা check
    // ══════════════════════════════════════════

    public function isAvailable(): bool
    {
        if (! function_exists('snmpget')) return false;

        try {
            $result = @snmpget($this->ip, $this->community, '1.3.6.1.2.1.1.1.0', 2000000, 1);
            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ══════════════════════════════════════════
    // PUBLIC: সব ONU data আনো
    // ══════════════════════════════════════════

    /**
     * SNMP দিয়ে সব ONU data fetch করে return করে
     * @return array [ ['onu_id'=>, 'mac'=>, 'status'=>, 'rx_power'=>, ...], ... ]
     */
    public function fetchAllOnu(): array
    {
        if (! function_exists('snmp2_walk')) {
            throw new \RuntimeException('PHP SNMP extension not available');
        }

        try {
            $macList    = $this->snmpWalk(self::OID_ONU_MAC_LIST);
            $statusList = $this->snmpWalk(self::OID_ONU_STATUS);
            $rxPowerList = $this->snmpWalk(self::OID_ONU_RX_POWER);
            $distanceList = $this->snmpWalk(self::OID_ONU_DISTANCE);
        } catch (\Exception $e) {
            Log::error("VSOL SNMP walk failed [{$this->ip}]: " . $e->getMessage());
            throw $e;
        }

        if (empty($macList)) return [];

        $result = [];

        foreach ($macList as $oid => $mac) {
            // OID এর শেষ অংশ = index (port.onu_number)
            $index = $this->extractIndex($oid);
            $mac   = $this->formatMac($mac);

            if (! $mac) continue;

            // Status: 1=online, অন্যথা offline
            $statusRaw = $statusList[$index] ?? '2';
            $status    = (int) $this->cleanSnmpValue($statusRaw) === 1 ? 'online' : 'offline';

            // RX Power: raw value / 100 = dBm
            $rxRaw  = $rxPowerList[$index] ?? null;
            $rxPower = $rxRaw ? round($this->cleanSnmpValue($rxRaw) / 100, 2) : null;

            // Distance
            $distRaw  = $distanceList[$index] ?? null;
            $distance = $distRaw ? (int) $this->cleanSnmpValue($distRaw) : null;

            // Port format: EPON0/1:1
            $portParts = explode('.', $index);
            $oltPort   = count($portParts) >= 2
                ? 'EPON0/' . $portParts[0] . ':' . $portParts[1]
                : 'EPON0/' . $index;

            $result[] = [
                'onu_id'    => $oltPort,
                'olt_port'  => 'EPON0/' . ($portParts[0] ?? $index),
                'mac'       => $mac,
                'status'    => $status,
                'rx_power'  => $rxPower,
                'distance'  => $distance,
                'last_deregister_time' => null,
                'deregister_reason'    => null,
            ];
        }

        return $result;
    }

    // ══════════════════════════════════════════
    // PRIVATE: SNMP Helpers
    // ══════════════════════════════════════════

    private function snmpWalk(string $oid): array
    {
        $result = @snmp2_walk($this->ip, $this->community, $oid, 2000000, 1);
        return $result ?: [];
    }

    private function extractIndex(string $oid): string
    {
        // OID এর শেষ দুটো segment নাও (port.onu_number)
        $parts = explode('.', $oid);
        $len   = count($parts);
        return $len >= 2 ? $parts[$len - 2] . '.' . $parts[$len - 1] : end($parts);
    }

    private function formatMac(string $raw): ?string
    {
        // SNMP MAC format: "Hex-STRING: A0 7E 12 25 26 E0" বা হেক্স string
        $raw = preg_replace('/[^0-9A-Fa-f]/', '', $raw);
        if (strlen($raw) !== 12) return null;

        return strtoupper(implode(':', str_split($raw, 2)));
    }

    private function cleanSnmpValue(string $value): string
    {
        // "INTEGER: 1" বা "Gauge32: 100" থেকে শুধু number নাও
        if (preg_match('/:\s*(-?\d+)/', $value, $m)) return $m[1];
        return preg_replace('/[^0-9\-]/', '', $value);
    }
}
