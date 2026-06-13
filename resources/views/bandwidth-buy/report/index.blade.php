@extends('adminlte::page')
@section('title', 'Bandwidth Purchase Report')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h4 class="mb-0 font-weight-bold" style="color:#1a237e;">
            <i class="fas fa-chart-bar mr-2"></i>Bandwidth Purchase Report
        </h4>
        <small class="text-muted">Filter purchase lines by date range, service & provider</small>
    </div>
    <a href="{{ route('bandwidth-buy.purchase.index') }}" class="btn btn-sm btn-outline-secondary px-3">
        <i class="fas fa-file-invoice mr-1"></i> Purchase List
    </a>
</div>
@endsection

@section('content')

{{-- ── Filter Card ─────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius:12px; overflow:hidden;">
    <div class="card-header border-0 py-3 px-4"
         style="background:linear-gradient(135deg,#1a237e,#3949ab);">
        <div class="d-flex align-items-center">
            <i class="fas fa-filter text-white mr-2" style="font-size:16px;"></i>
            <div>
                <h6 class="mb-0 text-white font-weight-bold">Search Filters</h6>
                <small class="text-white-50">Narrow down purchase data by the fields below</small>
            </div>
        </div>
    </div>
    <div class="card-body px-4 py-4">
        <form method="GET" action="{{ route('bandwidth-buy.report') }}" id="reportForm">
            <input type="hidden" name="search" value="1">
            <div class="row">

                {{-- From Date --}}
                <div class="col-md-3 mb-3">
                    <label class="field-label">
                        <i class="fas fa-calendar-alt mr-1 text-primary"></i>From Date
                    </label>
                    <div class="input-group">
                        <input type="text" name="from_date" id="fromDate"
                               class="form-control custom-input datepicker"
                               value="{{ request('from_date', now()->startOfMonth()->format('m/d/Y')) }}"
                               autocomplete="off" placeholder="MM/DD/YYYY">
                        <div class="input-group-append">
                            <span class="input-group-text cal-icon"
                                  onclick="$('#fromDate').datepicker('show')">
                                <i class="fas fa-calendar text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- To Date --}}
                <div class="col-md-3 mb-3">
                    <label class="field-label">
                        <i class="fas fa-calendar-check mr-1 text-primary"></i>To Date
                    </label>
                    <div class="input-group">
                        <input type="text" name="to_date" id="toDate"
                               class="form-control custom-input datepicker"
                               value="{{ request('to_date', now()->format('m/d/Y')) }}"
                               autocomplete="off" placeholder="MM/DD/YYYY">
                        <div class="input-group-append">
                            <span class="input-group-text cal-icon"
                                  onclick="$('#toDate').datepicker('show')">
                                <i class="fas fa-calendar text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Service --}}
                <div class="col-md-3 mb-3">
                    <label class="field-label">
                        <i class="fas fa-network-wired mr-1 text-primary"></i>Service
                    </label>
                    <select name="service_id" class="form-control custom-input select2">
                        <option value="">All Services</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}"
                                {{ request('service_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Provider --}}
                <div class="col-md-3 mb-3">
                    <label class="field-label">
                        <i class="fas fa-building mr-1 text-primary"></i>Provider
                    </label>
                    <select name="provider_id" class="form-control custom-input select2">
                        <option value="">All Providers</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}"
                                {{ request('provider_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Buttons --}}
            <div class="d-flex justify-content-between align-items-center mt-1">
                <div>
                    {{-- Quick range shortcuts --}}
                    <span class="text-muted mr-2" style="font-size:12px;">Quick:</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary range-btn mr-1" data-range="this_month">This Month</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary range-btn mr-1" data-range="last_month">Last Month</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary range-btn" data-range="this_year">This Year</button>
                </div>
                <div>
                    <a href="{{ route('bandwidth-buy.report') }}"
                       class="btn btn-light border px-3 mr-2" style="border-radius:8px;">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </a>
                    <button type="submit" class="btn btn-primary px-4"
                            style="border-radius:8px; background:linear-gradient(135deg,#1a237e,#3949ab); border:none;">
                        <i class="fas fa-search mr-1"></i>Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Results ──────────────────────────────────────────────────────────────── --}}
@if($searched)

    @if($results->count() > 0)

    {{-- Summary Cards --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm mb-0" style="border-radius:10px; overflow:hidden;">
                <span class="info-box-icon elevation-1"
                      style="background:linear-gradient(135deg,#1a237e,#3949ab);">
                    <i class="fas fa-list-ol text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Lines</span>
                    <span class="info-box-number font-weight-bold">{{ $summary['total_lines'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm mb-0" style="border-radius:10px; overflow:hidden;">
                <span class="info-box-icon elevation-1"
                      style="background:linear-gradient(135deg,#1b5e20,#388e3c);">
                    <i class="fas fa-money-bill-wave text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Grand Total</span>
                    <span class="info-box-number font-weight-bold">৳ {{ number_format($summary['grand_total'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm mb-0" style="border-radius:10px; overflow:hidden;">
                <span class="info-box-icon elevation-1"
                      style="background:linear-gradient(135deg,#bf360c,#e64a19);">
                    <i class="fas fa-tachometer-alt text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Qty (MB)</span>
                    <span class="info-box-number font-weight-bold">{{ number_format($summary['total_qty_mb'], 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box shadow-sm mb-0" style="border-radius:10px; overflow:hidden;">
                <span class="info-box-icon elevation-1"
                      style="background:linear-gradient(135deg,#4a148c,#7b1fa2);">
                    <i class="fas fa-network-wired text-white"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Services / Providers</span>
                    <span class="info-box-number font-weight-bold">
                        {{ $summary['unique_services'] }} / {{ $summary['unique_providers'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
        <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(135deg,#1b5e20,#388e3c);">
            <div class="d-flex align-items-center">
                <i class="fas fa-table text-white mr-2"></i>
                <div>
                    <h6 class="mb-0 text-white font-weight-bold">
                        Report Results
                        <span class="badge badge-light text-success ml-2">
                            {{ $results->count() }} line(s)
                        </span>
                    </h6>
                    <small class="text-white-50">
                        {{ request('from_date') }} — {{ request('to_date') }}
                    </small>
                </div>
            </div>
            {{-- Print button --}}
            <button onclick="window.print()" class="btn btn-sm btn-light shadow-sm px-3"
                    style="border-radius:20px; font-size:12px;">
                <i class="fas fa-print mr-1"></i>Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" id="reportTable">
                    <thead>
                        <tr style="background:#f1f8e9; border-bottom:2px solid #c8e6c9;">
                            <th class="rpt-th text-center" style="width:50px;">#</th>
                            <th class="rpt-th">Invoice</th>
                            <th class="rpt-th">Provider</th>
                            <th class="rpt-th">Service</th>
                            <th class="rpt-th">From Date</th>
                            <th class="rpt-th">To Date</th>
                            <th class="rpt-th text-right">Qty (MB)</th>
                            <th class="rpt-th text-right">Rate (৳)</th>
                            <th class="rpt-th text-center">VAT %</th>
                            <th class="rpt-th text-right">Total (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $svcBg  = ['#E3F2FD','#E8F5E9','#FFF3E0','#F3E5F5','#FCE4EC','#E0F7FA'];
                        $svcTxt = ['#1565C0','#2E7D32','#E65100','#6A1B9A','#B71C1C','#006064'];
                        $svcMap = [];
                        $svcIdx = 0;
                        @endphp
                        @foreach($results as $i => $line)
                        @php
                            $svcName = $line->service->name ?? '—';
                            if (!isset($svcMap[$svcName])) {
                                $svcMap[$svcName] = $svcIdx % 6;
                                $svcIdx++;
                            }
                            $ci = $svcMap[$svcName];
                        @endphp
                        <tr class="rpt-row">
                            <td class="text-center text-muted small">{{ $i + 1 }}</td>
                            <td>
                                <span class="font-weight-bold text-primary" style="font-size:13px;">
                                    {{ $line->purchase->invoice_no ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="prov-avatar mr-2"
                                         style="background:{{ $svcBg[$ci] }}; color:{{ $svcTxt[$ci] }};">
                                        {{ strtoupper(substr($line->purchase->provider->company_name ?? 'P', 0, 1)) }}
                                    </div>
                                    <span style="font-size:13px;">{{ $line->purchase->provider->company_name ?? '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="svc-badge"
                                      style="background:{{ $svcBg[$ci] }}; color:{{ $svcTxt[$ci] }};">
                                    <i class="fas fa-wifi mr-1" style="font-size:10px;"></i>{{ $svcName }}
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:13px;">
                                <i class="fas fa-calendar-alt mr-1" style="font-size:10px;"></i>
                                {{ $line->from_date->format('d M Y') }}
                            </td>
                            <td class="text-muted" style="font-size:13px;">
                                <i class="fas fa-calendar-check mr-1" style="font-size:10px;"></i>
                                {{ $line->to_date->format('d M Y') }}
                            </td>
                            <td class="text-right font-weight-bold" style="font-size:13px;">
                                {{ number_format($line->quantity_mb, 2) }}
                            </td>
                            <td class="text-right" style="font-size:13px;">
                                {{ number_format($line->rate, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border" style="font-size:12px;">
                                    {{ number_format($line->vat_percent, 2) }}%
                                </span>
                            </td>
                            <td class="text-right font-weight-bold"
                                style="font-size:14px; color:#1b5e20;">
                                ৳ {{ number_format($line->line_total, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#e8f5e9; border-top:2px solid #a5d6a7;">
                            <td colspan="6" class="font-weight-bold text-right py-3 pr-3"
                                style="font-size:13px; color:#1b5e20;">
                                Grand Total
                            </td>
                            <td class="text-right font-weight-bold py-3"
                                style="font-size:13px; color:#1b5e20;">
                                {{ number_format($results->sum('quantity_mb'), 2) }}
                            </td>
                            <td colspan="2"></td>
                            <td class="text-right font-weight-bold py-3 pr-3"
                                style="font-size:15px; color:#1b5e20;">
                                ৳ {{ number_format($results->sum('line_total'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    @else
    {{-- No results --}}
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:12px;">
        <div style="opacity:.4;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
        </div>
        <h5 class="text-muted">No Records Found</h5>
        <p class="text-muted mb-0" style="font-size:13px;">
            No purchase lines match the selected filters.<br>
            Try adjusting the date range, service, or provider.
        </p>
    </div>
    @endif

@else
{{-- Initial state --}}
<div class="card border-0 shadow-sm text-center py-5" style="border-radius:12px; border:2px dashed #c5cae9 !important;">
    <i class="fas fa-chart-bar fa-3x mb-3 d-block" style="color:#c5cae9;"></i>
    <h5 style="color:#9fa8da;">Set filters and click Search</h5>
    <p class="text-muted mb-0" style="font-size:13px;">
        Select date range, service, or provider above and press <strong>Search</strong>.
    </p>
</div>
@endif

@endsection


@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
/* field label */
.field-label { font-size:13px; font-weight:700; color:#37474f; margin-bottom:5px; display:block; }

/* inputs */
.custom-input { border-color:#d0d7e8; border-radius:8px !important; font-size:14px; height:40px; }
.custom-input:focus { border-color:#3949ab !important; box-shadow:0 0 0 3px rgba(57,73,171,.12) !important; }

/* input-group cal icon */
.input-group-text.cal-icon {
    background:#f0f4ff; border-left:0; border-color:#d0d7e8;
    cursor:pointer; border-radius:0 8px 8px 0 !important;
}
.input-group .form-control { border-right:0; }

/* select2 height */
.select2-container--default .select2-selection--single {
    height:40px !important; border-color:#d0d7e8 !important; border-radius:8px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height:40px !important; font-size:14px; padding-left:12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height:38px !important; }

/* quick range buttons */
.range-btn { font-size:11px !important; padding:2px 10px !important; border-radius:20px !important; }
.range-btn:hover { background:#e8eaf6 !important; border-color:#3949ab !important; color:#1a237e !important; }

/* report table */
.rpt-th {
    padding:9px 12px !important; font-size:11px !important;
    font-weight:700 !important; text-transform:uppercase; letter-spacing:.5px;
    color:#2e7d32 !important;
}
.rpt-row td { padding:9px 12px; vertical-align:middle; }
.rpt-row:hover { background:#f1f8e9 !important; }

/* service badge */
.svc-badge {
    display:inline-flex; align-items:center; padding:3px 10px;
    border-radius:20px; font-size:12px; font-weight:700; white-space:nowrap;
}

/* provider avatar */
.prov-avatar {
    width:26px; height:26px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:11px; font-weight:800;
}

/* info-box radius */
.info-box { border-radius:10px !important; }

/* datepicker z-index */
.datepicker-dropdown { z-index:9999 !important; }

/* print styles */
@media print {
    .content-header, .main-sidebar, .main-header,
    .card-header button, form, .info-box { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
    .rpt-row:hover { background:transparent !important; }
}
</style>
@stop


@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(function () {
    // Datepicker init
    $('.datepicker').datepicker({ format: 'mm/dd/yyyy', autoclose: true });

    // Quick range buttons
    const today = new Date();

    function pad(n) { return String(n).padStart(2,'0'); }
    function fmt(d) { return pad(d.getMonth()+1)+'/'+pad(d.getDate())+'/'+d.getFullYear(); }

    $('.range-btn').on('click', function () {
        const range = $(this).data('range');
        let from, to;

        if (range === 'this_month') {
            from = new Date(today.getFullYear(), today.getMonth(), 1);
            to   = today;
        } else if (range === 'last_month') {
            from = new Date(today.getFullYear(), today.getMonth()-1, 1);
            to   = new Date(today.getFullYear(), today.getMonth(), 0);
        } else if (range === 'this_year') {
            from = new Date(today.getFullYear(), 0, 1);
            to   = today;
        }

        $('#fromDate').datepicker('setDate', from);
        $('#toDate').datepicker('setDate', to);

        // Brief highlight
        $('#fromDate, #toDate').addClass('border-primary');
        setTimeout(() => $('#fromDate, #toDate').removeClass('border-primary'), 800);
    });
});
</script>
@stop
