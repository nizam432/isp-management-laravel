@extends('super-admin.layouts.app')

@section('title', 'Send Notification')

@section('content')

<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <h5 class="m-0"><i class="fas fa-bullhorn mr-2"></i> Broadcast Notification</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small">This notification will be sent to all Admin/Employee users.</p>

        <form id="broadcastForm">
            @csrf
            <div class="form-group">
                <label class="font-weight-bold small">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required maxlength="255">
            </div>
            <div class="form-group">
                <label class="font-weight-bold small">Message <span class="text-danger">*</span></label>
                <textarea name="message" class="form-control" rows="4" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold small">Color</label>
                        <select name="color" class="form-control">
                            <option value="primary">Blue (Info)</option>
                            <option value="success">Green (Success)</option>
                            <option value="warning">Orange (Warning)</option>
                            <option value="danger">Red (Urgent)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold small">Icon (Font Awesome class)</label>
                        <input type="text" name="icon" class="form-control" placeholder="fa-bullhorn" value="fa-bullhorn">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold small">Link URL (optional)</label>
                        <input type="text" name="url" class="form-control" placeholder="https://...">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-paper-plane mr-1"></i> Send to All Users
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="m-0">Sent History</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered mb-0">
            <thead style="background:#f4f6f9">
                <tr><th>Title</th><th>Message</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($sentNotifications as $n)
                <tr>
                    <td>{{ $n->title }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($n->message, 60) }}</td>
                    <td>{{ $n->created_at->format('d M Y h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">No notifications sent yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop

@section('js')
<script>
document.getElementById('broadcastForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);

    fetch("{{ route('super-admin.notifications.store') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
            form.reset();
            setTimeout(() => location.reload(), 600);
        }
    })
    .catch(() => alert('Failed to send notification.'));
});
</script>
@stop
