{{-- resources/views/mikrotik/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'MikroTik Routers')
@section('page_actions')
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addRouterModal">
        <i class="fas fa-plus"></i> Add Router
    </button>
@endsection
@section('page_content')

@foreach($routers as $router)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-network-wired mr-1 text-{{ $router->is_active ? 'success' : 'danger' }}"></i>
            {{ $router->name }}
            <small class="text-muted ml-2">{{ $router->ip_address }}:{{ $router->api_port }}</small>
        </h3>
        <div class="card-tools">
            <span class="badge badge-{{ $router->is_active ? 'success' : 'danger' }}">
                {{ $router->is_active ? 'Online' : 'Offline' }}
            </span>

            {{-- Edit Button --}}
            <button class="btn btn-xs btn-warning ml-1"
                    data-toggle="modal"
                    data-target="#editRouter{{ $router->id }}">
                <i class="fas fa-edit"></i> Edit
            </button>

            {{-- Add IP Pool Button --}}
            <button class="btn btn-xs btn-success ml-1"
                    data-toggle="modal"
                    data-target="#addPool{{ $router->id }}">
                <i class="fas fa-plus"></i> Add IP Pool
            </button>

            {{-- Delete Button --}}
            <form action="{{ route('mikrotik.destroy', $router) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('এই router delete করবেন?')">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-danger ml-1">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>Pool Name</th>
                    <th>Start IP</th>
                    <th>End IP</th>
                    <th>Total</th>
                    <th>Used</th>
                    <th>Available</th>
                    <th>Usage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($router->ipPools as $pool)
                <tr>
                    <td><code>{{ $pool->pool_name }}</code></td>
                    <td>{{ $pool->start_ip }}</td>
                    <td>{{ $pool->end_ip }}</td>
                    <td>{{ $pool->total_ip }}</td>
                    <td>{{ $pool->used_ip }}</td>
                    <td>{{ $pool->available_ip }}</td>
                    <td>
                        <div class="progress" style="height:14px;">
                            <div class="progress-bar bg-{{ $pool->usage_percent > 80 ? 'danger' : 'success' }}"
                                 style="width:{{ $pool->usage_percent }}%">
                                {{ $pool->usage_percent }}%
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">No IP pools added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Edit Router Modal ─────────────────────────────── --}}
<div class="modal fade" id="editRouter{{ $router->id }}">
    <div class="modal-dialog">
        <form action="{{ route('mikrotik.update', $router) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-1"></i> Edit Router — {{ $router->name }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Router Name</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ $router->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" name="ip_address" class="form-control"
                               value="{{ $router->ip_address }}" required>
                    </div>
                    <div class="form-group">
                        <label>API Port</label>
                        <input type="number" name="api_port" class="form-control"
                               value="{{ $router->api_port }}" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control"
                               value="{{ $router->username }}" required>
                    </div>
                    <div class="form-group">
                        <label>Password <small class="text-muted">(খালি রাখলে পরিবর্তন হবে না)</small></label>
                        <input type="password" name="password" class="form-control"
                               placeholder="নতুন password দিন">
                    </div>
                    <div class="form-group">
                        <label>Area</label>
                        <input type="text" name="area" class="form-control"
                               value="{{ $router->area }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Update Router
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Add IP Pool Modal ─────────────────────────────── --}}
<div class="modal fade" id="addPool{{ $router->id }}">
    <div class="modal-dialog">
        <form action="{{ route('mikrotik.pool.store', $router) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add IP Pool — {{ $router->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pool Name</label>
                        <input type="text" name="pool_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Start IP</label>
                        <input type="text" name="start_ip" class="form-control" placeholder="192.168.1.1" required>
                    </div>
                    <div class="form-group">
                        <label>End IP</label>
                        <input type="text" name="end_ip" class="form-control" placeholder="192.168.1.254" required>
                    </div>
                    <div class="form-group">
                        <label>Total IPs</label>
                        <input type="number" name="total_ip" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus mr-1"></i> Add Pool
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endforeach

{{-- ── Add Router Modal ──────────────────────────────── --}}
<div class="modal fade" id="addRouterModal">
    <div class="modal-dialog">
        <form action="{{ route('mikrotik.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus mr-1"></i> Add MikroTik Router
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Router Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.1" required>
                    </div>
                    <div class="form-group">
                        <label>API Port</label>
                        <input type="number" name="api_port" class="form-control" value="8728" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Area</label>
                        <input type="text" name="area" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Add Router
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection