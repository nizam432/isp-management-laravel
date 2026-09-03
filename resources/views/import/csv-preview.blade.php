{{-- resources/views/import/csv-preview.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Excel Import Preview')
@section('page_actions')
    <a href="{{ route('import.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back
    </a>
@endsection
@section('page_content')

@php
    $willImport = collect($rows)->where('_will_import', true)->count();
    $willSkip   = count($rows) - $willImport;
@endphp

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    মোট <strong>{{ count($rows) }}</strong> row পাওয়া গেছে।
    <strong class="text-success">{{ $willImport }}</strong> টি import হবে।
    <strong class="text-warning">{{ $willSkip }}</strong> টি skip হবে।
</div>

<form action="{{ route('import.csv.execute') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Excel Data Preview</h3>
        </div>
        <div class="card-body p-0" style="overflow-x:auto">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>নাম</th>
                        <th>Mobile</th>
                        <th>PPPoE Username</th>
                        <th>Zone</th>
                        <th>Package</th>
                        <th>Protocol</th>
                        <th>Reason (if skipped)</th>
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
                                {{-- Hidden inputs for rows that will be imported --}}
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
                            @if(!empty($row['zone']) && !$row['_zone_ok'])
                                <br><small class="text-danger">not found</small>
                            @endif
                        </td>
                        <td>
                            {{ $row['package'] ?? '—' }}
                            @if(!empty($row['package']) && !$row['_package_ok'])
                                <br><small class="text-danger">not found</small>
                            @endif
                        </td>
                        <td>
                            {{ $row['protocol_type'] ?? '—' }}
                            @if(!empty($row['protocol_type']) && !$row['_protocol_ok'])
                                <br><small class="text-danger">not found</small>
                            @endif
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
                    onclick="return confirm('{{ $willImport }} জন customer import করবেন?')">
                <i class="fas fa-file-import mr-1"></i> {{ $willImport }} জন Import করুন
            </button>
            @else
            <div class="alert alert-warning mb-0">
                Import করার মতো কোনো নতুন data নেই।
            </div>
            @endif
            <a href="{{ route('import.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        </div>
    </div>
</form>

@endsection