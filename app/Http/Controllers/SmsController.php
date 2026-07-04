<?php

namespace App\Http\Controllers;

use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Services\SmsService;
use App\Models\Customer;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    /** GET /admin/sms — list all gateways and recent SMS logs. */
    public function index(SmsService $sms)
    {
        $gateways    = SmsGateway::all();
        $logs        = SmsLog::latest()->paginate(20);
        $todaySent   = SmsLog::today()->success()->count();
        $todayFailed = SmsLog::today()->failed()->count();
        $templates   = \App\Models\SmsTemplate::active()->get();
        $smsBalance  = $sms->getBalance();

        // "Current vs last" comparison data for the redesigned stat cards
        // (matches invoices/index.blade.php's stat-card style).
        $yesterdaySent   = SmsLog::whereDate('created_at', today()->subDay())->where('status', 'sent')->count();
        $yesterdayFailed = SmsLog::whereDate('created_at', today()->subDay())->where('status', 'failed')->count();
        $totalSmsAllTime = SmsLog::count();

        // Bulk SMS filter dropdown — count per category so admin knows how many
        // customers each option will actually message before sending.
        $filterCounts = [
            'all'       => Customer::count(),
            'active'    => Customer::where('status', 'active')->count(),
            'suspended' => Customer::where('status', 'suspended')->count(),
            'expired'   => Customer::where('status', 'expired')->count(),
            'paid'      => Customer::whereDoesntHave('invoices', fn($q) =>
                $q->whereIn('status', ['unpaid', 'partial', 'overdue']))->count(),
            'due'       => Customer::whereHas('invoices', fn($q) =>
                $q->whereIn('status', ['unpaid', 'partial']))->count(),
            'overdue'   => Customer::whereHas('invoices', fn($q) =>
                $q->where('status', 'overdue'))->count(),
        ];

        return view('sms.index', compact(
            'gateways', 'logs', 'todaySent', 'todayFailed', 'templates', 'smsBalance', 'filterCounts',
            'yesterdaySent', 'yesterdayFailed', 'totalSmsAllTime'
        ));
    }

    /** POST /admin/sms/gateway/{gateway}/toggle — activate this gateway and deactivate all others. */
    public function toggleGateway(SmsGateway $gateway)
    {
        // Only one gateway may be active at a time.
        SmsGateway::where('id', '!=', $gateway->id)->update(['is_active' => false]);

        $gateway->update(['is_active' => !$gateway->is_active]);

        $status = $gateway->is_active ? 'চালু' : 'বন্ধ';
        return back()->with('success', "{$gateway->name} {$status} করা হয়েছে।");
    }

    /** POST /admin/sms/gateway/{gateway}/config — save gateway API credentials. */
    public function updateConfig(Request $request, SmsGateway $gateway)
    {
        $config = $request->input('config', []);
        $gateway->update(['config' => $config]);

        return back()->with('success', "{$gateway->name} configuration সংরক্ষণ হয়েছে।");
    }

    /** POST /admin/sms/test — send a single test SMS to verify gateway connectivity. */
    public function sendTest(Request $request, SmsService $sms)
    {
        $request->validate([
            'mobile'  => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $sms->send($request->mobile, $request->message, 'test');

        return back()->with(
            $result ? 'success' : 'error',
            $result ? 'Test SMS পাঠানো হয়েছে!' : 'SMS পাঠাতে ব্যর্থ হয়েছে। Log দেখুন।'
        );
    }

    /** POST /admin/sms/send-bulk — broadcast an SMS to all matching customers. */
    public function sendBulk(Request $request, SmsService $sms)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'status'  => 'nullable|in:active,suspended,expired,paid,due,overdue,all',
        ]);

        $query = Customer::query();

        switch ($request->status) {
            case 'active':
            case 'suspended':
            case 'expired':
                $query->where('status', $request->status);
                break;

            case 'paid':
                $query->whereDoesntHave('invoices', fn($q) =>
                    $q->whereIn('status', ['unpaid', 'partial', 'overdue']));
                break;

            case 'due':
                $query->whereHas('invoices', fn($q) =>
                    $q->whereIn('status', ['unpaid', 'partial']));
                break;

            case 'overdue':
                $query->whereHas('invoices', fn($q) =>
                    $q->where('status', 'overdue'));
                break;

            // 'all' or null — no filter, every customer
        }

        $customers = $query->get();

        // sendDynamic() — same message to everyone here, but still batched into a
        // single DynamicSMSApi call instead of looping sendMany() (N separate
        // smsSendApi calls). Message is identical per recipient in this case.
        $recipients = [];
        foreach ($customers as $customer) {
            if (!$customer->phone) continue;
            $recipients[] = ['mobile' => $customer->phone, 'message' => $request->message];
        }

        if (empty($recipients)) {
            return back()->with('error', 'পাঠানোর মতো কোনো customer (phone number সহ) পাওয়া যায়নি।');
        }

        $result = $sms->sendDynamic($recipients, 'bulk');

        $msg = "{$result['sent']} জন customer কে SMS পাঠানো হয়েছে (১টা API call-এ)।";
        if ($result['failed'] > 0) {
            $msg .= " {$result['failed']} টি ব্যর্থ হয়েছে।";
        }

        return back()->with('success', $msg);
    }

    /** DELETE /admin/sms/logs — purge SMS logs older than 30 days. */
    public function clearLogs()
    {
        SmsLog::where('created_at', '<', now()->subDays(30))->delete();
        return back()->with('success', '৩০ দিনের পুরনো SMS log মুছে ফেলা হয়েছে।');
    }
}
