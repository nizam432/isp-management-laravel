<?php

namespace App\Http\Controllers;

use App\Models\ClientSupportTicket;
use App\Models\SupportCategory;
use App\Models\Customer;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\Zone;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientSupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = ClientSupportTicket::with(['customer', 'category', 'createdBy', 'solvedBy', 'assignees'])
            ->when($request->category_id,  fn($q) => $q->where('support_category_id', $request->category_id))
            ->when($request->zone_id,      fn($q) => $q->whereHas('customer', fn($c) => $c->where('zone_id', $request->zone_id)))
            ->when($request->status,       fn($q) => $q->where('status', $request->status))
            ->when($request->priority,     fn($q) => $q->where('priority', $request->priority))
            ->when($request->from_date,    fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date,      fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->when($request->complained_no,fn($q) => $q->where('complained_no', 'like', "%{$request->complained_no}%"))
            ->when($request->created_by,   fn($q) => $q->where('created_by', $request->created_by))
            ->when($request->solved_by,    fn($q) => $q->where('solved_by', $request->solved_by))
            ->latest()
            ->get();

        // Summary counts
        $totalTickets      = ClientSupportTicket::whereMonth('created_at', now()->month)->count();
        $pendingTickets    = ClientSupportTicket::pending()->count();
        $processingTickets = ClientSupportTicket::processing()->count();
        $solvedTickets     = ClientSupportTicket::solved()->count();

        $categories  = SupportCategory::active()->orderBy('name')->get();
        $zones       = Zone::orderBy('name')->get();
        $employees   = Employee::where('status', 'active')->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        // NOTE: "Created By" / "Solved By" filters store User IDs (see model:
        // createdBy()/solvedBy() belongsTo User::class), not Employee IDs.
        // If those two filter dropdowns are meant to filter created_by/solved_by,
        // populate them from User::all() instead of $employees in the Blade view.

        return view('client_support.index', compact(
            'tickets', 'totalTickets', 'pendingTickets', 'processingTickets', 'solvedTickets',
            'categories', 'zones', 'employees', 'departments'
        ));
    }

    // AJAX — load customer info by username/pppoe_username
    public function customerInfo(Request $request)
    {
        $customer = Customer::with(['zone', 'package'])
            ->where(function ($q) use ($request) {
                $q->where('pppoe_username', $request->username)
                  ->orWhere('customer_code', $request->username);
            })
            ->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.']);
        }

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'              => $customer->id,
                'name'            => $customer->name,
                'phone'           => $customer->phone,
                'address'         => $customer->address,
                'zone'            => $customer->zone->name ?? '—',
                'billing_status'  => $customer->billing_status ?? 'active',
                'monthly_bill'    => $customer->monthly_bill_amount ?? 0,
                'mikrotik_status' => $customer->mikrotik_status ?? 'pending',
                'ip_address'      => $customer->ip_address,
                'mac_address'     => $customer->mac_address,
                'pppoe_username'  => $customer->pppoe_username,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'support_category_id' => 'required|exists:support_categories,id',
            'priority'            => 'required|in:low,medium,high,urgent',
            'complained_no'       => 'required|string|max:50',
            'remarks'             => 'required|string',
        ]);

        // Whitelisted fields only — do NOT use $request->except('attachment') here.
        // That previously allowed status/solved_by/solved_at/created_by to be
        // mass-assigned directly from the form, bypassing solve()/auth().
        $data = $request->only([
            'customer_id', 'support_category_id', 'priority',
            'complained_no', 'subject', 'remarks',
        ]);
        $data['ticket_no']    = ClientSupportTicket::generateNumber();
        $data['created_by']   = auth()->id();
        $data['created_from'] = 'admin';
        $data['send_sms']     = $request->boolean('send_sms');

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('tickets/attachments', 'public');
        }

        $ticket = ClientSupportTicket::create($data);
        $ticket->load(['customer', 'category', 'createdBy', 'assignees']);

        // ── SMS to customer on ticket creation ──
        if (\App\Models\Setting::get('support_ticket_created_sms', '1') == '1'
            && $ticket->send_sms
            && $ticket->customer?->phone) {
            try {
                (new SmsService())->sendTicketCreated(
                    $ticket->customer->phone,
                    $ticket->customer->name,
                    $ticket->ticket_no,
                    $ticket->category->name ?? '',
                    $ticket->complained_no
                );
            } catch (\Exception $e) {
                \Log::error('Support ticket created SMS failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Ticket {$ticket->ticket_no} created successfully.",
            'ticket'  => $this->formatRow($ticket),
        ]);
    }

    public function edit(ClientSupportTicket $ticket)
    {
        $ticket->load(['customer.zone', 'customer.package', 'category', 'assignees']);
        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    public function update(Request $request, ClientSupportTicket $ticket)
    {
        $request->validate([
            'support_category_id' => 'required|exists:support_categories,id',
            'priority'            => 'required|in:low,medium,high,urgent',
            'complained_no'       => 'required|string|max:50',
            'remarks'             => 'required|string',
        ]);

        // Whitelisted fields only — same reasoning as store().
        $data = $request->only([
            'support_category_id', 'priority',
            'complained_no', 'subject', 'remarks',
        ]);
        $data['send_sms'] = $request->boolean('send_sms');

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment) {
                Storage::disk('public')->delete($ticket->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('tickets/attachments', 'public');
        }

        $ticket->update($data);
        $ticket->load(['customer', 'category', 'createdBy', 'assignees']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully.',
            'ticket'  => $this->formatRow($ticket),
        ]);
    }

    public function destroy(ClientSupportTicket $ticket)
    {
        if ($ticket->attachment) {
            Storage::disk('public')->delete($ticket->attachment);
        }
        $ticket->assignees()->detach();
        $ticket->delete();

        return response()->json(['success' => true, 'message' => 'Ticket deleted.']);
    }

    // Check mikrotik connection status for solve modal
    public function mikrotikStatus(ClientSupportTicket $ticket)
    {
        $customer = $ticket->customer;

        try {
            $routerId = $customer->router_id;
            if (!$routerId || !$customer->pppoe_username) {
                return response()->json(['online' => false, 'uptime' => 'N/A', 'last_logout' => 'N/A']);
            }

            $router = \App\Models\MikrotikRouter::find($routerId);
            if (!$router) {
                return response()->json(['online' => false, 'uptime' => 'N/A', 'last_logout' => 'N/A']);
            }

            $sessions = (new \App\Services\MikrotikService())->withRouter($router, function ($api) {
                return $api->getActiveSessions();
            });

            $session = collect($sessions)->firstWhere('name', $customer->pppoe_username);

            if ($session) {
                return response()->json(['online' => true, 'uptime' => $session['uptime'] ?? '—', 'last_logout' => '—']);
            }

            return response()->json([
                'online'      => false,
                'uptime'      => '—',
                'last_logout' => $customer->updated_at?->format('d/m/Y h:i A') ?? 'N/A',
            ]);

        } catch (\Exception $e) {
            \Log::error('Mikrotik status check FAILED', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['online' => false, 'uptime' => 'N/A', 'last_logout' => 'N/A']);
        }
    }

    // ── Admin Chat ────────────────────────────────────────────────

    public function chat(ClientSupportTicket $ticket)
    {
        $ticket->load(['customer', 'category', 'assignees', 'replies.customer', 'replies.user']);
        return view('client_support.chat', compact('ticket'));
    }

    public function chatReply(Request $request, ClientSupportTicket $ticket)
    {
        $request->validate(['message' => 'required|string|min:1|max:2000']);

        $data = [
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'message'     => $request->message,
            'sender_type' => 'admin',
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('ticket-replies', 'public');
        }

        $reply = \App\Models\ClientTicketReply::create($data);
        $reply->load('user');

        // Move to processing if pending
        if ($ticket->status === 'pending') {
            $ticket->update(['status' => 'processing']);
        }

        return response()->json([
            'success' => true,
            'reply'   => [
                'id'          => $reply->id,
                'message'     => $reply->message,
                'sender_type' => 'admin',
                'sender_name' => $reply->user->name ?? 'Admin',
                'attachment'  => $reply->attachment ? asset('storage/' . $reply->attachment) : null,
                'time'        => $reply->created_at->format('d M Y h:i A'),
                'ago'         => $reply->created_at->diffForHumans(),
            ],
        ]);
    }

    // Polling — fetch new messages after last ID
    public function chatMessages(Request $request, ClientSupportTicket $ticket)
    {
        $after   = $request->after ?? 0;
        $replies = \App\Models\ClientTicketReply::with(['customer', 'user'])
            ->where('ticket_id', $ticket->id)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'message'     => $r->message,
                'sender_type' => $r->sender_type,
                'sender_name' => $r->sender_type === 'admin'
                    ? ($r->user->name ?? 'Admin')
                    : ($r->customer->name ?? 'Customer'),
                'attachment'  => $r->attachment ? asset('storage/' . $r->attachment) : null,
                'time'        => $r->created_at->format('d M Y h:i A'),
                'ago'         => $r->created_at->diffForHumans(),
            ]);

        return response()->json(['success' => true, 'replies' => $replies]);
    }

    // ── Admin Chat ────────────────────────────────────────────────

    // Quick Solve
    public function solve(Request $request, ClientSupportTicket $ticket)
    {
        $ticket->update([
            'status'    => 'solved',
            'solved_by' => auth()->id(),
            'solved_at' => now(),
        ]);

        $ticket->load('customer');

        // ── SMS to customer on ticket solve ──
        if (\App\Models\Setting::get('support_ticket_solved_sms', '1') == '1'
            && $ticket->send_sms
            && $ticket->customer?->phone) {
            try {
                (new SmsService())->sendTicketSolved(
                    $ticket->customer->phone,
                    $ticket->customer->name,
                    $ticket->ticket_no
                );
            } catch (\Exception $e) {
                \Log::error('Support ticket solved SMS failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Ticket marked as solved.',
            'duration' => $ticket->fresh()->duration,
        ]);
    }

    // Reassign employees
    public function reassign(Request $request, ClientSupportTicket $ticket)
    {
       
        $request->validate([
            'employee_ids'  => 'required|array',
            'employee_ids.*'=> 'exists:employees,id',
        ]);

        $ticket->assignees()->sync($request->employee_ids);
        $ticket->update(['status' => 'processing']);

        $employees = Employee::whereIn('id', $request->employee_ids)->get(['id', 'name', 'phone']);
        $names     = $employees->pluck('name')->implode(', ');

        // ── SMS to assigned employees ──
        // NOTE: requires the frontend to send `sms` in the AJAX payload
        // (btnConfirmReassign handler must add: sms: $('#reassign_sms').is(':checked') ? 1 : 0)
        if ($request->boolean('sms')) {// \App\Models\Setting::get('support_ticket_assigned_sms', '1') == '1'
         
           $smsService = new SmsService();
            foreach ($employees as $emp) {
                if ($emp->phone) {
                    try {
                        $smsService->sendTicketAssigned(
                            $emp->phone,
                            $emp->name,
                            $ticket->ticket_no,
                            $ticket->complained_no
                        );
                    } catch (\Exception $e) {
                        \Log::error('Support ticket assigned SMS failed: ' . $e->getMessage());
                    }
                }
            }
              
        }

        return response()->json([
            'success' => true,
            'message' => "Reassigned to: {$names}",
            'names'   => $names,
        ]);
    }

    // AJAX — get employees by department
    public function getEmployees(Department $department)
    {
        $employees = Employee::where('department_id', $department->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'employees' => $employees]);
    }

    private function formatRow(ClientSupportTicket $t): array
    {
        $customer = $t->customer;
        return [
            'id'            => $t->id,
            'ticket_no'     => $t->ticket_no,
            'client_code'   => $customer?->customer_code ?? '—',
            'pppoe_username'=> $customer?->pppoe_username ?? '—',
            'customer_name' => $customer?->name ?? '—',
            'mobile'        => $customer?->phone ?? '—',
            'complained_no' => $t->complained_no,
            'zone'          => $customer?->zone?->name ?? '—',
            'sub_zone'      => $customer?->subZone?->name ?? '—',
            'category'      => $t->category->name ?? '—',
            'priority'      => $t->priority,
            'priority_badge'=> $t->priority_badge,
            'status'        => $t->status,
            'status_badge'  => $t->status_badge,
            'created_at'    => $t->created_at->format('d M Y H:i A'),
            'created_by'    => $t->createdBy->name ?? '—',
            'solved_at'     => $t->solved_at?->format('d M Y H:i A'),
            'solved_by'     => $t->solvedBy->name ?? '—',
            'duration'      => $t->solved_at ? $t->duration : null,
            'mac_address'   => $customer?->mac_address ?? '',
            'ip_address'    => $customer?->ip_address ?? '',
            'assignees'     => $t->assignees->map(fn($e) => ['id' => $e->id, 'name' => $e->name]),
        ];
    }
}