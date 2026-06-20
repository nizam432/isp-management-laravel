{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Dashboard')

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

            {{-- Custom range inputs — শুধু "Custom Range" select করলে দেখা যাবে --}}
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
    {{-- Row 1 — Customer Status --}}
    <div class="row mb-0">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#17a2b8">
                <a href="{{ route('customers.index') }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-users mr-1"></i> Total Customer</div>
                    <div class="sc-value" data-stat="total_customers">{{ $stats['total_customers'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#00a65a">
                <a href="{{ route('customers.index', ['status' => 'active']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Active Customer</div>
                    <div class="sc-value" data-stat="active_customers">{{ $stats['active_customers'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#6c757d">
                <a href="{{ route('customers.index', ['status' => 'inactive']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-user-clock mr-1"></i> Inactive Customer</div>
                    <div class="sc-value" data-stat="inactive_customers">{{ $stats['inactive_customers'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#dd4b39">
                <a href="{{ route('customers.index', ['status' => 'expired']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-user-times mr-1"></i> Expired Customer</div>
                    <div class="sc-value" data-stat="expired_customers">{{ $stats['expired_customers'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
    </div>

    {{-- Row 2 — Billing / Network --}}
    <div class="row mb-0">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#28a745">
                <a href="{{ route('invoices.index', ['status' => 'paid']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-money-check mr-1"></i> Paid Customer</div>
                    <div class="sc-value" data-stat="paid_customers">{{ $stats['paid_customers'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-money-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#e74c3c">
                <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-money-bill-wave mr-1"></i> Unpaid Customer</div>
                    <div class="sc-value" data-stat="unpaid_customers">{{ $stats['unpaid_customers'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#16a085">
                <a href="#" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-wifi mr-1"></i> Online Client</div>
                    <div class="sc-value" data-stat="online_clients">{{ $stats['online_clients'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-wifi"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#9b59b6">
                <a href="{{ route('customers.index') }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-gift mr-1"></i> Free Client</div>
                    <div class="sc-value" data-stat="free_clients">{{ $stats['free_clients'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-gift"></i></div>
            </div>
        </div>
    </div>

    {{-- Row 3 — Finance --}}
    <div class="row mb-0">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#f39c12">
                <a href="{{ route('payments.index') }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-coins mr-1"></i> Collection</div>
                    <div class="sc-value" data-stat="collection" data-currency="1">{{ number_format($stats['collection']) }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#c0392b">
                <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-file-invoice mr-1"></i> Due Invoice</div>
                    <div class="sc-value" data-stat="due_invoice" data-currency="1">{{ number_format($stats['due_invoice']) }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#d35400">
                <a href="#" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-arrow-circle-down mr-1"></i> Total Expense</div>
                    <div class="sc-value" data-stat="total_expense" data-currency="1">{{ number_format($stats['total_expense']) }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-arrow-circle-down"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#27ae60">
                <a href="#" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-arrow-circle-up mr-1"></i> Total Income</div>
                    <div class="sc-value" data-stat="total_income" data-currency="1">{{ number_format($stats['total_income']) }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-arrow-circle-up"></i></div>
            </div>
        </div>
    </div>

    {{-- Row 4 — Tickets --}}
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#e67e22">
                <a href="{{ route('tickets.index', ['status' => 'pending']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-ticket-alt mr-1"></i> Open Ticket</div>
                    <div class="sc-value" data-stat="open_tickets">{{ $stats['open_tickets'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-ticket-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#2980b9">
                <a href="{{ route('tickets.index', ['status' => 'processing']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-spinner mr-1"></i> Processing Ticket</div>
                    <div class="sc-value" data-stat="processing_tickets">{{ $stats['processing_tickets'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-spinner"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#2ecc71">
                <a href="{{ route('tickets.index', ['status' => 'solved']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-check-circle mr-1"></i> Solved Ticket</div>
                    <div class="sc-value" data-stat="solved_tickets">{{ $stats['solved_tickets'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="cust-stat-card" style="background:#7f8c8d">
                <a href="{{ route('tickets.index', ['status' => 'closed']) }}" class="sc-link"></a>
                <div class="sc-left">
                    <div class="sc-label"><i class="fas fa-lock mr-1"></i> Close Ticket</div>
                    <div class="sc-value" data-stat="closed_tickets">{{ $stats['closed_tickets'] }}</div>
                </div>
                <div class="sc-icon"><i class="fas fa-lock"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Income vs Expense Chart — Last 12 Months --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Last 1 Year — Income vs Expense</h3>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Recent Tickets --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-ticket-alt mr-1"></i> Recent Tickets</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentTickets as $ticket)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span>
                                <a href="{{ route('tickets.show', $ticket) }}">{{ Str::limit($ticket->subject, 30) }}</a>
                                <br><small class="text-muted">{{ $ticket->customer->name }}</small>
                            </span>
                            <span class="badge badge-{{ $ticket->priority === 'urgent' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">No tickets found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Recent Payments Table --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-money-bill mr-1"></i> Recent Payments</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->customer->name }}</td>
                            <td>{{ $payment->invoice->invoice_no ?? '-' }}</td>
                            <td>{{ number_format($payment->amount) }}</td>
                            <td><span class="badge badge-info">{{ strtoupper($payment->method) }}</span></td>
                            <td>{{ $payment->receivedBy->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ── Income vs Expense Chart (Last 1 Year) ───────────
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($chartData, 'month')) !!},
            datasets: [
                {
                    label: 'Income (BDT)',
                    data: {!! json_encode(array_column($chartData, 'income')) !!},
                    backgroundColor: 'rgba(40,167,69,0.75)',
                    borderColor: 'rgba(40,167,69,1)',
                    borderWidth: 1
                },
                {
                    label: 'Expense (BDT)',
                    data: {!! json_encode(array_column($chartData, 'expense')) !!},
                    backgroundColor: 'rgba(220,53,69,0.75)',
                    borderColor: 'rgba(220,53,69,1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // ── Date Filter → AJAX Stats Reload ──────────────────
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
            $.get("{{ route('dashboard.stats') }}", { range: range, from: from, to: to })
                .done(function (res) {
                    if (!res.success) return;
                    $.each(res.stats, function (key, value) {
                        const $el = $(`[data-stat="${key}"]`);
                        if (!$el.length) return;
                        const formatted = $el.data('currency') ? Number(value).toLocaleString() : value;
                        $el.text(formatted);
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
                return; // dropdown খোলা থাকবে, Apply বাটনে ক্লিক করলে load হবে
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

        // dropdown ভেতরে ক্লিক করলে বন্ধ না হোক (Custom Range box ব্যবহারের সময়)
        $('#dateFilterMenu').on('click', function (e) {
            if ($(e.target).hasClass('date-range-item')) return;
            e.stopPropagation();
        });
    });
</script>
@endsection
