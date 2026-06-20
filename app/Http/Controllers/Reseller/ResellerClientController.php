<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerClientController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $query = Customer::with(['package', 'zone'])
            ->forReseller($resellerId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('customer_code', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('pppoe_username', 'like', "%{$s}%");
            });
        }

        $clients = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'    => Customer::forReseller($resellerId)->count(),
            'active'   => Customer::forReseller($resellerId)->where('status', 'active')->count(),
            'expired'  => Customer::forReseller($resellerId)->where('status', 'expired')->count(),
            'inactive' => Customer::forReseller($resellerId)->whereIn('status', ['inactive', 'suspended'])->count(),
        ];

        return view('reseller.client.index', compact('clients', 'stats'));
    }

    public function show(Customer $client)
    {
        // Ownership check — অন্য reseller-এর client দেখা যাবে না
        abort_unless(
            $client->mac_reseller_id === Auth::guard('mac_reseller')->id(),
            403,
            'You do not have access to this client.'
        );

        $client->load(['package', 'zone', 'subZone', 'connectionType', 'clientType', 'protocolType', 'invoices', 'payments']);

        return view('reseller.client.show', compact('client'));
    }
}
