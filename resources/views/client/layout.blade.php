{{-- resources/views/client/layout.blade.php --}}
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — {{ \App\Models\Setting::get('company_name', 'SmartISP') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f6f9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Topbar ── */
        .topbar {
            background: #1a1f36;
            color: #fff;
            height: 56px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px;
            position: fixed; top: 0; left: 0; right: 0; z-index: 200;
        }
        .topbar-brand {
            display: flex; align-items: center; gap: 10px;
            font-size: 18px; font-weight: 700; color: #fff; text-decoration: none;
        }
        .topbar-brand .logo-box {
            width: 36px; height: 36px; background: #00c897;
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 800; color: #fff;
        }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .user-pill {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.1); border-radius: 999px;
            padding: 5px 14px 5px 5px; cursor: pointer;
        }
        .user-avatar {
            width: 30px; height: 30px; background: #00c897;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
        }
        .user-pill span { font-size: 13px; color: #fff; }
        .hamburger { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; display: none; }

        /* ── Wrapper ── */
        .main-wrapper { display: flex; margin-top: 56px; min-height: calc(100vh - 56px); }

        /* ── Sidebar ── */
        .sidebar {
            width: 200px; background: #fff; border-right: 1px solid #e8eaf0;
            position: fixed; top: 56px; left: 0; bottom: 0;
            overflow-y: auto; z-index: 100;
            transition: transform .25s;
        }
        .sidebar-nav { padding: 12px 0; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 20px; font-size: 13.5px; color: #555;
            text-decoration: none; border-left: 3px solid transparent;
            transition: all .15s;
        }
        .nav-item i { width: 18px; text-align: center; font-size: 15px; color: #aaa; }
        .nav-item:hover { background: #f0faf7; color: #00c897; }
        .nav-item:hover i { color: #00c897; }
        .nav-item.active { background: #f0faf7; color: #00c897; border-left-color: #00c897; font-weight: 600; }
        .nav-item.active i { color: #00c897; }
        .nav-divider { height: 1px; background: #f0f0f0; margin: 8px 0; }

        /* ── Page ── */
        .page-content { margin-left: 200px; padding: 24px; flex: 1; }
        .page-title { font-size: 22px; font-weight: 600; color: #1a1f36; margin-bottom: 20px; }

        /* ── Alert ── */
        .alert { padding: 14px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 10px; }
        .alert-danger  { background: #fff0f0; border: 1px solid #ffd0d0; color: #c0392b; border-left: 4px solid #e74c3c; }
        .alert-warning { background: #fffbf0; border: 1px solid #ffe0a0; color: #b8860b; border-left: 4px solid #f39c12; }
        .alert-success { background: #f0fff8; border: 1px solid #b0e8d0; color: #1a7a50; border-left: 4px solid #00c897; }
        .alert i { margin-top: 1px; flex-shrink: 0; }
        .alert .btn-paynow {
            margin-left: auto; background: #e74c3c; color: #fff;
            border: none; border-radius: 6px; padding: 5px 14px;
            font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap;
            text-decoration: none;
        }

        /* ── Stat Cards ── */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: #fff; border-radius: 12px; padding: 18px 20px;
            border: 1px solid #eef0f5; display: flex; align-items: center; gap: 16px;
        }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .stat-icon.teal   { background: #e0faf3; color: #00c897; font-size: 20px; }
        .stat-icon.blue   { background: #e0eeff; color: #3a7bd5; font-size: 20px; }
        .stat-icon.green  { background: #e8fbe8; color: #27ae60; font-size: 20px; }
        .stat-icon.orange { background: #fff3e0; color: #e67e22; font-size: 20px; }
        .stat-info .stat-value { font-size: 22px; font-weight: 700; color: #1a1f36; }
        .stat-info .stat-label { font-size: 12px; color: #888; margin-top: 2px; }

        /* ── Cards ── */
        .card { background: #fff; border-radius: 12px; border: 1px solid #eef0f5; margin-bottom: 20px; overflow: hidden; }
        .card-header {
            padding: 14px 20px; border-bottom: 1px solid #f0f2f7;
            font-size: 13px; font-weight: 600; color: #1a1f36;
            display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: .5px;
        }
        .card-header i { color: #00c897; }
        .card-body { padding: 20px; }

        /* ── Customer Info Card ── */
        .customer-card { display: flex; gap: 20px; flex-wrap: wrap; }
        .customer-avatar {
            width: 70px; height: 70px; background: #1a1f36;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 700; color: #00c897; flex-shrink: 0;
        }
        .customer-info { flex: 1; min-width: 200px; }
        .customer-id { font-size: 18px; font-weight: 700; color: #1a1f36; margin-bottom: 4px; }
        .online-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 4px; }
        .online-dot.online  { background: #00c897; }
        .online-dot.offline { background: #e74c3c; }
        .info-table { width: 100%; font-size: 13px; margin-top: 10px; }
        .info-table td { padding: 4px 0; }
        .info-table td:first-child { color: #888; width: 110px; font-weight: 500; }
        .info-table td:last-child { color: #333; }
        .expire-urgent { color: #e74c3c; font-weight: 600; }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }
        table.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.data-table th { background: #f8f9fc; padding: 10px 14px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eef0f5; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
        table.data-table td { padding: 11px 14px; border-bottom: 1px solid #f4f6f9; color: #444; vertical-align: middle; }
        table.data-table tr:last-child td { border-bottom: none; }
        table.data-table tr:hover td { background: #fafbfd; }

        /* ── Badge ── */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .badge-success  { background: #00c897; color: #fff; }
        .badge-danger   { background: #e74c3c; color: #fff; }
        .badge-warning  { background: #f39c12; color: #fff; }
        .badge-info     { background: #3a7bd5; color: #fff; }
        .badge-secondary{ background: #95a5a6; color: #fff; }

        /* ── Buttons ── */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; text-decoration: none; transition: all .15s; }
        .btn-primary { background: #00c897; color: #fff; }
        .btn-primary:hover { background: #00b386; }
        .btn-danger  { background: #e74c3c; color: #fff; }
        .btn-danger:hover  { background: #c0392b; }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        .btn-outline { background: transparent; border: 1px solid #ddd; color: #555; }
        .btn-outline:hover { background: #f5f5f5; }

        /* ── Form ── */
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px; }
        .form-control { width: 100%; padding: 9px 12px; border: 1.5px solid #e0e4ef; border-radius: 8px; font-size: 13px; color: #333; outline: none; transition: border-color .2s; }
        .form-control:focus { border-color: #00c897; }
        .form-control.is-invalid { border-color: #e74c3c; }
        .invalid-feedback { font-size: 11px; color: #e74c3c; margin-top: 4px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        /* ── Pagination ── */
        .pagination { display: flex; gap: 4px; flex-wrap: wrap; padding: 12px 20px; border-top: 1px solid #f0f2f7; }
        .pagination a, .pagination span { padding: 5px 10px; border-radius: 6px; font-size: 12px; border: 1px solid #e0e4ef; color: #555; text-decoration: none; }
        .pagination .active span { background: #00c897; color: #fff; border-color: #00c897; }

        /* ── Footer ── */
        .footer { text-align: center; padding: 16px; font-size: 12px; color: #aaa; border-top: 1px solid #eef0f5; margin-top: auto; margin-left: 200px; background: #fff; }
        .footer a { color: #00c897; text-decoration: none; }

        /* ── Mobile ── */
        /* ── User Dropdown ── */
        .user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,.15);
            min-width: 220px;
            z-index: 999;
            overflow: hidden;
            border: 1px solid #eef0f5;
        }
        .user-dropdown.open { display: block; }
        .dropdown-header {
            display: flex; align-items: center; gap: 12px;
            padding: 16px; background: #f8f9fc;
        }
        .dropdown-avatar {
            width: 42px; height: 42px; background: #1a1f36;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: #00c897; flex-shrink: 0;
        }
        .dropdown-name { font-size: 14px; font-weight: 600; color: #1a1f36; }
        .dropdown-sub  { font-size: 12px; color: #888; margin-top: 2px; }
        .dropdown-divider { height: 1px; background: #f0f2f7; }
        .dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; font-size: 13px; color: #444;
            text-decoration: none; transition: background .15s;
        }
        .dropdown-item i { width: 16px; text-align: center; color: #aaa; font-size: 14px; }
        .dropdown-item:hover { background: #f0faf7; color: #00c897; }
        .dropdown-item:hover i { color: #00c897; }

        @media (max-width: 768px) {
            .hamburger { display: block; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .page-content { margin-left: 0; padding: 16px; }
            .footer { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
    @yield('extra_css')
</head>
<body>

{{-- Topbar --}}
<div class="topbar">
    <div style="display:flex; align-items:center; gap:12px;">
        <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a href="{{ route('client.dashboard') }}" class="topbar-brand">
            <div class="logo-box">{{ strtoupper(substr(\App\Models\Setting::get('company_name', 'S'), 0, 1)) }}</div>
            {{ \App\Models\Setting::get('company_name', 'SmartISP') }}
        </a>
    </div>
    <div class="topbar-right">
        <div class="user-pill">
            <div class="user-avatar">{{ strtoupper(substr(Auth::guard('customer')->user()->name, 0, 2)) }}</div>
            <span>{{ Auth::guard('customer')->user()->name }}</span>
            <i class="fas fa-chevron-down" style="font-size:10px; color:#aaa; margin-left:4px;"></i>
        </div>
    </div>
</div>

{{-- Sidebar --}}
<div class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-bell"></i> Notices
        </a>
        <a href="{{ route('client.invoices') }}" class="nav-item {{ request()->routeIs('client.invoices*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Bill Payment
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-chart-line"></i> Live Traffic
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-list-ul"></i> Usages List
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-box"></i> Package List
        </a>
        <a href="{{ route('client.tickets') }}" class="nav-item {{ request()->routeIs('client.tickets*') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i> Ticket List
        </a>
        <div class="nav-divider"></div>
        <a href="{{ route('client.profile') }}" class="nav-item {{ request()->routeIs('client.profile*') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> Profile
        </a>
        <form method="POST" action="{{ route('client.logout') }}">
            @csrf
            <button type="submit" class="nav-item" style="width:100%; background:none; border:none; cursor:pointer; text-align:left;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </nav>
</div>

{{-- Main Content --}}
<div class="main-wrapper">
    <div class="page-content">

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @yield('content')
    </div>
</div>

{{-- Footer --}}
<div class="footer">
    {{ date('Y') }} &copy; {{ \App\Models\Setting::get('company_name', 'SmartISP') }}
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// Dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
    var pill     = document.getElementById('userPill');
    var dropdown = document.getElementById('userDropdown');

    if (pill && dropdown) {
        pill.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
        });

        document.addEventListener('click', function() {
            dropdown.classList.remove('open');
        });

        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>
@yield('extra_js')
</body>
</html>
