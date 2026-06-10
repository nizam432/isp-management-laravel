{{-- resources/views/expenses/show.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Expense: ' . $expense->expense_no)
@section('page_actions')
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
    @if(!$expense->isVoid())
        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning btn-sm ml-1">
            <i class="fas fa-edit mr-1"></i> Edit
        </a>
        <button type="button" class="btn btn-danger btn-sm ml-1" id="btnVoid">
            <i class="fas fa-ban mr-1"></i> Void
        </button>
    @else
        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline ml-1"
              onsubmit="return confirm('পুরোপুরি delete করবেন?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        </form>
    @endif
@endsection
@section('page_content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice mr-1"></i>
                    <code>{{ $expense->expense_no }}</code>
                </h3>
                {!! $expense->statusBadge !!}
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width:40%">তারিখ</td>
                                <td class="font-weight-bold">
                                    {{ $expense->expense_date->format('d M Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Category</td>
                                <td>
                                    @if($expense->category)
                                        <span class="badge" style="{{ $expense->category->badgeStyle }}">
                                            {{ $expense->category->name }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">পরিমাণ</td>
                                <td class="font-weight-bold text-danger" style="font-size:20px">
                                    {{ $expense->formattedAmount }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment Method</td>
                                <td>
                                    <span class="badge badge-light border">
                                        {{ strtoupper($expense->payment_method) }}
                                    </span>
                                </td>
                            </tr>
                            @if($expense->transaction_id)
                            <tr>
                                <td class="text-muted">Transaction ID</td>
                                <td><code>{{ $expense->transaction_id }}</code></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            @if($expense->payee)
                            <tr>
                                <td class="text-muted" style="width:40%">Payee</td>
                                <td>{{ $expense->payee }}</td>
                            </tr>
                            @endif
                            @if($expense->reference_no)
                            <tr>
                                <td class="text-muted">Reference</td>
                                <td><code>{{ $expense->reference_no }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Created by</td>
                                <td>{{ $expense->createdBy->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Approved by</td>
                                <td>{{ $expense->approvedBy->name ?? '—' }}</td>
                            </tr>
                            @if($expense->approved_at)
                            <tr>
                                <td class="text-muted">Approved at</td>
                                <td>{{ $expense->approved_at->format('d M Y h:i A') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($expense->description)
                <div class="mt-2 p-3 bg-light rounded">
                    <small class="text-muted d-block mb-1"><i class="fas fa-sticky-note mr-1"></i> বিবরণ</small>
                    {{ $expense->description }}
                </div>
                @endif

                @if($expense->isVoid() && $expense->reject_reason)
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="fas fa-ban mr-1"></i>
                    <strong>Void Reason:</strong> {{ $expense->reject_reason }}
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Receipt --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paperclip mr-1"></i> Receipt</h3>
            </div>
            <div class="card-body text-center">
                @if($expense->receipt_path)
                    @php $ext = pathinfo($expense->receipt_path, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                        <img src="{{ asset('storage/' . $expense->receipt_path) }}"
                             class="img-fluid rounded border" alt="Receipt"
                             style="max-height:300px">
                    @else
                        <a href="{{ asset('storage/' . $expense->receipt_path) }}"
                           target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-file-pdf mr-1"></i> PDF Receipt দেখুন
                        </a>
                    @endif
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $expense->receipt_path) }}"
                           download class="btn btn-sm btn-light border">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                    </div>
                @else
                    <div class="text-muted py-4">
                        <i class="fas fa-file-upload fa-3x mb-2 d-block"></i>
                        কোনো receipt upload করা হয়নি।
                    </div>
                @endif
            </div>
        </div>

        {{-- Meta --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Info</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted pl-3">Created</td>
                        <td>{{ $expense->created_at->format('d M Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Updated</td>
                        <td>{{ $expense->updated_at->format('d M Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Void Modal --}}
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('expenses.void', $expense) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-ban mr-1"></i> Expense Void করুন</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Void করার কারণ <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" class="form-control"
                                  placeholder="কারণ লিখুন..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-ban mr-1"></i> Void করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    document.getElementById('btnVoid')?.addEventListener('click', function () {
        $('#voidModal').modal('show');
    });
</script>
@endpush

@endsection
