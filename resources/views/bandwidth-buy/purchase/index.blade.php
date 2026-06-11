@extends('adminlte::page')
@section('title', 'Purchase List')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>Purchase Bills
            </h4>
            <small class="text-muted">Upstream bandwidth purchase invoices</small>
        </div>
        <a href="{{ route('bandwidth-buy.purchase.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> New Purchase
        </a>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif

{{-- ── Summary Cards ──────────────────────────────────────────────────────── --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-file-invoice"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Bills</span>
                <span class="info-box-number">{{ $purchases->total() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Paid</span>
                <span class="info-box-number">৳ {{ number_format($purchases->sum('paid')) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Due</span>
                <span class="info-box-number">৳ {{ number_format($purchases->sum('due')) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm mb-0">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Grand Total</span>
                <span class="info-box-number">৳ {{ number_format($purchases->sum('sub_total')) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Table Card ──────────────────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Purchase List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Search invoice / provider..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="purchaseTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="text-center" style="width:50px;">#</th>
                        <th>Invoice No</th>
                        <th>Provider</th>
                        <th class="text-center">Document</th>
                        <th>Billing Date</th>
                        <th class="text-right">Sub Total (৳)</th>
                        <th class="text-right">Paid (৳)</th>
                        <th class="text-right">Due (৳)</th>
                        <th class="text-center" style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="purchaseTableBody">
                    @forelse($purchases as $i => $p)
                    <tr>
                        <td class="text-center text-muted small">{{ $purchases->firstItem() + $i }}</td>
                        <td>
                            <span class="font-weight-bold text-primary">{{ $p->invoice_no }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-2"
                                     style="width:32px;height:32px;border-radius:50%;
                                            background:{{ ['#1976D2','#388E3C','#F57C00','#7B1FA2','#C62828','#00838F'][($p->provider_id - 1) % 6] }};
                                            display:flex;align-items:center;justify-content:center;
                                            color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">
                                    {{ strtoupper(substr($p->provider->company_name ?? 'P', 0, 1)) }}
                                </div>
                                <span class="font-weight-bold">{{ $p->provider->company_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($p->document)
                                <img src="{{ asset('storage/'.$p->document) }}"
                                     style="height:40px;width:60px;object-fit:contain;border-radius:4px;
                                            border:1px solid #eee;background:#fff;padding:2px;">
                            @else
                                <span class="badge badge-light border text-muted small">No Image</span>
                            @endif
                        </td>
                        <td>
                            <i class="fas fa-calendar-alt text-muted mr-1"></i>
                            {{ $p->billing_date->format('d M Y') }}
                        </td>
                        <td class="text-right font-weight-bold">
                            {{ number_format($p->sub_total, 2) }}
                        </td>
                        <td class="text-right text-success font-weight-bold">
                            {{ number_format($p->paid, 2) }}
                        </td>
                        <td class="text-right font-weight-bold {{ $p->due > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ number_format($p->due, 2) }}
                            @if($p->due > 0)
                                <i class="fas fa-exclamation-circle ml-1"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            {{-- View Lines Button --}}
                            <button class="btn btn-sm btn-info btn-view-lines px-2"
                                    data-id="{{ $p->id }}"
                                    data-invoice="{{ $p->invoice_no }}"
                                    data-provider="{{ $p->provider->company_name ?? '' }}"
                                    data-date="{{ $p->billing_date->format('d M Y') }}"
                                    data-total="{{ number_format($p->sub_total, 2) }}"
                                    data-paid="{{ number_format($p->paid, 2) }}"
                                    data-due="{{ number_format($p->due, 2) }}"
                                    data-bank="{{ $p->bank_account ?? '' }}"
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            {{-- Edit Button --}}
                            <a href="{{ route('bandwidth-buy.purchase.edit', $p) }}"
                               class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No purchase bills found. Click <strong>+ New Purchase</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($purchases->hasPages())
    <div class="card-footer bg-light py-2">
        {{ $purchases->links() }}
    </div>
    @endif
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LINE ITEMS VIEW MODAL                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="linesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header text-white border-0 py-3"
                 style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%); border-radius:8px 8px 0 0;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-list-alt mr-2"></i>
                        Invoice: <span id="mInvoice" class="text-warning"></span>
                    </h5>
                    <small class="text-white-50">
                        <i class="fas fa-building mr-1"></i><span id="mProvider"></span>
                        &nbsp;|&nbsp;
                        <i class="fas fa-calendar mr-1"></i><span id="mDate"></span>
                    </small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-0">

                {{-- Summary bar --}}
                <div class="d-flex border-bottom px-4 py-3" style="background:#f8f9fa; gap:24px; flex-wrap:wrap;">
                    <div class="text-center">
                        <div class="text-muted small">Sub Total</div>
                        <div class="font-weight-bold text-dark h6 mb-0" id="mTotal"></div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted small">Paid</div>
                        <div class="font-weight-bold text-success h6 mb-0" id="mPaid"></div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted small">Due</div>
                        <div class="font-weight-bold h6 mb-0" id="mDue"></div>
                    </div>
                    <div class="text-center" id="mBankWrap" style="display:none!important;">
                        <div class="text-muted small">Bank Account</div>
                        <div class="font-weight-bold text-dark small mb-0" id="mBank"></div>
                    </div>
                </div>

                {{-- Lines table --}}
                <div class="table-responsive px-0">
                    <table class="table table-hover mb-0" id="linesTable">
                        <thead>
                            <tr style="background:#f1f3f8; border-bottom:2px solid #dee2e6;">
                                <th class="text-center pl-4" style="width:50px;">#</th>
                                <th>Service</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th class="text-right">Qty (MB)</th>
                                <th class="text-right">Rate (৳)</th>
                                <th class="text-center">VAT (%)</th>
                                <th class="text-right pr-4">Line Total (৳)</th>
                            </tr>
                        </thead>
                        <tbody id="linesTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Loading...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="linesTableFoot" style="display:none;">
                            <tr style="background:#e8eaf6;">
                                <td colspan="7" class="text-right font-weight-bold pr-3">Grand Total</td>
                                <td class="text-right font-weight-bold pr-4" id="mGrandTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 bg-light px-4" style="border-radius:0 0 8px 8px;">
                <a href="#" id="mEditBtn" class="btn btn-warning px-4">
                    <i class="fas fa-edit mr-1"></i> Edit Purchase
                </a>
                <button type="button" class="btn btn-light border px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    #purchaseTable tbody tr:hover { background:#f0f4ff !important; }
    #purchaseTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        padding: 10px 12px;
    }
    #purchaseTable tbody td { padding: 10px 12px; vertical-align: middle; }

    #linesTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #555;
        padding: 10px 12px;
    }
    #linesTable tbody td { padding: 10px 12px; vertical-align: middle; }
    #linesTable tbody tr:hover { background: #f0f4ff !important; }

    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
    .modal-content { border-radius: 10px; overflow: hidden; }

    /* Toastr on top */
    #toast-container { z-index: 99999 !important; }
    .toast { z-index: 99999 !important; }

    /* service badge */
    .svc-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .4px;
    }
</style>
@stop

@section('js')
<script>
const CSRF = '{{ csrf_token() }}';

// Per-purchase line data — PHP renders into JS
const PURCHASE_LINES = {
    @foreach($purchases as $p)
    {{ $p->id }}: [
        @foreach($p->lines as $line)
        {
            service: "{{ $line->service->name ?? '—' }}",
            from_date: "{{ $line->from_date->format('d M Y') }}",
            to_date:   "{{ $line->to_date->format('d M Y') }}",
            qty:       "{{ number_format($line->quantity_mb, 2) }}",
            rate:      "{{ number_format($line->rate, 2) }}",
            vat:       "{{ number_format($line->vat_percent, 2) }}",
            total:     "{{ number_format($line->line_total, 2) }}",
        },
        @endforeach
    ],
    @endforeach
};

const SVC_COLORS = [
    {bg:'#E3F2FD',txt:'#1565C0'},
    {bg:'#E8F5E9',txt:'#2E7D32'},
    {bg:'#FFF3E0',txt:'#E65100'},
    {bg:'#F3E5F5',txt:'#6A1B9A'},
    {bg:'#FCE4EC',txt:'#B71C1C'},
    {bg:'#E0F7FA',txt:'#006064'},
];
let svcColorMap = {};
let svcColorIdx = 0;
function svcColor(name) {
    if (!svcColorMap[name]) {
        svcColorMap[name] = SVC_COLORS[svcColorIdx % SVC_COLORS.length];
        svcColorIdx++;
    }
    return svcColorMap[name];
}

$(function () {

    toastr.options = {
        closeButton: true, progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3500, preventDuplicates: true,
    };

    // ── Live search ───────────────────────────────────────────────────────────
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#purchaseTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    // ── Open Line Items Modal ─────────────────────────────────────────────────
    $(document).on('click', '.btn-view-lines', function () {
        const $btn    = $(this);
        const id      = $btn.data('id');
        const invoice = $btn.data('invoice');
        const due     = $btn.data('due');
        const bank    = $btn.data('bank');

        // Populate header
        $('#mInvoice').text(invoice);
        $('#mProvider').text($btn.data('provider'));
        $('#mDate').text($btn.data('date'));
        $('#mTotal').text('৳ ' + $btn.data('total'));
        $('#mPaid').text('৳ ' + $btn.data('paid'));

        const dueNum = parseFloat($btn.data('due').replace(/,/g,''));
        $('#mDue').text('৳ ' + due)
                  .removeClass('text-danger text-muted')
                  .addClass(dueNum > 0 ? 'text-danger' : 'text-muted');

        if (bank) {
            $('#mBank').text(bank);
            $('#mBankWrap').show();
        } else {
            $('#mBankWrap').hide();
        }

        // Edit link
        $('#mEditBtn').attr('href', `/bandwidth-buy/purchase/${id}/edit`);

        // Populate lines from pre-rendered JS data
        const lines = PURCHASE_LINES[id] || [];
        let grandTotal = 0;

        if (lines.length === 0) {
            $('#linesTableBody').html(`
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox mr-1"></i> No line items found.
                    </td>
                </tr>`);
            $('#linesTableFoot').hide();
        } else {
            let html = '';
            lines.forEach(function (line, idx) {
                const c = svcColor(line.service);
                html += `
                <tr>
                    <td class="text-center text-muted small pl-4">${idx + 1}</td>
                    <td>
                        <span class="svc-badge"
                              style="background:${c.bg};color:${c.txt};">
                            <i class="fas fa-wifi mr-1"></i>${line.service}
                        </span>
                    </td>
                    <td><i class="fas fa-calendar-alt text-muted mr-1"></i>${line.from_date}</td>
                    <td><i class="fas fa-calendar-alt text-muted mr-1"></i>${line.to_date}</td>
                    <td class="text-right font-weight-bold">${line.qty}</td>
                    <td class="text-right">${line.rate}</td>
                    <td class="text-center">
                        <span class="badge badge-light border">${line.vat}%</span>
                    </td>
                    <td class="text-right font-weight-bold pr-4">${line.total}</td>
                </tr>`;
                grandTotal += parseFloat(line.total.replace(/,/g,''));
            });

            $('#linesTableBody').html(html);
            $('#mGrandTotal').text('৳ ' + grandTotal.toLocaleString('en-US', {minimumFractionDigits:2}));
            $('#linesTableFoot').show();
        }

        $('#linesModal').modal('show');
    });

});
</script>
@stop
