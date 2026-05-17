<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * Display a paginated list of agents.
     * Includes customer count and total commission per agent.
     */
    public function index()
    {
        $agents = Agent::with('user')
            ->withCount('customers')             // number of customers under this agent
            ->withSum('commissions', 'amount')   // total commission earned
            ->latest()
            ->paginate(15);

        return view('agents.index', compact('agents'));
    }

    /**
     * Show the form for creating a new agent.
     * Only shows users who are not already assigned as an agent.
     */
    public function create()
    {
        // Active users who do not yet have an agent record
        $users = User::active()->doesntHave('agent')->get();

        return view('agents.create', compact('users'));
    }

    /**
     * Store a newly created agent in the database.
     * Assigns the 'agent' role to the linked user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'         => 'required|exists:users,id|unique:agents', // one user = one agent
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'area'            => 'nullable|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100', // max 100%
        ]);

        $agent = Agent::create($request->all());

        // Assign Spatie 'agent' role to the linked user
        $agent->user->assignRole('agent');

        ActivityLog::log('Agent created', 'Agent', $agent->id, null, $agent->toArray());

        return redirect()->route('agents.index')
                         ->with('success', 'Agent created successfully.');
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
     * Update the specified agent's information.
     */
    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'area'            => 'nullable|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_active'       => 'nullable|boolean',
        ]);

        $agent->update($request->all());

        return back()->with('success', 'Agent updated successfully.');
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
