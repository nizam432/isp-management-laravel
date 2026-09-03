<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsTemplate;
use App\Models\SmsTemplateMapping;
use App\Models\TenantSmsSetting;
use App\Models\ResellerSmsSetting;
use App\Models\ResellerSmsTemplate;
use App\Models\ResellerSmsTemplateMapping;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * SmsService — Tenant Aware (and now Reseller Aware)
 * ─────────────────────────────────────────────
 * Takes the gateway from the ISP company's own SMS settings, OR — when a
 * $macResellerId is passed in — from that MAC Reseller's own SMS settings
 * instead (each reseller can configure their own gateway credentials,
 * separate from the tenant-wide ones). The ISP/reseller can only see
 * gateways that the Super Admin has enabled.
 */
class SmsService
{
    private ?string $tenantId;
    private ?int $macResellerId;

    public function __construct(?string $tenantId = null, ?int $macResellerId = null)
    {
        if ($tenantId === null && function_exists('tenant') && tenant()) {
            $tenantId = tenant()->getTenantKey();
        }

        $this->tenantId      = $tenantId;
        $this->macResellerId = $macResellerId;
    }

    /**
     * Send an SMS
     */
    public function send(string $mobile, string $message, string $type = 'general'): bool
    {
        // Find the active gateway (reseller's own, if set — else tenant's)
        $setting = $this->getActiveSetting();

        if (!$setting) {
            Log::warning('SMS: no active gateway found.');
            return false;
        }

        $gateway = SmsGateway::where('slug', $setting->gateway_slug)->first();
        if (!$gateway) {
            Log::error("SMS: gateway slug '{$setting->gateway_slug}' not found in sms_gateways table.");
            return false;
        }

        $mobile = $this->formatMobile($mobile);

        try {
            $response = match($gateway->slug) {
                '24bulksmsbd'  => $this->send24BulkSMS($setting->config, $mobile, $message),
                'ssl_wireless' => $this->sendSSLWireless($setting->config, $mobile, $message),
                'muthofun'     => $this->sendMuthofun($setting->config, $mobile, $message),
                'alpha_net'    => $this->sendAlphaNet($setting->config, $mobile, $message),
                'twilio'       => $this->sendTwilio($setting->config, $mobile, $message),
                default        => throw new \Exception("Unknown gateway: {$gateway->slug}"),
            };

            SmsLog::create([
                'gateway'         => $gateway->slug,
                'mobile'          => $mobile,
                'phone'           => $mobile,
                'message'         => $message,
                'type'            => $type,
                'status'          => 'sent',
                'response'        => $response,
                'count_sms'       => $this->countSms($message),
                'mac_reseller_id' => $this->macResellerId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("SMS failed [{$gateway->slug}]: " . $e->getMessage());

            SmsLog::create([
                'gateway'         => $gateway->slug,
                'mobile'          => $mobile,
                'phone'           => $mobile,
                'message'         => $message,
                'type'            => $type,
                'status'          => 'failed',
                'response'        => $e->getMessage(),
                'count_sms'       => $this->countSms($message),
                'mac_reseller_id' => $this->macResellerId,
            ]);

            return false;
        }
    }

    public function sendMany(array $mobiles, string $message, string $type = 'general'): int
    {
        $sent = 0;
        foreach ($mobiles as $mobile) {
            if ($this->send($mobile, $message, $type)) $sent++;
        }
        return $sent;
    }

    /**
     * Dynamic SMS — sends a different message to multiple numbers in a single call.
     */
    public function sendDynamic(array $recipients, string $type = 'general'): array
    {
        if (empty($recipients)) {
            return ['sent' => 0, 'failed' => 0];
        }

        $setting = $this->getActiveSetting();
        if (!$setting) {
            Log::warning('SMS: no active gateway found.');
            return ['sent' => 0, 'failed' => count($recipients)];
        }

        $gateway = SmsGateway::where('slug', $setting->gateway_slug)->first();
        if (!$gateway) {
            Log::error("SMS dynamic: gateway slug '{$setting->gateway_slug}' not found in sms_gateways table.");
            return ['sent' => 0, 'failed' => count($recipients)];
        }

        $recipients = array_map(function ($r) {
            return [
                'mobile'  => $this->formatMobile($r['mobile']),
                'message' => $r['message'],
            ];
        }, $recipients);

        if ($gateway->slug !== '24bulksmsbd') {
            $sent = 0;
            foreach ($recipients as $r) {
                if ($this->send($r['mobile'], $r['message'], $type)) $sent++;
            }
            return ['sent' => $sent, 'failed' => count($recipients) - $sent];
        }

        try {
            $response = $this->send24BulkSMSDynamic($setting->config, $recipients);

            foreach ($recipients as $r) {
                SmsLog::create([
                    'gateway'         => $gateway->slug,
                    'mobile'          => $r['mobile'],
                    'phone'           => $r['mobile'],
                    'message'         => $r['message'],
                    'type'            => $type,
                    'status'          => 'sent',
                    'response'        => $response,
                    'count_sms'       => $this->countSms($r['message']),
                    'mac_reseller_id' => $this->macResellerId,
                ]);
            }

            return ['sent' => count($recipients), 'failed' => 0];

        } catch (\Exception $e) {
            Log::error("SMS dynamic batch failed [{$gateway->slug}]: " . $e->getMessage());

            foreach ($recipients as $r) {
                SmsLog::create([
                    'gateway'         => $gateway->slug,
                    'mobile'          => $r['mobile'],
                    'phone'           => $r['mobile'],
                    'message'         => $r['message'],
                    'type'            => $type,
                    'status'          => 'failed',
                    'response'        => $e->getMessage(),
                    'count_sms'       => $this->countSms($r['message']),
                    'mac_reseller_id' => $this->macResellerId,
                ]);
            }

            return ['sent' => 0, 'failed' => count($recipients)];
        }
    }

    // ── SMS Templates ──────────────────────────────

    private function renderTemplate(string $type, array $data, string $fallback): string
    {
        // Reseller-triggered sends check THEIR OWN mapping/template first.
        if ($this->macResellerId) {
            $mapping = ResellerSmsTemplateMapping::where('mac_reseller_id', $this->macResellerId)
                ->where('type', $type)->first();
            if ($mapping) {
                $template = ResellerSmsTemplate::where('mac_reseller_id', $this->macResellerId)
                    ->active()->where('title', $mapping->title)->first();
                if ($template) {
                    return $template->render($data);
                }
            }
        }

        $mapping = SmsTemplateMapping::where('type', $type)->first();
        if ($mapping) {
            $template = SmsTemplate::active()->where('title', $mapping->title)->first();
            if ($template) {
                return $template->render($data);
            }
        }
        return $fallback;
    }

    private function countSms(string $message): int
    {
        $message = trim($message);
        if ($message === '') return 0;

        $totalLineBreak = substr_count($message, "\n");
        $encoding       = mb_detect_encoding($message);

        if ($encoding === 'UTF-8' && mb_strlen($message, 'UTF-8') !== strlen($message)) {
            $totalChar = mb_strlen($message, 'UTF-8') + $totalLineBreak;

            if ($totalChar <= 70) return 1;
            if ($totalChar <= 134) return 2;
            if ($totalChar <= 200) return 3;
            if ($totalChar <= 267) return 4;
            if ($totalChar <= 334) return 5;
            if ($totalChar <= 401) return 6;
            if ($totalChar <= 468) return 7;
            if ($totalChar <= 535) return 8;

            $remaining = $totalChar - 536;
            return (int) floor($remaining / 66) + 8 + 1;
        }

        $totalChar = strlen($message);

        if ($totalChar <= 160) return 1;
        if ($totalChar <= 306) return 2;
        if ($totalChar <= 459) return 3;
        if ($totalChar <= 612) return 4;
        if ($totalChar <= 765) return 5;
        if ($totalChar <= 918) return 6;
        if ($totalChar <= 1071) return 7;
        if ($totalChar <= 1224) return 8;

        $remaining = $totalChar - 1224;
        return (int) floor($remaining / 153) + 8 + 1;
    }

    private function formatMonth(string $month): string
    {
        try {
            return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y');
        } catch (\Exception $e) {
            return $month;
        }
    }

    public function buildBillDueMessage(string $name, float $amount, string $month): string
    {
        return $this->renderTemplate('bill_due', [
            'name'   => $name,
            'amount' => $amount,
            'month'  => $this->formatMonth($month),
        ], "প্রিয় {$name}, আপনার {$this->formatMonth($month)} মাসের ইন্টারনেট বিল {$amount} টাকা বাকি আছে। দ্রুত পরিশোধ করুন।");
    }

    public function sendBillDue(string $mobile, string $name, float $amount, string $month): bool
    {
        $message = $this->buildBillDueMessage($name, $amount, $month);
        return $this->send($mobile, $message, 'bill_due');
    }

    public function sendPaymentConfirm(string $mobile, string $name, float $amount, string $method): bool
    {
        $message = $this->renderTemplate('payment_confirm', [
            'name'   => $name,
            'amount' => $amount,
            'method' => $method,
        ], "প্রিয় {$name}, আপনার {$amount} টাকা পেমেন্ট ({$method}) সফলভাবে গ্রহণ করা হয়েছে। ধন্যবাদ।");

        return $this->send($mobile, $message, 'payment_confirm');
    }

    public function sendSuspendNotice(string $mobile, string $name): bool
    {
        $message = $this->renderTemplate('suspend', [
            'name' => $name,
        ], "প্রিয় {$name}, বিল বাকি থাকায় আপনার ইন্টারনেট সংযোগ সাময়িকভাবে বন্ধ করা হয়েছে।");

        return $this->send($mobile, $message, 'suspend');
    }

    public function sendRestoreNotice(string $mobile, string $name): bool
    {
        $message = $this->renderTemplate('restore', [
            'name' => $name,
        ], "প্রিয় {$name}, আপনার ইন্টারনেট সংযোগ পুনরায় চালু করা হয়েছে। ধন্যবাদ।");

        return $this->send($mobile, $message, 'restore');
    }

    public function sendWelcome(string $mobile, string $name, string $user, string $pass): bool
    {
        $message = $this->renderTemplate('welcome', [
            'name'           => $name,
            'pppoe_username' => $user,
            'pppoe_password' => $pass,
        ], "প্রিয় {$name}, আপনার ইন্টারনেট সংযোগ চালু হয়েছে। User: {$user}, Pass: {$pass}।");

        return $this->send($mobile, $message, 'welcome');
    }

    public function buildInvoiceGeneratedMessage(string $name, float $totalDue, string $month): string
    {
        return $this->renderTemplate('invoice_generated', [
            'name'     => $name,
            'bill_due' => $totalDue,
            'month'    => $this->formatMonth($month),
        ], "প্রিয় {$name}, আপনার {$this->formatMonth($month)} মাসের ইনভয়েস তৈরি হয়েছে। আপনার মোট বিল: {$totalDue} টাকা। দ্রুত পরিশোধ করুন।");
    }

    public function sendInvoiceGenerated(string $mobile, string $name, float $totalDue, string $month): bool
    {
        $message = $this->buildInvoiceGeneratedMessage($name, $totalDue, $month);
        return $this->send($mobile, $message, 'invoice_generated');
    }

    // ── Support Ticket Notifications ──────────────

    public function buildTicketCreatedMessage(string $name, string $ticketNo, string $category, string $complainedNo): string
    {
        return $this->renderTemplate('support_ticket_created', [
            'name'          => $name,
            'ticket_no'     => $ticketNo,
            'category'      => $category,
            'complained_no' => $complainedNo,
        ], "প্রিয় {$name}, আপনার সাপোর্ট টিকিট #{$ticketNo} সফলভাবে গ্রহণ করা হয়েছে। বিষয়: {$category}। আমাদের টিম শীঘ্রই যোগাযোগ করবে।");
    }

    public function sendTicketCreated(string $mobile, string $name, string $ticketNo, string $category, string $complainedNo): bool
    {
        $message = $this->buildTicketCreatedMessage($name, $ticketNo, $category, $complainedNo);
        return $this->send($mobile, $message, 'support_ticket_created');
    }

    public function buildTicketSolvedMessage(string $name, string $ticketNo): string
    {
        return $this->renderTemplate('support_ticket_solved', [
            'name'      => $name,
            'ticket_no' => $ticketNo,
        ], "প্রিয় {$name}, আপনার সাপোর্ট টিকিট #{$ticketNo} সমাধান করা হয়েছে। ধন্যবাদ।");
    }

    public function sendTicketSolved(string $mobile, string $name, string $ticketNo): bool
    {
        $message = $this->buildTicketSolvedMessage($name, $ticketNo);
        return $this->send($mobile, $message, 'support_ticket_solved');
    }

    public function buildTicketAssignedMessage(string $employeeName, string $ticketNo, string $complainedNo): string
    {
        return $this->renderTemplate('support_ticket_assigned', [
            'name'          => $employeeName,
            'ticket_no'     => $ticketNo,
            'complained_no' => $complainedNo,
        ], "আপনাকে সাপোর্ট টিকিট #{$ticketNo} ({$complainedNo}) এসাইন করা হয়েছে। দ্রুত সমাধান করুন।");
    }

    public function sendTicketAssigned(string $mobile, string $employeeName, string $ticketNo, string $complainedNo): bool
    {
        $message = $this->buildTicketAssignedMessage($employeeName, $ticketNo, $complainedNo);
        return $this->send($mobile, $message, 'support_ticket_assigned');
    }

    // ── Private Helpers ────────────────────────────

    public function getBalance(): ?string
    {
        $setting = $this->getActiveSetting();
        if (!$setting) return null;

        $gateway = SmsGateway::where('slug', $setting->gateway_slug)->first();
        if (!$gateway) return null;

        if ($gateway->slug !== '24bulksmsbd') {
            return null;
        }

        $cacheKey = 'sms_balance_' . $gateway->slug . ($this->macResellerId ? '_reseller_' . $this->macResellerId : '');

        return Cache::remember($cacheKey, 300, function () use ($setting) {
            try {
                return $this->send24BulkSMSBalance($setting->config);
            } catch (\Exception $e) {
                Log::error('SMS balance fetch failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    private function send24BulkSMSBalance(array $config): ?string
    {
        $ch = curl_init('https://www.24bulksmsbd.com/api/balance');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'customer_id' => $config['customer_id'],
                'api_key'     => $config['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("cURL error: {$curlError}");
        }

        $decoded = json_decode($response, true);

        if (is_array($decoded)) {
            return $decoded['balance'] ?? $decoded['sms_balance'] ?? $decoded['credit'] ?? null;
        }

        return null;
    }

    /**
     * Resolves the active gateway setting to use, in priority order:
     *   1. THIS reseller's own ResellerSmsSetting (if a mac_reseller_id was passed in)
     *   2. The tenant-wide TenantSmsSetting (existing Admin behavior)
     *   3. Global fallback gateway config (existing backward-compatible behavior)
     *
     * Both ResellerSmsSetting and TenantSmsSetting expose the same
     * `gateway_slug` / `config` attributes, so callers can use either
     * interchangeably without caring which one was actually resolved.
     *
     * Was: ->whereHas('gateway', fn($q) => $q->where('is_enabled', true))
     * That built a SINGLE SQL query joining tenant_sms_settings (tenant DB)
     * with sms_gateways (central DB — SmsGateway's connection is hardcoded
     * to central). Eloquent can't do a cross-database JOIN, so this always
     * threw "table sms_gateways doesn't exist" (it was looking for that
     * table inside the tenant's own database). Split into two separate
     * queries instead: first get enabled gateway slugs from central, then
     * filter tenant_sms_settings by that list.
     */
    private function getActiveSetting(): TenantSmsSetting|ResellerSmsSetting|null
    {
        $enabledGatewaySlugs = SmsGateway::where('is_enabled', true)->pluck('slug');

        if ($this->macResellerId) {
            $setting = ResellerSmsSetting::where('mac_reseller_id', $this->macResellerId)
                ->where('is_active', true)
                ->whereIn('gateway_slug', $enabledGatewaySlugs)
                ->first();

            if ($setting) return $setting;

            // this reseller has no active gateway of their own — fall through
            // to the tenant/global gateway below (same as Admin would use)
        }

        if ($this->tenantId) {
            $setting = TenantSmsSetting::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->whereIn('gateway_slug', $enabledGatewaySlugs)
                ->first();

            if ($setting) return $setting;

            // No tenant-specific setting matched — fall through to global.
        }

        // Fallback: global gateway config (backward compatible).
        $gateway = SmsGateway::where('is_active', true)->first();
        if (!$gateway) return null;

        $setting = new TenantSmsSetting();
        $setting->gateway_slug = $gateway->slug;
        $setting->config       = $gateway->config;
        return $setting;
    }

    private function send24BulkSMS(array $config, string $mobile, string $message): string
    {
        $ch = curl_init('https://www.24bulksmsbd.com/api/smsSendApi');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'customer_id' => $config['customer_id'],
                'api_key'     => $config['api_key'],
                'message'     => $message,
                'mobile_no'   => $mobile,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 60,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (isset($decoded['status']) && $decoded['status'] === 'ok') {
            return $response;
        }
        throw new \Exception($decoded['message'] ?? $response);
    }

    private function send24BulkSMSDynamic(array $config, array $recipients): string
    {
        $messages = array_map(fn($r) => [
            'to'      => $r['mobile'],
            'message' => $r['message'],
        ], $recipients);

        $ch = curl_init('https://www.24bulksmsbd.com/api/DynamicSMSApi');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'customer_id' => $config['customer_id'],
                'api_key'     => $config['api_key'],
                'messages'    => json_encode($messages),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 60,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("cURL error: {$curlError}");
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['status']) && $decoded['status'] === 'ok') {
            return $response;
        }
        throw new \Exception($decoded['message'] ?? $response);
    }

    private function sendSSLWireless(array $config, string $mobile, string $message): string
    {
        $response = file_get_contents(
            'https://sms.sslwireless.com/pushapi/dynamic/server.php?' . http_build_query([
                'user'   => $config['username'],
                'pass'   => $config['password'],
                'sid'    => $config['sid'],
                'sms'    => $message,
                'mobile' => $mobile,
                'tid'    => time(),
            ])
        );
        return $response;
    }

    private function sendMuthofun(array $config, string $mobile, string $message): string
    {
        $ch = curl_init('https://api.muthofun.com/api/v1/send-sms');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'api_key'  => $config['api_key'],
                'type'     => 'text',
                'number'   => $mobile,
                'senderid' => $config['sender_id'],
                'message'  => $message,
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function sendAlphaNet(array $config, string $mobile, string $message): string
    {
        $response = file_get_contents(
            'http://alphanet.com.bd/sendSMS?' . http_build_query([
                'user'     => $config['username'],
                'password' => $config['password'],
                'sender'   => $config['sender_id'],
                'SMSText'  => $message,
                'GSM'      => $mobile,
            ])
        );
        return $response;
    }

    private function sendTwilio(array $config, string $mobile, string $message): string
    {
        $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/{$config['account_sid']}/Messages.json");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => "{$config['account_sid']}:{$config['auth_token']}",
            CURLOPT_POSTFIELDS     => http_build_query([
                'From' => $config['from_number'],
                'To'   => '+88' . $mobile,
                'Body' => $message,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function formatMobile(string $mobile): string
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (str_starts_with($mobile, '88')) {
            $mobile = substr($mobile, 2);
        }
        return $mobile;
    }
}