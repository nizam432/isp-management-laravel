{{-- resources/views/super-admin/sms-gateways.blade.php --}}
@extends('layouts.app')
@section('page_title', 'SMS Gateway Management')
@section('page_content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-sms mr-1"></i> SMS Gateways — ISP দের জন্য Enable/Disable
        </h3>
        <div class="card-tools">
            <span class="badge badge-info">
                {{ $gateways->where('is_enabled', true)->count() }} / {{ $gateways->count() }} Enabled
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th style="width:220px">Gateway</th>
                    <th>Description</th>
                    <th style="width:160px">ISP Status</th>
                    <th style="width:140px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gateways as $gw)
                <tr>
                    <td>
                        <strong>{{ $gw->name }}</strong>
                        <br><small class="text-muted"><code>{{ $gw->slug }}</code></small>
                    </td>
                    <td>
                        <small class="text-muted">{{ $gw->description ?? '—' }}</small>
                    </td>
                    <td>
                        @if($gw->is_enabled)
                            <span class="badge badge-success">
                                <i class="fas fa-check mr-1"></i> ISP দেখতে পাবে
                            </span>
                        @else
                            <span class="badge badge-danger">
                                <i class="fas fa-times mr-1"></i> ISP দেখতে পাবে না
                            </span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('super-admin.sms.toggle', $gw->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm btn-{{ $gw->is_enabled ? 'danger' : 'success' }}">
                                <i class="fas fa-{{ $gw->is_enabled ? 'ban' : 'check' }} mr-1"></i>
                                {{ $gw->is_enabled ? 'বন্ধ করুন' : 'চালু করুন' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        কোনো SMS Gateway পাওয়া যায়নি।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small">
        <i class="fas fa-info-circle mr-1"></i>
        এখানে <strong>চালু</strong> করা gateway গুলো ISP-admin রা তাদের SMS Settings পেজে দেখতে ও ব্যবহার করতে পারবে।
    </div>
</div>

@endsection