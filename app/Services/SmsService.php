<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\TenantSmsSetting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;

/**
 * SmsService — Tenant Aware
 * ─────────────────────────────────────────────
 * ISP Company এর নিজস্ব SMS settings থেকে gateway নেবে।
 * Super Admin enabled gateway গুলো ISP দেখতে পাবে।
 */
class SmsService
{
    private ?string $tenantId;

    public function __construct(?string $tenantId = null)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * SMS পাঠাও
     */
    public function send(string $mobile, string $message, string $type = 'general'): bool
    {
        // Tenant এর active gateway খুঁজো
        $setting = $this->getActiveSetting();

        if (!$setting) {
            Log::warning('SMS: কোনো active gateway নেই।');
            return false;
        }

        $gateway = SmsGateway::where('slug', $setting->gateway_slug)->first();
        if (!$gateway) return false;

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
                'gateway'  => $gateway->slug,
                'mobile'   => $mobile,
                'message'  => $message,
                'type'     => $type,
                'status'   => 'success',
                'response' => $response,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("SMS failed [{$gateway->slug}]: " . $e->getMessage());

            SmsLog::create([
                'gateway'  => $gateway->slug,
                'mobile'   => $mobile,
                'message'  => $message,
                'type'     => $type,
                'status'   => 'failed',
                'response' => $e->getMessage(),
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

    // ── SMS Templates ──────────────────────────────

    public function sendBillDue(string $mobile, string $name, float $amount, string $month): bool
    {
        $message = "প্রিয় {$name}, আপনার {$month} মাসের ইন্টারনেট বিল {$amount} টাকা বাকি আছে। দ্রুত পরিশোধ করুন।";
        return $this->send($mobile, $message, 'bill_due');
    }

    public function sendPaymentConfirm(string $mobile, string $name, float $amount, string $method): bool
    {
        $message = "প্রিয় {$name}, আপনার {$amount} টাকা পেমেন্ট ({$method}) সফলভাবে গ্রহণ করা হয়েছে। ধন্যবাদ।";
        return $this->send($mobile, $message, 'payment_confirm');
    }

    public function sendSuspendNotice(string $mobile, string $name): bool
    {
        $message = "প্রিয় {$name}, বিল বাকি থাকায় আপনার ইন্টারনেট সংযোগ সাময়িকভাবে বন্ধ করা হয়েছে।";
        return $this->send($mobile, $message, 'suspend');
    }

    public function sendRestoreNotice(string $mobile, string $name): bool
    {
        $message = "প্রিয় {$name}, আপনার ইন্টারনেট সংযোগ পুনরায় চালু করা হয়েছে। ধন্যবাদ।";
        return $this->send($mobile, $message, 'restore');
    }

    public function sendWelcome(string $mobile, string $name, string $user, string $pass): bool
    {
        $message = "প্রিয় {$name}, আপনার ইন্টারনেট সংযোগ চালু হয়েছে। User: {$user}, Pass: {$pass}।";
        return $this->send($mobile, $message, 'welcome');
    }

    // ── Private Helpers ────────────────────────────

    private function getActiveSetting(): ?TenantSmsSetting
    {
        if ($this->tenantId) {
            return TenantSmsSetting::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->whereHas('gateway', fn($q) => $q->where('is_enabled', true))
                ->first();
        }

        // Fallback: পুরনো global gateway (backward compatible)
        $gateway = SmsGateway::where('is_active', true)->first();
        if (!$gateway) return null;

        // Fake setting তৈরি করো global config থেকে
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
