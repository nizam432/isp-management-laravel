{{-- resources/views/super-admin/sms-gateways/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'SMS Gateways')
@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-server mr-1"></i> SMS Gateways</h3>
    </div>
    <div class="card-body p-0">
        @foreach($gateways as $gw)
        <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>{{ $gw->name }}</strong>
                    <span class="badge badge-{{ $gw->is_active ? 'success' : 'secondary' }} ml-1">
                        {{ $gw->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <br><small class="text-muted">{{ $gw->description }}</small>
                </div>
                <form action="{{ route('super-admin.sms-gateways.toggle', $gw) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-{{ $gw->is_active ? 'danger' : 'success' }}">
                        {{ $gw->is_active ? 'বন্ধ করুন' : 'চালু করুন' }}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection