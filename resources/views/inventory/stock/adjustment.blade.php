@extends('adminlte::page')
@section('title', 'Stock Adjustment')

@section('content_header')
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-sliders-h mr-2 text-primary"></i>Stock Adjustment
        </h4>
        <small class="text-muted">Manually adjust stock quantities</small>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-plus-minus mr-1"></i> New Adjustment
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.stock.adjustment') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold small">Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ $p->stock_quantity }} {{ $p->unit }})</option>
                            @endforeach
                        </select>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Location <span class="text-danger">*</span></label>
                        <select name="location_id" class="form-control @error('location_id') is-invalid @enderror" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Date <span class="text-danger">*</span></label>
                        <input type="date" name="adjustment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="add">Add — Stock বাড়াও</option>
                            <option value="subtract">Subtract — Stock কমাও</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                               min="0.01" step="0.01" placeholder="0" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Reason <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror"
                               placeholder="e.g. Physical count correction, Damage" required>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i> Save Adjustment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Adjustment History
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Date</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Product</th>
                                <th class="text-center" style="font-size:12px;color:#555;padding:10px 12px;">Type</th>
                                <th class="text-center" style="font-size:12px;color:#555;padding:10px 12px;">Qty</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">Reason</th>
                                <th style="font-size:12px;color:#555;padding:10px 12px;">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adj)
                            <tr>
                                <td style="padding:10px 12px;" class="small text-muted">{{ $adj->adjustment_date->format('d M Y') }}</td>
                                <td style="padding:10px 12px;" class="font-weight-bold">{{ $adj->product->name }}</td>
                                <td style="padding:10px 12px;" class="text-center">
                                    <span class="badge badge-{{ $adj->type == 'add' ? 'success' : 'danger' }}">
                                        {{ ucfirst($adj->type) }}
                                    </span>
                                </td>
                                <td style="padding:10px 12px;" class="text-center font-weight-bold">{{ $adj->quantity }}</td>
                                <td style="padding:10px 12px;" class="text-muted small">{{ $adj->reason }}</td>
                                <td style="padding:10px 12px;" class="text-muted small">{{ $adj->createdBy->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No adjustments found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($adjustments->hasPages())
            <div class="card-footer bg-light py-2">{{ $adjustments->links() }}</div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .card-header h6 { font-size:13px; letter-spacing:.3px; }
    .form-group label { color:#555; }
    .table tbody td { vertical-align: middle; }
</style>
@stop
