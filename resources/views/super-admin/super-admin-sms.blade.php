{{-- resources/views/super-admin/sms-gateways.blade.php --}}
@extends('layouts.app')
@section('page_title', 'SMS Gateway Management')
@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sms mr-1"></i> SMS Gateways — ISP দের জন্য Enable/Disable</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Gateway</th>
                    <th>Description</th>
                    <th>ISP দের কাছে দেখাবে</th>
                    <th>Switch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gateways as $gw)
                <tr>
                    <td>
                        <strong>{{ $gw->name }}</strong>
                        <br><small class="text-muted"><code>{{ $gw->slug }}</code></small>
                    </td>
                    <td><small>{{ $gw->description }}</small></td>
                    <td>
                        <span class="badge badge-{{ $gw->is_enabled ? 'success' : 'danger' }} badge-lg">
                            {{ $gw->is_enabled ? '✅ ISP দেখতে পাবে' : '❌ ISP দেখতে পাবে না' }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('super-admin.sms.toggle', $gw) }}" method="POST">
                            @csrf
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input"
                                       id="switch{{ $gw->id }}"
                                       {{ $gw->is_enabled ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <label class="custom-control-label" for="switch{{ $gw->id }}">
                                    {{ $gw->is_enabled ? 'ON' : 'OFF' }}
                                </label>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
