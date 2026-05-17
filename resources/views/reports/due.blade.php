{{-- resources/views/reports/due.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Outstanding Dues Report')
@section('page_actions')
    <a href="{{ route('reports.export.pdf', 'due') }}" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i> Export PDF</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box bg-danger mb-0">
                    <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Outstanding</span>
                        <span class="info-box-number">{{ number_format($totalDue) }} BDT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-sm">
            <thead class="thead-light">
                <tr><th>#</th><th>Customer</th><th>Phone</th><th>Area</th><th>Invoice</th><th>Month</th><th>Amount</th><th>Due</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($invoices as $i => $inv)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><a href="{{ route('customers.show', $inv->customer) }}">{{ $inv->customer->name }}</a></td>
                    <td>{{ $inv->customer->phone }}</td>
                    <td>{{ $inv->customer->area ?? '-' }}</td>
                    <td><code>{{ $inv->invoice_no }}</code></td>
                    <td>{{ $inv->month }}</td>
                    <td>{{ number_format($inv->amount) }}</td>
                    <td class="text-danger font-weight-bold">{{ number_format($inv->due_amount) }}</td>
                    <td>
                        <span class="badge badge-{{ $inv->status === 'overdue' ? 'danger' : 'warning' }}">
                            {{ ucfirst($inv->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted">No outstanding dues found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-weight-bold bg-light">
                    <td colspan="7">Total Outstanding</td>
                    <td class="text-danger">{{ number_format($totalDue) }} BDT</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
