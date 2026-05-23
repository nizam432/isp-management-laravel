{{-- resources/views/customers/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Customers')
@section('page_actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add Customer
    </a>
@endsection
@section('page_content')

{{-- Stats --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $totalCustomers }}</h3><p>মোট Customer</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $activeCustomers }}</h3><p>Active</p></div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $suspendedCustomers }}</h3><p>Suspended</p></div>
            <div class="icon"><i class="fas fa-user-slash"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $expiredCustomers }}</h3><p>Expired</p></div>
            <div class="icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Search & Filter</h3>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                {{-- Search --}}
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Search</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Name / Phone / Code"
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                {{-- Status --}}
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>Active</option>
                            <option value="inactive"  {{ request('status') == 'inactive'  ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="expired"   {{ request('status') == 'expired'   ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </div>
                {{-- Package --}}
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Package</label>
                        <select name="package_id" class="form-control form-control-sm">
                            <option value="">All Packages</option>
                            @foreach($packages as $pkg)
                                <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- Area --}}
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Area</label>
                        <select name="area" class="form-control form-control-sm">
                            <option value="">All Areas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>
                                    {{ $area }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- Billing Date --}}
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Billing Date</label>
                        <select name="billing_date" class="form-control form-control-sm">
                            <option value="">All Dates</option>
                            @for($d = 1; $d <= 28; $d++)
                                <option value="{{ $d }}" {{ request('billing_date') == $d ? 'selected' : '' }}>
                                    {{ $d }} তারিখ
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary ml-1">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
                @if(request()->hasAny(['search','status','package_id','area','billing_date']))
                    <span class="badge badge-warning ml-2">
                        Filtered: {{ $customers->total() }} results
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Customer Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-users mr-1"></i> Customer List
        </h3>
        <div>
            <a href="{{ route('import.index') }}" class="btn btn-xs btn-success mr-1">
                <i class="fas fa-file-import mr-1"></i> Import
            </a>
            <span class="badge badge-info">{{ $customers->total() }} জন</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Package</th>
                    <th>Area</th>
                    <th>Billing</th>
                    <th>Status</th>
                    <th style="width:100px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $i => $customer)
                @if(!$customer || !$customer->id) @continue @endif
                <tr>
                    <td class="text-muted small">{{ $customers->firstItem() + $i }}</td>
                    <td><code class="small">{{ $customer->customer_code }}</code></td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="font-weight-bold">
                            {{ $customer->name }}
                        </a>
                        @if($customer->email)
                            <br><small class="text-muted">{{ $customer->email }}</small>
                        @endif
                    </td>
                    <td>
                        <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                    </td>
                    <td>
                        @if($customer->package)
                            <span class="badge badge-light border">{{ $customer->package->name }}</span>
                        @else
                            <small class="text-muted">N/A</small>
                        @endif
                    </td>
                    <td><small>{{ $customer->area ?? '-' }}</small></td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $customer->billing_date }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{
                            $customer->status === 'active'    ? 'success'   :
                            ($customer->status === 'suspended' ? 'warning'  :
                            ($customer->status === 'expired'   ? 'danger'   : 'secondary'))
                        }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}"
                           class="btn btn-xs btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}"
                           class="btn btn-xs btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('customers.destroy', $customer) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('{{ $customer->name }} কে delete করবেন?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x d-block mb-2"></i>
                        কোনো customer পাওয়া যায়নি।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            মোট {{ $customers->total() }} জন — page {{ $customers->currentPage() }}/{{ $customers->lastPage() }}
        </small>
        {{ $customers->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>

@endsection