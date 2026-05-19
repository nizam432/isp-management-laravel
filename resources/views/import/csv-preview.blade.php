{{-- resources/views/import/csv-preview.blade.php --}}
@extends('layouts.app')
@section('page_title', 'CSV Import Preview')
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
    <strong class="text-warning">{{ $willSkip }}</strong> টি skip হবে (already আছে)।
</div>

<form action="{{ route('import.csv.execute') }}" method="POST">
    @csrf
    <input type="hidden" name="package_id" value="{{ $package_id }}">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">CSV Data Preview</h3>
        </div>
        <div class="card-body p-0" style="overflow-x:auto">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>নাম</th>
                        <th>Phone</th>
                        <th>PPPoE Username</th>
                        <th>PPPoE Password</th>
                        <th>IP</th>
                        <th>Area</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                    <tr class="{{ !$row['_will_import'] ? 'table-warning' : '' }}">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            @if(!$row['_will_import'])
                                <span class="badge badge-warning">Skip</span>
                                @if($row['_exists_username'])
                                    <small class="text-muted">username exists</small>
                                @elseif($row['_exists_phone'])
                                    <small class="text-muted">phone exists</small>
                                @endif
                            @else
                                <span class="badge badge-success">Import</span>
                                {{-- Hidden inputs for rows that will be imported --}}
                                <input type="hidden" name="rows[{{ $i }}][name]"           value="{{ $row['name'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][phone]"          value="{{ $row['phone'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][email]"          value="{{ $row['email'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][address]"        value="{{ $row['address'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][area]"           value="{{ $row['area'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][pppoe_username]" value="{{ $row['pppoe_username'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][pppoe_password]" value="{{ $row['pppoe_password'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][ip_address]"     value="{{ $row['ip_address'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $i }}][billing_date]"   value="{{ $row['billing_date'] ?? '1' }}">
                            @endif
                        </td>
                        <td>{{ $row['name'] ?? '—' }}</td>
                        <td>{{ $row['phone'] ?? '—' }}</td>
                        <td><code>{{ $row['pppoe_username'] ?? '—' }}</code></td>
                        <td><small>{{ $row['pppoe_password'] ?? '—' }}</small></td>
                        <td>{{ $row['ip_address'] ?? '—' }}</td>
                        <td>{{ $row['area'] ?? '—' }}</td>
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
