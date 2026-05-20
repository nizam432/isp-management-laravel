<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('tenants')->get();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'slug'             => 'required|string|unique:plans,slug|alpha_dash',
            'price'            => 'required|numeric|min:0',
            'max_customers'    => 'required|integer|min:-1',
            'max_routers'      => 'required|integer|min:-1',
            'sms_enabled'      => 'nullable|boolean',
            'reseller_enabled' => 'nullable|boolean',
            'trial_days'       => 'required|integer|min:0',
            'description'      => 'nullable|string',
        ]);

        Plan::create([
            'name'             => $request->name,
            'slug'             => $request->slug,
            'price'            => $request->price,
            'max_customers'    => $request->max_customers,
            'max_routers'      => $request->max_routers,
            'sms_enabled'      => $request->boolean('sms_enabled'),
            'reseller_enabled' => $request->boolean('reseller_enabled'),
            'trial_days'       => $request->trial_days,
            'description'      => $request->description,
        ]);

        return back()->with('success', 'Plan তৈরি হয়েছে।');
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'price'            => 'required|numeric|min:0',
            'max_customers'    => 'required|integer|min:-1',
            'max_routers'      => 'required|integer|min:-1',
            'trial_days'       => 'required|integer|min:0',
        ]);

        $plan->update([
            'name'             => $request->name,
            'price'            => $request->price,
            'max_customers'    => $request->max_customers,
            'max_routers'      => $request->max_routers,
            'sms_enabled'      => $request->boolean('sms_enabled'),
            'reseller_enabled' => $request->boolean('reseller_enabled'),
            'trial_days'       => $request->trial_days,
            'description'      => $request->description,
        ]);

        return back()->with('success', 'Plan আপডেট হয়েছে।');
    }

    public function toggle(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        return back()->with('success', 'Plan status পরিবর্তন হয়েছে।');
    }
}
