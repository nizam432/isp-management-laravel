{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('page_content')

{{-- Stats Cards Row 1 --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_customers'] }}</h3>
                <p>Total Customers</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('customers.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active_customers'] }}</h3>
                <p>Active Customers</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
            <a href="{{ route('customers.index', ['status' => 'active']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['today_payments']) }}</h3>
                <p>Today's Collection (BDT)</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill"></i></div>
            <a href="{{ route('payments.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['unpaid_invoices'] }}</h3>
                <p>Unpaid Invoices</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
            <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- Stats Cards Row 2 --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box" style="background:#6f42c1; color:#fff;">
            <div class="inner">
                <h3>{{ number_format($stats['month_payments']) }}</h3>
                <p>This Month (BDT)</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <a href="{{ route('reports.revenue') }}" class="small-box-footer text-white">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $stats['expired_customers'] }}</h3>
                <p>Expired Customers</p>
            </div>
            <div class="icon"><i class="fas fa-user-times"></i></div>
            <a href="{{ route('customers.index', ['status' => 'expired']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['open_tickets'] }}</h3>
                <p>Open Tickets</p>
            </div>
            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
            <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['urgent_tickets'] }}</h3>
                <p>Urgent Tickets</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('tickets.index', ['priority' => 'urgent']) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    {{-- Revenue Chart --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Last 6 Months Revenue</h3>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
    </div>

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
</div>

{{-- Recent Payments Table --}}
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
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $payment)
                <tr>
                    <td>{{ $payment->customer->name }}</td>
                    <td>{{ $payment->invoice->invoice_no }}</td>
                    <td>{{ number_format($payment->amount) }}</td>
                    <td><span class="badge badge-info">{{ strtoupper($payment->method) }}</span></td>
                    <td>{{ $payment->receivedBy->name ?? 'N/A' }}</td>
                    <td><td></td></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($chartData, 'month')) !!},
            datasets: [{
                label: 'Revenue (BDT)',
                data: {!! json_encode(array_column($chartData, 'amount')) !!},
                backgroundColor: 'rgba(60,141,188,0.7)',
                borderColor: 'rgba(60,141,188,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endsection
