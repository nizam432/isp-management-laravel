{{-- resources/views/super-admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Super Admin Dashboard')

@section('page_actions')
    {{-- Date Filter Dropdown --}}
    <div class="dropdown" id="dateFilterWrap">
        <button class="btn btn-light btn-sm dropdown-toggle shadow-sm" type="button" id="dateFilterBtn" data-toggle="dropdown" aria-expanded="false">
            <i class="far fa-calendar-alt mr-1"></i> <span id="dateFilterLabel">All Time</span>
        </button>
        <div class="dropdown-menu dropdown-menu-right shadow" style="min-width:220px" id="dateFilterMenu">
            <a class="dropdown-item date-range-item font-weight-bold" data-range="all_time" href="#">All Time</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item date-range-item" data-range="today" href="#">Today</a>
            <a class="dropdown-item date-range-item" data-range="yesterday" href="#">Yesterday</a>
            <a class="dropdown-item date-range-item" data-range="last_7_days" href="#">Last 7 Days</a>
            <a class="dropdown-item date-range-item" data-range="last_30_days" href="#">Last 30 Days</a>
            <a class="dropdown-item date-range-item" data-range="this_month" href="#">This Month</a>
            <a class="dropdown-item date-range-item" data-range="last_month" href="#">Last Month</a>
            <a class="dropdown-item date-range-item" data-range="this_month_last_year" href="#">This month last year</a>
            <a class="dropdown-item date-range-item" data-range="this_year" href="#">This Year</a>
            <a class="dropdown-item date-range-item" data-range="last_year" href="#">Last Year</a>
            <a class="dropdown-item date-range-item" data-range="current_financial_year" href="#">Current financial year</a>
            <a class="dropdown-item date-range-item" data-range="last_financial_year" href="#">Last financial year</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item date-range-item" data-range="custom" href="#">Custom Range</a>

            <div id="customRangeBox" class="px-3 py-2 d-none">
                <label class="small font-weight-bold mb-1">From</label>
                <input type="date" id="customFrom" class="form-control form-control-sm mb-2">
                <label class="small font-weight-bold mb-1">To</label>
                <input type="date" id="customTo" class="form-control form-control-sm mb-2">
                <button type="button" class="btn btn-primary btn-sm btn-block" id="applyCustomRange">Apply</button>
            </div>
        </div>
    </div>
@endsection

@section('page_content')

<style>
.cust-stat-card {
    border-radius: 4px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
    transition: opacity .2s;
}
.cust-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.cust-stat-card .sc-left .sc-value {
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}
.cust-stat-card .sc-icon {
    font-size: 48px;
    color: rgba(255,255,255,.18);
}
.cust-stat-card a.sc-link {
    position: absolute;
    inset: 0;
    z-index: 2;
}
.stats-loading { opacity: .45; pointer-events: none; }
</style>

<div id="statsGrid">
    {{-- Row 1 --}}
    <div class="row mb-0">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#17a2b8">
                <a href="{{ route('super-admin.tenants.index') }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-building mr-1"></i> Total ISP</div>
                    <div class="sc-value" data-stat="total_isp">{{ $stats['total_isp'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-building"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#00a65a">
                <a href="{{ route('super-admin.tenants.index', ['type' => 'active']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-check-circle mr-1"></i> Active ISP</div>
                    <div class="sc-value" data-stat="active_isp">{{ $stats['active_isp'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#3c8dbc">
                <a href="{{ route('super-admin.tenants.index', ['type' => 1]) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-network-wired mr-1"></i> Pure ISP</div>
                    <div class="sc-value" data-stat="pure_isp">{{ $stats['pure_isp'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-network-wired"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#f39c12">
                <a href="{{ route('super-admin.tenants.index', ['type' => 2]) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-sitemap mr-1"></i> Master Reseller</div>
                    <div class="sc-value" data-stat="master_reseller">{{ $stats['master_reseller'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-sitemap"></i></div>
            </div>
        </div>
    </div>

    {{-- Row 2 --}}
    <div class="row mb-0">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#dd4b39">
                <a href="{{ route('super-admin.tenants.index', ['type' => 3]) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-project-diagram mr-1"></i> Sub Reseller</div>
                    <div class="sc-value" data-stat="sub_reseller">{{ $stats['sub_reseller'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-project-diagram"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#6c757d">
                <a href="{{ route('super-admin.plans.index') }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-tags mr-1"></i> Plans</div>
                    <div class="sc-value" data-stat="total_plans">{{ $stats['total_plans'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-tags"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Recent ISPs --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-building mr-1"></i> Recent ISPs</h3>
        <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Add ISP
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                <tr>
                    <td>
                        <strong>{{ $tenant->name }}</strong>
                        <br><small class="text-muted">{{ $tenant->email }}</small>
                    </td>
                    <td>
                        @if($tenant->is_reseller == 1)
                            <span class="badge badge-info">Pure ISP</span>
                        @elseif($tenant->is_reseller == 2)
                            <span class="badge badge-warning">Master Reseller</span>
                        @else
                            <span class="badge badge-secondary">Sub Reseller</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $tenant->plan->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $tenant->is_active ? 'success' : 'danger' }}">
                            {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <small>{{ $tenant->plan_expires_at ? \Carbon\Carbon::parse($tenant->plan_expires_at)->format('d M Y') : '—' }}</small>
                    </td>
                    <td>
                        <a href="{{ route('super-admin.tenants.show', $tenant->id) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('super-admin.tenants.edit', $tenant->id) }}" class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('super-admin.tenants.toggle', $tenant->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-{{ $tenant->is_active ? 'danger' : 'success' }}">
                                {{ $tenant->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No ISP found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-sm btn-default">
            View All →
        </a>
    </div>
</div>

@endsection

@section('extra_js')
<script>
    $(function () {
        const labels = {
            all_time: 'All Time', today: 'Today', yesterday: 'Yesterday', last_7_days: 'Last 7 Days',
            last_30_days: 'Last 30 Days', this_month: 'This Month', last_month: 'Last Month',
            this_month_last_year: 'This month last year', this_year: 'This Year',
            last_year: 'Last Year', current_financial_year: 'Current financial year',
            last_financial_year: 'Last financial year', custom: 'Custom Range',
        };

        function loadStats(range, from, to) {
            $('#statsGrid').addClass('stats-loading');
            $.get("{{ route('super-admin.dashboard.stats') }}", { range: range, from: from, to: to })
                .done(function (res) {
                    if (!res.success) return;
                    $.each(res.stats, function (key, value) {
                        const $el = $(`[data-stat="${key}"]`);
                        if (!$el.length) return;
                        $el.text(value);
                    });
                    $('#dateFilterLabel').text(labels[range] || res.label);
                })
                .fail(function () {
                    toastr?.error ? toastr.error('Failed to load stats.') : alert('Failed to load stats.');
                })
                .always(function () {
                    $('#statsGrid').removeClass('stats-loading');
                });
        }

        $('.date-range-item').on('click', function (e) {
            const range = $(this).data('range');

            if (range === 'custom') {
                e.preventDefault();
                $('#customRangeBox').removeClass('d-none');
                return;
            }

            e.preventDefault();
            $('#customRangeBox').addClass('d-none');
            loadStats(range);
            $('#dateFilterMenu').removeClass('show');
        });

        $('#applyCustomRange').on('click', function () {
            const from = $('#customFrom').val();
            const to   = $('#customTo').val();
            if (!from || !to) { alert('Please select both From and To dates.'); return; }
            loadStats('custom', from, to);
            $('#dateFilterLabel').text(`${from} to ${to}`);
            $('#dateFilterMenu').removeClass('show');
        });

        $('#dateFilterMenu').on('click', function (e) {
            if ($(e.target).hasClass('date-range-item')) return;
            e.stopPropagation();
        });
    });
</script>
@endsection
