@extends('reseller.layouts.app')

@section('title', 'Bulk Clients Import')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Bulk Clients Import (Excel)</h4>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-excel mr-1"></i> Import via Excel</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Upload an Excel (.xlsx) file with name, mobile, Zone, Package, and PPPoE info to import many clients at once.
                </p>

                <div class="mb-3">
                    <a href="{{ route('reseller.mikrotik-client.bulk-import.template') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download mr-1"></i> Download Excel Template
                    </a>
                </div>

                <form action="{{ route('reseller.mikrotik-client.bulk-import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="excel_file" class="form-control-file" accept=".xlsx,.xls" required>
                        <small class="text-muted">Max 5MB, .xlsx format</small>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Excel Format Guide</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr><th>Column</th><th>Required</th><th>Example</th><th>Notes</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>name</code></td><td><span class="badge badge-warning">Optional</span></td><td>Md Nizam Uddin</td><td>Auto-generated if left blank</td></tr>
                        <tr><td><code>mobile</code></td><td><span class="badge badge-warning">Optional</span></td><td>01712345678</td><td>Placeholder used if left blank</td></tr>
                        <tr><td><code>email</code></td><td><span class="badge badge-secondary">No</span></td><td>nizam@gmail.com</td><td></td></tr>
                        <tr><td><code>nid</code></td><td><span class="badge badge-secondary">No</span></td><td>1994381557500</td><td></td></tr>
                        <tr><td><code>address</code></td><td><span class="badge badge-secondary">No</span></td><td>Meraj Nagar</td><td></td></tr>
                        <tr><td><code>zone</code></td><td><span class="badge badge-secondary">No</span></td><td>Meraj Nagar</td><td>Must match one of your own Zone names</td></tr>
                        <tr><td><code>sub_zone</code></td><td><span class="badge badge-secondary">No</span></td><td>Block A</td><td>Must match one of your own Sub Zone names</td></tr>
                        <tr><td><code>package</code></td><td><span class="badge badge-secondary">No</span></td><td>Home 10Mbps</td><td>Must match one of your own Tariff Package names</td></tr>
                        <tr><td><code>protocol_type</code></td><td><span class="badge badge-secondary">No</span></td><td>PPPoE</td><td></td></tr>
                        <tr><td><code>pppoe_username</code></td><td><span class="badge badge-danger">Yes</span></td><td>nizam_isp</td><td>Must be unique</td></tr>
                        <tr><td><code>pppoe_password</code></td><td><span class="badge badge-danger">Yes</span></td><td>pass12345</td><td></td></tr>
                        <tr><td><code>ip_address</code></td><td><span class="badge badge-secondary">No</span></td><td>192.168.1.100</td><td></td></tr>
                        <tr><td><code>status</code></td><td><span class="badge badge-secondary">No</span></td><td>active</td><td>Default: active</td></tr>
                        <tr><td><code>monthly_bill</code></td><td><span class="badge badge-secondary">No</span></td><td>500</td><td></td></tr>
                        <tr><td><code>join_date</code></td><td><span class="badge badge-secondary">No</span></td><td>2026-07-29</td><td>Today's date used if left blank</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection