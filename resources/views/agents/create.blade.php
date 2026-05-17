{{-- resources/views/agents/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Add Agent')
@section('page_actions')
    <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Agent Information</h3></div>
    <form action="{{ route('agents.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Link User Account <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control" required>
                            <option value="">-- Select User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} — {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Agent Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Area / Zone</label>
                        <input type="text" name="area" class="form-control" value="{{ old('area') }}">
                    </div>
                    <div class="form-group">
                        <label>Commission Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" name="commission_rate" class="form-control" step="0.01" min="0" max="100" value="{{ old('commission_rate', 0) }}" required>
                        <small class="text-muted">Percentage of each payment collected by this agent.</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Agent</button>
            <a href="{{ route('agents.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
