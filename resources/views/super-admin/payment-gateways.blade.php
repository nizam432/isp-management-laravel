{{-- resources/views/super-admin/payment-gateways.blade.php --}}
@extends('adminlte::page')

@section('title', 'Payment Gateway Management')

@section('content_header')
    <h1><i class="fas fa-credit-card mr-2"></i> Payment Gateway Management</h1>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @php
        $local         = $gateways->where('type','local');
        $international = $gateways->where('type','international');
        $enabledCount  = $gateways->where('is_enabled',true)->count();

        $meta = [
            'bkash'      => ['color'=>'#E2136E','icon'=>'fas fa-mobile-alt'],
            'nagad'      => ['color'=>'#F05A22','icon'=>'fas fa-mobile-alt'],
            'sslcommerz' => ['color'=>'#0B6E4F','icon'=>'fas fa-credit-card'],
            'amarpayz'   => ['color'=>'#FF6B00','icon'=>'fas fa-credit-card'],
            'stripe'     => ['color'=>'#6772E5','icon'=>'fab fa-stripe-s'],
            'paypal'     => ['color'=>'#003087','icon'=>'fab fa-paypal'],
            'razorpay'   => ['color'=>'#072654','icon'=>'fas fa-credit-card'],
        ];
    @endphp

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-credit-card mr-2 text-primary"></i>
                <strong>Payment Gateways — ISP দের জন্য Enable/Disable</strong>
            </div>
            <span class="badge badge-info badge-pill px-3 py-2" style="font-size:.85rem">
                {{ $enabledCount }} / {{ $gateways->count() }} Enabled
            </span>
        </div>

        <div class="card-body p-0">

            {{-- LOCAL ───────────────────────────────────────────── --}}
            <div class="px-3 pt-3 pb-1">
                <span class="badge badge-secondary px-3 py-1" style="font-size:.8rem;letter-spacing:.05em">
                    <i class="fas fa-map-marker-alt mr-1"></i> LOCAL — Bangladesh
                </span>
            </div>

            <table class="table table-hover mb-0">
                <thead style="background:#f8f9fa">
                    <tr>
                        <th width="240">Gateway</th>
                        <th>Description</th>
                        <th width="180">ISP Status</th>
                        <th width="160">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($local as $gw)
                @php $m = $meta[$gw->slug] ?? ['color'=>'#6c757d','icon'=>'fas fa-credit-card']; @endphp
                <tr>
                    <td class="align-middle">
                        <i class="{{ $m['icon'] }} mr-2" style="color:{{ $m['color'] }}"></i>
                        <span class="font-weight-bold">{{ $gw->name }}</span>
                        <div><small class="text-muted">{{ $gw->slug }}</small></div>
                    </td>
                    <td class="align-middle text-muted small">{{ $gw->description }}</td>
                    <td class="align-middle">
                        @if($gw->is_enabled)
                            <span class="badge badge-success px-3 py-2">
                                <i class="fas fa-check mr-1"></i> ISP দেখতে পাবে
                            </span>
                        @else
                            <span class="badge badge-danger px-3 py-2">
                                <i class="fas fa-times mr-1"></i> ISP দেখতে পাবে না
                            </span>
                        @endif
                    </td>
                    <td class="align-middle">
                        <form method="POST" action="{{ route('super-admin.payment-gateways.toggle', $gw) }}">
                            @csrf
                            <button class="btn btn-sm {{ $gw->is_enabled ? 'btn-danger' : 'btn-success' }}">
                                @if($gw->is_enabled)
                                    <i class="fas fa-ban mr-1"></i> বন্ধ করুন
                                @else
                                    <i class="fas fa-check mr-1"></i> চালু করুন
                                @endif
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>

            {{-- INTERNATIONAL ────────────────────────────────────── --}}
            <div class="px-3 pt-3 pb-1 border-top mt-2">
                <span class="badge badge-primary px-3 py-1" style="font-size:.8rem;letter-spacing:.05em">
                    <i class="fas fa-globe mr-1"></i> INTERNATIONAL
                </span>
            </div>

            <table class="table table-hover mb-0">
                <tbody>
                @foreach($international as $gw)
                @php $m = $meta[$gw->slug] ?? ['color'=>'#6c757d','icon'=>'fas fa-credit-card']; @endphp
                <tr>
                    <td class="align-middle" width="240">
                        <i class="{{ $m['icon'] }} mr-2" style="color:{{ $m['color'] }}"></i>
                        <span class="font-weight-bold">{{ $gw->name }}</span>
                        <div><small class="text-muted">{{ $gw->slug }}</small></div>
                    </td>
                    <td class="align-middle text-muted small">{{ $gw->description }}</td>
                    <td class="align-middle" width="180">
                        @if($gw->is_enabled)
                            <span class="badge badge-success px-3 py-2">
                                <i class="fas fa-check mr-1"></i> ISP দেখতে পাবে
                            </span>
                        @else
                            <span class="badge badge-danger px-3 py-2">
                                <i class="fas fa-times mr-1"></i> ISP দেখতে পাবে না
                            </span>
                        @endif
                    </td>
                    <td class="align-middle" width="160">
                        <form method="POST" action="{{ route('super-admin.payment-gateways.toggle', $gw) }}">
                            @csrf
                            <button class="btn btn-sm {{ $gw->is_enabled ? 'btn-danger' : 'btn-success' }}">
                                @if($gw->is_enabled)
                                    <i class="fas fa-ban mr-1"></i> বন্ধ করুন
                                @else
                                    <i class="fas fa-check mr-1"></i> চালু করুন
                                @endif
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer text-muted small">
            <i class="fas fa-info-circle mr-1"></i>
            Enable করলে ISP Admin তাদের Settings → Payment Gateways এ গিয়ে credentials যোগ করতে পারবে।
        </div>
    </div>

</div>
@endsection
