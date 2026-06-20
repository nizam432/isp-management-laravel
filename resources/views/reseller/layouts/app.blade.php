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
        }
        .reseller-sidebar .brand {
            padding: 18px 20px; border-bottom: 1px solid rgba(255,255,255,.08);
            color:#fff; font-weight:700; font-size:1.05rem; display:flex; align-items:center; gap:10px;
        }
        .reseller-sidebar .nav-link {
            color: rgba(255,255,255,.7); padding: 11px 20px; font-size:.875rem;
            display:flex; align-items:center; gap:10px; transition: all .15s;
        }
        .reseller-sidebar .nav-link:hover, .reseller-sidebar .nav-link.active {
            background: rgba(255,255,255,.08); color:#fff; border-left: 3px solid #28a745;
        }
        .reseller-sidebar .nav-link i { width:18px; text-align:center; }

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
                $menuLinks = [
                    'CONFIGURATION'   => ['route' => 'reseller.configuration',   'icon' => 'fa-sliders-h', 'label' => 'Configuration'],
                    'MIKROTIK CLIENT' => ['route' => 'reseller.mikrotik-client', 'icon' => 'fa-server',     'label' => 'Mikrotik Client'],
                    'EMPLOYEES'       => ['route' => 'reseller.employees',       'icon' => 'fa-users',      'label' => 'Employees'],
                    'CLIENT'          => ['route' => 'reseller.client',          'icon' => 'fa-user',       'label' => 'Client'],
                    'BILLING'         => ['route' => 'reseller.billing',         'icon' => 'fa-file-invoice-dollar', 'label' => 'Billing'],
                    'MONITORING'      => ['route' => 'reseller.monitoring',      'icon' => 'fa-chart-line', 'label' => 'Monitoring'],
                    'CLIENT SUPPORT'  => ['route' => 'reseller.client-support',  'icon' => 'fa-headset',    'label' => 'Client Support'],
                    'SMS SERVICE'     => ['route' => 'reseller.sms-service',     'icon' => 'fa-sms',        'label' => 'SMS Service'],
                    'REPORT'          => ['route' => 'reseller.report',          'icon' => 'fa-chart-bar',  'label' => 'Report'],
                    'FUND HISTORY'    => ['route' => 'reseller.fund-history',    'icon' => 'fa-wallet',     'label' => 'Fund History'],
                    'TUTORIALS'       => ['route' => 'reseller.tutorials',       'icon' => 'fa-book',       'label' => 'Tutorials'],
                ];
                $allowed = auth('mac_reseller')->user()->allowed_menus ?? [];
                $allowedUpper = array_map('strtoupper', $allowed);
            @endphp

            @foreach($menuLinks as $key => $item)
                @if(in_array($key, $allowedUpper))
                <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                    <i class="fas {{ $item['icon'] }}"></i> {{ $item['label'] }}
                </a>
                @endif
            @endforeach
        </nav>
    </div>

    <div class="reseller-content">
        <div class="reseller-topbar">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('resellerSidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="m-0 d-none d-md-block">@yield('title', 'Dashboard')</h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-user-circle"></i> {{ auth('mac_reseller')->user()->contact_person ?? 'Reseller' }}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <form action="{{ route('reseller.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
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
    @yield('js')
</body>
</html>
