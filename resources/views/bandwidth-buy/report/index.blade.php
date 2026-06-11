@extends('adminlte::page')
@section('title', 'Bandwidth Report')

@section('content_header')
    <h1 class="m-0 text-dark">Bandwidth Report</h1>
@endsection

@section('content')

<div class="card">
    <div class="card-body">

        {{-- ── Filter Form ──────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('bandwidth-buy.report') }}" id="reportForm">
            <input type="hidden" name="search" value="1">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="text" name="from_date" class="form-control datepicker"
                           value="{{ request('from_date', now()->startOfMonth()->format('m/d/Y')) }}"
                           autocomplete="off" placeholder="MM/DD/YYYY">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="text" name="to_date" class="form-control datepicker"
                           value="{{ request('to_date', now()->format('m/d/Y')) }}"
                           autocomplete="off" placeholder="MM/DD/YYYY">
                </div>
                <div class="col-md-3">
                    <label>Service</label>
                    <select name="service_id" class="form-control select2">
                        <option value="">All Services</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected':'' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Provider</label>
                    <select name="provider_id" class="form-control select2">
                        <option value="">All Providers</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}" {{ request('provider_id') == $p->id ? 'selected':'' }}>
                                {{ $p->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 text-right">
                    <button type="submit" class="btn btn-info px-4">
                        <i class="fas fa-search mr-1"></i> Search
                    </button>
                    <a href="{{ route('bandwidth-buy.report') }}" class="btn btn-secondary ml-1">
                        Reset
                    </a>
                </div>
            </div>
        </form>

    </div>
</div>

{{-- ── Results ─────────────────────────────────────────────────────────────── --}}
@if(request('search') && $results->count() > 0)
<div class="card mt-2">
    <div class="card-header py-2">
        <strong>Results — {{ $results->count() }} line(s)</strong>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-sm mb-0">
            <thead style="background:#5a6268; color:#fff;">
                <tr>
                    <th>SL</th>
                    <th>Invoice</th>
                    <th>Provider</th>
                    <th>Service</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Quantity (MB)</th>
                    <th>Rate (TK)</th>
                    <th>VAT (%)</th>
                    <th>Line Total (TK)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->purchase->invoice_no }}</td>
                    <td>{{ $line->purchase->provider->company_name ?? '—' }}</td>
                    <td>{{ $line->service->name }}</td>
                    <td>{{ $line->from_date->format('Y-m-d') }}</td>
                    <td>{{ $line->to_date->format('Y-m-d') }}</td>
                    <td>{{ number_format($line->quantity_mb, 2) }}</td>
                    <td>{{ number_format($line->rate, 2) }}</td>
                    <td>{{ number_format($line->vat_percent, 2) }}</td>
                    <td class="font-weight-bold">{{ number_format($line->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-secondary font-weight-bold">
                    <td colspan="9" class="text-right">Grand Total</td>
                    <td>{{ number_format($results->sum('line_total'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@elseif(request('search') && $results->count() === 0)
<div class="alert alert-warning mt-2">
    No records found for the selected filters.
</div>
@endif

@endsection

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endpush

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(function () {
    $('.datepicker').datepicker({ format: 'mm/dd/yyyy', autoclose: true });
});
</script>
@endpush
