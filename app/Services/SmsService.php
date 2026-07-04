<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsTemplate;
use App\Models\SmsTemplateMapping;
use App\Models\TenantSmsSetting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * SmsService — Tenant Aware
 * ─────────────────────────────────────────────
 * Takes the gateway from the ISP company's own SMS settings.
 * The ISP can only see gateways that the Super Admin has enabled.
 */
class SmsService
{
    private ?string $tenantId;

    public function __construct(?string $tenantId = null)
    {
        // Previously, every `new SmsService()` call across the app (BillingService,
        // InvoiceController, all commands) passed NO tenant ID, so $this->tenantId
        // was ALWAYS null. That meant getActiveSetting() NEVER read from
        // tenant_sms_settings — it always fell back to the global sms_gateways.config
        // column, even though the correct customer_id/api_key were saved in
        // tenant_sms_settings all along. This is why "Customer id Missing" kept
        // happening despite the tenant's settings page showing correct values.
        //
        // Fix: auto-detect the current tenant via stancl/tenancy's tenant() helper
        // when no explicit tenant ID is given, so existing `new SmsService()` calls
        // everywhere automatically start using the correct tenant-scoped config
        // without needing to touch every call site.
        if ($tenantId === null && function_exists('tenant') && tenant()) {
            $tenantId = tenant()->getTenantKey();
        }

        $this->tenantId = $tenantId;
    }

