<?php

namespace App\Services\Olt;

use App\Models\Olt;
use Illuminate\Support\Facades\Log;

/**
 * VSOL V1600D8 Web Scraping Service
 * Login: POST /action/login
 * ONU List: /onuauthinfo.html?select=PORT
 * OPM Diag: /onuopmdiag.html?select=PORT
 * ONU Status: /onustatusinfo.html?select=PORT
 */
class VsolWebScraper
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $cookieJar;

    public function __construct(Olt $olt)
    {
        $webIp         = $olt->web_ip ?: $olt->ip_address;
        $this->baseUrl = rtrim("https://{$webIp}", '/');
        $this->username = $olt->web_username ?? 'admin';
        $this->password = $olt->web_password ?? '';
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'vsol_');
    }

    // ══════════════════════════════════════════
    // PUBLIC
    // ══════════════════════════════════════════

    public function fetchAllOnu(): array
    {
        if (! $this->login()) {
            throw new \RuntimeException('VSOL login failed — username/password ভুল হতে পারে।');
        }

        $allOnu = [];

        for ($port = 1; $port <= 8; $port++) {
            try {
                $portOnu = $this->fetchOnuByPort($port);
                $allOnu  = array_merge($allOnu, $portOnu);
                Log::info("VSOL PON{$port}: " . count($portOnu) . " ONUs found");
            } catch (\Exception $e) {
                Log::warning("VSOL PON{$port} fetch failed: " . $e->getMessage());
            }
        }

        $this->logout();
        $this->cleanupCookie();

        return $allOnu;
    }

    // ══════════════════════════════════════════
    // PRIVATE: Login/Logout
    // ══════════════════════════════════════════

    private function login(): bool
    {
        // Step 1: GET login page (cookie initialize)
        $this->curl("{$this->baseUrl}/action/login.html", 'GET');

        // Step 2: POST login
        // Form action = main.html, fields: user, pass, who=100
        $response = $this->curl(
            "{$this->baseUrl}/action/main.html",
            'POST',
            http_build_query([
                'user' => $this->username,
                'pass' => $this->password,
                'who'  => '100',
            ])
        );

        if ($response === false) return false;

        // Login fail হলে login page এ থাকে
        $isLoginPage = str_contains($response, 'lgform') ||
                       str_contains($response, 'login.html') ||
                       str_contains($response, 'loginBtn');

        return ! $isLoginPage;
    }

    private function logout(): void
    {
        $this->curl("{$this->baseUrl}/action/logout", 'GET');
    }

    // ══════════════════════════════════════════
    // PRIVATE: একটা PORT এর ONU data আনো
    // ══════════════════════════════════════════

    private function fetchOnuByPort(int $port): array
    {
        // ONU List — GET onuauthinfo.html?select=PORT (iframe content)
        $listHtml = $this->curl(
            "{$this->baseUrl}/action/onuauthinfo.html?select={$port}",
            'GET'
        );

        if (! $listHtml) return [];

        // Login page এ redirect হয়েছে কিনা
        if (str_contains($listHtml, 'lgform') || str_contains($listHtml, 'loginBtn')) {
            Log::warning("VSOL PON{$port}: session expired");
            return [];
        }

        // Debug: প্রথম PON এ HTML দেখি
        if ($port === 1) {
            Log::debug("VSOL PON1 HTML: " . substr(strip_tags($listHtml), 0, 300));
        }

        $listData = $this->parseOnuList($listHtml, $port);

        if (empty($listData)) return [];

        // OPM Diag — GET onuopmdiag.html?select=PORT
        $diagHtml = $this->curl(
            "{$this->baseUrl}/action/onuopmdiag.html?select={$port}",
            'GET'
        );
        $diagData = $diagHtml ? $this->parseOpmDiag($diagHtml) : [];

        // ONU Status — GET onustatusinfo.html?select=PORT
        $statusHtml = $this->curl(
            "{$this->baseUrl}/action/onustatusinfo.html?select={$port}",
            'GET'
        );
        $statusData = $statusHtml ? $this->parseOnuStatus($statusHtml) : [];

        return $this->mergeOnuData($listData, $diagData, $statusData);
    }

    // ══════════════════════════════════════════
    // PRIVATE: HTML Parse
    // ══════════════════════════════════════════

    /**
     * ONU List parse
     * HTML pattern: <td class='hd'>EPON0/1:1</td> <td><font color=...>Online</font></td> <td>MAC</td>
     */
    private function parseOnuList(string $html, int $port): array
    {
        $result = [];

        // প্রতিটা <tr> block থেকে data বের করো
        preg_match_all('/<tr>(.*?)<\/tr>/si', $html, $rows);

        foreach ($rows[1] as $row) {
            // সব <td> content বের করো
            preg_match_all('/<td[^>]*>(.*?)<\/td>/si', $row, $cols);
            $cols = array_map(fn($c) => trim(strip_tags($c)), $cols[1]);

            if (count($cols) < 3) continue;

            // EPON0/1:1 format check
            if (! preg_match('/EPON\d+\/\d+:\d+/i', $cols[0])) continue;

            $mac = strtoupper(trim($cols[2]));
            if (! $this->isValidMac($mac)) continue;

            // Status check — color=#008040 = Online, color=#ff0000 = Offline
            $isOnline = str_contains($row, '#008040') || 
                        stripos($row, '>Online<') !== false;

            $result[$mac] = [
                'onu_id'   => trim($cols[0]),
                'olt_port' => "EPON0/{$port}",
                'mac'      => $mac,
                'status'   => $isOnline ? 'online' : 'offline',
            ];
        }

        return $result;
    }

    /**
     * OPM Diag parse → [mac => [rx_power, distance, temperature]]
     * HTML: EPON0/1:1 | MAC | Distance | Temperature | Supply | TX_Bias | TX_Power | RX_Power
     */
    private function parseOpmDiag(string $html): array
    {
        $result = [];

        preg_match_all('/<tr>(.*?)<\/tr>/si', $html, $rows);

        foreach ($rows[1] as $row) {
            preg_match_all('/<td[^>]*>(.*?)<\/td>/si', $row, $cols);
            $cols = array_map(fn($c) => trim(strip_tags($c)), $cols[1]);

            if (count($cols) < 8) continue;

            $mac = strtoupper(trim($cols[1]));
            if (! $this->isValidMac($mac)) continue;

            $result[$mac] = [
                'distance'    => is_numeric($cols[2]) ? (int) $cols[2] : null,
                'temperature' => is_numeric($cols[3]) ? (float) $cols[3] : null,
                'rx_power'    => is_numeric($cols[7]) ? (float) $cols[7] : null,
            ];
        }

        return $result;
    }

    /**
     * ONU Status parse → [mac => [last_deregister_time, deregister_reason]]
     */
    private function parseOnuStatus(string $html): array
    {
        $result = [];

        preg_match_all('/<tr>(.*?)<\/tr>/si', $html, $rows);

        foreach ($rows[1] as $row) {
            preg_match_all('/<td[^>]*>(.*?)<\/td>/si', $row, $cols);
            $cols = array_map(fn($c) => trim(strip_tags($c)), $cols[1]);

            // Columns: ONU_ID | Status | MAC | Description | Distance | RTT | Last_Register | Last_Deregister | Deregister_Reason | Alive | Upgrade | Detail
            if (count($cols) < 9) continue;

            $mac = strtoupper(trim($cols[2]));
            if (! $this->isValidMac($mac)) continue;

            $result[$mac] = [
                'distance'             => is_numeric($cols[4]) ? (int) $cols[4] : null,
                'last_deregister_time' => $this->parseDate($cols[7] ?? ''),
                'deregister_reason'    => trim($cols[8] ?? '') ?: null,
            ];
        }

        return $result;
    }

    private function mergeOnuData(array $list, array $diag, array $status): array
    {
        $merged = [];

        foreach ($list as $mac => $data) {
            $merged[] = array_merge(
                $data,
                $diag[$mac]   ?? ['rx_power' => null, 'temperature' => null],
                $status[$mac] ?? ['distance' => null, 'last_deregister_time' => null, 'deregister_reason' => null]
            );
        }

        return $merged;
    }

    // ══════════════════════════════════════════
    // PRIVATE: CURL Helper
    // ══════════════════════════════════════════

    private function curl(string $url, string $method = 'GET', string $postData = ''): string|false
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEFILE     => $this->cookieJar,
            CURLOPT_COOKIEJAR      => $this->cookieJar,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml',
                'Accept-Language: en-US,en;q=0.9',
            ],
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            Log::warning("VSOL curl error [{$url}]: {$error}");
            return false;
        }

        Log::debug("VSOL curl [{$method}] {$url} → HTTP {$httpCode}");

        return $response;
    }

    // ══════════════════════════════════════════
    // PRIVATE: Helpers
    // ══════════════════════════════════════════

    private function isValidMac(string $mac): bool
    {
        return (bool) preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/i', $mac);
    }

    private function parseDate(string $date): ?string
    {
        $date = trim($date);
        if (empty($date) || $date === 'N/A') return null;

        try {
            // VSOL format: "2020/10/20 21:38:18"
            $date = str_replace('/', '-', $date);
            $carbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date);
            // Year sanity check — Buddhist calendar fix
            if ($carbon->year > 2100) {
                $carbon->subYears(543);
            }
            return $carbon->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function cleanupCookie(): void
    {
        if (file_exists($this->cookieJar)) {
            @unlink($this->cookieJar);
        }
    }
}
