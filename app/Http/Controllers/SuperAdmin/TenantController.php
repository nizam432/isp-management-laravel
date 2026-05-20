<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    // ══════════════════════════════════════════════
    // Dashboard
    // ══════════════════════════════════════════════

    public function dashboard()
    {
        $stats = [
            'total_isp'       => Tenant::count(),
            'active_isp'      => Tenant::where('is_active', true)->count(),
            'pure_isp'        => Tenant::where('is_reseller', 1)->count(),
            'master_reseller' => Tenant::where('is_reseller', 2)->count(),
            'sub_reseller'    => Tenant::where('is_reseller', 3)->count(),
            'total_plans'     => Plan::count(),
        ];

        $recentTenants = Tenant::with('plan')->latest()->take(10)->get();

        return view('super-admin.dashboard', compact('stats', 'recentTenants'));
    }

    // ══════════════════════════════════════════════
    // ISP Management
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $tenants = Tenant::with(['plan', 'parent'])
            ->when($request->search, fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) => $q->where('is_reseller', $request->type))
            ->when($request->plan, fn($q) => $q->where('plan_id', $request->plan))
            ->latest()
            ->paginate(20);

        $plans = Plan::active()->get();

        return view('super-admin.tenants.index', compact('tenants', 'plans'));
    }

    public function create()
    {
        $plans         = Plan::active()->get();
        $masterResellers = Tenant::where('is_reseller', 2)->where('is_active', true)->get();

        return view('super-admin.tenants.create', compact('plans', 'masterResellers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'plan_id'      => 'required|exists:plans,id',
            'is_reseller'  => 'required|in:1,2,3',
            'parent_id'    => 'required_if:is_reseller,3',
            'password'     => 'required|string|min:6',
            'subdomain'    => 'required|string|max:50|unique:domains,domain|alpha_dash',
        ]);

        // Parent check for Sub Reseller
        if ($request->is_reseller == 3 && !$request->parent_id) {
            return back()->with('error', 'Sub Reseller এর জন্য Parent ISP select করুন।');
        }

        // User তৈরি করো
        $user = User::create([
            'name'     => $request->company_name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('isp-admin');

        // Plan info
        $plan = Plan::findOrFail($request->plan_id);

        // Tenant তৈরি করো
        $tenant = Tenant::create([
            'id'              => Str::slug($request->subdomain),
            'name'            => $request->company_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'address'         => $request->address,
            'plan_id'         => $request->plan_id,
            'is_reseller'     => $request->is_reseller,
            'parent_id'       => $request->parent_id ?? 0,
            'is_active'       => true,
            'plan_expires_at' => $plan->price == 0
                ? now()->addDays($plan->trial_days ?: 30)
                : now()->addMonth(),
        ]);

        // Domain তৈরি করো
        $tenant->domains()->create([
            'domain' => $request->subdomain . '.' . env('APP_DOMAIN', 'innovativeitbd.com'),
        ]);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', "ISP '{$request->company_name}' তৈরি হয়েছে।");
    }

    public function show(string $id)
    {
        $tenant = Tenant::with(['plan', 'parent', 'children'])->findOrFail($id);
        return view('super-admin.tenants.show', compact('tenant'));
    }

    public function edit(string $id)
    {
        $tenant          = Tenant::findOrFail($id);
        $plans           = Plan::active()->get();
        $masterResellers = Tenant::where('is_reseller', 2)->where('is_active', true)->get();

        return view('super-admin.tenants.edit', compact('tenant', 'plans', 'masterResellers'));
    }

    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:100',
            'plan_id'      => 'required|exists:plans,id',
            'is_reseller'  => 'required|in:1,2,3',
        ]);

        $tenant->update([
            'name'        => $request->company_name,
            'phone'       => $request->phone,
            'address'     => $request->address,
            'plan_id'     => $request->plan_id,
            'is_reseller' => $request->is_reseller,
            'parent_id'   => $request->parent_id ?? 0,
        ]);

        return back()->with('success', 'ISP আপডেট হয়েছে।');
    }

    /**
     * ISP Active/Inactive toggle
     */
    public function toggle(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['is_active' => !$tenant->is_active]);

        $status = $tenant->is_active ? 'চালু' : 'বন্ধ';
        return back()->with('success', "{$tenant->name} {$status} করা হয়েছে।");
    }

    /**
     * Plan change করো
     */
    public function changePlan(Request $request, string $id)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        $tenant = Tenant::findOrFail($id);
        $plan   = Plan::findOrFail($request->plan_id);

        $tenant->update([
            'plan_id'         => $plan->id,
            'plan_expires_at' => $plan->price == 0
                ? now()->addDays($plan->trial_days ?: 30)
                : now()->addMonth(),
        ]);

        return back()->with('success', "Plan পরিবর্তন হয়েছে → {$plan->name}");
    }
}
