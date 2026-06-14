{{-- resources/views/client/invoice-detail.blade.php --}}
@extends('client.layout')
@section('title', 'ইনভয়েস বিবরণ')

@section('content')

<div style="margin-bottom:1rem;">
    <a href="{{ route('client.invoices') }}" style="color:#6b7280; font-size:13px; text-decoration:none;">
        <i class="fas fa-arrow-left"></i> বিলের তালিকায় ফিরুন
    </a>
</div>

<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fas fa-file-invoice" style="color:#1a56db;"></i> {{ $invoice->invoice_no }}</span>
        @php
            $statusMap = [
                'paid'    => ['badge-success', 'পরিশোধিত'],
                'partial' => ['badge-warning', 'আংশিক'],
                'overdue' => ['badge-danger',  'বিলম্বিত'],
                'unpaid'  => ['badge-danger',  'বাকি'],
            ];
            [$badgeClass, $statusBn] = $statusMap[$invoice->status] ?? ['badge-secondary', ucfirst($invoice->status)];
        @endphp
        <span class="badge {{ $badgeClass }}">{{ $statusBn }}</span>
    </div>
    <div class="card-body">

        {{-- Invoice Info --}}
        <table style="width:100%; font-size:13px; margin-bottom:1rem;">
            <tr>
                <td style="color:#6b7280; padding:5px 0; width:45%;">গ্রাহকের নাম</td>
                <td>{{ $customer->name }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">Customer ID</td>
                <td>{{ $customer->customer_code }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">প্যাকেজ</td>
                <td>{{ $invoice->package->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">মাস / মেয়াদ</td>
                <td>{{ $invoice->period_label }}</td>
            </tr>
            <tr>
                <td style="color:#6b7280; padding:5px 0;">শেষ পরিশোধের তারিখ</td>
                <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
            </tr>
        </table>

        <hr style="border:none; border-top:1px solid #e5e7eb; margin:1rem 0;">

        {{-- Amount breakdown --}}
        <table style="width:100%; font-size:13px;">
            <tr>
                <td style="padding:5px 0; color:#374151;">বিলের পরিমাণ</td>
                <td style="text-align:right;">৳{{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->discount > 0)
            <tr>
                <td style="padding:5px 0; color:#374151;">ছাড়</td>
                <td style="text-align:right; color:#16a34a;">- ৳{{ number_format($invoice->discount, 2) }}</td>
            </tr>
            @endif
            <tr style="border-top:2px solid #111;">
                <td style="padding:8px 0 5px; font-weight:600;">বকেয়া পরিমাণ</td>
                <td style="text-align:right; font-weight:600; font-size:18px; color:{{ $invoice->due_amount > 0 ? '#dc2626' : '#16a34a' }};">
                    ৳{{ number_format($invoice->due_amount, 2) }}
                </td>
            </tr>
        </table>

        {{-- Payment history --}}
        @if($invoice->payments->where('status', 'active')->count() > 0)
        <div style="margin-top:1.25rem;">
            <div style="font-size:13px; font-weight:500; margin-bottom:8px; color:#374151;">
                <i class="fas fa-history" style="color:#1a56db;"></i> পেমেন্ট ইতিহাস
            </div>
            <table style="width:100%; font-size:12px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:7px 10px; text-align:left; color:#6b7280; font-weight:500;">পরিমাণ</th>
                        <th style="padding:7px 10px; text-align:left; color:#6b7280; font-weight:500;">পদ্ধতি</th>
                        <th style="padding:7px 10px; text-align:left; color:#6b7280; font-weight:500;">তারিখ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments->where('status', 'active') as $pay)
                    <tr>
                        <td style="padding:7px 10px; color:#16a34a; font-weight:500;">৳{{ number_format($pay->amount, 2) }}</td>
                        <td style="padding:7px 10px;">{{ strtoupper($pay->method) }}</td>
                        <td style="padding:7px 10px;">{{ optional($pay->paid_at)->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($invoice->due_amount > 0)
        <div style="margin-top:1.25rem; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:12px 14px; font-size:13px; color:#9a3412;">
            <i class="fas fa-info-circle"></i>
            বিল পরিশোধের জন্য আপনার ISP অফিসে যোগাযোগ করুন অথবা bKash/Nagad এ পেমেন্ট করুন।<br>
            <strong>{{ \App\Models\Setting::get('company_phone', '') }}</strong>
        </div>
        @endif

        @if($footerText)
        <div style="margin-top:1rem; font-size:12px; color:#9ca3af; text-align:center;">
            {{ $footerText }}
        </div>
        @endif
    </div>
</div>

@endsection
