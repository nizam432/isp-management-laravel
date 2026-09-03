@extends('reseller.layouts.app')

@section('title', 'Excel Import Preview')

@section('content')

@php
    $willImport = collect($rows)->where('_will_import', true)->count();
    $willSkip   = count($rows) - $willImport;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Excel Import Preview</h4>
    <a href="{{ route('reseller.mikrotik-client.bulk-import') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    Found <strong>{{ count($rows) }}</strong> row(s) total.
    <strong class="text-success">{{ $willImport }}</strong> will be imported.
    <strong class="text-warning">{{ $willSkip }}</strong> will be skipped.
</div>

<form action="{{ route('reseller.mikrotik-client.bulk-import.execute') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header"><h3 class="card-title mb-0">Excel Data Preview</h3></div>
        <div class="card-body p-0" style="overflow-x:auto">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th><th>Status</th><th>Name</th><th>Mobile</th>
                        <th>PPPoE Username</th><th>Zone</th><th>Package</th><th>Protocol</th><th>Reason (if skipped)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                    <tr class="{{ !$row['_will_import'] ? 'table-warning' : '' }}">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            @if(!$row['_will_import'])
                                <span class="badge badge-warning">Skip</span>
                            @else
                                <span class="badge badge-success">Import</span>
                                <input type="hidden" name="rows[{{ $i }}][name]"           value="{{ $row['name'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][phone]"          value="{{ $row['phone'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][email]"          value="{{ $row['email'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][nid]"            value="{{ $row['nid'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][address]"        value="{{ $row['address'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][zone_id]"        value="{{ $row['_zone_id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][sub_zone_id]"    value="{{ $row['_sub_zone_id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][package_id]"     value="{{ $row['_package_id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][protocol_id]"    value="{{ $row['_protocol_id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][pppoe_username]" value="{{ $row['pppoe_username'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][pppoe_password]" value="{{ $row['pppoe_password'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][ip_address]"     value="{{ $row['ip_address'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][status]"         value="{{ $row['status'] ?? 'active' }}">
                                <input type="hidden" name="rows[{{ $i }}][monthly_bill]"   value="{{ $row['monthly_bill'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][join_date]"      value="{{ $row['join_date'] ?? '' }}">
                            @endif
                        </td>
                        <td>{{ $row['name'] ?? '—' }}</td>
                        <td>{{ $row['phone'] ?? '—' }}</td>
                        <td><code>{{ $row['pppoe_username'] ?? '—' }}</code></td>
                        <td>
                            {{ $row['zone'] ?? '—' }}
                            @if(!empty($row['zone']) && !$row['_zone_ok'])<br><small class="text-danger">not found</small>@endif
                        </td>
                        <td>
                            {{ $row['package'] ?? '—' }}
                            @if(!empty($row['package']) && !$row['_package_ok'])<br><small class="text-danger">not found</small>@endif
                        </td>
                        <td>
                            {{ $row['protocol_type'] ?? '—' }}
                            @if(!empty($row['protocol_type']) && !$row['_protocol_ok'])<br><small class="text-danger">not found</small>@endif
                        </td>
                        <td>
                            <small class="text-muted">
                                @if($row['_exists_username'] ?? false) Username already exists @endif
                                @if($row['_exists_phone'] ?? false) Phone already exists @endif
                                @if(empty($row['pppoe_username'])) PPPoE Username missing @endif
                                @if(empty($row['pppoe_password'])) PPPoE Password missing @endif
                                @if(!($row['_zone_ok'] ?? true)) Zone not found @endif
                                @if(!($row['_package_ok'] ?? true)) Package not found @endif
                                @if(!($row['_protocol_ok'] ?? true)) Protocol not found @endif
                            </small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @if($willImport > 0)
            <button type="submit" class="btn btn-success btn-lg"
                    onclick="return confirm('Import {{ $willImport }} client(s)?')">
                <i class="fas fa-file-import mr-1"></i> Import {{ $willImport }} Client(s)
            </button>
            @else
            <div class="alert alert-warning mb-0">There is no new data to import.</div>
            @endif
            <a href="{{ route('reseller.mikrotik-client.bulk-import') }}" class="btn btn-secondary ml-2">Cancel</a>
        </div>
    </div>
</form>

@endsection