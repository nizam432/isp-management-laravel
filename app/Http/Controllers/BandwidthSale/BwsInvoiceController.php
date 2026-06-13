<?php

namespace App\Http\Controllers\BandwidthSale;

use App\Http\Controllers\Controller;
use App\Models\BwsInvoice;
use App\Models\BwsInvoiceItem;
use App\Models\BwsInvoicePayment;
use App\Models\BandwidthSaleCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BwsInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = BwsInvoice::with(['bwsCustomer', 'createdBy'])
            ->when($request->from_month,  fn($q) => $q->where('billing_month', '>=', $request->from_month))
            ->when($request->to_month,    fn($q) => $q->where('billing_month', '<=', $request->to_month))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn($q) => $q->where('bws_customer_id', $request->customer_id))
            ->when($request->created_by,  fn($q) => $q->where('created_by', $request->created_by))
            ->latest();

        $invoices  = $query->paginate($request->get('per_page', 20))->withQueryString();
        $customers = BandwidthSaleCustomer::orderBy('customer_name')->get();

        $bwsServices = \App\Models\BandwidthBuy\BandwidthService::orderBy('name')
            ->get(['id', 'name', 'description'])
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'unit' => '']);

        $employees = \App\Models\HR\Employee::select('id', 'name', 'user_id')
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();

        $stats = [
            'total'    => BwsInvoice::count(),
            'paid'     => BwsInvoice::where('status', 'paid')->count(),
            'unpaid'   => BwsInvoice::whereIn('status', ['unpaid', 'overdue'])->count(),
            'received' => BwsInvoicePayment::where('status', 'active')->sum('received_amount'),
        ];

        return view('bandwidth-sale.invoices.index',
            compact('invoices', 'customers', 'employees', 'bwsServices', 'stats'));
    }

    public function create()
    {
        $customers = BandwidthSaleCustomer::where('activity_status', 'active')->orderBy('customer_name')->get();
        $items = [];
        return view('bandwidth-sale.invoices.create', compact('customers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bws_customer_id' => 'required|exists:bandwidth_sale_customers,id',
            'billing_month'   => 'required|date_format:Y-m',
            'grand_total'     => 'required|numeric|min:0',
            'items_json'      => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $invoice = BwsInvoice::create([
                'invoice_no'      => BwsInvoice::generateNumber(),
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'payment_due'     => $request->payment_due,
                'daily_basis'     => $request->boolean('daily_basis'),
                'total_amount'    => $request->total_amount ?? 0,
                'vat_amount'      => $request->vat_amount ?? 0,
                'discount'        => $request->discount ?? 0,
                'grand_total'     => $request->grand_total,
                'received_amount' => 0,
                'due_amount'      => $request->grand_total,
                'status'          => $request->status ?? 'unpaid',
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);
            $this->saveItems($invoice->id, $request->items_json);
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => "Invoice {$invoice->invoice_no} created.", 'id' => $invoice->id]);
            }
            return redirect()->route('bandwidth-sale.invoices.index')->with('success', "Invoice {$invoice->invoice_no} created.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'items', 'activePayments.receivedBy', 'createdBy']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'         => true,
                'invoice' => [
                    'id'              => $bwsInvoice->id,
                    'invoice_no'      => $bwsInvoice->invoice_no,
                    'bws_customer_id' => $bwsInvoice->bws_customer_id,
                    'customer_name'   => $bwsInvoice->bwsCustomer?->customer_name,
                    'contact_person'  => $bwsInvoice->bwsCustomer?->contact_person,
                    'mobile'          => $bwsInvoice->bwsCustomer?->mobile_number,
                    'billing_month'   => $bwsInvoice->billing_month,
                    'payment_due'     => optional($bwsInvoice->payment_due)->format('Y-m-d'),
                    'daily_basis'     => $bwsInvoice->daily_basis,
                    'status'          => $bwsInvoice->status,
                    'total_amount'    => $bwsInvoice->total_amount,
                    'vat_amount'      => $bwsInvoice->vat_amount,
                    'discount'        => $bwsInvoice->discount,
                    'grand_total'     => $bwsInvoice->grand_total,
                    'received_amount' => $bwsInvoice->received_amount,
                    'due_amount'      => $bwsInvoice->due_amount,
                    'notes'           => $bwsInvoice->notes,
                    'items'           => $bwsInvoice->items->map(fn($i) => [
                        'item_name'   => $i->item_name,
                        'description' => $i->description,
                        'unit'        => $i->unit,
                        'quantity'    => $i->quantity,
                        'rate'        => $i->rate,
                        'vat_percent' => $i->vat_percent,
                        'from_date'   => optional($i->from_date)->format('Y-m-d'),
                        'to_date'     => optional($i->to_date)->format('Y-m-d'),
                        'total'       => $i->total,
                    ])->toArray(),
                    'payments' => $bwsInvoice->activePayments->map(fn($p) => [
                        'payment_no'      => $p->payment_no,
                        'received_date'   => optional($p->received_date)->format('d M Y'),
                        'payment_method'  => $p->payment_method,
                        'received_amount' => $p->received_amount,
                        'discount'        => $p->discount,
                        'status'          => $p->status,
                    ])->toArray(),
                ],
            ]);
        }

        return view('bandwidth-sale.invoices.show', compact('bwsInvoice'));
    }

    public function edit(BwsInvoice $bwsInvoice)
    {
        if ($bwsInvoice->isPaid()) return back()->with('error', 'Paid invoices cannot be edited.');
        $bwsInvoice->load(['bwsCustomer', 'items']);
        $customers = BandwidthSaleCustomer::where('activity_status', 'active')->orderBy('customer_name')->get();
        $items = [];
        return view('bandwidth-sale.invoices.edit', compact('bwsInvoice', 'customers', 'items'));
    }

    public function update(Request $request, BwsInvoice $bwsInvoice)
    {
        if ($bwsInvoice->isPaid()) return back()->with('error', 'Paid invoices cannot be edited.');
        $request->validate([
            'bws_customer_id' => 'required|exists:bandwidth_sale_customers,id',
            'billing_month'   => 'required|date_format:Y-m',
            'grand_total'     => 'required|numeric|min:0',
            'items_json'      => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $bwsInvoice->update([
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'payment_due'     => $request->payment_due,
                'daily_basis'     => $request->boolean('daily_basis'),
                'total_amount'    => $request->total_amount ?? 0,
                'vat_amount'      => $request->vat_amount ?? 0,
                'discount'        => $request->discount ?? 0,
                'grand_total'     => $request->grand_total,
                'notes'           => $request->notes,
            ]);
            $bwsInvoice->items()->delete();
            $this->saveItems($bwsInvoice->id, $request->items_json);
            $bwsInvoice->recalcDue();
            DB::commit();
            return redirect()->route('bandwidth-sale.invoices.index')->with('success', "Invoice {$bwsInvoice->invoice_no} updated.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(BwsInvoice $bwsInvoice)
    {
        if ($bwsInvoice->isPaid()) return response()->json(['success' => false, 'message' => 'Paid invoices cannot be deleted.']);
        if ($bwsInvoice->activePayments()->exists()) return response()->json(['success' => false, 'message' => 'Payments exist — cannot delete.']);
        $no = $bwsInvoice->invoice_no;
        $bwsInvoice->items()->delete();
        $bwsInvoice->delete();
        return response()->json(['success' => true, 'message' => "Invoice {$no} deleted."]);
    }

    public function pdf(BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'items', 'createdBy']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bandwidth-sale.invoices.pdf', compact('bwsInvoice'))->setPaper('a4');
        return $pdf->download("invoice-{$bwsInvoice->invoice_no}.pdf");
    }

    public function nextNo()
    {
        return response()->json(['invoice_no' => BwsInvoice::generateNumber()]);
    }

    public function dueForCustomer(BandwidthSaleCustomer $customer)
    {
        $invoices = BwsInvoice::where('bws_customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderByDesc('billing_month')
            ->get(['id', 'invoice_no', 'billing_month', 'grand_total', 'received_amount', 'due_amount']);
        return response()->json(['success' => true, 'invoices' => $invoices]);
    }

    public function receiveData(BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'activePayments']);
        $prev = $bwsInvoice->activePayments->sum('received_amount') + $bwsInvoice->activePayments->sum('discount');
        return response()->json([
            'success'        => true,
            'invoice_no'     => $bwsInvoice->invoice_no,
            'billing_month'  => $bwsInvoice->billing_month,
            'customer_name'  => $bwsInvoice->bwsCustomer->customer_name,
            'mobile'         => $bwsInvoice->bwsCustomer->mobile_number,
            'payable_amount' => $bwsInvoice->grand_total,
            'previous_paid'  => $prev,
            'balance_due'    => $bwsInvoice->due_amount,
        ]);
    }

    public function receiveStore(Request $request, BwsInvoice $bwsInvoice)
    {
        $request->validate([
            'received_date'   => 'required|date',
            'received_amount' => 'required|numeric|min:0.01',
            'payment_method'  => 'required|in:cash,bkash,nagad,rocket,bank,cheque,card',
        ]);

        DB::beginTransaction();
        try {
            $payment = BwsInvoicePayment::create([
                'payment_no'             => BwsInvoicePayment::generateNumber(),
                'bws_invoice_id'         => $bwsInvoice->id,
                'bws_customer_id'        => $bwsInvoice->bws_customer_id,
                'received_date'          => $request->received_date,
                'received_from'          => $request->received_from,
                'received_by'            => $request->received_by,
                'payment_method'         => $request->payment_method,
                'payable_amount'         => $bwsInvoice->due_amount,
                'received_amount'        => $request->received_amount,
                'discount'               => $request->discount ?? 0,
                'receipt_transaction_no' => $request->receipt_transaction_no,
                'remarks'                => $request->remarks,
                'status'                 => 'active',
                'created_by'             => auth()->id(),
            ]);
            DB::commit();
            return response()->json([
                'success'    => true,
                'message'    => "Payment {$payment->payment_no} saved. Income auto-recorded.",
                'payment_no' => $payment->payment_no,
                'income_no'  => $payment->income?->income_no,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function voidPayment(Request $request, BwsInvoicePayment $payment)
    {
        $request->validate(['reason' => 'required|string|max:255']);
        if ($payment->isVoid()) return response()->json(['success' => false, 'message' => 'Already voided.']);
        $payment->voidPayment($request->reason);
        return response()->json(['success' => true, 'message' => 'Payment voided. Income also voided.']);
    }

    public function dailyBill(Request $request)
    {
        $query = BwsInvoicePayment::with(['bwsInvoice', 'bwsCustomer', 'receivedBy', 'createdBy'])
            ->when($request->pop, function($q) use ($request) {
                $q->whereHas('bwsCustomer', fn($c) => $c->where('pop_info', $request->pop));
            })
            ->when($request->from_month, fn($q) =>
                $q->whereHas('bwsInvoice', fn($i) =>
                    $i->where('billing_month', '>=', $request->from_month)
                )
            )
            ->when($request->to_month, fn($q) =>
                $q->whereHas('bwsInvoice', fn($i) =>
                    $i->where('billing_month', '<=', $request->to_month)
                )
            )
            ->when($request->received_by, fn($q) => $q->where('received_by', $request->received_by))
            ->when($request->created_by,  fn($q) => $q->where('created_by', $request->created_by))
            ->when($request->tx_status,   fn($q) => $q->where('status', $request->tx_status))
            ->latest('received_date');

        $payments  = $query->paginate($request->get('per_page', 100))->withQueryString();
        $customers = BandwidthSaleCustomer::orderBy('customer_name')->get();
        $pops      = BandwidthSaleCustomer::whereNotNull('pop_info')->distinct()->pluck('pop_info');

        // isp-admin employees list
        $employees = \App\Models\HR\Employee::select('id', 'name', 'user_id')
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();

        return view('bandwidth-sale.daily-bill.index',
            compact('payments', 'customers', 'pops', 'employees'));
    }

    public function recurringIndex()
    {
        $invoices = BwsInvoice::with('bwsCustomer')->where('is_recurring', 1)->latest()->paginate(25);
        return view('bandwidth-sale.recurring.index', compact('invoices'));
    }

    public function recurringCreate()
    {
        $customers = BandwidthSaleCustomer::where('activity_status', 'active')->orderBy('customer_name')->get();
        $items = [];
        return view('bandwidth-sale.recurring.create', compact('customers', 'items'));
    }

    public function recurringStore(Request $request)
    {
        $request->validate([
            'bws_customer_id' => 'required|exists:bandwidth_sale_customers,id',
            'billing_month'   => 'required|date_format:Y-m',
            'repeat_date'     => 'required|integer|min:1|max:28',
            'grand_total'     => 'required|numeric|min:0',
            'items_json'      => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            $invoice = BwsInvoice::create([
                'invoice_no'      => BwsInvoice::generateNumber(),
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'payment_due'     => $request->payment_due,
                'daily_basis'     => $request->boolean('daily_basis'),
                'total_amount'    => $request->total_amount ?? 0,
                'vat_amount'      => $request->vat_amount ?? 0,
                'discount'        => $request->discount ?? 0,
                'grand_total'     => $request->grand_total,
                'due_amount'      => $request->grand_total,
                'status'          => 'unpaid',
                'notes'           => $request->notes,
                'is_recurring'    => 1,
                'repeat_date'     => $request->repeat_date,
                'recurring_start' => $request->start_date,
                'recurring_end'   => $request->end_date ?: null,
                'created_by'      => auth()->id(),
            ]);
            $this->saveItems($invoice->id, $request->items_json);
            DB::commit();
            return redirect()->route('bandwidth-sale.recurring.index')->with('success', "Recurring invoice {$invoice->invoice_no} created.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function recurringEdit(BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->load(['bwsCustomer', 'items']);
        $customers = BandwidthSaleCustomer::where('activity_status', 'active')->orderBy('customer_name')->get();
        $items = [];
        return view('bandwidth-sale.recurring.edit', compact('bwsInvoice', 'customers', 'items'));
    }

    public function recurringUpdate(Request $request, BwsInvoice $bwsInvoice)
    {
        DB::beginTransaction();
        try {
            $bwsInvoice->update([
                'bws_customer_id' => $request->bws_customer_id,
                'billing_month'   => $request->billing_month,
                'total_amount'    => $request->total_amount ?? 0,
                'grand_total'     => $request->grand_total,
                'repeat_date'     => $request->repeat_date,
                'recurring_start' => $request->start_date,
                'recurring_end'   => $request->end_date ?: null,
                'notes'           => $request->notes,
            ]);
            $bwsInvoice->items()->delete();
            $this->saveItems($bwsInvoice->id, $request->items_json);
            DB::commit();
            return redirect()->route('bandwidth-sale.recurring.index')->with('success', 'Recurring invoice updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function recurringDestroy(BwsInvoice $bwsInvoice)
    {
        $bwsInvoice->items()->delete();
        $bwsInvoice->delete();
        return response()->json(['success' => true, 'message' => 'Recurring invoice deleted.']);
    }

    public function dashboard()
    {
        $totalCustomers  = BandwidthSaleCustomer::count();
        $activeCustomers = BandwidthSaleCustomer::where('activity_status', 'active')->count();
        $totalInvoices   = BwsInvoice::count();
        $paidInvoices    = BwsInvoice::where('status', 'paid')->count();
        $dueInvoices     = BwsInvoice::whereIn('status', ['unpaid', 'overdue'])->count();
        $thisMonthIncome = BwsInvoicePayment::where('status', 'active')
            ->whereYear('received_date', now()->year)
            ->whereMonth('received_date', now()->month)
            ->sum('received_amount');
        $totalDue = BwsInvoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('due_amount');

        return view('bandwidth-sale.dashboard', compact(
            'totalCustomers', 'activeCustomers', 'totalInvoices',
            'paidInvoices', 'dueInvoices', 'thisMonthIncome', 'totalDue'
        ));
    }

    private function saveItems(int $invoiceId, string $itemsJson): void
    {
        $items = json_decode($itemsJson, true);
        foreach ($items as $i => $item) {
            if (empty($item['rate']) && empty($item['quantity'])) continue;
            BwsInvoiceItem::create([
                'bws_invoice_id' => $invoiceId,
                'item_name'      => $item['item_id'] ?? null,
                'description'    => $item['description'] ?? null,
                'unit'           => $item['unit'] ?? null,
                'quantity'       => $item['quantity'] ?? 1,
                'rate'           => $item['rate'] ?? 0,
                'vat_percent'    => $item['vat'] ?? 0,
                'from_date'      => $item['from_date'] ?: null,
                'to_date'        => $item['to_date'] ?: null,
                'total'          => $item['total'] ?? 0,
                'sort_order'     => $i,
            ]);
        }
    }
}
