{{-- resources/views/reports/bill/renewal.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Renewal / Expiry Report')
@section('page_content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline flex-wrap">
            <div class="btn-group mr-3" role="group">
                <a href="{{ route('reports.bill.renewal', array_merge(request()->except(['range','page']), ['range' => 'today'])) }}"
                   class="btn btn-sm {{ $range === 'today' ? 'btn-primary' : 'btn-outline-primary' }}">Today</a>
                <a href="{{ route('reports.bill.renewal', array_merge(request()->except(['range','page']), ['range' => '3'])) }}"
                   class="btn btn-sm {{ $range === '3' ? 'btn-primary' : 'btn-outline-primary' }}">Next 3 Days</a>
                <a href="{{ route('reports.bill.renewal', array_merge(request()->except(['range','page']), ['range' => '7'])) }}"
                   class="btn btn-sm {{ $range === '7' ? 'btn-primary' : 'btn-outline-primary' }}">Next 7 Days</a>
                <a href="{{ route('reports.bill.renewal', array_merge(request()->except(['range','page']), ['range' => 'expired'])) }}"
                   class="btn btn-sm {{ $range === 'expired' ? 'btn-danger' : 'btn-outline-danger' }}">Already Expired</a>
            </div>

            <label class="mr-2 mb-0">Zone:</label>
            <select name="zone_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                @endforeach
            </select>

            <label class="mr-2 mb-0">Package:</label>
            <select name="package_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>{{ $package->name }}</option>
                @endforeach
            </select>
            <input type="hidden" name="range" value="{{ $range }}">
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-calendar-day"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Expiring Today</span>
                        <span class="info-box-number">{{ $summary['today'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-calendar-week"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Next 3 Days</span>
                        <span class="info-box-number">{{ $summary['next3'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Next 7 Days</span>
                        <span class="info-box-number">{{ $summary['next7'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Already Expired</span>
                        <span class="info-box-number">{{ $summary['expired'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-striped table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Customer</th><th>Phone</th><th>Package</th>
                    <th>Zone</th><th>Agent</th><th>POP/Reseller</th>
                    <th>Expire Date</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $i => $customer)
                <tr>
                    <td>{{ $customers->firstItem() + $i }}</td>
                    <td>{{ $customer->name }} <small class="text-muted">({{ $customer->customer_code }})</small></td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->package->name ?? '-' }}</td>
                    <td>{{ $customer->zone->name ?? '-' }}</td>
                    <td>{{ $customer->agent->name ?? '-' }}</td>
                    <td>{{ $customer->macReseller->business_name ?? '-' }}</td>
                    <td>
                        @php $daysLeft = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($customer->expire_date), false); @endphp
                        {{ \Carbon\Carbon::parse($customer->expire_date)->format('d M Y') }}
                        @if($daysLeft < 0)
                            <span class="badge badge-danger">{{ abs($daysLeft) }} days overdue</span>
                        @elseif($daysLeft == 0)
                            <span class="badge badge-warning">Today</span>
                        @else
                            <span class="badge badge-info">{{ $daysLeft }} days left</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $customer->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted">No customers found for this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $customers->links() }}
    </div>
</div>
@endsection
