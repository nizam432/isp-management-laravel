{{-- resources/views/agents/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Agents')
@section('page_actions')
    <a href="{{ route('agents.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Agent</a>
@endsection
@section('page_content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr><th>Name</th><th>Phone</th><th>Area</th><th>Commission %</th><th>Customers</th><th>Total Commission</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($agents as $agent)
                <tr>
                    <td><a href="{{ route('agents.show', $agent) }}">{{ $agent->name }}</a></td>
                    <td>{{ $agent->phone ?? '-' }}</td>
                    <td>{{ $agent->area ?? '-' }}</td>
                    <td>{{ $agent->commission_rate }}%</td>
                    <td><span class="badge badge-info">{{ $agent->customers_count }}</span></td>
                    <td>{{ number_format($agent->commissions_sum_amount ?? 0) }} BDT</td>
                    <td><span class="badge badge-{{ $agent->is_active ? 'success' : 'secondary' }}">{{ $agent->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('agents.show', $agent) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No agents found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $agents->links() }}</div>
</div>
@endsection
