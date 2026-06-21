{{-- resources/views/reports/bill/package-revenue.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Package-wise Revenue Report')
@section('page_content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label class="mr-2 mb-0">Month:</label>
            <input type="month" name="month" class="form-control form-control-sm mr-2" value="{{ $month }}">
            <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active Subscribers</span>
                        <span class="info-box-number">{{ number_format($totals->subscribers) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Billed</span>
                        <span class="info-box-number">{{ number_format($totals->billed, 0) }} BDT</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Collected</span>
                        <span class="info-box-number">{{ number_format($totals->collected, 0) }} BDT</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Due</span>
                        <span class="info-box-number">{{ number_format($totals->due, 0) }} BDT</span>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-striped table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Package</th><th>Price (Monthly)</th>
                    <th>Active Subscribers</th><th>Invoices</th>
                    <th>Billed</th><th>Collected</th><th>Due</th><th>Collection %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->package->name }}</td>
                    <td>{{ number_format($row->package->price, 2) }}</td>
                    <td>{{ number_format($row->subscribers) }}</td>
                    <td>{{ number_format($row->invoices) }}</td>
                    <td>{{ number_format($row->billed, 2) }}</td>
                    <td class="text-success">{{ number_format($row->collected, 2) }}</td>
                    <td class="text-danger">{{ number_format($row->due, 2) }}</td>
                    <td>
                        @php $pct = $row->billed > 0 ? round(($row->collected / $row->billed) * 100) : 0; @endphp
                        <span class="badge {{ $pct >= 90 ? 'badge-success' : ($pct >= 60 ? 'badge-warning' : 'badge-danger') }}">{{ $pct }}%</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted">No package data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="3">Total</td>
                    <td>{{ number_format($totals->subscribers) }}</td>
                    <td></td>
                    <td>{{ number_format($totals->billed, 2) }}</td>
                    <td>{{ number_format($totals->collected, 2) }}</td>
                    <td>{{ number_format($totals->due, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
