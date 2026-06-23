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

{{-- ── Filter Card (customer-style) ───────────────────────────────────────── --}}
<div class="card invoice-box border-0 shadow-sm mb-3" style="border-radius:12px; overflow:hidden;">
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
    <div class="card-body pb-2 report-border-top px-4 py-4" style="border-top:3px solid #1a237e;">
        <form method="GET" action="javascript:void(0);" id="reportForm">
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
                    <select name="service_id" id="serviceFilter" class="form-control custom-input select2">
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
                    <select name="provider_id" id="providerFilter" class="form-control custom-input select2">
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
                    <button type="button" class="btn btn-primary px-4 mr-2"
                            onclick="$('#reportTable').DataTable().ajax.reload(); return false;"
                            style="border-radius:8px; background:linear-gradient(135deg,#1a237e,#3949ab); border:none;">
                        <i class="fas fa-search mr-1"></i>Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-3"
                            onclick="window.print();" style="border-radius:8px;">
                        <i class="fas fa-print mr-1"></i>Print
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Results ──────────────────────────────────────────────────────────────── --}}

{{-- Summary Cards --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0 accent-primary" style="border-radius:10px; overflow:hidden; border-top:3px solid #1a237e;">
            <span class="info-box-icon elevation-1"
                  style="background:linear-gradient(135deg,#1a237e,#3949ab);">
                <i class="fas fa-list-ol text-white"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Lines</span>
                <span class="info-box-number font-weight-bold" id="totalLinesNum">0</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0 accent-success" style="border-radius:10px; overflow:hidden; border-top:3px solid #1b5e20;">
            <span class="info-box-icon elevation-1"
                  style="background:linear-gradient(135deg,#1b5e20,#388e3c);">
                <i class="fas fa-money-bill-wave text-white"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Grand Total</span>
                <span class="info-box-number font-weight-bold" id="grandTotalNum">৳ 0</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0 accent-danger" style="border-radius:10px; overflow:hidden; border-top:3px solid #bf360c;">
            <span class="info-box-icon elevation-1"
                  style="background:linear-gradient(135deg,#bf360c,#e64a19);">
                <i class="fas fa-tachometer-alt text-white"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Qty (MB)</span>
                <span class="info-box-number font-weight-bold" id="totalQtyNum">0</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0 accent-purple" style="border-radius:10px; overflow:hidden; border-top:3px solid #4a148c;">
            <span class="info-box-icon elevation-1"
                  style="background:linear-gradient(135deg,#4a148c,#7b1fa2);">
                <i class="fas fa-network-wired text-white"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Services / Providers</span>
                <span class="info-box-number font-weight-bold" id="servicesProviderNum">0 / 0</span>
            </div>
        </div>
    </div>
</div>

{{-- Results Table (customer-style) --}}
<div class="card invoice-box border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
    <div class="card-header border-0 py-3 px-4"
         style="background:linear-gradient(135deg,#1b5e20,#388e3c);">
        <div class="d-flex align-items-center">
            <i class="fas fa-table text-white mr-2"></i>
            <div>
                <h6 class="mb-0 text-white font-weight-bold">
                    Report Results
                </h6>
                <small class="text-white-50" id="dateRangeText">
                    &nbsp;
                </small>
            </div>
        </div>
    </div>
    <div class="card-body p-0" style="border-top:3px solid #1b5e20;">
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
                </tbody>
                <tfoot>
                    <tr style="background:#e8f5e9; border-top:2px solid #a5d6a7;">
                        <td colspan="6" class="font-weight-bold text-right py-3 pr-3"
                            style="font-size:13px; color:#1b5e20;">
                            Grand Total
                        </td>
                        <td class="text-right font-weight-bold py-3"
                            style="font-size:13px; color:#1b5e20;" id="footerQty">
                            0
                        </td>
                        <td colspan="2"></td>
                        <td class="text-right font-weight-bold py-3 pr-3"
                            style="font-size:15px; color:#1b5e20;" id="footerTotal">
                            ৳ 0
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ── Results ──────────────────────────────────────────────────────────────── --}}

