<?php

namespace App\Http\Controllers;

use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Services\SmsService;
use App\Models\Customer;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    // ══════════════════════════════════════════════
    // Gateway Management (SuperAdmin)
    // ══════════════════════════════════════════════

    /**
     * GET /admin/sms
     * সব gateway list + SMS logs
     */
    public function index()
    {
        $gateways = SmsGateway::all();
        $logs     = SmsLog::latest()->paginate(20);
        $todaySent = SmsLog::today()->success()->count();
        $todayFailed = SmsLog::today()->failed()->count();

        return view('sms.index', compact('gateways', 'logs', 'todaySent', 'todayFailed'));
    }

    /**
     * POST /admin/sms/gateway/{gateway}/toggle
     * Gateway on/off করো
     */
    public function toggleGateway(SmsGateway $gateway)
    {
        // অন্য সব gateway off করো
        SmsGateway::where('id', '!=', $gateway->id)->update(['is_active' => false]);

        // এটা toggle করো
        $gateway->update(['is_active' => !$gateway->is_active]);

        $status = $gateway->is_active ? 'চালু' : 'বন্ধ';
        return back()->with('success', "{$gateway->name} {$status} করা হয়েছে।");
    }

    /**
     * POST /admin/sms/gateway/{gateway}/config
     * Gateway config (API key) আপডেট করো
     */
    public function updateConfig(Request $request, SmsGateway $gateway)
    {
        $config = $request->input('config', []);

        // Empty value filter করো না — সব save করো
        $gateway->update(['config' => $config]);

        return back()->with('success', "{$gateway->name} configuration সংরক্ষণ হয়েছে।");
    }

    /**
     * POST /admin/sms/test
     * Test SMS পাঠাও
     */
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

    /**
     * POST /admin/sms/send-bulk
     * সব active customer কে SMS পাঠাও
     */
    public function sendBulk(Request $request, SmsService $sms)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'status'  => 'nullable|in:active,suspended,all',
        ]);

        $query = Customer::query();
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $customers = $query->get();
        $mobiles   = $customers->pluck('phone')->toArray();
        $sent      = $sms->sendMany($mobiles, $request->message, 'bulk');

        return back()->with('success', "{$sent} জন customer কে SMS পাঠানো হয়েছে।");
    }

    /**
     * DELETE /admin/sms/logs
     * পুরনো SMS log মুছে ফেলো
     */
    public function clearLogs()
    {
        SmsLog::where('created_at', '<', now()->subDays(30))->delete();
        return back()->with('success', '৩০ দিনের পুরনো SMS log মুছে ফেলা হয়েছে।');
    }
}
