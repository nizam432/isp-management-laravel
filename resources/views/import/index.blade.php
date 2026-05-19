{{-- resources/views/import/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Customer Import')
@section('page_content')

<div class="row">

    {{-- MikroTik Direct Import --}}
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-network-wired mr-1"></i> MikroTik থেকে Import
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    MikroTik এর সব PPPoE user গুলো সরাসরি software এ import করুন।
                    <br><small>⚠️ নাম ও phone পরে manually দিতে হবে।</small>
                </p>

                <form action="{{ route('import.mikrotik.preview') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Router Select করুন <span class="text-danger">*</span></label>
                        <select name="router_id" class="form-control" required>
                            <option value="">-- Router Select --</option>
                            @foreach($routers as $router)
                            <option value="{{ $router->id }}">
                                {{ $router->name }} ({{ $router->ip_address }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-download mr-1"></i> MikroTik থেকে User আনো
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- CSV Import --}}
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-csv mr-1"></i> CSV দিয়ে Import
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    CSV file এ নাম, phone, PPPoE info দিয়ে একসাথে অনেক customer import করুন।
                    <br><small>✅ নাম ও phone সহ সম্পূর্ণ তথ্য import হবে।</small>
                </p>

                <div class="mb-3">
                    <a href="{{ route('import.csv.template') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download mr-1"></i> CSV Template Download করুন
                    </a>
                </div>

                <form action="{{ route('import.csv.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Package <span class="text-danger">*</span></label>
                        <select name="package_id" class="form-control" required>
                            <option value="">-- Package Select --</option>
                            @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ $pkg->price }} BDT)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control-file" accept=".csv,.txt" required>
                        <small class="text-muted">সর্বোচ্চ 2MB, .csv format</small>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-eye mr-1"></i> Preview দেখুন
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- CSV Format Guide --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> CSV Format Guide</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Column</th>
                    <th>Required</th>
                    <th>Example</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>name</code></td><td><span class="badge badge-warning">Optional</span></td><td>Md Nizam Uddin</td><td>না দিলে auto generate হবে</td></tr>
                <tr><td><code>phone</code></td><td><span class="badge badge-warning">Optional</span></td><td>01712345678</td><td>না দিলে placeholder দেওয়া হবে</td></tr>
                <tr><td><code>email</code></td><td><span class="badge badge-secondary">No</span></td><td>nizam@gmail.com</td><td></td></tr>
                <tr><td><code>address</code></td><td><span class="badge badge-secondary">No</span></td><td>Meraj Nagar</td><td></td></tr>
                <tr><td><code>area</code></td><td><span class="badge badge-secondary">No</span></td><td>Meraj Nagar</td><td></td></tr>
                <tr><td><code>pppoe_username</code></td><td><span class="badge badge-danger">Yes</span></td><td>nizam_isp</td><td>Unique হতে হবে</td></tr>
                <tr><td><code>pppoe_password</code></td><td><span class="badge badge-warning">Optional</span></td><td>pass12345</td><td></td></tr>
                <tr><td><code>ip_address</code></td><td><span class="badge badge-secondary">No</span></td><td>192.168.1.100</td><td></td></tr>
                <tr><td><code>billing_date</code></td><td><span class="badge badge-secondary">No</span></td><td>1</td><td>১-২৮, default: 1</td></tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
