<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;

/**
 * SmsService
 * ─────────────────────────────────────────────
 * Multi-gateway SMS service।
 * SuperAdmin থেকে gateway on/off করা যাবে।
 *
 * Supported Gateways:
 * - 24bulksmsbd
 * - ssl_wireless
 * - muthofun
 * - alpha_net
 * - twilio
 */
class SmsService
{
    /**
     * SMS পাঠাও — active gateway automatically select হবে
     */
    public function send(string $mobile, string $message, string $type = 'general'): bool
    {
        $gateway = SmsGateway::where('is_active', true)->first();

        if (!$gateway) {
            Log::warning('SMS: কোনো active gateway নেই।');
            return false;
        }

        $mobile = $this->formatMobile($mobile);

        try {
            $response = match($gateway->slug) {
                '24bulksmsbd'  => $this->send24BulkSMS($gateway, $mobile, $message),
                'ssl_wireless' => $this->sendSSLWireless($gateway, $mobile, $message),
                'muthofun'     => $this->sendMuthofun($gateway, $mobile, $message),
                'alpha_net'    => $this->sendAlphaNet($gateway, $mobile, $message),
                'twilio'       => $this->sendTwilio($gateway, $mobile, $message),
                default        => throw new \Exception("Unknown gateway: {$gateway->slug}"),
            };

            // Log success
            SmsLog::create([
                'gateway'    => $gateway->slug,
                'mobile'     => $mobile,
                'message'    => $message,
                'type'       => $type,
                'status'     => 'success',
                'response'   => $response,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("SMS send failed [{$gateway->slug}]: " . $e->getMessage());

            SmsLog::create([
                'gateway'    => $gateway->slug,
                'mobile'     => $mobile,
                'message'    => $message,
                'type'       => $type,
                'status'     => 'failed',
                'response'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * একাধিক নম্বরে SMS পাঠাও
     */
    public function sendMany(array $mobiles, string $message, string $type = 'general'): int
    {
        $sent = 0;
        foreach ($mobiles as $mobile) {
            if ($this->send($mobile, $message, $type)) {
                $sent++;
            }
        }
        return $sent;
    }

    // ══════════════════════════════════════════════
    // SMS Templates
    // ══════════════════════════════════════════════

    public function sendBillDue(string $mobile, string $name, float $amount, string $month): bool
    {
        $message = "প্রিয় {$name}, আপনার {$month} মাসের ইন্টারনেট বিল {$amount} টাকা বাকি আছে। দ্রুত পরিশোধ করুন। - ISP Management";
        return $this->send($mobile, $message, 'bill_due');
    }

    public function sendPaymentConfirm(string $mobile, string $name, float $amount, string $method): bool
    {
        $message = "প্রিয় {$name}, আপনার {$amount} টাকা পেমেন্ট ({$method}) সফলভাবে গ্রহণ করা হয়েছে। ধন্যবাদ। - ISP Management";
        return $this->send($mobile, $message, 'payment_confirm');
    }

    public function sendSuspendNotice(string $mobile, string $name): bool
    {
        $message = "প্রিয় {$name}, বিল বাকি থাকায় আপনার ইন্টারনেট সংযোগ সাময়িকভাবে বন্ধ করা হয়েছে। বিল পরিশোধ করুন। - ISP Management";
        return $this->send($mobile, $message, 'suspend');
    }

    public function sendRestoreNotice(string $mobile, string $name): bool
    {
        $message = "প্রিয় {$name}, আপনার ইন্টারনেট সংযোগ পুনরায় চালু করা হয়েছে। ধন্যবাদ। - ISP Management";
        return $this->send($mobile, $message, 'restore');
    }

    public function sendWelcome(string $mobile, string $name, string $pppoeUser, string $pppoePass): bool
    {
        $message = "প্রিয় {$name}, আপনার ইন্টারনেট সংযোগ চালু হয়েছে। PPPoE User: {$pppoeUser}, Password: {$pppoePass}। - ISP Management";
        return $this->send($mobile, $message, 'welcome');
    }

    // ══════════════════════════════════════════════
    // Gateway Implementations
    // ══════════════════════════════════════════════

    /**
     * 24BulkSMSBD Gateway
     */
private function send24BulkSMS(SmsGateway $gateway, string $mobile, string $message): string
{
    $config = $gateway->config;

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
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    // সব সময় success — response log করো
    \Log::info('24BulkSMS Response: ' . $response);

    return $response; // কোনো exception throw করব না
}

    /**
     * SSL Wireless Gateway
     */
    private function sendSSLWireless(SmsGateway $gateway, string $mobile, string $message): string
    {
        $config = $gateway->config;

        $response = file_get_contents(
            'https://sms.sslwireless.com/pushapi/dynamic/server.php?' . http_build_query([
                'user'    => $config['username'],
                'pass'    => $config['password'],
                'sid'     => $config['sid'],
                'sms'     => $message,
                'mobile'  => $mobile,
                'tid'     => time(),
            ])
        );

        return $response;
    }

    /**
     * Muthofun Gateway
     */
    private function sendMuthofun(SmsGateway $gateway, string $mobile, string $message): string
    {
        $config = $gateway->config;

        $ch = curl_init('https://api.muthofun.com/api/v1/send-sms');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'api_key'   => $config['api_key'],
                'type'      => 'text',
                'number'    => $mobile,
                'senderid'  => $config['sender_id'],
                'message'   => $message,
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * Alpha Net Gateway
     */
    private function sendAlphaNet(SmsGateway $gateway, string $mobile, string $message): string
    {
        $config = $gateway->config;

        $response = file_get_contents(
            'http://alphanet.com.bd/sendSMS?' . http_build_query([
                'user'    => $config['username'],
                'password'=> $config['password'],
                'sender'  => $config['sender_id'],
                'SMSText' => $message,
                'GSM'     => $mobile,
            ])
        );

        return $response;
    }

    /**
     * Twilio Gateway
     */
    private function sendTwilio(SmsGateway $gateway, string $mobile, string $message): string
    {
        $config = $gateway->config;

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

    // ══════════════════════════════════════════════
    // Helper
    // ══════════════════════════════════════════════

    private function formatMobile(string $mobile): string
    {
        // 01XXXXXXXXX → 01XXXXXXXXX (Bangladesh format)
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (str_starts_with($mobile, '88')) {
            $mobile = substr($mobile, 2);
        }
        return $mobile;
    }
}
