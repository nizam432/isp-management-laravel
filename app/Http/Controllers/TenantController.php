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

    /**
     * AJAX endpoint — date filter অনুযায়ী stats reload
     * GET /super-admin/dashboard/stats
     */
    public function dashboardStats(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request->range, $request->from, $request->to);

        $query = Tenant::query();
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $stats = [
            'total_isp'       => (clone $query)->count(),
            'active_isp'      => (clone $query)->where('is_active', true)->count(),
            'pure_isp'        => (clone $query)->where('is_reseller', 1)->count(),
            'master_reseller' => (clone $query)->where('is_reseller', 2)->count(),
            'sub_reseller'    => (clone $query)->where('is_reseller', 3)->count(),
            'total_plans'     => Plan::count(), // plans not date-bound
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Resolve a named date range (or custom from/to) into [start, end] Carbon dates.
     */
    private function resolveDateRange(?string $range, ?string $from, ?string $to): array
    {
        $now = now();

        return match ($range) {
            'today'                  => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday'              => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'last_7_days'            => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'last_30_days'           => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_month'             => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month'             => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_month_last_year'   => [$now->copy()->subYear()->startOfMonth(), $now->copy()->subYear()->endOfMonth()],
            'this_year'              => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year'              => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'current_financial_year' => [$now->month >= 7 ? $now->copy()->month(7)->startOfMonth() : $now->copy()->subYear()->month(7)->startOfMonth(),
                                          $now->month >= 7 ? $now->copy()->addYear()->month(6)->endOfMonth() : $now->copy()->month(6)->endOfMonth()],
            'last_financial_year'    => [$now->month >= 7 ? $now->copy()->subYear()->month(7)->startOfMonth() : $now->copy()->subYears(2)->month(7)->startOfMonth(),
                                          $now->month >= 7 ? $now->copy()->month(6)->endOfMonth() : $now->copy()->subYear()->month(6)->endOfMonth()],
            'custom'                 => [$from ? \Carbon\Carbon::parse($from)->startOfDay() : null, $to ? \Carbon\Carbon::parse($to)->endOfDay() : null],
            default                  => [null, null], // all_time
        };
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

        // ── Auto Seed Default Data ────────────────────────────────
        $tenant->run(function () {
            $now = now();

            // ── 1. Protocol Types (Fixed — edit/delete করা যাবে না) ──
            $protocols = ['PPPoE', 'Hotspot', 'OVPN', 'PPTP', 'Static IP'];
            foreach ($protocols as $protocol) {
                \App\Models\ProtocolType::create([
                    'name'      => $protocol,
                    'is_active' => true,
                ]);
            }

            // ── 2. Client Types ───────────────────────────────────────
            $clientTypes = [
                'Home', 'Corporate', 'SME (Small & Medium Enterprise)',
                'Student', 'Government', 'NGO', 'Hospital / Clinic',
                'School / College', 'Hotel / Restaurant', 'Shop / Retail',
            ];
            foreach ($clientTypes as $type) {
                \App\Models\ClientType::create([
                    'name'      => $type,
                    'is_active' => true,
                ]);
            }

            // ── 3. Connection Types ───────────────────────────────────
            $connectionTypes = [
                'Fiber Optic', 'Cable', 'Wireless', 'Radio Link',
                'VSAT', '4G/LTE', 'ADSL', 'VDSL', 'Leased Line', 'Point to Point',
            ];
            foreach ($connectionTypes as $type) {
                \App\Models\ConnectionType::create([
                    'name'      => $type,
                    'is_active' => true,
                ]);
            }

            // ── 4. Income Categories ──────────────────────────────────
            $incomeCategories = [
                ['name' => 'Monthly Bill',   'slug' => 'monthly_bill',   'is_system' => true,  'sort_order' => 1],
                ['name' => 'Connection Fee', 'slug' => 'connection_fee', 'is_system' => true,  'sort_order' => 2],
                ['name' => 'Bandwidth Sale', 'slug' => 'bandwidth_sale', 'is_system' => true,  'sort_order' => 3],
                ['name' => 'Product Sale',   'slug' => 'product_sale',   'is_system' => true,  'sort_order' => 4],
                ['name' => 'Sale Return',    'slug' => 'sale_return',    'is_system' => true,  'sort_order' => 5],
                ['name' => 'Other Income',   'slug' => 'other_income',   'is_system' => false, 'sort_order' => 6],
            ];
            foreach ($incomeCategories as $cat) {
                \App\Models\IncomeCategory::create([
                    'name'       => $cat['name'],
                    'slug'       => $cat['slug'],
                    'is_system'  => $cat['is_system'],
                    'is_active'  => true,
                    'sort_order' => $cat['sort_order'],
                ]);
            }

            // ── 5. Expense Categories (সব is_system = true) ───────────
            $expenseCategories = [
                ['name' => 'Bandwidth Purchase',   'slug' => 'bandwidth_purchase',   'sort_order' => 1],
                ['name' => 'Salary',               'slug' => 'salary',               'sort_order' => 2],
                ['name' => 'Stock Purchase',       'slug' => 'stock_purchase',       'sort_order' => 3],
                ['name' => 'Consumption Expense',  'slug' => 'consumption_expense',  'sort_order' => 4],
                ['name' => 'Inventory Loss',       'slug' => 'inventory_loss',       'sort_order' => 5],
                ['name' => 'Purchase Return',      'slug' => 'purchase_return',      'sort_order' => 6],
                ['name' => 'Office Rent',          'slug' => 'office_rent',          'sort_order' => 7],
                ['name' => 'Electricity Bill',     'slug' => 'electricity_bill',     'sort_order' => 8],
                ['name' => 'Maintenance',          'slug' => 'maintenance',          'sort_order' => 9],
                ['name' => 'Transport',            'slug' => 'transport',            'sort_order' => 10],
                ['name' => 'Marketing',            'slug' => 'marketing',            'sort_order' => 11],
                ['name' => 'Conveyance Allowance', 'slug' => 'conveyance_allowance', 'sort_order' => 12],
                ['name' => 'Agent Commission',     'slug' => 'agent_commission',     'sort_order' => 13],
                ['name' => 'Other Expense',        'slug' => 'other_expense',        'sort_order' => 14],
            ];
            foreach ($expenseCategories as $cat) {
                \App\Models\ExpenseCategory::create([
                    'name'       => $cat['name'],
                    'slug'       => $cat['slug'],
                    'is_active'  => true,
                    'sort_order' => $cat['sort_order'],
                ]);
            }

            // ── 6. Support Categories ─────────────────────────────────
            $supportCategories = [
                'Network Issue', 'Slow Speed', 'Connection Down',
                'Router/ONU Problem', 'Cable Damage', 'IP Conflict',
                'Billing Issue', 'Package Change Request', 'New Connection Request',
                'Relocation Request', 'Device Configuration', 'Other',
            ];
            foreach ($supportCategories as $cat) {
                \App\Models\SupportCategory::create([
                    'name'      => $cat,
                    'is_active' => true,
                ]);
            }

            // ── 7. Bandwidth Services ─────────────────────────────────
            $bandwidthServices = [
                ['name' => 'IIG',     'description' => 'International Internet Gateway'],
                ['name' => 'GGC',     'description' => 'Google Global Cache'],
                ['name' => 'FNA',     'description' => 'Facebook Network Accelerator'],
                ['name' => 'BDIX',    'description' => 'Bangladesh Internet Exchange'],
                ['name' => 'PNI',     'description' => 'Private Network Interconnect'],
                ['name' => 'CDN',     'description' => 'Content Delivery Network'],
                ['name' => 'NTTN',    'description' => 'National Transmission Network'],
                ['name' => 'IX',      'description' => 'Internet Exchange'],
                ['name' => 'Peering', 'description' => 'Peering'],
                ['name' => 'Transit', 'description' => 'Transit'],
            ];
            foreach ($bandwidthServices as $service) {
                \App\Models\BandwidthBuy\BandwidthService::create([
                    'name'        => $service['name'],
                    'description' => $service['description'],
                    'is_active'   => true,
                ]);
            }

            // ── 8. OLT Types ──────────────────────────────────────────
            $oltTypes = [
                'BDCOM_EPON', 'BDCOM_GPON',
                'VSOL_EPON', 'VSOL_EPON_TYPE_2', 'VSOL_GPON',
                'ZTE_EPON', 'ZTE_GPON',
                'Huawei_EPON', 'Huawei_GPON',
                'CDATA_EPON', 'CDATA_GPON',
                'FiberHome_EPON', 'FiberHome_GPON',
                'ATOP_EPON', 'AURORA_EPON',
                'AVEIS_EPON', 'AVEIS_GPON',
                'CoreLink_EPON',
                'DBC_EPON', 'DBC_GPON',
                'ECOM_EPON', 'ECOM_GPON',
                'ITLINK_EPON',
                'PHOTON_EPON', 'PHOTON_GPON',
                'Nokia_GPON',
                'Dasan_EPON', 'Dasan_GPON',
                'Cisco_EPON', 'Cisco_GPON',
                'Alcatel_GPON', 'Ericsson_GPON',
                'Adtran_GPON', 'Calix_GPON',
                'Tellabs_GPON', 'Sumitomo_GPON',
                'Mitsubishi_GPON', 'Furukawa_GBN',
                'UTStarcom_EPON', 'UTStarcom_GPON',
                'Zhone_GPON', 'Ribbon_GPON',
            ];
            foreach ($oltTypes as $type) {
                \App\Models\OltType::create([
                    'name'      => $type,
                    'is_active' => true,
                ]);
            }

            // ── 9. Roles ──────────────────────────────────────────────
            $roles = [
                'Admin', 'Support Manager', 'Support Executive',
                'Accounts Manager', 'Accounts Executive', 'Asst. Manager',
                'Store Manager', 'Billing Man', 'Marketing Manager', 'agent',
            ];
            foreach ($roles as $role) {
                \Spatie\Permission\Models\Role::create([
                    'name'       => $role,
                    'guard_name' => 'web',
                ]);
            }
        });

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
