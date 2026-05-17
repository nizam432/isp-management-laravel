<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Customer;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a paginated list of support tickets.
     * Supports filtering by status, priority, and search keyword.
     */
    public function index(Request $request)
    {
        $tickets = Ticket::with(['customer', 'assignedTo'])
            // Filter by status: open / assigned / in_progress / resolved / closed
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            // Filter by priority: low / medium / high / urgent
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            // Search by ticket subject or customer name
            ->when($request->search, fn($q) => $q
                ->where('subject', 'like', "%{$request->search}%")
                ->orWhereHas('customer', fn($c) =>
                    $c->where('name', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(20);

        // Summary counts for the view
        $openCount   = Ticket::open()->count();
        $urgentCount = Ticket::urgent()->count();

        return view('tickets.index', compact('tickets', 'openCount', 'urgentCount'));
    }

    /**
     * Show the form for creating a new support ticket.
     */
    public function create()
    {
        $customers   = Customer::active()->get();
        // Only show users with the technician role
        $technicians = User::role('technician')->get();

        return view('tickets.create', compact('customers', 'technicians'));
    }

    /**
     * Store a newly created ticket in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject'     => 'required|string|max:200',
            'category'    => 'required|in:connection,billing,speed,device,other',
            'priority'    => 'required|in:low,medium,high,urgent',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data = $request->all();

        // Auto-generate ticket number e.g. TKT-2025-0001
        $data['ticket_no'] = Ticket::generateNumber();

        // If assigned at creation time, mark status as assigned
        if ($request->assigned_to) {
            $data['status'] = 'assigned';
        }

        $ticket = Ticket::create($data);

        ActivityLog::log('Ticket created', 'Ticket', $ticket->id, null, $ticket->toArray());

        return redirect()->route('tickets.show', $ticket)
                         ->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified ticket with all replies and technician info.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['customer', 'assignedTo', 'replies.user']);
        $technicians = User::role('technician')->get();

        return view('tickets.show', compact('ticket', 'technicians'));
    }

    /**
     * Update the status, priority, or assigned technician of a ticket.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status'      => 'required|in:open,assigned,in_progress,resolved,closed',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $old  = $ticket->toArray();
        $data = $request->only('status', 'priority', 'assigned_to');

        // Set resolved timestamp when ticket is first marked as resolved
        if ($request->status === 'resolved' && !$ticket->resolved_at) {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        ActivityLog::log('Ticket updated', 'Ticket', $ticket->id, $old, $ticket->toArray());

        return back()->with('success', 'Ticket updated successfully.');
    }

    /**
     * Add a reply to the specified ticket.
     * Automatically moves ticket to in_progress if it was open or assigned.
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'message'   => $request->message,
        ]);

        // Move ticket to in_progress after first reply
        if (in_array($ticket->status, ['open', 'assigned'])) {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Delete the specified ticket along with all its replies.
     */
    public function destroy(Ticket $ticket)
    {
        // Remove all replies before deleting the ticket
        $ticket->replies()->delete();
        $ticket->delete();

        return redirect()->route('tickets.index')
                         ->with('success', 'Ticket deleted successfully.');
    }
}
