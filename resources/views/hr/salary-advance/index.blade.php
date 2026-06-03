{{-- resources/views/hr/salary-advance/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Salary Advance')
@section('page_content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-hand-holding-usd mr-1"></i> Salary Advance List</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $advances->total() }} records</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Amount</th>
                            <th>Advance Date</th>
                            <th>Deduct Month</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($advances as $i => $adv)
                        <tr>
                            <td class="text-muted">{{ $advances->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $adv->employee->name }}</strong>
                                <br><small class="text-muted"><code>{{ $adv->employee->employee_code }}</code></small>
                            </td>
                            <td><strong>৳ {{ number_format($adv->amount) }}</strong></td>
                            <td>{{ $adv->advance_date->format('d M Y') }}</td>
                            <td>
                                @if($adv->deduct_month)
                                    {{ \Carbon\Carbon::parse($adv->deduct_month . '-01')->format('M Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $adv->status === 'deducted' ? 'success' : 'warning' }}">
                                    {{ ucfirst($adv->status) }}
                                </span>
                            </td>
                            <td><small>{{ Str::limit($adv->note, 30) ?? '—' }}</small></td>
                            <td>
                                @if($adv->status === 'pending')
                                    <form action="{{ route('salary-advance.deduct', $adv) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-xs btn-success swal-delete"
                                                data-message="Mark this advance as deducted?">
                                            <i class="fas fa-check mr-1"></i> Deduct
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No advance records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Total {{ $advances->total() }}</small>
                {{ $advances->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus mr-1"></i> New Advance</h3>
            </div>
            <form action="{{ route('salary-advance.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">৳</span>
                            </div>
                            <input type="number" name="amount" class="form-control"
                                   min="1" placeholder="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Advance Date <span class="text-danger">*</span></label>
                        <input type="date" name="advance_date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Deduct Month</label>
                        <input type="month" name="deduct_month" class="form-control"
                               value="{{ now()->format('Y-m') }}">
                        <small class="text-muted">Month when this will be deducted</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Note</label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="Optional..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i> Save Advance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
