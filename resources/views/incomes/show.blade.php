{{-- resources/views/incomes/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Income: ' . $income->income_no)
@section('page_actions')
    <a href="{{ route('incomes.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    @if(!$income->isVoid())
        <button type="button" class="btn btn-danger btn-sm ml-1" id="btnVoidIncome">
            <i class="fas fa-ban mr-1"></i> Void
        </button>
    @endif
@endsection
@section('page_content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice-dollar mr-1"></i>
                    <code>{{ $income->income_no }}</code>
                </h3>
                {!! $income->statusBadge !!}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width:40%">Date</td>
                                <td class="font-weight-bold">{{ $income->income_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Category</td>
                                <td>
                                    @if($income->category)
                                        <span class="badge" style="{{ $income->category->badgeStyle }}">
                                            {{ $income->category->name }}
                                        </span>
                                    @else — @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Amount</td>
                                <td class="font-weight-bold text-success" style="font-size:20px">
                                    {{ $income->formattedAmount }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment Method</td>
                                <td>
                                    <span class="badge badge-light border">
                                        {{ strtoupper($income->payment_method) }}
                                    </span>
                                </td>
                            </tr>
                            @if($income->transaction_id)
                            <tr>
                                <td class="text-muted">Transaction ID</td>
                                <td><code>{{ $income->transaction_id }}</code></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            @if($income->customer)
                            <tr>
                                <td class="text-muted" style="width:40%">Customer</td>
                                <td>
                                    <a href="{{ route('customers.show', $income->customer) }}">
                                        {{ $income->customer->name }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            @if($income->payer)
                            <tr>
                                <td class="text-muted">Payer</td>
                                <td>{{ $income->payer }}</td>
                            </tr>
                            @endif
                            @if($income->reference_no)
                            <tr>
                                <td class="text-muted">Reference</td>
                                <td><code>{{ $income->reference_no }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Recorded by</td>
                                <td>{{ $income->createdBy->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created at</td>
                                <td>{{ $income->created_at->format('d M Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($income->description)
                <div class="mt-2 p-3 bg-light rounded">
                    <small class="text-muted d-block mb-1">
                        <i class="fas fa-sticky-note mr-1"></i> Description
                    </small>
                    {{ $income->description }}
                </div>
                @endif

                @if($income->isVoid() && $income->void_reason)
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="fas fa-ban mr-1"></i>
                    <strong>Void Reason:</strong> {{ $income->void_reason }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Receipt --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paperclip mr-1"></i> Receipt</h3>
            </div>
            <div class="card-body text-center">
                @if($income->receipt_path)
                    @php $ext = pathinfo($income->receipt_path, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                        <img src="{{ asset('storage/' . $income->receipt_path) }}"
                             class="img-fluid rounded border" style="max-height:280px">
                    @else
                        <a href="{{ asset('storage/' . $income->receipt_path) }}"
                           target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-file-pdf mr-1"></i> View PDF Receipt
                        </a>
                    @endif
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $income->receipt_path) }}"
                           download class="btn btn-sm btn-light border">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                    </div>
                @else
                    <div class="text-muted py-4">
                        <i class="fas fa-file-upload fa-3x mb-2 d-block"></i>
                        No receipt uploaded.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Void Modal --}}
@if(!$income->isVoid())
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('incomes.void', $income) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-ban mr-1"></i> Void Income</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" class="form-control"
                                  placeholder="Enter reason for voiding..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-ban mr-1"></i> Confirm Void
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('js')
<script>
document.getElementById('btnVoidIncome')?.addEventListener('click', function () {
    $('#voidModal').modal('show');
});
</script>
@endpush

@endsection
