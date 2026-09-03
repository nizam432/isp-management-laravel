<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Reseller Portal</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body { background:#f4f6f9; font-family:-apple-system,Segoe UI,Roboto,sans-serif; }

        /* ── Sidebar ─────────────────────────── */
        .reseller-sidebar {
            width: 250px; min-height: 100vh; background:#1a1a2e;
            position: fixed; left:0; top:0; padding-top: 0; z-index:1030;
            transition: transform .25s ease;
            overflow-y: auto;
        }
        .reseller-sidebar .brand {
            padding: 18px 20px; border-bottom: 1px solid rgba(255,255,255,.08);
            color:#fff; font-weight:700; font-size:1.05rem; display:flex; align-items:center; gap:10px;
        }
        .reseller-sidebar .nav-link {
            color: rgba(255,255,255,.7); padding: 11px 20px; font-size:.875rem;
            display:flex; align-items:center; gap:10px; transition: all .15s;
            text-decoration: none !important;
        }
        .reseller-sidebar .nav-link:hover, .reseller-sidebar .nav-link.active {
            background: rgba(255,255,255,.08); color:#fff; border-left: 3px solid #28a745;
            text-decoration: none !important;
        }
        .reseller-sidebar .nav-link:focus {
            text-decoration: none !important; outline: none;
        }
        .reseller-sidebar .nav-link i { width:18px; text-align:center; }

        /* ── Dropdown (parent w/ children) ──────── */
        .reseller-sidebar .nav-toggle {
            color: rgba(255,255,255,.7); padding: 11px 20px; font-size:.875rem;
            display:flex; align-items:center; gap:10px; cursor:pointer;
            transition: all .15s; justify-content: space-between;
            text-decoration: none !important;
        }
        .reseller-sidebar .nav-toggle .nav-toggle-left {
            display:flex; align-items:center; gap:10px;
        }
        .reseller-sidebar .nav-toggle:hover,
        .reseller-sidebar .nav-toggle.active,
        .reseller-sidebar .nav-toggle:focus {
            background: rgba(255,255,255,.08); color:#fff;
            text-decoration: none !important; outline: none;
        }
        .reseller-sidebar .nav-toggle i.chevron {
            width:auto; font-size:.7rem; transition: transform .2s;
        }
        .reseller-sidebar .nav-toggle[aria-expanded="true"] i.chevron {
            transform: rotate(90deg);
        }
        .reseller-sidebar .submenu {
            background: rgba(0,0,0,.18);
        }
        .reseller-sidebar .submenu .nav-link {
            padding: 9px 20px 9px 48px;
            font-size: .82rem;
        }
        .reseller-sidebar .submenu .nav-link:hover,
        .reseller-sidebar .submenu .nav-link.active {
            border-left-color: #28a745;
        }

        /* ── Topbar ──────────────────────────── */
        .reseller-content { margin-left: 250px; min-height:100vh; }
        .reseller-topbar {
            background:#fff; padding: 12px 24px; display:flex;
            justify-content:space-between; align-items:center;
            border-bottom: 1px solid #e2e8f0; position:sticky; top:0; z-index:1020;
        }
        .reseller-main { padding: 24px; }

        @media (max-width: 768px) {
            .reseller-sidebar { transform: translateX(-100%); }
            .reseller-sidebar.show { transform: translateX(0); }
            .reseller-content { margin-left: 0; }
        }
    </style>
    @yield('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
</head>
<body>

    <div class="reseller-sidebar" id="resellerSidebar">
        <div class="brand">
            <i class="fas fa-network-wired text-success"></i>
            {{ auth('mac_reseller')->user()->business_name ?? 'Reseller Panel' }}
        </div>
        <nav class="nav flex-column py-2">
            <a class="nav-link {{ request()->routeIs('reseller.dashboard') ? 'active' : '' }}" href="{{ route('reseller.dashboard') }}">
                <i class="fas fa-th-large"></i> Dashboard
            </a>

            @php
                // Every item is rendered as an accordion (chevron + collapsible submenu),
                // matching the reference sidebar's look. Each currently has one child
                // (its existing index page) — add more entries to 'children' later
                // as soon as a menu gets additional sub-pages/routes.
                $menuLinks = [
                    'CONFIGURATION' => [
                        'icon' => 'fa-sliders-h', 'label' => 'Configuration',
                        'children' => [
                            ['route' => 'reseller.configuration.zone.index',    'icon' => 'fa-map-marker-alt', 'label' => 'Zone'],
                            ['route' => 'reseller.configuration.subzone.index', 'icon' => 'fa-map-pin',        'label' => 'Sub Zone'],
                            ['route' => 'reseller.configuration.box.index',         'icon' => 'fa-box',           'label' => 'Box'],
                            ['route' => 'reseller.configuration.package.index',     'icon' => 'fa-boxes',         'label' => 'Package'],
                        ],
                    ],
                    'MIKROTIK CLIENT' => [
                        'icon' => 'fa-server', 'label' => 'Mikrotik Client',
                        'children' => [
                            ['route' => 'reseller.mikrotik-client.index',       'icon' => 'fa-list',          'label' => 'Mikrotik Clients'],
                            ['route' => 'reseller.mikrotik-client.bulk-import', 'icon' => 'fa-file-import',   'label' => 'Bulk Clients Import'],
                        ],
                    ],
                    'EMPLOYEES' => [
                        'icon' => 'fa-user-shield', 'label' => 'Staff Login',
                        'children' => [
                            ['route' => 'reseller.employees.index',  'icon' => 'fa-list',      'label' => 'Staff List'],
                        ],
                    ],
                    'CLIENT' => [
                        'icon' => 'fa-user', 'label' => 'Client',
                        'children' => [
                            ['route' => 'reseller.client.create',         'icon' => 'fa-user-plus',    'label' => 'Add Client'],
                            ['route' => 'reseller.client.index',          'icon' => 'fa-list',         'label' => 'Client List'],
                        ],
                    ],
                    'BILLING' => [
                        'icon' => 'fa-file-invoice-dollar', 'label' => 'Billing',
                        'children' => [
                            ['route' => 'reseller.billing.index',   'icon' => 'fa-list',          'label' => 'Billing List'],
                            ['route' => 'reseller.billing.create',  'icon' => 'fa-file-invoice',  'label' => 'Create Invoice'],
                            ['route' => 'reseller.payment.collect', 'icon' => 'fa-hand-holding-usd','label' => 'Collect Payment'],
                            ['route' => 'reseller.payment.index',   'icon' => 'fa-history',       'label' => 'Payment History'],
                        ],
                    ],
                    'MONITORING' => [
                        'icon' => 'fa-chart-line', 'label' => 'Monitoring',
                        'children' => [
                            ['route' => 'reseller.monitoring.index', 'icon' => 'fa-desktop', 'label' => 'Client Monitoring'],
                        ],
                    ],
                    'CLIENT SUPPORT' => [
                        'icon' => 'fa-headset', 'label' => 'Support & Ticketing',
                        'children' => [
                            ['route' => 'reseller.client-support.category.index', 'icon' => 'fa-tag',      'label' => 'Support Category'],
                            ['route' => 'reseller.client-support.index',    'icon' => 'fa-tablet-alt', 'label' => 'Client Support'],
                            ['route' => 'reseller.client-support.monthly',  'icon' => 'fa-redo',      'label' => 'Support History'],
                        ],
                    ],
                    'SMS SERVICE' => [
                        'icon' => 'fa-sms', 'label' => 'SMS Service',
                        'children' => [
                            ['route' => 'reseller.sms-service.settings.index',  'icon' => 'fa-server',      'label' => 'Gateway Settings'],
                            ['route' => 'reseller.sms-service.templates.index', 'icon' => 'fa-file-alt',    'label' => 'Templates'],
                            ['route' => 'reseller.sms-service.send',           'icon' => 'fa-paper-plane', 'label' => 'Send SMS'],
                            ['route' => 'reseller.sms-service.reports',        'icon' => 'fa-chart-bar',   'label' => 'SMS Reports'],
                        ],
                    ],
                    'REPORT' => [
                        'icon' => 'fa-chart-bar', 'label' => 'Report',
                        'children' => [
                            ['route' => 'reseller.report.btrc',            'icon' => 'fa-file-alt',   'label' => 'BTRC Report'],
                            ['route' => 'reseller.report.status-history',  'icon' => 'fa-history',    'label' => 'Enable/Disable History'],
                            ['route' => 'reseller.report.bill-collection', 'icon' => 'fa-hand-holding-usd', 'label' => 'Bill Collection'],
                            ['route' => 'reseller.report.messages',        'icon' => 'fa-comment-dots','label' => 'Messages Report'],
                            ['route' => 'reseller.report.index',           'icon' => 'fa-list',       'label' => 'All Report'],
                        ],
                    ],
                    'FUND HISTORY' => [
                        'icon' => 'fa-wallet', 'label' => 'Fund History',
                        'children' => [
                            ['route' => 'reseller.fund-history.debit',  'icon' => 'fa-arrow-down', 'label' => 'Debit History'],
                            ['route' => 'reseller.fund-history.credit', 'icon' => 'fa-arrow-up',   'label' => 'Credit History'],
                        ],
                    ],
                    'TUTORIALS' => [
                        'icon' => 'fa-book', 'label' => 'Tutorials',
                        'children' => [
                            ['route' => 'reseller.tutorials.index', 'icon' => 'fa-video', 'label' => 'All Tutorials'],
                        ],
                    ],
                ];
                $allowedUpper = [];
                $isStaffSession = session('reseller_employee_id');

                if ($isStaffSession) {
                    // logged in as a staff (sub-employee) — use THEIR OWN allowed_menus,
                    // not the reseller owner's
                    $staffEmployee = \App\Models\ResellerEmployee::find($isStaffSession);
                    $allowed = $staffEmployee->allowed_menus ?? [];
                } else {
                    // logged in as the reseller owner — use the owner's allowed_menus
                    // (set by ISP Admin for this reseller/POP)
                    $allowed = auth('mac_reseller')->user()->allowed_menus ?? [];
                }

                $allowedUpper = array_map('strtoupper', $allowed);

                // split so HR & Payroll can be rendered between Client Support and SMS Service
                $menuLinksTop    = array_slice($menuLinks, 0, 7, true);  // CONFIGURATION .. CLIENT SUPPORT
                $menuLinksBottom = array_slice($menuLinks, 7, null, true); // SMS SERVICE .. TUTORIALS
            @endphp

            @foreach($menuLinksTop as $key => $item)
                @if(in_array($key, $allowedUpper))

                    @if(!empty($item['children']))
                        @php
                            $isParentActive = collect($item['children'])->contains(function ($child) {
                                return request()->routeIs(str_replace('.index', '.*', $child['route']))
                                    || request()->routeIs($child['route'] . '*');
                            });
                            $collapseId = 'submenu-' . \Illuminate\Support\Str::slug($key);
                        @endphp
                        <a href="#" class="nav-toggle {{ $isParentActive ? 'active' : '' }}"
                           data-toggle="collapse" data-target="#{{ $collapseId }}"
                           aria-expanded="{{ $isParentActive ? 'true' : 'false' }}">
                            <span class="nav-toggle-left">
                                <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                            </span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="collapse submenu {{ $isParentActive ? 'show' : '' }}" id="{{ $collapseId }}">
                            @foreach($item['children'] as $child)
                                <a class="nav-link {{ request()->routeIs(str_replace('.index', '.*', $child['route'])) || request()->routeIs($child['route'] . '*') ? 'active' : '' }}"
                                   href="{{ route($child['route']) }}">
                                    <i class="fas {{ $child['icon'] }}"></i> {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <a class="nav-link {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                            <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                        </a>
                    @endif

                @endif
            @endforeach

            @unless($isStaffSession)
            {{-- HR & Payroll — owner only, never assignable to staff — placed before SMS Service --}}
            <a href="#" class="nav-toggle {{ request()->routeIs('reseller.hr.*') ? 'active' : '' }}"
               data-toggle="collapse" data-target="#hrPayrollSubmenu"
               aria-expanded="{{ request()->routeIs('reseller.hr.*') ? 'true' : 'false' }}">
                <span class="nav-toggle-left"><i class="fas fa-user-tie"></i> HR & Payroll</span>
                <i class="fas fa-chevron-right chevron"></i>
            </a>
            <div class="collapse submenu {{ request()->routeIs('reseller.hr.*') ? 'show' : '' }}" id="hrPayrollSubmenu">
                <a class="nav-link {{ request()->routeIs('reseller.hr.department.*') ? 'active' : '' }}" href="{{ route('reseller.hr.department.index') }}">
                    <i class="fas fa-building"></i> Departments
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.position.*') ? 'active' : '' }}" href="{{ route('reseller.hr.position.index') }}">
                    <i class="fas fa-briefcase"></i> Positions
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.salary-head.*') ? 'active' : '' }}" href="{{ route('reseller.hr.salary-head.index') }}">
                    <i class="fas fa-list"></i> Salary Heads
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.employee.index') ? 'active' : '' }}" href="{{ route('reseller.hr.employee.index') }}">
                    <i class="fas fa-users"></i> Employee List
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.employee.create') ? 'active' : '' }}" href="{{ route('reseller.hr.employee.create') }}">
                    <i class="fas fa-user-plus"></i> Add Employee
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.payroll.index') ? 'active' : '' }}" href="{{ route('reseller.hr.payroll.index') }}">
                    <i class="fas fa-money-check-alt"></i> Payroll
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.payroll.create') ? 'active' : '' }}" href="{{ route('reseller.hr.payroll.create') }}">
                    <i class="fas fa-plus-circle"></i> Generate Payroll
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.salary-advance.*') ? 'active' : '' }}" href="{{ route('reseller.hr.salary-advance.index') }}">
                    <i class="fas fa-hand-holding-usd"></i> Salary Advance
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.leave-type.*') ? 'active' : '' }}" href="{{ route('reseller.hr.leave-type.index') }}">
                    <i class="fas fa-calendar-alt"></i> Leave Types
                </a>
                <a class="nav-link {{ request()->routeIs('reseller.hr.leave-application.*') ? 'active' : '' }}" href="{{ route('reseller.hr.leave-application.index') }}">
                    <i class="fas fa-calendar-check"></i> Leave Applications
                </a>
            </div>
            @endunless

            @foreach($menuLinksBottom as $key => $item)
                @if(in_array($key, $allowedUpper))

                    @if(!empty($item['children']))
                        @php
                            $isParentActive = collect($item['children'])->contains(function ($child) {
                                return request()->routeIs(str_replace('.index', '.*', $child['route']))
                                    || request()->routeIs($child['route'] . '*');
                            });
                            $collapseId = 'submenu-' . \Illuminate\Support\Str::slug($key);
                        @endphp
                        <a href="#" class="nav-toggle {{ $isParentActive ? 'active' : '' }}"
                           data-toggle="collapse" data-target="#{{ $collapseId }}"
                           aria-expanded="{{ $isParentActive ? 'true' : 'false' }}">
                            <span class="nav-toggle-left">
                                <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                            </span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="collapse submenu {{ $isParentActive ? 'show' : '' }}" id="{{ $collapseId }}">
                            @foreach($item['children'] as $child)
                                <a class="nav-link {{ request()->routeIs(str_replace('.index', '.*', $child['route'])) || request()->routeIs($child['route'] . '*') ? 'active' : '' }}"
                                   href="{{ route($child['route']) }}">
                                    <i class="fas {{ $child['icon'] }}"></i> {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <a class="nav-link {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                            <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                        </a>
                    @endif

                @endif
            @endforeach

            @unless($isStaffSession)
            {{-- Settings — owner only, never assignable to staff --}}
            <a class="nav-link {{ request()->routeIs('reseller.settings.*') ? 'active' : '' }}" href="{{ route('reseller.settings.index') }}">
                <i class="fas fa-cog"></i> Settings
            </a>
            @endunless
        </nav>
    </div>

    <div class="reseller-content">
        <div class="reseller-topbar">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('resellerSidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="m-0 d-none d-md-block">@yield('title', 'Dashboard')</h5>
            <div class="d-flex align-items-center" style="gap:8px;">
                @if(session('impersonator_admin_id'))
                <a href="{{ route('reseller.back-to-admin') }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Admin
                </a>
                @endif
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> {{ auth('mac_reseller')->user()->contact_person ?? 'Reseller' }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('reseller.configuration.index') }}" class="dropdown-item">
                            <i class="fas fa-user-cog mr-1"></i> Account Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        @if(session('impersonator_admin_id'))
                        <a href="{{ route('reseller.back-to-admin') }}" class="dropdown-item">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Admin
                        </a>
                        <div class="dropdown-divider"></div>
                        @endif
                        <form action="{{ route('reseller.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="reseller-main">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    @yield('js')
</body>
</html>