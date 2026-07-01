<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ResellerDashboardController extends Controller
{
    public function index()
    {
        $reseller = Auth::guard('mac_reseller')->user();

        $stats = [
            'remaining_fund'   => $reseller->remaining_fund,
            'total_clients'    => 0,
            'active_clients'   => 0,
            'disabled_clients' => 0,
        ];

        return view('reseller.dashboard.index', compact('reseller', 'stats'));
    }
}

class ResellerPlaceholderController extends Controller
{
    public function show(string $menu)
    {
        $titles = [
            'configuration'   => 'Configuration',
            'mikrotik-client' => 'Mikrotik Client',
            'employees'       => 'Employees',
            'client'          => 'Client',
            'billing'         => 'Billing',
            'monitoring'      => 'Monitoring',
            'client-support'  => 'Client Support',
            'sms-service'     => 'SMS Service',
            'report'          => 'Report',
            'fund-history'    => 'Fund History',
            'tutorials'       => 'Tutorials',
        ];

        $title = $titles[$menu] ?? ucfirst(str_replace('-', ' ', $menu));

        return view('reseller.dashboard.coming-soon', compact('title'));
    }
}
