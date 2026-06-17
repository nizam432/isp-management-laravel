{{-- ════════════════════════════════════════
    resources/views/mac-reseller/pgw/index.blade.php
════════════════════════════════════════ --}}
@extends('adminlte::page')
@section('title', 'POP Client PGW Payments')
@section('content_header')
    <h1 class="m-0"><i class="fas fa-credit-card mr-1"></i> POP Client PGW Payments
        <small class="text-muted">Client PGW Payments</small>
    </h1>
@stop
@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('mac-reseller.pgw.index') }}">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="small">POPS</label>
                    <select name="reseller_id" class="form-control form-control-sm">
                        <option value="">Select</option>
                        @foreach($resellers as $r)
                        <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>{{ $r->business_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small">TRANSACTION STATUS</label>
                    <select name="transaction_status" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small">FROM DATE</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="small">TO DATE</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="small">PAYMENT GATEWAY</label>
                    <select name="payment_gateway" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option>bKash</option><option>Nagad</option><option>SSLCommerz</option>
                    </select>
                </div>
                <div class="col-md-2 mt-2">
                    <label class="small">GATEWAY TYPE</label>
                    <select name="gateway_type" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option>Mobile Banking</option><option>Card</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end mt-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
        <table class="table table-bordered table-sm" style="font-size:12px">
            <thead class="bg-dark text-white">
                <tr>
                    <th>R.Date</th><th>POPName</th><th>C.Code</th><th>ID/IP</th>
                    <th>Name</th><th>Package</th><th>B.Status</th><th>TrxId</th>
                    <th>MonthlyBill</th><th>Received</th><th>Money Receipt No</th>
                    <th>CreatedBy</th><th>CreationDate</th><th>ReceivedBy</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td>{{ $p->created_at->format('d/m/Y') }}</td>
                    <td>{{ $p->reseller?->business_name }}</td>
                    <td>{{ $p->client_code }}</td>
                    <td>{{ $p->client_ip }}</td>
                    <td>{{ $p->client_name }}</td>
                    <td>{{ $p->package }}</td>
                    <td>{{ $p->billing_status }}</td>
                    <td>{{ $p->trx_id }}</td>
                    <td>{{ number_format($p->monthly_bill, 2) }}</td>
                    <td>{{ number_format($p->received, 2) }}</td>
                    <td>{{ $p->money_receipt_no }}</td>
                    <td>{{ $p->createdBy?->name }}</td>
                    <td>{{ $p->created_at->format('d/m/Y') }}</td>
                    <td>{{ $p->receivedBy?->name }}</td>
                </tr>
                @empty
                <tr><td colspan="14" class="text-center">No data available in table</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-light font-weight-bold">
                <tr>
                    <td colspan="8" class="text-right">TOTAL</td>
                    <td>{{ number_format($totalBill, 2) }}</td>
                    <td>{{ number_format($totalReceived, 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
        </div>
        {{ $payments->links() }}
    </div>
</div>
@stop
