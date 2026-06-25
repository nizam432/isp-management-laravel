<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ClientLedger;
use App\Models\Customer;
use Illuminate\Http\Request;

class ClientLedgerController extends Controller
{
    public function index(Request $request)
    {
        // Due আছে এমন client list
        $clients = Customer::whereHas('inventoryLedger')
                    ->withSum('inventoryLedger as total_credit', 'credit')
                    ->withSum('inventoryLedger as total_debit', 'debit')
                    ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                                                          ->orWhere('phone', 'like', '%' . $request->search . '%'))
                    ->paginate(20);

        return view('inventory.client-ledger.index', compact('clients'));
    }

    public function show(Request $request, Customer $customer)
    {
        $ledger = ClientLedger::where('client_id', $customer->id)
                    ->when($request->from, fn($q) => $q->whereDate('date', '>=', $request->from))
                    ->when($request->to, fn($q) => $q->whereDate('date', '<=', $request->to))
                    ->orderBy('date')
                    ->orderBy('id')
                    ->get();

        $summary = [
            'total_credit'  => $ledger->sum('credit'),
            'total_debit'   => $ledger->sum('debit'),
            'balance'       => $ledger->sum('credit') - $ledger->sum('debit'),
        ];

        return view('inventory.client-ledger.show', compact('customer', 'ledger', 'summary'));
    }
}
