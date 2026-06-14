<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ClientSupportTicket;
use App\Models\SupportCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientPortalController extends Controller
{
    // ══════════════════════════════════════════════════════
    // AUTH — Login / Logout
    // ══════════════════════════════════════════════════════

    /**
     * Login page দেখাও
     */
    public function loginForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('client.dashboard');
        }
        return view('client.login');
    }

    /**
     * Login process করো
     * PPPoE username + pppoe_password দিয়ে login হবে
     * pppoe_password plain text — সরাসরি compare করা হবে
     */
    public function login(Request $request)
    {
        $request->validate([
            'pppoe_username' => 'required|string',
            'password'       => 'required|string',
        ], [
            'pppoe_username.required' => 'PPPoE Username দিন।',
            'password.required'       => 'পাসওয়ার্ড দিন।',
        ]);

        $username = trim($request->pppoe_username);
        $password = $request->password;

        // pppoe_username দিয়ে customer খুঁজব
        $customer = Customer::where('pppoe_username', $username)->first();

        // pppoe_password plain text — সরাসরি compare
        if ($customer && $customer->pppoe_password === $password) {

            // Laravel guard এ manually login করাব
            Auth::guard('customer')->login($customer, $request->boolean('remember'));

            $request->session()->regenerate();

            return redirect()->route('client.dashboard')
                ->with('success', 'স্বাগতম, ' . $customer->name . '!');
        }

        return back()
            ->withInput($request->only('pppoe_username'))
            ->withErrors(['pppoe_username' => 'PPPoE Username বা পাসওয়ার্ড সঠিক নয়।']);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login')->with('success', 'সফলভাবে লগআউট হয়েছেন।');
    }

    // ══════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════

    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['package', 'zone', 'invoices', 'payments']);

        // এই মাসের invoice
        $currentInvoice = Invoice::where('customer_id', $customer->id)
            ->where('month', now()->format('Y-m'))
            ->first();

        // সব বকেয়া invoice
        $unpaidInvoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue', 'partial'])
            ->orderBy('due_date')
            ->get();

        // মোট বকেয়া
        $totalDue = $unpaidInvoices->sum('due_amount');

        // সর্বশেষ ৫টি পেমেন্ট
        $recentPayments = Payment::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->with('invoice')
            ->latest('paid_at')
            ->take(5)
            ->get();

        // Open টিকেট সংখ্যা
        $openTickets = ClientSupportTicket::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        // Closed টিকেট সংখ্যা
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

    // ══════════════════════════════════════════════════════
    // INVOICES
    // ══════════════════════════════════════════════════════

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

        // অন্য customer এর invoice দেখতে পারবে না
        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'এই invoice দেখার অনুমতি নেই।');
        }

        $invoice->load(['package', 'payments']);
        $currency   = Setting::get('currency', 'BDT');
        $footerText = Setting::get('invoice_footer_text', 'ধন্যবাদ আপনার পেমেন্টের জন্য।');

        return view('client.invoice-detail', compact('customer', 'invoice', 'currency', 'footerText'));
    }

    // ══════════════════════════════════════════════════════
    // SUPPORT TICKETS
    // ══════════════════════════════════════════════════════

    public function tickets(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $tickets = ClientSupportTicket::where('customer_id', $customer->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('category')
            ->latest()
            ->paginate(10);

        $categories = SupportCategory::active()->orderBy('name')->get();

        $stats = [
            'total'      => ClientSupportTicket::where('customer_id', $customer->id)->count(),
            'pending'    => ClientSupportTicket::where('customer_id', $customer->id)->where('status', 'pending')->count(),
            'processing' => ClientSupportTicket::where('customer_id', $customer->id)->where('status', 'processing')->count(),
            'solved'     => ClientSupportTicket::where('customer_id', $customer->id)->where('status', 'solved')->count(),
        ];

        return view('client.tickets', compact('customer', 'tickets', 'categories', 'stats'));
    }

    /**
     * নতুন ticket জমা দাও
     */
    public function ticketStore(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'support_category_id' => 'required|exists:support_categories,id',
            'priority'            => 'required|in:low,medium,high,urgent',
            'remarks'             => 'required|string|min:10|max:1000',
        ], [
            'support_category_id.required' => 'সমস্যার ধরন বেছে নিন।',
            'priority.required'            => 'অগ্রাধিকার বেছে নিন।',
            'remarks.required'             => 'সমস্যার বিবরণ লিখুন।',
            'remarks.min'                  => 'কমপক্ষে ১০ অক্ষর লিখুন।',
        ]);

        $ticket = ClientSupportTicket::create([
            'ticket_no'           => ClientSupportTicket::generateNumber(),
            'customer_id'         => $customer->id,
            'support_category_id' => $request->support_category_id,
            'priority'            => $request->priority,
            'complained_no'       => $customer->phone,
            'remarks'             => $request->remarks,
            'status'              => 'pending',
            'created_from'        => 'client_portal',
            'send_sms'            => false,
        ]);

        return redirect()->route('client.tickets')
            ->with('success', 'টিকেট সফলভাবে জমা হয়েছে। টিকেট নং: ' . $ticket->ticket_no);
    }

    // ══════════════════════════════════════════════════════
    // PROFILE
    // ══════════════════════════════════════════════════════

    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['package', 'zone', 'subZone', 'connectionType']);
        return view('client.profile', compact('customer'));
    }

    /**
     * পাসওয়ার্ড পরিবর্তন
     */
    public function changePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড দিন।',
            'password.required'         => 'নতুন পাসওয়ার্ড দিন।',
            'password.min'              => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
            'password.confirmed'        => 'নতুন পাসওয়ার্ড মিলছে না।',
        ]);

        // বর্তমান পাসওয়ার্ড চেক করো
        if (!Hash::check($request->current_password, $customer->portal_password)) {
            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।']);
        }

        $customer->update([
            'portal_password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।');
    }
}