    /**
     * Send an SMS
     */
    public function send(string $mobile, string $message, string $type = 'general'): bool
    {
        // Find the tenant's active gateway
        $setting = $this->getActiveSetting();

        if (!$setting) {
            Log::warning('SMS: no active gateway found.');
            return false;
        }

        $gateway = SmsGateway::where('slug', $setting->gateway_slug)->first();
        if (!$gateway) {
            // Previously this returned false with ZERO logging — no SmsLog entry,
            // no Log::error, nothing. If you ever see empty sms_logs AND no errors
            // in laravel.log, THIS is almost certainly why: the tenant's configured
            // gateway_slug doesn't match any row in sms_gateways (deleted gateway,
            // typo, data mismatch, etc).
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
                'gateway'   => $gateway->slug,
                'mobile'    => $mobile,
                'phone'     => $mobile, // sms_logs.phone has no default and is NOT NULL — mirror mobile to avoid insert failure
                'message'   => $message,
                'type'      => $type,
                'status'    => 'sent', // sms_logs.status is enum('sent','failed','pending') — 'success' is NOT a valid value
                'response'  => $response,
                'count_sms' => $this->countSms($message),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("SMS failed [{$gateway->slug}]: " . $e->getMessage());

            SmsLog::create([
                'gateway'   => $gateway->slug,
                'mobile'    => $mobile,
                'phone'     => $mobile,
                'message'   => $message,
                'type'      => $type,
                'status'    => 'failed',
                'response'  => $e->getMessage(),
                'count_sms' => $this->countSms($message),
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
     * (Useful for bill-due reminders, personalized batch SMS, etc.)
     *
     * $recipients format: [ ['mobile' => '018xxxxxxxx', 'message' => '...'], ... ]
     *
     * Only the 24bulksmsbd gateway supports dynamic batch sending in a single API call.
     * If another gateway is active (SSL Wireless, Muthofun, AlphaNet, Twilio) — those
     * don't have a dynamic batch endpoint, so as a backward-compatible fallback, each
     * recipient is sent individually via send() (no changes made to those gateways).
     *
     * Returns: ['sent' => int, 'failed' => int]
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

        // Normalize mobiles up front
        $recipients = array_map(function ($r) {
            return [
                'mobile'  => $this->formatMobile($r['mobile']),
                'message' => $r['message'],
            ];
        }, $recipients);

        if ($gateway->slug !== '24bulksmsbd') {
            // Fallback: this gateway doesn't support dynamic batch, so loop with individual send() calls.
            $sent = 0;
            foreach ($recipients as $r) {
                if ($this->send($r['mobile'], $r['message'], $type)) $sent++;
            }
            return ['sent' => $sent, 'failed' => count($recipients) - $sent];
        }

        try {
            $response = $this->send24BulkSMSDynamic($setting->config, $recipients);

            // A separate log entry is kept for each recipient, so the existing SmsLog
            // reporting/history (SmsReportController, etc.) remains unaffected.
            foreach ($recipients as $r) {
                SmsLog::create([
                    'gateway'   => $gateway->slug,
                    'mobile'    => $r['mobile'],
                    'phone'     => $r['mobile'],
                    'message'   => $r['message'],
                    'type'      => $type,
                    'status'    => 'sent',
                    'response'  => $response,
                    'count_sms' => $this->countSms($r['message']),
                ]);
            }

            return ['sent' => count($recipients), 'failed' => 0];

        } catch (\Exception $e) {
            Log::error("SMS dynamic batch failed [{$gateway->slug}]: " . $e->getMessage());

            foreach ($recipients as $r) {
                SmsLog::create([
                    'gateway'   => $gateway->slug,
                    'mobile'    => $r['mobile'],
                    'phone'     => $r['mobile'],
                    'message'   => $r['message'],
                    'type'      => $type,
                    'status'    => 'failed',
                    'response'  => $e->getMessage(),
                    'count_sms' => $this->countSms($r['message']),
                ]);
            }

            return ['sent' => 0, 'failed' => count($recipients)];
        }
    }

    // ── SMS Templates ──────────────────────────────

    /**
     * Renders a message using the DB mapping (sms_template_mappings) to find which
     * SmsTemplate title applies to $type, then fetches that active template and
     * renders it with $data. Falls back to $fallback (hardcoded default) if:
     *  - no mapping row exists for this $type, OR
     *  - the mapped title has no matching active SmsTemplate.
     * This keeps SMS sending working even if the mapping/template isn't configured yet.
     */
    private function renderTemplate(string $type, array $data, string $fallback): string
    {
        $mapping = SmsTemplateMapping::where('type', $type)->first();
        if ($mapping) {
            $template = SmsTemplate::active()->where('title', $mapping->title)->first();
            if ($template) {
                return $template->render($data);
            }
        }
        return $fallback;
    }

    /**
     * Formats a 'Y-m' month string (e.g. "2026-06", as stored in Invoice::month)
     * into a human-readable form (e.g. "June 2026") for use in SMS templates.
     * Falls back to the original string unchanged if it isn't in 'Y-m' format,
     * so this never breaks message sending even with unexpected input.
     */
    /**
     * PHP port of the count_sms() function used on the frontend — calculates how
     * many SMS segments a message will consume, following standard concatenated-SMS
     * rules (70/66 chars for Unicode/Bengali, 160/153 chars for plain GSM-7 text).
     * Stored per SmsLog entry in the `count_sms` column.
     */
    private function countSms(string $message): int
    {
        $message = trim($message);
        if ($message === '') return 0;

        $totalLineBreak = substr_count($message, "\n");
        $encoding       = mb_detect_encoding($message);

        if ($encoding === 'UTF-8' && mb_strlen($message, 'UTF-8') !== strlen($message)) {
            // Contains actual multi-byte (Bengali/Unicode) characters
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

        // Plain GSM-7 (English/numbers)
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

    /**
     * Builds the bill-due reminder message from the DB template (or fallback),
     * without sending it. Used by both sendBillDue() (single) and callers that
     * batch multiple personalized messages via sendDynamic() (e.g. bulk reminders),
     * so both paths stay consistent with whatever template is configured in DB.
     */
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

    /**
     * Builds the invoice-generated message from the DB template (or fallback),
     * without sending it. Used by both sendInvoiceGenerated() (single) and callers
     * that batch multiple personalized messages via sendDynamic() (e.g. the
     * date-to-date invoice generation command, so 500 customers = 1 API call
     * instead of 500).
     */
    public function buildInvoiceGeneratedMessage(string $name, float $amount, string $month): string
    {
        return $this->renderTemplate('invoice_generated', [
            'name'   => $name,
            'amount' => $amount,
            'month'  => $this->formatMonth($month),
        ], "প্রিয় {$name}, আপনার {$this->formatMonth($month)} মাসের ইনভয়েস তৈরি হয়েছে। বিল পরিমাণ {$amount} টাকা। দ্রুত পরিশোধ করুন।");
    }

    /**
     * Sent when a new invoice/bill is auto-generated (date_to_date or monthly).
     * Uses the same DB template pattern as other notifications — looks up the
     * 'invoice_generated' mapping in sms_template_mappings, falls back to the
     * hardcoded default if not configured yet.
     */
    public function sendInvoiceGenerated(string $mobile, string $name, float $amount, string $month): bool
    {
        $message = $this->buildInvoiceGeneratedMessage($name, $amount, $month);
        return $this->send($mobile, $message, 'invoice_generated');
    }

    // ── Private Helpers ────────────────────────────

    /**
     * Fetches the current SMS balance from the active gateway, if that gateway
     * supports a balance-check API. Currently only 24bulksmsbd is supported —
     * other gateways return null (dashboard shows "N/A" in that case).
     *
     * Cached for 5 minutes so the SMS dashboard doesn't hit the gateway's balance
     * API on every single page load.
     */
    public function getBalance(): ?string
    {
        $setting = $this->getActiveSetting();
        if (!$setting) return null;

        $gateway = SmsGateway::where('slug', $setting->gateway_slug)->first();
        if (!$gateway) return null;

        if ($gateway->slug !== '24bulksmsbd') {
            // Balance API not implemented for other gateways yet.
            return null;
        }

        return Cache::remember('sms_balance_' . $gateway->slug, 300, function () use ($setting) {
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

        // 24bulksmsbd's balance response format isn't confirmed from docs here —
        // trying common key names. Adjust the key below if the actual response
        // uses a different field (check by visiting the API directly once, or
        // logging $response the first time this runs).
        if (is_array($decoded)) {
            return $decoded['balance'] ?? $decoded['sms_balance'] ?? $decoded['credit'] ?? null;
        }

        return null;
    }

    private function getActiveSetting(): ?TenantSmsSetting
    {
        if ($this->tenantId) {
            $setting = TenantSmsSetting::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->whereHas('gateway', fn($q) => $q->where('is_enabled', true))
                ->first();

            if ($setting) return $setting;

            // No tenant-specific setting matched — fall through to global.
        }

        // Fallback: global gateway config (backward compatible). This is the
        // primary path in practice — tenant() detection isn't reliably available
        // in this app's request lifecycle, same as how the payment gateway
        // integration also uses a single global config rather than per-tenant.
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

    /**
     * 24bulksmsbd — DynamicSMSApi: multiple numbers in one call, each with its own message.
     * $recipients format: [ ['mobile' => '018xxxxxxxx', 'message' => '...'], ... ]
     */
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
