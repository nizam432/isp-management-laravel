<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $agents = Agent::with('user')
            ->withCount('customers')
            ->withSum('commissions', 'amount')
            ->withSum(['commissions as pending_commission_sum' => fn($q) => $q->pending()], 'amount')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('email', 'like', "%{$request->search}%")))
            ->when($request->area, fn($q) => $q->where('area', $request->area))
            ->when($request->status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalAgents            = Agent::count();
        $activeAgents           = Agent::where('is_active', true)->count();
        $inactiveAgents         = Agent::where('is_active', false)->count();
        $pendingCommissionTotal = \App\Models\AgentCommission::pending()->sum('amount');

        return view('agents.index', compact(
            'agents', 'totalAgents', 'activeAgents', 'inactiveAgents', 'pendingCommissionTotal'
        ));
    }

    public function create()
    {
        return view('agents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'           => 'required|email|max:150|unique:users,email',
            'password'        => 'required|string|min:6|confirmed',
            'name'            => 'required|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'area'            => 'nullable|string|max:100',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'is_active'       => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            $agentRole = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
            $user->assignRole($agentRole);

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

            return redirect()->route('agents.show', $agent)
                             ->with('success', "Agent '{$agent->name}' created successfully. Login: {$user->email}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create agent: ' . $e->getMessage());
        }
    }

    public function show(Agent $agent)
    {
        $agent->load(['user', 'customers.zone', 'commissions' => fn($q) => $q->latest()->with('payment')]);

        $totalCommission   = $agent->commissions->sum('amount');
        $pendingCommission = $agent->commissions->where('status', 'pending')->sum('amount');
        $paidCommission    = $agent->commissions->where('status', 'paid')->sum('amount');

        return view('agents.show', compact(
            'agent', 'totalCommission', 'pendingCommission', 'paidCommission'
        ));
    }

    public function edit(Agent $agent)
    {
        $agent->load('user');
        return view('agents.edit', compact('agent'));
    }

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

            if ($agent->user) {
                $userData = ['name' => $request->name];
                if ($request->filled('email'))    $userData['email']    = $request->email;
                if ($request->filled('password')) $userData['password'] = Hash::make($request->password);
                $agent->user->update($userData);
            }

            ActivityLog::log('Agent updated', 'Agent', $agent->id);

            DB::commit();
            return redirect()->route('agents.show', $agent)->with('success', 'Agent updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy(Agent $agent)
    {
        if ($agent->customers()->count() > 0) {
            return back()->with('error', 'Cannot delete — customers are linked to this agent.');
        }

        ActivityLog::log('Agent deleted', 'Agent', $agent->id, $agent->toArray(), null);

        // Delete linked user account too
        $agent->user?->delete();
        $agent->delete();

        return redirect()->route('agents.index')->with('success', 'Agent deleted successfully.');
    }

    public function toggle(Agent $agent)
    {
        $agent->update(['is_active' => !$agent->is_active]);
        $status = $agent->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Agent '{$agent->name}' {$status}.");
    }

    public function payCommission(Request $request, Agent $agent)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $pending = $agent->commissions()->pending()->sum('amount');

        if ($request->amount > $pending) {
            return back()->with('error', 'Payment amount exceeds pending commission.');
        }

        $agent->commissions()
              ->pending()
              ->update([
                  'status'  => 'paid',
                  'paid_at' => now(),
              ]);

        ActivityLog::log('Commission paid', 'Agent', $agent->id);

        return back()->with('success', '৳' . number_format($request->amount, 2) . ' commission paid successfully.');
    }
}
