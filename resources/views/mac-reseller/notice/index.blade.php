@extends('adminlte::page')
@section('title', 'MACReseller Notice')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-bullhorn mr-1"></i> POP Notice Management
            <small class="text-muted">POP Notice</small>
        </h1>
        <span class="text-muted small"><i class="fas fa-bell"></i> POP Notice &rsaquo; POP Notice <i class="fas fa-sync-alt"></i></span>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-right mb-3">
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addNoticeModal">
                <i class="fas fa-plus"></i> Add Notice
            </button>
        </div>

        <div class="row mb-2">
            <div class="col-sm-2 d-flex align-items-center" style="gap:8px">
                <label class="mb-0 small">SHOW</label>
                <select class="form-control form-control-sm" style="width:70px">
                    <option selected>10</option><option>25</option>
                </select>
                <span class="small">ENTRIES</span>
            </div>
            <div class="col-sm-4 offset-sm-6 text-right d-flex align-items-center justify-content-end" style="gap:8px">
                <label class="mb-0 small">SEARCH:</label>
                <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px">
            </div>
        </div>

        <table class="table table-bordered table-sm" id="noticeTable" style="font-size:13px">
            <thead class="bg-dark text-white">
                <tr>
                    <th>Sr.</th>
                    <th>MAC Reseller Name</th>
                    <th>Title</th>
                    <th>Details</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notices as $i => $n)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $n->reseller?->business_name ?? '(All)' }}</td>
                    <td>{{ $n->title }}</td>
                    <td style="max-width:400px">{{ Str::limit($n->details, 150) }}</td>
                    <td>{{ $n->start_date?->format('d/m/Y') }}</td>
                    <td>{{ $n->end_date?->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $n->is_active ? 'success' : 'secondary' }}">
                            {{ $n->is_active ? 'Enable' : 'Disable' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-success edit-notice-btn"
                            data-id="{{ $n->id }}"
                            data-toggle="modal" data-target="#editNoticeModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-notice-btn" data-id="{{ $n->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center">No notices found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $notices->links() }}
    </div>
</div>

{{-- Add Notice Modal --}}
<div class="modal fade" id="addNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Notice</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="addNoticeForm">
                    @csrf
                    <div class="form-group">
                        <label class="small font-weight-bold">MAC RESELLER (Leave blank for all)</label>
                        <select name="reseller_id" class="form-control form-control-sm">
                            <option value="">All Resellers</option>
                            @foreach($resellers as $r)
                            <option value="{{ $r->id }}">{{ $r->business_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">TITLE <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">DETAILS <span class="text-danger">*</span></label>
                        <textarea name="details" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="small font-weight-bold">START DATE <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control form-control-sm" required value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="small font-weight-bold">END DATE <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control form-control-sm" required value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="small font-weight-bold">STATUS</label>
                                <select name="is_active" class="form-control form-control-sm">
                                    <option value="1">Enable</option>
                                    <option value="0">Disable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Notice Modal --}}
<div class="modal fade" id="editNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Notice</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editNoticeForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editNoticeId">
                    <div class="form-group">
                        <label class="small font-weight-bold">MAC RESELLER</label>
                        <select name="reseller_id" id="editResellerId" class="form-control form-control-sm">
                            <option value="">All Resellers</option>
                            @foreach($resellers as $r)
                            <option value="{{ $r->id }}">{{ $r->business_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">TITLE <span class="text-danger">*</span></label>
                        <input type="text" id="editTitle" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">DETAILS <span class="text-danger">*</span></label>
                        <textarea id="editDetails" name="details" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="small font-weight-bold">START DATE</label>
                                <input type="date" id="editStartDate" name="start_date" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="small font-weight-bold">END DATE</label>
                                <input type="date" id="editEndDate" name="end_date" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="small font-weight-bold">STATUS</label>
                                <select id="editStatus" name="is_active" class="form-control form-control-sm">
                                    <option value="1">Enable</option>
                                    <option value="0">Disable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
// Add Notice
$('#addNoticeForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: "{{ route('mac-reseller.notice.store') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: (res) => {
            if (res.success) {
                $('#addNoticeModal').modal('hide');
                toastr.success(res.message);
                setTimeout(() => location.reload(), 800);
            }
        }
    });
});

// Load edit data
$(document).on('click', '.edit-notice-btn', function() {
    const id = $(this).data('id');
    $.get(`/mac-reseller/notice/${id}`, function(data) {
        $('#editNoticeId').val(data.id);
        $('#editResellerId').val(data.reseller_id);
        $('#editTitle').val(data.title);
        $('#editDetails').val(data.details);
        $('#editStartDate').val(data.start_date);
        $('#editEndDate').val(data.end_date);
        $('#editStatus').val(data.is_active ? '1' : '0');
    });
});

// Update Notice
$('#editNoticeForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#editNoticeId').val();
    $.ajax({
        url: `/mac-reseller/notice/${id}`,
        method: 'POST',
        data: $(this).serialize() + '&_method=PUT',
        success: (res) => {
            if (res.success) {
                $('#editNoticeModal').modal('hide');
                toastr.success(res.message);
                setTimeout(() => location.reload(), 800);
            }
        }
    });
});

// Delete
$(document).on('click', '.delete-notice-btn', function() {
    const id = $(this).data('id');
    if (!confirm('Delete this notice?')) return;
    $.ajax({
        url: `/mac-reseller/notice/${id}`,
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
        success: (res) => { if (res.success) setTimeout(() => location.reload(), 500); }
    });
});

// Search
$('#searchInput').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('#noticeTable tbody tr').each(function() {
        $(this).toggle($(this).text().toLowerCase().includes(val));
    });
});
</script>
@stop
