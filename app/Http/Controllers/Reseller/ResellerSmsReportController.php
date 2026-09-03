<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSmsReportController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $logs = SmsLog::forReseller($resellerId)
            ->when($request->mobile,    fn($q) => $q->where('mobile', 'like', '%' . $request->mobile . '%'))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->when($request->type,      fn($q) => $q->where('type', $request->type))
            ->when($request->gateway,   fn($q) => $q->where('gateway', $request->gateway))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(10);

        $gateways    = SmsLog::forReseller($resellerId)->select('gateway')->distinct()->pluck('gateway');
        $totalSent   = SmsLog::forReseller($resellerId)->success()->count();
        $totalFailed = SmsLog::forReseller($resellerId)->failed()->count();
        $todaySent   = SmsLog::forReseller($resellerId)->today()->success()->count();

        return view('reseller.sms.reports', compact(
            'logs', 'gateways', 'totalSent', 'totalFailed', 'todaySent'
        ));
    }
}
