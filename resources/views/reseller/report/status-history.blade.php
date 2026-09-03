@extends('reseller.layouts.app')

@section('title', 'Enable/Disable History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Client Status Overview</h4>
    <a href="{{ route('reseller.report.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    এখানে প্রতিটা client-এর বর্তমান status ও সর্বশেষ আপডেটের সময় দেখাচ্ছে (সাম্প্রতিক পরিবর্তন আগে)।
    সম্পূর্ণ change-history (কে কখন enable/disable করেছে) দরকার হলে জানান, আলাদা audit log বানাতে হবে।
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        @foreach(['active','inactive','suspended','expired'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr><th>Client</th><th>Status</th><th>Last Updated</th></tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td>{{ $c->name }} <small class="text-muted">({{ $c->customer_code }})</small></td>
                    <td>
                        <span class="badge badge-{{ $c->status === 'active' ? 'success' : ($c->status === 'suspended' ? 'warning' : ($c->status === 'expired' ? 'danger' : 'secondary')) }}">
                            {{ ucfirst($c->status) }}
                        </span>
                    </td>
                    <td>{{ $c->updated_at->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $clients->withQueryString()->links() }}</div>
</div>

@endsection