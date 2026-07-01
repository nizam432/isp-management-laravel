<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ClientSupportTicket;
use App\Models\Package;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use App\Models\ClientTicketReply;
use App\Models\SupportCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientPortalController extends Controller
{
    /** Show the client portal login page. */
    public function loginForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('client.dashboard');
        }
        return view('client.login');
    }

    /**
     * Authenticate the client using PPPoE username + password.
     * pppoe_password is stored as plain text — direct comparison.
     */
    public function login(Request $request)
    {
        $request->validate([
            'pppoe_username' => 'required|string',
            'password'       => 'required|string',
        ], [
            'pppoe_username.required' => 'PPPoE Username is required.',
            'password.required'       => 'Password is required.',
        ]);

        $username = trim($request->pppoe_username);
        $password = $request->password;

        $customer = Customer::where('pppoe_username', $username)->first();

        // pppoe_password is plain text — direct comparison
        if ($customer && $customer->pppoe_password === $password) {

            // Manually login via customer guard
            Auth::guard('customer')->login($customer, $request->boolean('remember'));

            $request->session()->regenerate();

            return redirect()->route('client.dashboard')
                ->with('success', 'Welcome, ' . $customer->name . '!');
        }

        return back()
            ->withInput($request->only('pppoe_username'))
            ->withErrors(['pppoe_username' => 'Invalid PPPoE username or password.']);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login')->with('success', 'You have been logged out successfully.');
    }

    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['package', 'zone', 'invoices', 'payments']);

        // Current month invoice
        $currentInvoice = Invoice::where('customer_id', $customer->id)
            ->where('month', now()->format('Y-m'))
            ->first();

        // All unpaid invoices
        $unpaidInvoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue', 'partial'])
            ->orderBy('due_date')
            ->get();

        // Total due amount
        $totalDue = $unpaidInvoices->sum('due_amount');

        $recentPayments = Payment::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->with('invoice')
            ->latest('paid_at')
            ->take(5)
            ->get();

        // Open ticket count
        $openTickets = ClientSupportTicket::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        // Closed ticket count
        $closedTickets = ClientSupportTicket::where('customer_id', $customer->id)
            ->whereIn('status', ['solved', 'closed'])
            ->count();

        // Advance balance
        $advanceBalance = $customer->advance_balance ?? 0;

        $currency = Setting::get('currency', 'BDT');
        $companyName = Setting::get('company_name', 'My ISP');

        return view('client.dashboard', compact(
            'customer', 'currentInvoice', 'unpaidInvoices',
            'totalDue', 'recentPayments', 'openTickets',
            'closedTickets', 'advanceBalance', 'currency', 'companyName'
        ));
    }

    public function invoices(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $currency = Setting::get('currency', 'BDT');

        $invoices = Invoice::where('customer_id', $customer->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('payments')
            ->orderByDesc('created_at')
            ->paginate(12);

        $totalDue  = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue', 'partial'])
            ->sum('due_amount');

        $totalPaid = Payment::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->sum('amount');

        return view('client.invoices', compact('customer', 'invoices', 'totalDue', 'totalPaid', 'currency'));
    }

    /**
     * Single invoice detail
     */
    public function invoiceShow(Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to view this invoice.');
        }

        $invoice->load(['package', 'payments']);
        $currency   = Setting::get('currency', 'BDT');
        $footerText = Setting::get('invoice_footer_text', 'ধন্যবাদ আপনার পেমেন্টের জন্য।');

        return view('client.invoice-detail', compact('customer', 'invoice', 'currency', 'footerText'));
    }

    public function tickets(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $perPage = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;

        $tickets = ClientSupportTicket::where('customer_id', $customer->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->whereHas('category', fn($c) =>
                $c->where('name', 'like', "%{$request->search}%"))
                ->orWhere('remarks', 'like', "%{$request->search}%")
            )
            ->with(['category', 'assignees'])
            ->latest()
            ->paginate($perPage);

        $categories = SupportCategory::active()->orderBy('name')->get();

        $stats = [
            'total'      => ClientSupportTicket::where('customer_id', $customer->id)->count(),
            'pending'    => ClientSupportTicket::where('customer_id', $customer->id)->where('status', 'pending')->count(),
            'processing' => ClientSupportTicket::where('customer_id', $customer->id)->where('status', 'processing')->count(),
            'solved'     => ClientSupportTicket::where('customer_id', $customer->id)->where('status', 'solved')->count(),
            'closed'     => ClientSupportTicket::where('customer_id', $customer->id)->where('status', 'closed')->count(),
        ];

        return view('client.tickets', compact('customer', 'tickets', 'categories', 'stats'));
        
    }

    /** Submit a new support ticket from the client portal. */
    public function ticketStore(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'support_category_id' => 'required|exists:support_categories,id',
            'priority'            => 'required|in:low,medium,high,urgent',
            'remarks'             => 'required|string|min:5|max:2000',
            'attachment'          => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:1024',
        ], [
            'support_category_id.required' => 'Please select a category.',
            'priority.required'            => 'Please select a priority.',
            'remarks.required'             => 'Description is required.',
            'attachment.max'               => 'Attachment must not exceed 1MB.',
            'attachment.mimes'             => 'Only png, jpg, jpeg, pdf files are allowed.',
        ]);

        $data = [
            'ticket_no'           => ClientSupportTicket::generateNumber(),
            'customer_id'         => $customer->id,
            'support_category_id' => $request->support_category_id,
            'priority'            => $request->priority,
            'complained_no'       => $customer->phone,
            'remarks'             => $request->remarks,
            'status'              => 'pending',
            'created_from'        => 'client',
            'send_sms'            => false,
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('tickets/attachments', 'public');
        }

        $ticket = ClientSupportTicket::create($data);

        return redirect()->route('client.tickets')
            ->with('success', 'টিকেট সফলভাবে জমা হয়েছে। টিকেট নং: ' . $ticket->ticket_no);
    }

    /**
     * Single ticket detail + discussion
     */
    public function ticketShow(ClientSupportTicket $ticket)
    {
        $customer = Auth::guard('customer')->user();

        if ($ticket->customer_id !== $customer->id) {
            abort(403);
        }

        $ticket->load(['category', 'replies.customer', 'replies.user', 'assignees']);

        return view('client.ticket-detail', compact('customer', 'ticket'));
    }

    /**
     * Customer reply to a ticket
     */
    public function ticketReply(Request $request, ClientSupportTicket $ticket)
    {
        $customer = Auth::guard('customer')->user();

        if ($ticket->customer_id !== $customer->id) {
            abort(403);
        }

        $request->validate([
            'message'    => 'required|string|min:2|max:2000',
            'attachment' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:1024',
        ], [
            'message.required' => 'Message is required.',
            'attachment.max'   => 'Attachment must not exceed 1MB.',
            'attachment.mimes' => 'Only png, jpg, jpeg, pdf files are allowed.',
        ]);

        $data = [
            'ticket_id'   => $ticket->id,
            'customer_id' => $customer->id,
            'message'     => $request->message,
            'sender_type' => 'customer',
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('ticket-replies', 'public');
        }

        ClientTicketReply::create($data);

        // Move ticket to processing if still pending
        if ($ticket->status === 'pending') {
            $ticket->update(['status' => 'processing']);
        }

        return back()->with('success', 'Message পাঠানো হয়েছে।');
    }

    public function liveTraffic()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['package', 'router']);
        return view('client.live-traffic', compact('customer'));
    }

    /**
     * AJAX — get customer live session from MikroTik
     */
    public function sessionData()
    {
        $customer = Auth::guard('customer')->user();

        try {
            $router = $customer->router ?? MikrotikRouter::where('is_active', 1)->first();

            if (!$router) {
                return response()->json(['online' => false, 'message' => 'No router found.']);
            }

            if (!$customer->pppoe_username) {
                return response()->json(['online' => false, 'message' => 'No PPPoE username.']);
            }

            $mikrotik = new MikrotikService();
            $session  = $mikrotik->withRouter($router, fn($m) => $m->getCustomerSession($customer->pppoe_username));

            if (!$session) {
                return response()->json(['online' => false, 'message' => 'Offline']);
            }

            return response()->json([
                'online'    => true,
                'uptime'    => $session['uptime']    ?? '—',
                'ip'        => $session['address']   ?? '—',
                'tx_bytes'  => $session['tx-bytes']  ?? 0,
                'rx_bytes'  => $session['rx-bytes']  ?? 0,
                'tx_rate'   => $session['tx-rate']   ?? 0,
                'rx_rate'   => $session['rx-rate']   ?? 0,
            ]);

        } catch (\Exception $e) {
            return response()->json(['online' => false, 'message' => $e->getMessage()]);
        }
    }

    public function packages()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load('package');

        $packages = Package::active()
            ->orderBy('price')
            ->get();

        return view('client.packages', compact('customer', 'packages'));
    }

    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['package', 'zone', 'subZone', 'connectionType']);
        return view('client.profile', compact('customer'));
    }

    /**
     * Change portal password
     */
    public function changePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required'         => 'New password is required.',
            'password.min'              => 'Password must be at least 6 characters.',
            'password.confirmed'        => 'Password confirmation does not match.',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $customer->portal_password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $customer->update([
            'portal_password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function invoicePdf(\App\Models\Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized.');
        }

        // Reuse existing InvoiceController@pdf
        return app(\App\Http\Controllers\InvoiceController::class)->pdf($invoice);
    }
}
