@extends('layouts.app')
@section('plugins.Datatables', true)
@section('page_title', 'Bandwidth Purchase Report')

@section('page_actions')
    <a href="{{ route('bandwidth-buy.purchase.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-invoice mr-1"></i> Purchase List
    </a>
@endsection

@section('page_content')

{{-- Stat Cards --}}
<style>
.bw-stat-card {
    border-radius: 4px;
    color: #fff;
    padding: 14px 16px;
    margin-bottom: 16px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
}
.bw-stat-card .sc-left .sc-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.85);
    margin-bottom: 4px;
}
.bw-stat-card .sc-left .sc-value {
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}
.bw-stat-card .sc-icon {
    font-size: 52px;
    color: rgba(255,255,255,.18);
}
</style>

<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="bw-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-list-ol mr-1"></i> Total Lines</div>
                <div class="sc-value" id="totalLinesNum">0</div>
            </div>
            <div class="sc-icon"><i class="fas fa-list-ol"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bw-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-money-bill-wave mr-1"></i> Grand Total</div>
                <div class="sc-value" id="grandTotalNum">৳ 0</div>
            </div>
            <div class="sc-icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bw-stat-card" style="background:#f39c12;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-tachometer-alt mr-1"></i> Total Qty (MB)</div>
                <div class="sc-value" id="totalQtyNum">0</div>
            </div>
            <div class="sc-icon"><i class="fas fa-tachometer-alt"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="bw-stat-card" style="background:#6f42c1;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-network-wired mr-1"></i> Svc / Provider</div>
                <div class="sc-value" id="servicesProviderNum">0 / 0</div>
            </div>
            <div class="sc-icon"><i class="fas fa-network-wired"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="javascript:void(0);" id="reportForm">
            <div class="row">
                {{-- From Date --}}
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">From Date</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" name="from_date" id="fromDate"
                                   class="form-control datepicker"
                                   value="{{ request('from_date', now()->startOfMonth()->format('m/d/Y')) }}"
                                   autocomplete="off" placeholder="MM/DD/YYYY">
                        </div>
                    </div>
                </div>

                {{-- To Date --}}
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">To Date</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                            </div>
                            <input type="text" name="to_date" id="toDate"
                                   class="form-control datepicker"
                                   value="{{ request('to_date', now()->format('m/d/Y')) }}"
                                   autocomplete="off" placeholder="MM/DD/YYYY">
                        </div>
                    </div>
                </div>

                {{-- Service --}}
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Service</label>
                        <select name="service_id" id="serviceFilter" class="form-control form-control-sm">
                            <option value="">All Services</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Provider --}}
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Provider</label>
                        <select name="provider_id" id="providerFilter" class="form-control form-control-sm">
                            <option value="">All Providers</option>
                            @foreach($providers as $p)
                                <option value="{{ $p->id }}" {{ request('provider_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center mt-1">
                <button type="button" class="btn btn-sm btn-primary mr-2"
                        onclick="$('#reportTable').DataTable().ajax.reload();">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('bandwidth-buy.report') }}" class="btn btn-sm btn-secondary mr-3">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>

                <span class="text-muted mr-2" style="font-size:12px;">Quick:</span>
                <button type="button" class="btn btn-xs btn-outline-secondary range-btn mr-1" data-range="this_month">This Month</button>
                <button type="button" class="btn btn-xs btn-outline-secondary range-btn mr-1" data-range="last_month">Last Month</button>
                <button type="button" class="btn btn-xs btn-outline-secondary range-btn mr-2" data-range="this_year">This Year</button>

                <button type="button" class="btn btn-sm btn-outline-secondary ml-auto" onclick="window.print();">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Results Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-table mr-1"></i> Report Results</h3>
        <small class="text-muted" id="dateRangeText"></small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0" id="reportTable">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Provider</th>
                        <th>Service</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th class="text-right">Qty (MB)</th>
                        <th class="text-right">Rate (৳)</th>
                        <th class="text-center">VAT %</th>
                        <th class="text-right">Total (৳)</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="font-weight-bold" style="background:#f8f9fa;">
                        <td colspan="6" class="text-right">Grand Total</td>
                        <td class="text-right" id="footerQty">0</td>
                        <td colspan="2"></td>
                        <td class="text-right text-success" id="footerTotal">৳ 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
.range-btn { font-size:11px !important; padding:2px 10px !important; border-radius:20px !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #343a40 !important; color: white !important; border: 1px solid #343a40 !important;
}
@media print {
    .content-header, .main-sidebar, .main-header, .card-header button, form { display:none !important; }
    .card { border:none !important; box-shadow:none !important; }
}
</style>
@endpush

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(function () {
    $('.datepicker').datepicker({ format: 'mm/dd/yyyy', autoclose: true });

    if ($.fn.DataTable.isDataTable('#reportTable')) {
        $('#reportTable').DataTable().destroy();
    }

    const table = $('#reportTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            url: "{{ route('bandwidth-buy.report.datatables') }}",
            type: 'GET',
            data: function (d) {
                d.from_date   = $('#fromDate').val();
                d.to_date     = $('#toDate').val();
                d.service_id  = $('#serviceFilter').val();
                d.provider_id = $('#providerFilter').val();
            }
        },
        columns: [
            { data: 'idx',         name: 'idx',         className: 'text-muted small' },
            { data: 'invoice_no',  name: 'invoice_no' },
            { data: 'provider',    name: 'provider' },
            { data: 'service',     name: 'service' },
            { data: 'from_date',   name: 'from_date' },
            { data: 'to_date',     name: 'to_date' },
            { data: 'quantity_mb', name: 'quantity_mb',  className: 'text-right font-weight-bold' },
            { data: 'rate',        name: 'rate',         className: 'text-right' },
            { data: 'vat_percent', name: 'vat_percent',  className: 'text-center' },
            { data: 'line_total',  name: 'line_total',   className: 'text-right font-weight-bold text-success' }
        ],
        rowCallback: function (row, data) {
            $('td', row).eq(1).html(`<span class="font-weight-bold">${data.invoice_no}</span>`);
            $('td', row).eq(8).html(`<span class="badge badge-light border">${data.vat_percent}%</span>`);
            $('td', row).eq(9).html(`৳ ${data.line_total}`);
        },
        drawCallback: function () {
            updateSummary();
        }
    });

    $('#fromDate, #toDate, #serviceFilter, #providerFilter').on('change', function () {
        table.ajax.reload();
    });

    const today = new Date();
    function pad(n) { return String(n).padStart(2, '0'); }

    $('.range-btn').on('click', function () {
        const range = $(this).data('range');
        let from, to;
        if (range === 'this_month') {
            from = new Date(today.getFullYear(), today.getMonth(), 1);
            to   = today;
        } else if (range === 'last_month') {
            from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            to   = new Date(today.getFullYear(), today.getMonth(), 0);
        } else if (range === 'this_year') {
            from = new Date(today.getFullYear(), 0, 1);
            to   = today;
        }
        $('#fromDate').datepicker('setDate', from);
        $('#toDate').datepicker('setDate', to);
        table.ajax.reload();
    });

    function updateSummary() {
        const rows = table.rows({ page: 'current' }).data();
        let grandTotal = 0, totalQty = 0;
        const services = new Set(), providers = new Set();

        rows.each(function (row) {
            grandTotal += parseFloat(String(row.line_total).replace(/,/g, '')) || 0;
            totalQty   += parseFloat(String(row.quantity_mb).replace(/,/g, '')) || 0;
            if (row.service_name) services.add(row.service_name);
            if (row.provider)     providers.add(row.provider);
        });

        const fmt = n => n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        $('#totalLinesNum').text(rows.length);
        $('#grandTotalNum').text('৳ ' + fmt(grandTotal));
        $('#totalQtyNum').text(fmt(totalQty));
        $('#servicesProviderNum').text(services.size + ' / ' + providers.size);
        $('#footerQty').text(fmt(totalQty));
        $('#footerTotal').html('৳ ' + fmt(grandTotal));
        $('#dateRangeText').text($('#fromDate').val() + ' — ' + $('#toDate').val());
    }

    updateSummary();
});
</script>
@endpush
