{{-- resources/views/bandwidth-sale/invoices/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice — {{ $bwsInvoice->invoice_no }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size:12px; color:#2c3e50; background:#fff; }

        /* ── Layout ── */
        .page { padding:30px 35px; }

        /* ── Header ── */
        .header { display:table; width:100%; margin-bottom:25px; border-bottom:3px solid #0073b7; padding-bottom:15px; }
        .header-left  { display:table-cell; width:60%; vertical-align:top; }
        .header-right { display:table-cell; width:40%; vertical-align:top; text-align:right; }

        .company-name { font-size:22px; font-weight:700; color:#0073b7; }
        .company-sub  { font-size:11px; color:#666; margin-top:3px; }

        .inv-title  { font-size:20px; font-weight:700; color:#2c3e50; }
        .inv-no     { font-size:13px; color:#0073b7; font-weight:700; margin-top:4px; }
        .inv-status { display:inline-block; padding:3px 12px; border-radius:20px; font-size:11px;
                      font-weight:700; color:#fff; margin-top:6px; }

        /* ── Info Grid ── */
        .info-grid { display:table; width:100%; margin-bottom:20px; }
        .info-left  { display:table-cell; width:50%; vertical-align:top; }
        .info-right { display:table-cell; width:50%; vertical-align:top; padding-left:20px; }

        .info-box { background:#f8f9fa; border-left:4px solid #0073b7;
                    border-radius:4px; padding:12px 15px; }
        .info-box h4 { font-size:11px; text-transform:uppercase; color:#0073b7;
                       font-weight:700; letter-spacing:.5px; margin-bottom:8px; }
        .info-row { margin-bottom:4px; }
        .info-label { color:#666; font-size:11px; display:inline-block; width:110px; }
        .info-val   { font-weight:600; font-size:12px; }

        /* ── Items Table ── */
        .items-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        .items-table thead tr { background:#2c3e50; color:#fff; }
        .items-table th { padding:9px 10px; text-align:left; font-size:11px;
                          font-weight:700; letter-spacing:.3px; }
        .items-table th.text-right,
        .items-table td.text-right { text-align:right; }
        .items-table th.text-center,
        .items-table td.text-center { text-align:center; }
        .items-table tbody tr { border-bottom:1px solid #e9ecef; }
        .items-table tbody tr:nth-child(even) { background:#f8f9fa; }
        .items-table td { padding:8px 10px; font-size:12px; }
        .items-table tfoot tr { background:#e8f4fd; font-weight:700; }
        .items-table tfoot td { padding:9px 10px; }

        /* ── Summary ── */
        .summary-wrap { display:table; width:100%; margin-bottom:20px; }
        .summary-notes { display:table-cell; width:55%; vertical-align:top; }
        .summary-box   { display:table-cell; width:45%; vertical-align:top; padding-left:20px; }

        .summary-table { width:100%; border-collapse:collapse; }
        .summary-table td { padding:6px 10px; font-size:12px; border:1px solid #dee2e6; }
        .summary-table .label-col { color:#555; width:55%; }
        .summary-table .val-col   { text-align:right; font-weight:600; }
        .summary-table .grand-row { background:#e8f4fd; }
        .summary-table .grand-row td { font-size:14px; font-weight:700; color:#0073b7; }
        .summary-table .due-row td   { background:#fff3cd; font-weight:700; color:#dc3545; }

        .notes-box { background:#f8f9fa; border:1px solid #dee2e6; border-radius:4px;
                     padding:12px 15px; font-size:11px; color:#555; }
        .notes-box h4 { font-size:11px; font-weight:700; color:#2c3e50; margin-bottom:6px; }

        /* ── Payments ── */
        .payments-title { font-size:13px; font-weight:700; color:#2c3e50;
                          border-left:4px solid #00a65a; padding-left:10px;
                          margin-bottom:10px; }
        .pay-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        .pay-table thead tr { background:#2c3e50; color:#fff; }
        .pay-table th { padding:7px 10px; font-size:11px; font-weight:700; }
        .pay-table td { padding:7px 10px; font-size:11px; border-bottom:1px solid #e9ecef; }
        .pay-table td.text-right { text-align:right; }
        .pay-table .badge { padding:2px 8px; border-radius:10px; color:#fff; font-size:10px; }
        .badge-success { background:#00a65a; }
        .badge-secondary { background:#6c757d; }

        /* ── Footer ── */
        .footer { border-top:1px solid #dee2e6; padding-top:15px; margin-top:10px;
                  font-size:10px; color:#888; text-align:center; }

        /* ── Status colors ── */
        .status-paid    { background:#00a65a; }
        .status-partial { background:#17a2b8; }
        .status-overdue { background:#dc3545; }
        .status-unpaid  { background:#f39c12; }

        .text-danger  { color:#dc3545; }
        .text-success { color:#00a65a; }
        .text-warning { color:#f39c12; }
        .text-primary { color:#0073b7; }
    </style>
</head>
<body>
<div class="page">

    {{-- ══ HEADER ══════════════════════════════════════════════════ --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ config('app.name', 'ISP Management') }}</div>
            <div class="company-sub">Bandwidth Sale Invoice</div>
        </div>
        <div class="header-right">
            <div class="inv-title">INVOICE</div>
            <div class="inv-no">{{ $bwsInvoice->invoice_no }}</div>
            @php
                $statusMap = [
                    'paid'    => ['class'=>'status-paid',   'label'=>'Paid'],
                    'partial' => ['class'=>'status-partial','label'=>'Partial'],
                    'overdue' => ['class'=>'status-overdue','label'=>'Overdue'],
                    'unpaid'  => ['class'=>'status-unpaid', 'label'=>'Unpaid'],
                ];
                $sc = $statusMap[$bwsInvoice->status] ?? $statusMap['unpaid'];
            @endphp
            <span class="inv-status {{ $sc['class'] }}">{{ $sc['label'] }}</span>
        </div>
    </div>

    {{-- ══ INFO GRID ═══════════════════════════════════════════════ --}}
    <div class="info-grid">
        <div class="info-left">
            <div class="info-box">
                <h4>Bill To</h4>
                <div class="info-row">
                    <span class="info-label">Customer</span>
                    <span class="info-val">{{ $bwsInvoice->bwsCustomer->customer_name }}</span>
                </div>
                @if($bwsInvoice->bwsCustomer->contact_person)
                <div class="info-row">
                    <span class="info-label">Contact</span>
                    <span class="info-val">{{ $bwsInvoice->bwsCustomer->contact_person }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Mobile</span>
                    <span class="info-val">{{ $bwsInvoice->bwsCustomer->mobile_number }}</span>
                </div>
                @if($bwsInvoice->bwsCustomer->email)
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-val">{{ $bwsInvoice->bwsCustomer->email }}</span>
                </div>
                @endif
                @if($bwsInvoice->bwsCustomer->address)
                <div class="info-row">
                    <span class="info-label">Address</span>
                    <span class="info-val">{{ $bwsInvoice->bwsCustomer->address }}</span>
                </div>
                @endif
            </div>
        </div>
        <div class="info-right">
            <div class="info-box">
                <h4>Invoice Info</h4>
                <div class="info-row">
                    <span class="info-label">Invoice No</span>
                    <span class="info-val">{{ $bwsInvoice->invoice_no }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Billing Month</span>
                    <span class="info-val">
                        {{ \Carbon\Carbon::parse($bwsInvoice->billing_month.'-01')->format('F Y') }}
                    </span>
                </div>
                @if($bwsInvoice->payment_due)
                <div class="info-row">
                    <span class="info-label">Payment Due</span>
                    <span class="info-val">
                        {{ optional($bwsInvoice->payment_due)->format('d M Y') }}
                    </span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Daily Basis</span>
                    <span class="info-val">{{ $bwsInvoice->daily_basis ? 'Yes' : 'No' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created By</span>
                    <span class="info-val">{{ $bwsInvoice->createdBy->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created On</span>
                    <span class="info-val">{{ optional($bwsInvoice->created_at)->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ ITEMS TABLE ════════════════════════════════════════════ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Item / Service</th>
                <th>Description</th>
                <th class="text-center">Unit</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Rate</th>
                <th class="text-right">VAT%</th>
                @if($bwsInvoice->daily_basis)
                <th class="text-center">From</th>
                <th class="text-center">To</th>
                @endif
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bwsInvoice->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->item_name ?? '—' }}</td>
                <td>{{ $item->description ?? '—' }}</td>
                <td class="text-center">{{ $item->unit ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td class="text-right">{{ $item->vat_percent }}%</td>
                @if($bwsInvoice->daily_basis)
                <td class="text-center">{{ optional($item->from_date)->format('d M Y') ?? '—' }}</td>
                <td class="text-center">{{ optional($item->to_date)->format('d M Y') ?? '—' }}</td>
                @endif
                <td class="text-right"><strong>{{ number_format($item->total, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ $bwsInvoice->daily_basis ? 9 : 7 }}"
                    class="text-right">Invoice Total</td>
                <td class="text-right text-primary">
                    Tk. {{ number_format($bwsInvoice->total_amount + $bwsInvoice->vat_amount, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- ══ SUMMARY + NOTES ════════════════════════════════════════ --}}
    <div class="summary-wrap">
        <div class="summary-notes">
            @if($bwsInvoice->notes)
            <div class="notes-box">
                <h4>Notes / Remarks</h4>
                {{ $bwsInvoice->notes }}
            </div>
            @endif
        </div>
        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td class="label-col">Sub Total</td>
                    <td class="val-col">Tk. {{ number_format($bwsInvoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label-col">VAT</td>
                    <td class="val-col">Tk. {{ number_format($bwsInvoice->vat_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label-col">Invoice Total</td>
                    <td class="val-col font-weight-bold">
                        Tk. {{ number_format($bwsInvoice->total_amount + $bwsInvoice->vat_amount, 2) }}
                    </td>
                </tr>
                @if($bwsInvoice->discount > 0)
                <tr>
                    <td class="label-col">Discount</td>
                    <td class="val-col text-warning">(Tk. {{ number_format($bwsInvoice->discount, 2) }})</td>
                </tr>
                @endif
                <tr class="grand-row">
                    <td class="label-col">Grand Total</td>
                    <td class="val-col">Tk. {{ number_format($bwsInvoice->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="label-col">Total Received</td>
                    <td class="val-col text-success">Tk. {{ number_format($bwsInvoice->received_amount, 2) }}</td>
                </tr>
                <tr class="due-row">
                    <td class="label-col">Balance Due</td>
                    <td class="val-col">Tk. {{ number_format($bwsInvoice->due_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ══ PAYMENT HISTORY ════════════════════════════════════════ --}}
    @if($bwsInvoice->activePayments->count() > 0)
    <div class="payments-title">Payment History</div>
    <table class="pay-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Payment No</th>
                <th>Date</th>
                <th>Method</th>
                <th>Transaction No</th>
                <th>Received By</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bwsInvoice->activePayments as $i => $pay)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pay->payment_no }}</td>
                <td>{{ optional($pay->received_date)->format('d M Y') }}</td>
                <td>{{ strtoupper($pay->payment_method) }}</td>
                <td>{{ $pay->receipt_transaction_no ?? '—' }}</td>
                <td>{{ $pay->receivedBy->name ?? '—' }}</td>
                <td class="text-right">{{ number_format($pay->discount, 2) }}</td>
                <td class="text-right"><strong>Tk. {{ number_format($pay->received_amount, 2) }}</strong></td>
                <td>
                    <span class="badge {{ $pay->isVoid() ? 'badge-secondary' : 'badge-success' }}">
                        {{ $pay->isVoid() ? 'Void' : 'Active' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ══ FOOTER ════════════════════════════════════════════════ --}}
    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp;
        {{ $bwsInvoice->invoice_no }} &nbsp;|&nbsp;
        {{ config('app.name', 'ISP Management') }}
    </div>

</div>
</body>
</html>
