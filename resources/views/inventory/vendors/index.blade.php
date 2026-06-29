@extends('adminlte::page')
@section('title', 'Vendors')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-handshake mr-2 text-primary"></i>Vendors
            </h4>
            <small class="text-muted">Manage your suppliers and manufacturers</small>
        </div>
        <a href="{{ route('inventory.vendors.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> Add Vendor
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="small font-weight-bold">Search Vendor</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search name / phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Status</option>
                    <option value="active"      {{ request('status') == 'active'      ? 'selected' : '' }}>Active</option>
                    <option value="inactive"    {{ request('status') == 'inactive'    ? 'selected' : '' }}>Inactive</option>
                    <option value="blacklisted" {{ request('status') == 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                </select>
            </div>
            <div class="col-md-5 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm px-3 mr-2">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('inventory.vendors.index') }}" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Vendor List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="vendorTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="text-center" style="width:50px;">#</th>
                        <th>Vendor No</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th class="text-right">Total Due</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="vendorTableBody">
                    @forelse($vendors as $vendor)
                    <tr>
                        <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                        <td class="text-muted small">{{ $vendor->vendor_no }}</td>
                        <td>
                            <span class="font-weight-bold">{{ $vendor->name }}</span>
                            @if($vendor->owner_name)
                                <br><small class="text-muted">{{ $vendor->owner_name }}</small>
                            @endif
                        </td>
                        <td class="text-muted">{{ $vendor->phone }}</td>
                        <td>
                            <span class="badge badge-light border">{{ ucfirst($vendor->vendor_type) }}</span>
                        </td>
                        <td class="text-right font-weight-bold {{ $vendor->total_due > 0 ? 'text-danger' : 'text-muted' }}">
                            ৳{{ number_format($vendor->total_due, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $vendor->status == 'active' ? 'success' : ($vendor->status == 'blacklisted' ? 'danger' : 'secondary') }}">
                                {{ ucfirst($vendor->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('inventory.vendors.show', $vendor) }}"
                               class="btn btn-sm btn-info px-2" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('inventory.vendors.edit', $vendor) }}"
                               class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-handshake fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No vendors found. Click <strong>+ Add Vendor</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($vendors->hasPages())
    <div class="card-footer bg-light py-2">
        {{ $vendors->links() }}
    </div>
    @endif
</div>

@endsection

@section('css')
<style>
    #vendorTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        padding: 10px 12px;
    }
    #vendorTable tbody td { padding: 10px 12px; vertical-align: middle; }
    #vendorTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#vendorTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
