<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AgentController extends Controller
{
    /**
     * Display a paginated list of agents.
     * Includes customer count and total commission per agent.
     */
    public function index(Request $request)
    {
        $agents = Agent::with('user')
            ->withCount('customers')             // number of customers under this agent
            ->withSum('commissions', 'amount')   // total commission earned
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('email', 'like', "%{$request->search}%")))
            ->when($request->area, fn($q) => $q->where('area', $request->area))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalAgents    = Agent::count();
        $activeAgents   = Agent::where('is_active', true)->count();
        $inactiveAgents = Agent::where('is_active', false)->count();
        $pendingCommissionTotal = \App\Models\AgentCommission::pending()->sum('amount');

        return view('agents.index', compact(
            'agents', 'totalAgents', 'activeAgents', 'inactiveAgents', 'pendingCommissionTotal'
        ));
    }

    /**
     * Show the form for creating a new agent.
     * Now creates a fresh User account (email/password) instead of linking existing.
     */
    public function create()
    {
        return view('agents.create');
    }

    /**
     * Store a newly created agent — creates a new User account (login)
     * and links it to the agent record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'           => 'required|email|max:150|unique:users,email',
            'password'        => 'required|string|min:6|confirmed',
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'area'            => 'nullable|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100', // max 100%
            'is_active'       => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // ── নতুন User account তৈরি — Agent login করতে পারবে ──
            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            // Spatie 'agent' role দাও
            $user->assignRole('agent');

            $agent = Agent::create([
                'user_id'         => $user->id,
                'name'            => $request->name,
                'phone'           => $request->phone,
                'area'            => $request->area,
                'commission_rate' => $request->commission_rate,
                'is_active'       => $request->boolean('is_active', true),
            ]);

            ActivityLog::log('Agent created', 'Agent', $agent->id, null, $agent->toArray());

            DB::commit();

            return redirect()->route('agents.index')
                             ->with('success', "Agent '{$agent->name}' created. Login: {$user->email}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create agent: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified agent with customer list and commission history.
     */
    public function show(Agent $agent)
    {
        $agent->load(['user', 'customers', 'commissions.payment']);

        // Total pending commission not yet paid out
        $pendingCommission = $agent->commissions()->pending()->sum('amount');

        return view('agents.show', compact('agent', 'pendingCommission'));
    }

    /**
     * Show edit form.
     */
    public function edit(Agent $agent)
    {
        $agent->load('user');
        return view('agents.edit', compact('agent'));
    }

    /**
     * Update the specified agent's information.
     * Optionally updates linked User's email/password too.
     */
    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'area'            => 'nullable|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_active'       => 'nullable|boolean',
            'email'           => 'nullable|email|max:150|unique:users,email,' . $agent->user_id,
            'password'        => 'nullable|string|min:6|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $agent->update([
                'name'            => $request->name,
                'phone'           => $request->phone,
                'area'            => $request->area,
                'commission_rate' => $request->commission_rate,
                'is_active'       => $request->boolean('is_active'),
            ]);

            // Linked User account update (optional email/password change)
            if ($agent->user) {
                $userData = ['name' => $request->name];
                if ($request->filled('email')) {
                    $userData['email'] = $request->email;
                }
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $agent->user->update($userData);
            }

            DB::commit();
            return back()->with('success', 'Agent updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified agent.
     * Will not delete if customers are linked to this agent.
     */
    public function destroy(Agent $agent)
    {
        // Prevent deletion if agent has customers assigned
        if ($agent->customers()->count() > 0) {
            return back()->with('error', 'Cannot delete — customers are linked to this agent.');
        }

        ActivityLog::log('Agent deleted', 'Agent', $agent->id, $agent->toArray(), null);
        $agent->delete();

        return redirect()->route('agents.index')
                         ->with('success', 'Agent deleted successfully.');
    }

    /**
     * Mark all pending commissions for the agent as paid.
     */
    public function payCommission(Request $request, Agent $agent)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        // Update all pending commissions to paid
        $agent->commissions()
              ->pending()
              ->update([
                  'status'  => 'paid',
                  'paid_at' => now(),
              ]);

        ActivityLog::log('Commission paid', 'Agent', $agent->id);

        return back()->with('success', 'Commission paid successfully.');
    }
}