{{-- Summary Cards --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
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
.rpt-row td { padding:14px 16px; vertical-align:middle; }
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

/* customer/invoice-style accents (used for report cards) */
.invoice-accent { color: #1a237e; }
.report-border-top { border-top-width: 3px; }
.section-label { color: #1b5e20; font-size: 11px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px; }

/* UI tweaks: larger icons, more spacing, accent overrides */
.info-box { padding: 12px !important; }
.info-box-icon { width:56px; height:56px; display:flex; align-items:center; justify-content:center; border-radius:8px; }
.info-box-icon i { font-size:22px !important; }
.info-box-content { padding-left:12px; }
.badge { padding: .4rem .65rem; font-size: .86rem; }

.info-box.accent-primary { border-top-color: #0b163d !important; }
.info-box.accent-success { border-top-color: #0b4c1a !important; }
.info-box.accent-danger { border-top-color: #7a1f08 !important; }
.info-box.accent-purple { border-top-color: #31104a !important; }

/* datepicker z-index */
.datepicker-dropdown { z-index:9999 !important; }

/* DataTables styling */
.dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: 4px !important; margin: 2px !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current { background: linear-gradient(135deg,#1a237e,#3949ab) !important; color: white !important; border: 1px solid #1a237e !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #e8eaf6 !important; color: #1a237e !important; border: 1px solid #3949ab !important; }
.dataTables_wrapper .dataTables_filter input { border-color: #d0d7e8 !important; border-radius: 8px !important; }
.dataTables_wrapper .dataTables_length select { border-color: #d0d7e8 !important; border-radius: 8px !important; }

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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
    // Datepicker init
    $('.datepicker').datepicker({ format: 'mm/dd/yyyy', autoclose: true });

    const svcBg  = ['#E3F2FD','#E8F5E9','#FFF3E0','#F3E5F5','#FCE4EC','#E0F7FA'];
    const svcTxt = ['#1565C0','#2E7D32','#E65100','#6A1B9A','#B71C1C','#006064'];
    const svcMap = {};
    let svcIdx = 0;

    function getServiceColor(svcName) {
        if (!svcMap[svcName]) {
            svcMap[svcName] = svcIdx % 6;
            svcIdx++;
        }
        return svcMap[svcName];
    }

    // Initialize DataTables with server-side processing
    const table = $('#reportTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            url: "{{ route('bandwidth-buy.report.datatables') }}",
            type: 'GET',
            data: function (d) {
                d.from_date = $('#fromDate').val();
                d.to_date = $('#toDate').val();
                d.service_id = $('#serviceFilter').val();
                d.provider_id = $('#providerFilter').val();
            }
        },
        columns: [
            { data: 'idx', name: 'idx' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'provider', name: 'provider' },
            { data: 'service', name: 'service' },
            { data: 'from_date', name: 'from_date' },
            { data: 'to_date', name: 'to_date' },
            { data: 'quantity_mb', name: 'quantity_mb' },
            { data: 'rate', name: 'rate' },
            { data: 'vat_percent', name: 'vat_percent' },
            { data: 'line_total', name: 'line_total' }
        ],
        rowCallback: function (row, data, index) {
            const ci = getServiceColor(data.service_name);
            const bgColor = svcBg[ci];
            const txtColor = svcTxt[ci];

            // Format row HTML
            $('td', row).eq(0).html(data.idx).addClass('text-center text-muted small');
            $('td', row).eq(1).html(`<span class="font-weight-bold text-primary" style="font-size:13px;">${data.invoice_no}</span>`);
            $('td', row).eq(2).html(`
                <div class="d-flex align-items-center">
                    <div class="prov-avatar mr-2" style="background:${bgColor}; color:${txtColor};">
                        ${data.provider.charAt(0).toUpperCase()}
                    </div>
                    <span style="font-size:13px;">${data.provider}</span>
                </div>
            `);
            $('td', row).eq(3).html(`
                <span class="svc-badge" style="background:${bgColor}; color:${txtColor};">
                    <i class="fas fa-wifi mr-1" style="font-size:10px;"></i>${data.service}
                </span>
            `);
            $('td', row).eq(4).html(`
                <span class="text-muted" style="font-size:13px;">
                    <i class="fas fa-calendar-alt mr-1" style="font-size:10px;"></i>${data.from_date}
                </span>
            `);
            $('td', row).eq(5).html(`
                <span class="text-muted" style="font-size:13px;">
                    <i class="fas fa-calendar-check mr-1" style="font-size:10px;"></i>${data.to_date}
                </span>
            `);
            $('td', row).eq(6).html(data.quantity_mb).addClass('text-right font-weight-bold').css('font-size', '13px');
            $('td', row).eq(7).html(data.rate).addClass('text-right').css('font-size', '13px');
            $('td', row).eq(8).html(`
                <span class="badge badge-light border" style="font-size:12px;">${data.vat_percent}%</span>
            `).addClass('text-center');
            $('td', row).eq(9).html(`৳ ${data.line_total}`).addClass('text-right font-weight-bold').css({'font-size': '14px', 'color': '#1b5e20'});

            $('tr', row).addClass('rpt-row');
        },
        drawCallback: function (settings) {
            updateSummaryCards();
        }
    });

    // Reload table on filter change
    $('#fromDate, #toDate, #serviceFilter, #providerFilter').on('change', function () {
        table.ajax.reload();
    });

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

        // Reload table
        table.ajax.reload();
    });

    // Update summary cards
    function updateSummaryCards() {
        const data = table.rows({page:'current'}).data();
        let totalLines = 0;
        let grandTotal = 0;
        let totalQty = 0;

        data.each(function (row) {
            totalLines++;
            const lineTotal = parseFloat(row.line_total.replace(/,/g, ''));
            const qty = parseFloat(row.quantity_mb.replace(/,/g, ''));
            grandTotal += isNaN(lineTotal) ? 0 : lineTotal;
            totalQty += isNaN(qty) ? 0 : qty;
        });

        $('#totalLinesNum').text(data.length);
        $('#grandTotalNum').html('৳ ' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#totalQtyNum').text(totalQty.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#footerQty').text(totalQty.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#footerTotal').html('৳ ' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('#dateRangeText').text($('#fromDate').val() + ' — ' + $('#toDate').val());
    }

    // Initial load
    updateSummaryCards();
});
</script>
@stop
            <div class="info-box shadow-sm mb-0 accent-danger" style="border-radius:10px; overflow:hidden; border-top:3px solid #bf360c;">
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
            <div class="info-box shadow-sm mb-0 accent-purple" style="border-radius:10px; overflow:hidden; border-top:3px solid #4a148c;">
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

    {{-- Results Table (customer-style) --}}
    <div class="card invoice-box border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
        <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center"
             style="background:linear-gradient(135deg,#1b5e20,#388e3c);">
            <div class="d-flex align-items-center">
                <i class="fas fa-table text-white mr-2"></i>
                <div>
                    <h6 class="mb-0 text-white font-weight-bold">
                        Report Results
                        <span class="badge badge-light text-success ml-2">
                            {{ $results->total() }} line(s)
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
        <div class="card-body p-0" style="border-top:3px solid #1b5e20;">
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
                                {{ number_format($summary['total_qty_mb'] ?? $results->sum('quantity_mb'), 2) }}
                            </td>
                            <td colspan="2"></td>
                            <td class="text-right font-weight-bold py-3 pr-3"
                                style="font-size:15px; color:#1b5e20;">
                                ৳ {{ number_format($summary['grand_total'] ?? $results->sum('line_total'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

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

/* customer/invoice-style accents (used for report cards) */
.invoice-accent { color: #1a237e; }
.report-border-top { border-top-width: 3px; }
.section-label { color: #1b5e20; font-size: 11px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px; }

/* UI tweaks: larger icons, more spacing, accent overrides */
.info-box { padding: 12px !important; }
.info-box-icon { width:56px; height:56px; display:flex; align-items:center; justify-content:center; border-radius:8px; }
.info-box-icon i { font-size:22px !important; }
.info-box-content { padding-left:12px; }
.rpt-row td { padding:14px 16px; }
.badge { padding: .4rem .65rem; font-size: .86rem; }

.info-box.accent-primary { border-top-color: #0b163d !important; }
.info-box.accent-success { border-top-color: #0b4c1a !important; }
.info-box.accent-danger { border-top-color: #7a1f08 !important; }
.info-box.accent-purple { border-top-color: #31104a !important; }

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
