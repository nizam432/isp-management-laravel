<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ClientSupportTicket;
use App\Models\ClientTicketReply;
use App\Models\Customer;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\MacResellerZone;
use App\Models\SupportCategory;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResellerClientSupportController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $tickets = ClientSupportTicket::with(['customer', 'category', 'createdBy', 'solvedBy', 'assignees'])
            ->whereHas('customer', fn($c) => $c->where('mac_reseller_id', $resellerId))
            ->when($request->category_id,   fn($q) => $q->where('support_category_id', $request->category_id))
            ->when($request->zone_id,       fn($q) => $q->whereHas('customer', fn($c) => $c->where('mac_reseller_zone_id', $request->zone_id)))
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->priority,      fn($q) => $q->where('priority', $request->priority))
            ->when($request->from_date,     fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date,       fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->when($request->complained_no, fn($q) => $q->where('complained_no', 'like', "%{$request->complained_no}%"))
            ->latest()
            ->get();

        // Summary counts — scoped to this reseller's own tickets only
        $baseQuery = fn() => ClientSupportTicket::whereHas('customer', fn($c) => $c->where('mac_reseller_id', $resellerId));

        $totalTickets      = $baseQuery()->whereMonth('created_at', now()->month)->count();
        $pendingTickets    = $baseQuery()->pending()->count();
        $processingTickets = $baseQuery()->processing()->count();
        $solvedTickets     = $baseQuery()->solved()->count();

        $categories  = SupportCategory::forReseller($resellerId)->active()->orderBy('name')->get();
        $zones       = MacResellerZone::forReseller($resellerId)->orderBy('name')->get();
        $employees   = Employee::forReseller($resellerId)->where('status', 'active')->orderBy('name')->get();
        $departments = Department::forReseller($resellerId)->active()->orderBy('name')->get();

        return view('reseller.client-support.index', compact(
            'tickets', 'totalTickets', 'pendingTickets', 'processingTickets', 'solvedTickets',
            'categories', 'zones', 'employees', 'departments'
        ));
    }

    /** AJAX — load THIS reseller's own customer by username/customer_code. */
    public function customerInfo(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $customer = Customer::forReseller($resellerId)
            ->with(['resellerZone', 'resellerTariffPackage.package'])
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
                'zone'            => $customer->resellerZone->name ?? '—',
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
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'support_category_id' => 'required|exists:support_categories,id',
            'priority'            => 'required|in:low,medium,high,urgent',
            'complained_no'       => 'required|string|max:50',
            'remarks'             => 'required|string',
        ]);

        // ownership checks — this customer and category must belong to this reseller
        Customer::forReseller($resellerId)->findOrFail($request->customer_id);
        SupportCategory::forReseller($resellerId)->findOrFail($request->support_category_id);

        $data = $request->only([
            'customer_id', 'support_category_id', 'priority',
            'complained_no', 'remarks',
        ]);
        $data['ticket_no']    = ClientSupportTicket::generateNumber();
        $data['created_by']   = null; // created by a reseller, not an internal Admin User
        $data['created_from'] = 'reseller';
        $data['send_sms']     = $request->boolean('send_sms');

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('tickets/attachments', 'public');
        }

        $ticket = ClientSupportTicket::create($data);
        $ticket->load(['customer', 'category', 'assignees']);

        // ── SMS to customer on ticket creation — uses THIS reseller's own gateway/template ──
        if ($ticket->send_sms && $ticket->customer?->phone) {
            try {
                (new SmsService(macResellerId: $resellerId))->sendTicketCreated(
                    $ticket->customer->phone,
                    $ticket->customer->name,
                    $ticket->ticket_no,
                    $ticket->category->name ?? '',
                    $ticket->complained_no
                );
            } catch (\Exception $e) {
                \Log::error('Reseller support ticket created SMS failed: ' . $e->getMessage());
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
        $this->authorizeItem($ticket);
        $ticket->load(['customer.resellerZone', 'customer.resellerTariffPackage.package', 'category', 'assignees']);
        return response()->json(['success' => true, 'ticket' => $ticket]);
    }

    public function update(Request $request, ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'support_category_id' => 'required|exists:support_categories,id',
            'priority'            => 'required|in:low,medium,high,urgent',
            'complained_no'       => 'required|string|max:50',
            'remarks'             => 'required|string',
        ]);

        SupportCategory::forReseller($resellerId)->findOrFail($request->support_category_id);

        $data = $request->only([
            'support_category_id', 'priority',
            'complained_no', 'remarks',
        ]);
        $data['send_sms'] = $request->boolean('send_sms');

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment) {
                Storage::disk('public')->delete($ticket->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('tickets/attachments', 'public');
        }

        $ticket->update($data);
        $ticket->load(['customer', 'category', 'assignees']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully.',
            'ticket'  => $this->formatRow($ticket),
        ]);
    }

    public function destroy(ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);

        if ($ticket->attachment) {
            Storage::disk('public')->delete($ticket->attachment);
        }
        $ticket->assignees()->detach();
        $ticket->delete();

        return response()->json(['success' => true, 'message' => 'Ticket deleted.']);
    }

    // ── Reseller Chat ────────────────────────────────────────────

    public function chat(ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);
        $ticket->load(['customer', 'category', 'assignees', 'replies.customer', 'replies.user']);
        return view('reseller.client-support.chat', compact('ticket'));
    }

    public function chatReply(Request $request, ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);
        $request->validate(['message' => 'required|string|min:1|max:2000']);

        $data = [
            'ticket_id'   => $ticket->id,
            'user_id'     => null, // sender is the reseller, not an Admin User
            'message'     => $request->message,
            'sender_type' => 'reseller',
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('ticket-replies', 'public');
        }

        $reply = ClientTicketReply::create($data);
        $reply->load('ticket.customer.macReseller');

        if ($ticket->status === 'pending') {
            $ticket->update(['status' => 'processing']);
        }

        return response()->json([
            'success' => true,
            'reply'   => [
                'id'          => $reply->id,
                'message'     => $reply->message,
                'sender_type' => 'reseller',
                'sender_name' => $reply->sender_name,
                'attachment'  => $reply->attachment ? asset('storage/' . $reply->attachment) : null,
                'time'        => $reply->created_at->format('d M Y h:i A'),
                'ago'         => $reply->created_at->diffForHumans(),
            ],
        ]);
    }

    public function chatMessages(Request $request, ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);

        $after   = $request->after ?? 0;
        $replies = ClientTicketReply::with(['customer', 'user', 'ticket.customer.macReseller'])
            ->where('ticket_id', $ticket->id)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'message'     => $r->message,
                'sender_type' => $r->sender_type,
                'sender_name' => $r->sender_name,
                'attachment'  => $r->attachment ? asset('storage/' . $r->attachment) : null,
                'time'        => $r->created_at->format('d M Y h:i A'),
                'ago'         => $r->created_at->diffForHumans(),
            ]);

        return response()->json(['success' => true, 'replies' => $replies]);
    }

    // Check mikrotik connection status for the Solve modal
    public function mikrotikStatus(ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);
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
            \Log::error('Reseller mikrotik status check FAILED', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['online' => false, 'uptime' => 'N/A', 'last_logout' => 'N/A']);
        }
    }

    // Quick Solve
    public function solve(Request $request, ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);
        $resellerId = Auth::guard('mac_reseller')->id();

        $ticket->update([
            'status'    => 'solved',
            'solved_by' => null, // solved by a reseller, not an internal Admin User
            'solved_at' => now(),
        ]);

        $ticket->load('customer');

        if ($ticket->send_sms && $ticket->customer?->phone) {
            try {
                (new SmsService(macResellerId: $resellerId))->sendTicketSolved(
                    $ticket->customer->phone,
                    $ticket->customer->name,
                    $ticket->ticket_no
                );
            } catch (\Exception $e) {
                \Log::error('Reseller support ticket solved SMS failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Ticket marked as solved.',
            'duration' => $ticket->fresh()->duration,
        ]);
    }

    // Reassign — only to THIS reseller's own HR employees
    public function reassign(Request $request, ClientSupportTicket $ticket)
    {
        $this->authorizeItem($ticket);
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'employee_ids'   => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $employees = Employee::forReseller($resellerId)->whereIn('id', $request->employee_ids)->get(['id', 'name', 'phone']);

        abort_if($employees->count() !== count($request->employee_ids), 403, 'One or more employees do not belong to you.');

        $ticket->assignees()->sync($employees->pluck('id'));
        $ticket->update(['status' => 'processing']);

        $names = $employees->pluck('name')->implode(', ');

        if ($request->boolean('sms')) {
            $smsService = new SmsService(macResellerId: $resellerId);
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
                        \Log::error('Reseller support ticket assigned SMS failed: ' . $e->getMessage());
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

    // AJAX — get THIS reseller's own employees by department
    public function getEmployees(Department $department)
    {
        $resellerId = Auth::guard('mac_reseller')->id();
        abort_unless($department->mac_reseller_id === $resellerId, 403);

        $employees = Employee::forReseller($resellerId)
            ->where('department_id', $department->id)
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
            'zone'          => $customer?->resellerZone?->name ?? '—',
            'sub_zone'      => $customer?->resellerSubZone?->name ?? '—',
            'category'      => $t->category->name ?? '—',
            'priority'      => $t->priority,
            'priority_badge'=> $t->priority_badge,
            'status'        => $t->status,
            'status_badge'  => $t->status_badge,
            'created_at'    => $t->created_at->format('d M Y H:i A'),
            'created_by'    => $t->createdBy->name ?? 'Reseller',
            'solved_at'     => $t->solved_at?->format('d M Y H:i A'),
            'solved_by'     => $t->solvedBy->name ?? '—',
            'duration'      => $t->solved_at ? $t->duration : null,
            'mac_address'   => $customer?->mac_address ?? '',
            'ip_address'    => $customer?->ip_address ?? '',
            'assignees'     => $t->assignees->map(fn($e) => ['id' => $e->id, 'name' => $e->name]),
        ];
    }

    private function authorizeItem(ClientSupportTicket $ticket): void
    {
        abort_unless(
            $ticket->customer && $ticket->customer->mac_reseller_id === Auth::guard('mac_reseller')->id(),
            403,
            'You do not have access to this ticket.'
        );
    }
}