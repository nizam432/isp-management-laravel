<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ClientSupportTicket;
use App\Models\ClientTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerSupportController extends Controller
{
    public function index(Request $request)
    {
        $resellerId  = Auth::guard('mac_reseller')->id();
        $customerIds = Customer::forReseller($resellerId)->pluck('id');

        $query = ClientSupportTicket::with('customer')
            ->whereIn('customer_id', $customerIds);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'  => ClientSupportTicket::whereIn('customer_id', $customerIds)->count(),
            'open'   => ClientSupportTicket::whereIn('customer_id', $customerIds)->where('status', 'open')->count(),
            'closed' => ClientSupportTicket::whereIn('customer_id', $customerIds)->where('status', 'closed')->count(),
        ];

        return view('reseller.support.index', compact('tickets', 'stats'));
    }

    public function show(ClientSupportTicket $ticket)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless(
            $ticket->customer && $ticket->customer->mac_reseller_id === $resellerId,
            403,
            'You do not have access to this ticket.'
        );

        $ticket->load(['customer', 'replies']);

        return view('reseller.support.show', compact('ticket'));
    }

    public function reply(Request $request, ClientSupportTicket $ticket)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless(
            $ticket->customer && $ticket->customer->mac_reseller_id === $resellerId,
            403
        );

        $request->validate(['message' => 'required|string']);

        ClientTicketReply::create([
            'ticket_id'  => $ticket->id,
            'message'    => $request->message,
            'replied_by' => 'reseller',
        ]);

        // Reopen the ticket if a reply arrives after it was closed.
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return response()->json(['success' => true, 'message' => 'Reply sent successfully.']);
    }

    public function close(ClientSupportTicket $ticket)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless(
            $ticket->customer && $ticket->customer->mac_reseller_id === $resellerId,
            403
        );

        $ticket->update(['status' => 'closed']);

        return response()->json(['success' => true, 'message' => 'Ticket closed.']);
    }
}
