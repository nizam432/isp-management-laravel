<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class MyResellerController extends Controller
{

    // ══════════════════════════════════════════════
    // My Resellers List
    // ══════════════════════════════════════════════

    public function index()
    {
        $myTenant  = $this->getMyTenant();
        $resellers = Tenant::where('parent_id', $myTenant?->id ?? 0)
                           ->where('is_reseller', 3)
                           ->with('plan')
                           ->latest()
                           ->paginate(20);

        $plans = Plan::active()->get();

        return view('my-resellers.index', compact('resellers', 'plans', 'myTenant'));
    }

    // ══════════════════════════════════════════════
    // Create Sub Reseller
    // ══════════════════════════════════════════════

    public function create()
    {

        $myTenant = $this->getMyTenant();
        $plans    = Plan::active()->get();

        return view('my-resellers.create', compact('myTenant', 'plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'nullable|string|max:20',
            'password'     => 'required|string|min:6',
            'plan_id'      => 'required|exists:plans,id',
            'subdomain'    => 'required|string|max:50|alpha_dash',
        ]);

        $myTenant = $this->getMyTenant();

        if (!$myTenant) {
            return back()->with('error', 'আপনার tenant info পাওয়া যায়নি।');
        }

        // User তৈরি করো
        $user = User::create([
            'name'     => $request->company_name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $user->givePermissionTo('isp-admin');

        // Plan info
        $plan = Plan::findOrFail($request->plan_id);

        // Sub Reseller Tenant তৈরি করো
        $tenant = Tenant::create([
            'id'              => Str::slug($request->subdomain),
            'name'            => $request->company_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'plan_id'         => $plan->id,
            'is_reseller'     => 3, // Sub Reseller
            'parent_id'       => $myTenant->id,
            'is_active'       => true,
            'plan_expires_at' => now()->addMonth(),
        ]);

        // Domain তৈরি করো
        Domain::create([
            'domain'    => $request->subdomain . '.' . env('APP_DOMAIN', 'innovativeitbd.com'),
            'tenant_id' => $tenant->id,
        ]);

        return redirect()->route('my-resellers.index')
            ->with('success', "Reseller '{$request->company_name}' তৈরি হয়েছে।");
    }

    // ══════════════════════════════════════════════
    // Toggle Active/Inactive
    // ══════════════════════════════════════════════

    public function toggle(string $id)
    {
        $myTenant = $this->getMyTenant();
        $reseller = Tenant::where('id', $id)
                          ->where('parent_id', $myTenant?->id)
                          ->firstOrFail();

        $reseller->update(['is_active' => !$reseller->is_active]);
        $status = $reseller->is_active ? 'চালু' : 'বন্ধ';

        return back()->with('success', "{$reseller->name} {$status} করা হয়েছে।");
    }
    public function edit(string $id)
    {
        $myTenant = $this->getMyTenant();
        $reseller = Tenant::where('id', $id)
                          ->where('parent_id', $myTenant?->id)
                          ->firstOrFail();
        $plans = Plan::active()->get();

        return view('my-resellers.edit', compact('reseller', 'plans'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'plan_id'      => 'required|exists:plans,id',
            'is_active'    => 'required|boolean',
        ]);

        $myTenant = $this->getMyTenant();
        $reseller = Tenant::where('id', $id)
                          ->where('parent_id', $myTenant?->id)
                          ->firstOrFail();

        $reseller->update([
            'name'      => $request->company_name,
            'phone'     => $request->phone,
            'plan_id'   => $request->plan_id,
            'is_active' => $request->is_active,
        ]);

        // Password পরিবর্তন করলে
        if ($request->filled('password')) {
            User::where('email', $reseller->email)
                ->first()
                ?->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('my-resellers.index')
            ->with('success', 'Reseller আপডেট হয়েছে।');
    }    
    
    private function getMyTenant(): ?Tenant{
        // email দিয়ে খুঁজুন
        $tenant = Tenant::where('email', auth()->user()->email)->first();
        
        // না পেলে user id দিয়ে খুঁজুন
        if (!$tenant) {
            $tenant = Tenant::where('name', auth()->user()->name)->first();
        }
        
        return $tenant;
    }    
}
