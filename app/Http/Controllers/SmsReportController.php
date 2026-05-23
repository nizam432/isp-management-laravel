<?php
 
namespace App\Http\Controllers;
 
use App\Models\SmsLog;
use Illuminate\Http\Request;
 
class SmsReportController extends Controller
{
    public function index(Request $request)
    {
        $logs = SmsLog::query()
            ->when($request->mobile,    fn($q) => $q->where('mobile', 'like', '%' . $request->mobile . '%'))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->when($request->type,      fn($q) => $q->where('type', $request->type))
            ->when($request->gateway,   fn($q) => $q->where('gateway', $request->gateway))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(10);
 
        $gateways   = SmsLog::select('gateway')->distinct()->pluck('gateway');
        $totalSent   = SmsLog::success()->count();
        $totalFailed = SmsLog::failed()->count();
        $todaySent   = SmsLog::today()->success()->count();
 
        return view('sms.reports', compact(
            'logs', 'gateways', 'totalSent', 'totalFailed', 'todaySent'
        ));
    }
}