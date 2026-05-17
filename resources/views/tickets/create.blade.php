{{-- resources/views/tickets/create.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Create Support Ticket')
@section('page_actions')
    <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Ticket Information</h3></div>
    <form action="{{ route('tickets.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', request('customer_id')) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} — {{ $c->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-control" required>
                            @foreach(['connection','billing','speed','device','other'] as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-control" required>
                            @foreach(['low','medium','high','urgent'] as $p)
                                <option value="{{ $p }}" {{ old('priority', 'medium') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To (Technician)</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">-- Unassigned --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ old('assigned_to') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Ticket</button>
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
