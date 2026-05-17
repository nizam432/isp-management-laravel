{{-- resources/views/invoices/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Invoices')
@section('page_actions')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Invoice</a>
    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#bulkModal">
        <i class="fas fa-magic"></i> Bulk Generate
    </button>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Customer name / phone" value="{{ request('search') }}">
            <input type="month" name="month" class="form-control form-control-sm mr-2" value="{{ request('month') }}">
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">All Status</option>
                @foreach(['unpaid','paid','partial','overdue'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-default mr-1"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-secondary">Reset</a>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr><th>Invoice No</th><th>Customer</th><th>Month</th><th>Amount</th><th>Due</th><th>Status</th><th>Due Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td><code>{{ $inv->invoice_no }}</code></td>
                    <td><a href="{{ route('customers.show', $inv->customer) }}">{{ $inv->customer->name }}</a></td>
                    <td>{{ $inv->month }}</td>
                    <td>{{ number_format($inv->amount) }}</td>
                    <td class="{{ $inv->due_amount > 0 ? 'text-danger font-weight-bold' : '' }}">{{ number_format($inv->due_amount) }}</td>
                    <td>
                        <span class="badge badge-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'overdue' ? 'danger' : ($inv->status === 'partial' ? 'warning' : 'secondary')) }}">
                            {{ ucfirst($inv->status) }}
                        </span>
                    </td>
                    <td>{{ $inv->due_date?->format('d M Y') ?? '-' }}</td>
                    <td>
                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('invoices.pdf', $inv) }}" class="btn btn-xs btn-secondary"><i class="fas fa-file-pdf"></i></a>
                        <form action="{{ route('invoices.destroy', $inv) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $invoices->withQueryString()->links() }}</div>
</div>

{{-- Bulk Generate Modal --}}
<div class="modal fade" id="bulkModal">
    <div class="modal-dialog">
        <form action="{{ route('invoices.bulk-generate') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h4 class="modal-title">Bulk Generate Invoices</h4></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Month</label>
                        <input type="month" name="month" class="form-control" value="{{ date('Y-m') }}" required>
                        <small class="text-muted">Invoices will be generated for all active customers.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-magic"></i> Generate</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
