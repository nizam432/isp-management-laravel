{{-- resources/views/reports/bill/aging-due.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Aging Due Report')
@section('page_content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label class="mr-2 mb-0">Zone:</label>
            <select name="zone_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                @endforeach
            </select>

            <label class="mr-2 mb-0">Agent:</label>
            <select name="agent_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                @endforeach
            </select>
            <input type="hidden" name="bucket" value="{{ $activeBucket }}">
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12 mb-2">
                <div class="info-box bg-secondary">
                    <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Outstanding Due</span>
                        <span class="info-box-number">{{ number_format($totalDue, 2) }} BDT</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            @php
                $bucketLabels = [
                    'not_due' => ['Not Yet Due', 'bg-info'],
                    '0_30'    => ['0 - 30 Days', 'bg-warning'],
                    '31_60'   => ['31 - 60 Days', 'bg-orange'],
                    '61_90'   => ['61 - 90 Days', 'bg-danger'],
                    '90_plus' => ['90+ Days', 'bg-dark'],
                ];
            @endphp
            @foreach($bucketLabels as $key => [$label, $color])
            <div class="col-md-2">
                <a href="{{ route('reports.bill.aging-due', array_merge(request()->except('bucket'), ['bucket' => $key])) }}" class="text-decoration-none">
                    <div class="info-box {{ $activeBucket === $key ? $color : '' }} {{ $activeBucket === $key ? '' : 'border' }}">
                        <div class="info-box-content text-center">
                            <span class="info-box-text">{{ $label }}</span>
                            <span class="info-box-number">{{ $summary[$key]['count'] }}</span>
                            <span class="d-block small">{{ number_format($summary[$key]['amount'], 0) }} BDT</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        <h5 class="mb-2">{{ $bucketLabels[$activeBucket][0] ?? '' }} — Invoice List</h5>
        <table class="table table-striped table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Invoice</th><th>Customer</th><th>Phone</th>
                    <th>Zone</th><th>Agent</th><th>Due Date</th><th>Due Amount</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeList as $i => $invoice)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><code>{{ $invoice->invoice_no }}</code></td>
                    <td>{{ $invoice->customer->name ?? '-' }}</td>
                    <td>{{ $invoice->customer->phone ?? '-' }}</td>
                    <td>{{ $invoice->customer->zone->name ?? '-' }}</td>
                    <td>{{ $invoice->customer->agent->name ?? '-' }}</td>
                    <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</td>
                    <td class="font-weight-bold text-danger">{{ number_format($invoice->due_amount, 2) }}</td>
                    <td><span class="badge badge-secondary">{{ ucfirst($invoice->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted">No invoices in this bucket.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="7">Bucket Total</td>
                    <td colspan="2">{{ number_format($activeList->sum('due_amount'), 2) }} BDT</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
