@extends('reseller.layouts.app')
@section('title', 'SMS Service')
@section('content')

<div class="row mb-3">
    <div class="col-md-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3"><p class="text-muted small mb-1">Total Sent</p><h4 class="font-weight-bold mb-0 text-success">{{ $stats['total_sent'] }}</h4></div>
        </div>
    </div>
    <div class="col-md-6 mb-2">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body py-3"><p class="text-muted small mb-1">Failed</p><h4 class="font-weight-bold mb-0 text-danger">{{ $stats['failed'] }}</h4></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-paper-plane text-primary mr-1"></i> Send SMS</h6>
                <form id="sendSmsForm">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">Client</label>
                        <select name="customer_id" class="form-control form-control-sm" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Message</label>
                        <textarea name="message" class="form-control form-control-sm" rows="4" maxlength="500" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success px-4 w-100">
                        <i class="fas fa-paper-plane mr-1"></i> Send SMS
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius:12px">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3"><i class="fas fa-history text-info mr-1"></i> SMS Log</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:.82rem">
                        <thead style="background:#f4f6f9"><tr><th>Phone</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->phone }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($log->message, 40) }}</td>
                                <td><span class="badge badge-{{ $log->status == 'sent' ? 'success' : 'danger' }}">{{ ucfirst($log->status) }}</span></td>
                                <td>{{ $log->created_at?->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No SMS sent yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$('#sendSmsForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: "{{ route('reseller.sms-service.send') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: (res) => { if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); } },
        error: (xhr) => {
            const errors = xhr.responseJSON?.errors;
            if (errors) toastr.error(Object.values(errors).flat().join('\n'));
        }
    });
});
</script>
@stop
