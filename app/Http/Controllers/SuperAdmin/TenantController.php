<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\DatabasePool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
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
            'total_plans'     => Plan::count(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

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
            default                  => [null, null],
        };
    }

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
        $plans            = Plan::active()->get();
        $masterResellers  = Tenant::where('is_reseller', 2)->where('is_active', true)->get();
        $availablePools   = DatabasePool::available()->orderBy('database_name')->get();

        return view('super-admin.tenants.create', compact('plans', 'masterResellers', 'availablePools'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'company_name'     => 'required|string|max:100',
            'email'            => 'required|email|unique:tenants,email',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'plan_id'          => 'required|exists:plans,id',
            'is_reseller'      => 'required|in:1,2,3',
            'parent_id'        => 'required_if:is_reseller,3',
            'password'         => 'required|string|min:6',
            'subdomain'        => 'required|string|max:50|unique:domains,domain|alpha_dash',
            'database_pool_id' => 'required|exists:database_pool,id',
        ]);

        if ($request->is_reseller == 3 && !$request->parent_id) {
            return back()->with('error', 'Sub Reseller এর জন্য Parent ISP select করুন।');
        }

        $pool = DB::transaction(function () use ($request) {
            $row = DatabasePool::where('id', $request->database_pool_id)
                ->where('is_used', false)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                return null;
            }

            $row->update(['is_used' => true]);
            return $row;
        });

        if (!$pool) {
            return back()->with('error', 'এই ডেটাবেজটি ইতিমধ্যে অন্য কোনো ISP-কে assign করা হয়ে গেছে। আবার চেষ্টা করুন।');
        }

        $plan = Plan::findOrFail($request->plan_id);

        $tenant = Tenant::create([
            'id'               => Str::slug($request->subdomain),
            'name'             => $request->company_name,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'address'          => $request->address,
            'plan_id'          => $request->plan_id,
            'is_reseller'      => $request->is_reseller,
            'parent_id'        => $request->parent_id ?? 0,
            'is_active'        => true,
            'plan_expires_at'  => $plan->price == 0
                ? now()->addDays($plan->trial_days ?: 30)
                : now()->addMonth(),
            'tenancy_db_name'  => $pool->database_name,
        ]);

        $pool->update(['tenant_id' => $tenant->id]);

        $tenant->domains()->create([
            'domain' => $request->subdomain . '.' . env('APP_DOMAIN', 'innovativeitbd.com'),
        ]);

        config(['database.connections.pool.database' => $pool->database_name]);
        DB::purge('pool');
        DB::setDefaultConnection('pool');

        $user = User::create([
            'name'     => $request->company_name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        \Spatie\Permission\Models\Role::firstOrCreate([
            'name'       => 'isp-admin',
            'guard_name' => 'web',
        ]);

        $user->assignRole('isp-admin');

        // ── Auto-seed default data for the new ISP ──────────────────

        // 1. Protocol Types
        try {
            foreach (['PPPoE', 'Hotspot', 'OVPN', 'PPTP', 'Static IP'] as $name) {
                \App\Models\ProtocolType::firstOrCreate(['name' => $name], ['is_active' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (protocol types): " . $e->getMessage());
        }

        // 2. Client Types
        try {
            foreach ([
                'Home', 'Corporate', 'SME (Small & Medium Enterprise)', 'Student',
                'Government', 'NGO', 'Hospital / Clinic', 'School / College',
                'Hotel / Restaurant', 'Shop / Retail', 'Other',
            ] as $name) {
                \App\Models\ClientType::firstOrCreate(['name' => $name], ['is_active' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (client types): " . $e->getMessage());
        }

        // 3. Connection Types
        try {
            foreach ([
                'Fiber Optic', 'Cable', 'Wireless', 'Radio Link', 'VSAT',
                '4G/LTE', 'ADSL', 'VDSL', 'Leased Line', 'Point to Point',
            ] as $name) {
                \App\Models\ConnectionType::firstOrCreate(['name' => $name], ['is_active' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (connection types): " . $e->getMessage());
        }

        // 4. Income Categories
        try {
            $now = now();
            $incomeCategories = [
                ['name' => 'Monthly Bill',   'slug' => 'monthly-bill',   'color' => '#0F6E56', 'icon' => 'fas fa-file-invoice-dollar', 'description' => 'Auto-generated from customer monthly bill payments.',    'is_system' => 1, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Connection Fee', 'slug' => 'connection-fee', 'color' => '#185FA5', 'icon' => 'fas fa-plug',                'description' => 'One-time fee charged for a new customer connection.',   'is_system' => 1, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Bandwidth Sale', 'slug' => 'bandwidth-sale', 'color' => '#534AB7', 'icon' => 'fas fa-wifi',                'description' => 'Income generated from bandwidth package sales.',        'is_system' => 1, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Product Sale',   'slug' => 'product-sale',   'color' => '#F59E0B', 'icon' => 'fas fa-box',                 'description' => 'Income generated from inventory product sales.',        'is_system' => 1, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Sale Return',    'slug' => 'sale-return',    'color' => '#DC3545', 'icon' => 'fas fa-undo-alt',            'description' => 'Adjustment for returned products sold from inventory.', 'is_system' => 1, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'MAC Reseller Funding', 'slug' => 'mac-reseller-funding', 'color' => '#6610F2', 'icon' => 'fas fa-sitemap', 'description' => 'Funds received from MAC Resellers (wallet recharge).', 'is_system' => 1, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ];
            foreach ($incomeCategories as $cat) {
                $exists = \App\Models\IncomeCategory::where('name', $cat['name'])
                    ->orWhere('slug', $cat['slug'])
                    ->exists();
                if (!$exists) {
                    \App\Models\IncomeCategory::create([
                        'name'       => $cat['name'],
                        'slug'       => $cat['slug'],
                        'icon'       => $cat['icon'],
                        'description'=> $cat['description'],
                        'is_system'  => $cat['is_system'],
                        'color'      => $cat['color'],
                        'is_active'  => true,
                        'sort_order' => $cat['sort_order'],
                        'created_at' => $cat['created_at'],
                        'updated_at' => $cat['updated_at'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (income categories): " . $e->getMessage());
        }

        // 5. Expense Categories
        try {
            $now = now();

                $expenseCategories = [
                    ['name' => 'Bandwidth Purchase',   'slug' => 'bandwidth-purchase',  'color' => '#185FA5', 'icon' => 'fas fa-wifi',              'description' => 'Upstream bandwidth purchase cost.',             'is_system' => 1, 'sort_order' => 1,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Salary',               'slug' => 'salary',              'color' => '#854F0B', 'icon' => 'fas fa-users',             'description' => 'Staff salary, bonus and overtime.',             'is_system' => 1, 'sort_order' => 2,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Stock Purchase',       'slug' => 'stock-purchase',      'color' => '#0F6E56', 'icon' => 'fas fa-shopping-cart',     'description' => 'Inventory product purchase cost.',              'is_system' => 1, 'sort_order' => 3,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Consumption Expense',  'slug' => 'consumption-expense', 'color' => '#3C3489', 'icon' => 'fas fa-box-open',          'description' => 'Inventory items consumed internally.',          'is_system' => 1, 'sort_order' => 4,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Inventory Loss',       'slug' => 'inventory-loss',      'color' => '#DC3545', 'icon' => 'fas fa-exclamation-triangle', 'description' => 'Lost, damaged or expired inventory items.',  'is_system' => 1, 'sort_order' => 5,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Purchase Return',      'slug' => 'purchase-return',     'color' => '#F59E0B', 'icon' => 'fas fa-reply',             'description' => 'Returned purchased inventory items.',           'is_system' => 1, 'sort_order' => 6,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Office Rent',          'slug' => 'office-rent',         'color' => '#6F42C1', 'icon' => 'fas fa-building',          'description' => 'Office, warehouse and tower rent.',             'is_system' => 1, 'sort_order' => 7,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Electricity Bill',     'slug' => 'electricity-bill',    'color' => '#FFC107', 'icon' => 'fas fa-bolt',              'description' => 'Electricity expenses.',                         'is_system' => 1, 'sort_order' => 8,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Maintenance',          'slug' => 'maintenance',         'color' => '#20C997', 'icon' => 'fas fa-tools',             'description' => 'Equipment and office maintenance costs.',       'is_system' => 1, 'sort_order' => 9,  'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Transport',            'slug' => 'transport',           'color' => '#FD7E14', 'icon' => 'fas fa-car',               'description' => 'Fuel and transportation expenses.',             'is_system' => 1, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Marketing',            'slug' => 'marketing',           'color' => '#E83E8C', 'icon' => 'fas fa-bullhorn',          'description' => 'Advertising and promotional expenses.',         'is_system' => 1, 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Conveyance Allowance', 'slug' => 'conveyance',          'color' => '#17A2B8', 'icon' => 'fas fa-briefcase',         'description' => 'Employee conveyance allowance.',                'is_system' => 1, 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Agent Commission',     'slug' => 'agent-commission',    'color' => '#6610F2', 'icon' => 'fas fa-user-check',        'description' => 'Commission paid to sales agents or resellers.', 'is_system' => 1, 'sort_order' => 13, 'created_at' => $now, 'updated_at' => $now],
                    ['name' => 'Other Expense',        'slug' => 'other-expense',       'color' => '#6C757D', 'icon' => 'fas fa-ellipsis-h',        'description' => 'Miscellaneous expenses not covered above.',     'is_system' => 1, 'sort_order' => 14, 'created_at' => $now, 'updated_at' => $now],
                    //['name' => 'MAC Reseller Settlement Payout', 'slug' => 'mac-reseller-settlement', 'color' => '#0DCAF0', 'icon' => 'fas fa-hand-holding-usd', 'description' => 'Payout to MAC Resellers for their end-customer PGW collections.', 'is_system' => 1, 'sort_order' => 15, 'created_at' => $now, 'updated_at' => $now],
                ];
            foreach ($expenseCategories as $cat_e) {
                $exists = \App\Models\ExpenseCategory::where('name', $cat_e['name'])
                    ->orWhere('slug', $cat_e['slug'])
                    ->exists();
                if (!$exists) {
                    \App\Models\ExpenseCategory::create([
                        'name'       => $cat_e['name'],
                        'slug'       => $cat_e['slug'],
                        'icon'       => $cat_e['icon'],
                        'is_system'  => $cat_e['is_system'],
                        'color'      => $cat_e['color'],
                        'description'=> $cat_e['description'],
                        'created_at' => $cat_e['created_at'],
                        'updated_at' => $cat_e['updated_at'],
                        'is_active'  => true,
                        'sort_order' => $cat_e['sort_order'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (expense categories): " . $e->getMessage());
        }

        // 6. Support Categories
        try {
            foreach ([
                'Network Issue', 'Slow Speed', 'Connection Down', 'Router/ONU Problem',
                'Cable Damage', 'IP Conflict', 'Billing Issue', 'Package Change Request',
                'New Connection Request', 'Relocation Request', 'Device Configuration', 'Other',
            ] as $name) {
                \App\Models\SupportCategory::firstOrCreate(['name' => $name]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (support categories): " . $e->getMessage());
        }

        // 7. Bandwidth Services
        try {
            foreach (['IIG', 'GGC', 'FNA', 'BDIX', 'PNI', 'CDN', 'NTTN', 'IX', 'Peering', 'Transit'] as $name) {
                \App\Models\BandwidthBuy\BandwidthService::firstOrCreate(['name' => $name]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (bandwidth services): " . $e->getMessage());
        }

        // 8. OLT Types
        try {
            foreach ([
                'BDCOM_EPON', 'BDCOM_GPON', 'VSOL_EPON', 'VSOL_EPON_TYPE_2', 'VSOL_GPON',
                'ZTE_EPON', 'ZTE_GPON', 'Huawei_EPON', 'Huawei_GPON', 'CDATA_EPON', 'CDATA_GPON',
                'FiberHome_EPON', 'FiberHome_GPON', 'ATOP_EPON', 'AURORA_EPON', 'AVEIS_EPON',
                'AVEIS_GPON', 'CoreLink_EPON', 'DBC_EPON', 'DBC_GPON', 'ECOM_EPON', 'ECOM_GPON',
                'ITLINK_EPON', 'PHOTON_EPON', 'PHOTON_GPON', 'Nokia_GPON', 'Dasan_EPON', 'Dasan_GPON',
                'Cisco_EPON', 'Cisco_GPON', 'Alcatel_GPON', 'Ericsson_GPON', 'Adtran_GPON',
                'Calix_GPON', 'Tellabs_GPON', 'Sumitomo_GPON', 'Mitsubishi_GPON', 'Furukawa_GBN',
                'UTStarcom_EPON', 'UTStarcom_GPON', 'Zhone_GPON', 'Ribbon_GPON',
            ] as $name) {
                \App\Models\Settings\OltType::firstOrCreate(['name' => $name]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (OLT types): " . $e->getMessage());
        }

        // 9. Roles
        try {
            foreach ([
                'Support Manager', 'Support Executive', 'Accounts Manager', 'Accounts Executive',
                'Asst. Manager', 'Store Manager', 'Billing Man', 'Marketing Manager', 'agent',
            ] as $roleName) {
                \Spatie\Permission\Models\Role::firstOrCreate([
                    'name'       => $roleName,
                    'guard_name' => 'web',
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (staff roles): " . $e->getMessage());
        }

        // 10. HR — Departments + Positions
        try {
            $departments = [
                ['name' => 'Management', 'positions' => ['Managing Director', 'General Manager']],
                ['name' => 'Human Resources (HR)', 'positions' => ['HR Manager', 'HR Executive']],
                ['name' => 'Accounts & Billing', 'positions' => ['Accounts Executive', 'Billing Officer', 'Cashier']],
                ['name' => 'Customer Support', 'positions' => ['Customer Care Executive', 'Support Executive']],
                ['name' => 'Technical Support', 'positions' => ['Technical Support Engineer', 'Junior Support Engineer']],
                ['name' => 'Field Support', 'positions' => ['Field Technician', 'Installation Technician', 'Fiber Technician']],
                ['name' => 'Network Operations Center (NOC)', 'positions' => ['Network Engineer', 'NOC Engineer', 'Senior Network Engineer']],
                ['name' => 'Sales & Marketing', 'positions' => ['Sales Executive', 'Marketing Executive']],
                ['name' => 'Store & Inventory', 'positions' => ['Store Keeper', 'Inventory Officer']],
            ];

            foreach ($departments as $dept) {
                $department = \App\Models\HR\Department::firstOrCreate(['name' => $dept['name']]);

                foreach ($dept['positions'] as $positionName) {
                    \App\Models\HR\Position::firstOrCreate([
                        'name'          => $positionName,
                        'department_id' => $department->id,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (HR departments/positions): " . $e->getMessage());
        }

        // 11. HR — Salary Heads
        // NOTE: verify \App\Models\HR\SalaryHead is the correct model namespace.
        try {
            $salaryHeadItems = [
                ['name' => 'Basic Salary',         'type' => 'addition',  'is_active' => 1],
                ['name' => 'House Rent Allowance', 'type' => 'addition',  'is_active' => 1],
                ['name' => 'Medical Allowance',    'type' => 'addition',  'is_active' => 1],
                ['name' => 'Mobile Allowance',     'type' => 'addition',  'is_active' => 1],
                ['name' => 'Conveyance Allowance', 'type' => 'addition',  'is_active' => 1],
                ['name' => 'Overtime',             'type' => 'addition',  'is_active' => 1],
                ['name' => 'Festival Bonus',       'type' => 'addition',  'is_active' => 1],
                ['name' => 'Performance Bonus',    'type' => 'addition',  'is_active' => 1],
                ['name' => 'Commission',           'type' => 'addition',  'is_active' => 1],
                ['name' => 'Other Addition',       'type' => 'addition',  'is_active' => 1],
                ['name' => 'Income Tax',           'type' => 'deduction', 'is_active' => 1],
                ['name' => 'Provident Fund',       'type' => 'deduction', 'is_active' => 1],
                ['name' => 'Advance Salary',       'type' => 'deduction', 'is_active' => 1],
                ['name' => 'Late Deduction',       'type' => 'deduction', 'is_active' => 1],
                ['name' => 'Absent Deduction',     'type' => 'deduction', 'is_active' => 1],
                ['name' => 'Penalty',              'type' => 'deduction', 'is_active' => 1],
                ['name' => 'Other Deduction',      'type' => 'deduction', 'is_active' => 1],
            ];

            foreach ($salaryHeadItems as $head) {
                \App\Models\HR\SalaryHead::firstOrCreate(
                    ['name' => $head['name']],
                    ['type' => $head['type'], 'is_active' => $head['is_active']]
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (salary heads): " . $e->getMessage());
        }

        // 12. HR — Leave Types
        // NOTE: verify \App\Models\HR\LeaveType is the correct model namespace.
        try {
            $now = now();
            $leaveTypes = [
                ['name' => 'Casual Leave',    'days_per_year' => 10,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Sick Leave',      'days_per_year' => 14,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Annual Leave',    'days_per_year' => 20,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Maternity Leave', 'days_per_year' => 180, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Paternity Leave', 'days_per_year' => 10,  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Unpaid Leave',    'days_per_year' => 365, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Emergency Leave', 'days_per_year' => 5,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ];

            foreach ($leaveTypes as $type) {
                \App\Models\HR\LeaveType::firstOrCreate(
                    ['name' => $type['name']],
                    [
                        'days_per_year' => $type['days_per_year'],
                        'is_active'     => $type['is_active'],
                        'created_at'    => $type['created_at'],
                        'updated_at'    => $type['updated_at'],
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant {$tenant->id} seed (leave types): " . $e->getMessage());
        }

        DB::setDefaultConnection(config('tenancy.database.central_connection', 'mysql'));

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

    public function toggle(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['is_active' => !$tenant->is_active]);

        $status = $tenant->is_active ? 'চালু' : 'বন্ধ';
        return back()->with('success', "{$tenant->name} {$status} করা হয়েছে।");
    }

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