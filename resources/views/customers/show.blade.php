{{-- resources/views/customers/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Customer: ' . $customer->name)

@section('page_actions')
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-edit"></i> Edit
    </a>
    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-invoice"></i> Create Invoice
    </a>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@section('page_content')
<div class="row">
    {{-- Customer Info Card --}}
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body text-center">
                @if($customer->photo)
                    <img src="{{ asset('storage/' . $customer->photo) }}" class="img-circle img-fluid" style="width:100px;height:100px;object-fit:cover;" alt="Photo">
                @else
                    <div class="img-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width:100px;height:100px;">
                        <i class="fas fa-user fa-3x text-white"></i>
                    </div>
                @endif
                <h5 class="mt-2 mb-0">{{ $customer->name }}</h5>
                <code>{{ $customer->customer_code }}</code>
                <br>
                <span class="badge badge-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'expired' ? 'danger' : 'warning') }} mt-1">
                    {{ ucfirst($customer->status) }}
                </span>
            </div>
            <div class="card-footer p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Phone:</b> {{ $customer->phone }}</li>
                    <li class="list-group-item"><b>Email:</b> {{ $customer->email ?? '-' }}</li>
                    <li class="list-group-item"><b>Area:</b> {{ $customer->area ?? '-' }}</li>
                    <li class="list-group-item"><b>Package:</b> {{ $customer->package->name ?? '-' }}</li>
                    <li class="list-group-item"><b>Billing Date:</b> {{ $customer->billing_date }}</li>
                    <li class="list-group-item"><b>Connection:</b> {{ $customer->connection_date?->format('d M Y') ?? '-' }}</li>
                    <li class="list-group-item"><b>Agent:</b> {{ $customer->agent->name ?? '-' }}</li>
                    <li class="list-group-item"><b>PPPoE User:</b> {{ $customer->pppoe_username ?? '-' }}</li>
                    <li class="list-group-item"><b>IP Address:</b> {{ $customer->ip_address ?? '-' }}</li>
                </ul>
            </div>
        </div>

        {{-- Status Change --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Change Status</h3></div>
            <div class="card-body">
                <form action="{{ route('customers.status', $customer) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="input-group">
                        <select name="status" class="form-control">
                            @foreach(['active','inactive','suspended','expired'] as $s)
                                <option value="{{ $s }}" {{ $customer->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Invoices & Payments --}}
    <div class="col-md-8">
        {{-- Invoice List --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i> Invoices</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Invoice No</th><th>Month</th><th>Amount</th><th>Due</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($customer->invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_no }}</td>
                            <td>{{ $inv->month }}</td>
                            <td>{{ number_format($inv->amount) }}</td>
                            <td>{{ number_format($inv->due_amount) }}</td>
                            <td>
                                <span class="badge badge-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'overdue' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('invoices.pdf', $inv) }}" class="btn btn-xs btn-secondary"><i class="fas fa-file-pdf"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tickets --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-ticket-alt mr-1"></i> Support Tickets</h3>
                <div class="card-tools">
                    <a href="{{ route('tickets.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> New Ticket
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Ticket No</th><th>Subject</th><th>Priority</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($customer->tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_no }}</td>
                            <td>{{ Str::limit($ticket->subject, 35) }}</td>
                            <td><span class="badge badge-{{ $ticket->priority === 'urgent' ? 'danger' : 'secondary' }}">{{ ucfirst($ticket->priority) }}</span></td>
                            <td><span class="badge badge-info">{{ ucfirst($ticket->status) }}</span></td>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">No tickets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
