{{-- resources/views/support_categories/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Support Categories')
@section('page_actions')
    <button class="btn btn-primary btn-sm" id="btnAddCategory">
        <i class="fas fa-plus mr-1"></i> Support Category
    </button>
@endsection

@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Configure Support Categories</h3>
        <div class="card-tools">
            <span class="badge badge-info" id="totalCount">{{ $categories->count() }} categories</span>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-hover mb-0" id="categoryTable">
            <thead class="thead-dark">
                <tr>
                    <th style="width:50px">Serial</th>
                    <th>Support Category</th>
                    <th>Department</th>
                    <th>Category Type</th>
                    <th>Details</th>
                    <th style="width:100px" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $i => $cat)
                <tr id="cat-row-{{ $cat->id }}">
                    <td>{{ $i + 1 }}</td>
                    <td class="font-weight-bold">{{ $cat->name }}</td>
                    <td>{{ $cat->department->name ?? '' }}</td>
                    <td>
                        <span class="badge badge-{{ $cat->category_type_badge }} px-3 py-1" style="border-radius:20px;">
                            {{ $cat->category_type_label }}
                        </span>
                    </td>
                    <td><small class="text-muted">{{ $cat->details }}</small></td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-success btn-edit" data-id="{{ $cat->id }}"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-xs btn-danger btn-delete" data-id="{{ $cat->id }}"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr id="empty-row">
                    <td colspan="6" class="text-center text-muted py-4">No categories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white"><i class="fas fa-plus mr-1"></i> Add Support Category</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="text-uppercase font-weight-bold small">Support Category <span class="text-danger">*</span></label>
                    <input type="text" id="add_name" class="form-control">
                    <div class="invalid-feedback" id="add_name_err"></div>
                </div>
                <div class="form-group">
                    <label class="text-uppercase font-weight-bold small">Responsible Department</label>
                    <select id="add_department_id" class="form-control">
                        <option value="">Select</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="text-uppercase font-weight-bold small">Details (Optional)</label>
                    <textarea id="add_details" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group d-flex align-items-center justify-content-between">
                    <label class="text-uppercase font-weight-bold small mb-0">
                        Do you want to save this category as <strong>Public</strong>?
                    </label>
                    <div class="d-flex align-items-center ml-3">
                        <span class="mr-2 text-muted small">NO</span>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="add_is_public">
                            <label class="custom-control-label" for="add_is_public"></label>
                        </div>
                        <span class="ml-2 text-muted small">YES</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" id="btnAddClear">Clear</button>
                <div>
                    <button type="button" class="btn btn-primary" id="btnAddSave"><i class="fas fa-save mr-1"></i> Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Support Category</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="form-group">
                    <label class="text-uppercase font-weight-bold small">Support Category <span class="text-danger">*</span></label>
                    <input type="text" id="edit_name" class="form-control">
                    <div class="invalid-feedback" id="edit_name_err"></div>
                </div>
                <div class="form-group">
                    <label class="text-uppercase font-weight-bold small">Responsible Department</label>
                    <select id="edit_department_id" class="form-control">
                        <option value="">Select</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="text-uppercase font-weight-bold small">Details (Optional)</label>
                    <textarea id="edit_details" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group d-flex align-items-center justify-content-between">
                    <label class="text-uppercase font-weight-bold small mb-0">
                        Do you want to save this category as <strong>Public</strong>?
                    </label>
                    <div class="d-flex align-items-center ml-3">
                        <span class="mr-2 text-muted small">NO</span>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_is_public">
                            <label class="custom-control-label" for="edit_is_public"></label>
                        </div>
                        <span class="ml-2 text-muted small">YES</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="btnEditSave"><i class="fas fa-save mr-1"></i> Update</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
const CSRF = '{{ csrf_token() }}';
let rowIndex = {{ $categories->count() }};

function badgeHtml(type) {
    const label = type === 'for_everyone' ? 'For Everyone' : 'Only For Office';
    const cls   = type === 'for_everyone' ? 'success' : 'warning';
    return `<span class="badge badge-${cls} px-3 py-1" style="border-radius:20px;">${label}</span>`;
}

function buildRow(cat, index) {
    return `<tr id="cat-row-${cat.id}">
        <td>${index}</td>
        <td class="font-weight-bold">${cat.name}</td>
        <td>${cat.department}</td>
        <td>${badgeHtml(cat.category_type)}</td>
        <td><small class="text-muted">${cat.details ?? ''}</small></td>
        <td class="text-center">
            <button class="btn btn-xs btn-success btn-edit" data-id="${cat.id}"><i class="fas fa-edit"></i></button>
            <button class="btn btn-xs btn-danger btn-delete" data-id="${cat.id}"><i class="fas fa-trash"></i></button>
        </td>
    </tr>`;
}

// Add Modal
$('#btnAddCategory').click(() => {
    $('#add_name').val('').removeClass('is-invalid');
    $('#add_department_id').val('');
    $('#add_details').val('');
    $('#add_is_public').prop('checked', false);
    $('#addCategoryModal').modal('show');
});

$('#btnAddClear').click(() => {
    $('#add_name').val('').removeClass('is-invalid');
    $('#add_department_id').val('');
    $('#add_details').val('');
    $('#add_is_public').prop('checked', false);
});

$('#btnAddSave').click(function () {
    const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
    $.ajax({
        url: '{{ route("support-categories.store") }}',
        method: 'POST',
        data: {
            _token:        CSRF,
            name:          $('#add_name').val(),
            department_id: $('#add_department_id').val(),
            category_type: $('#add_is_public').is(':checked') ? 'for_everyone' : 'only_for_office',
            details:       $('#add_details').val(),
        },
        success(res) {
            if (res.success) {
                $('#empty-row').remove();
                rowIndex++;
                $('#categoryTable tbody').append(buildRow(res.category, rowIndex));
                $('#totalCount').text(rowIndex + ' categories');
                $('#addCategoryModal').modal('hide');
                toastr.success(res.message);
            }
        },
        error(xhr) {
            const errors = xhr.responseJSON?.errors ?? {};
            $('#add_name').toggleClass('is-invalid', !!errors.name);
            $('#add_name_err').text(errors.name?.[0] ?? '');
        },
        complete() { btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save'); }
    });
});

// Edit
$(document).on('click', '.btn-edit', function () {
    const id = $(this).data('id');
    $.get(`/support-categories/${id}/edit`, function (res) {
        if (res.success) {
            const c = res.category;
            $('#edit_id').val(c.id);
            $('#edit_name').val(c.name).removeClass('is-invalid');
            $('#edit_department_id').val(c.department_id ?? '');
            $('#edit_details').val(c.details ?? '');
            $('#edit_is_public').prop('checked', c.category_type === 'for_everyone');
            $('#editCategoryModal').modal('show');
        }
    });
});

$('#btnEditSave').click(function () {
    const id  = $('#edit_id').val();
    const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
    $.ajax({
        url: `/support-categories/${id}`,
        method: 'POST',
        data: {
            _token:        CSRF,
            _method:       'PUT',
            name:          $('#edit_name').val(),
            department_id: $('#edit_department_id').val(),
            category_type: $('#edit_is_public').is(':checked') ? 'for_everyone' : 'only_for_office',
            details:       $('#edit_details').val(),
        },
        success(res) {
            if (res.success) {
                const c = res.category;
                const row = $(`#cat-row-${c.id}`);
                row.find('td:eq(1)').text(c.name);
                row.find('td:eq(2)').text(c.department);
                row.find('td:eq(3)').html(badgeHtml(c.category_type));
                row.find('td:eq(4)').html(`<small class="text-muted">${c.details ?? ''}</small>`);
                $('#editCategoryModal').modal('hide');
                toastr.success(res.message);
            }
        },
        error(xhr) {
            const errors = xhr.responseJSON?.errors ?? {};
            $('#edit_name').toggleClass('is-invalid', !!errors.name);
            $('#edit_name_err').text(errors.name?.[0] ?? '');
        },
        complete() { btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update'); }
    });
});

// Delete
$(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Delete Category?', icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Delete',
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/support-categories/${id}`,
                method: 'POST',
                data: { _token: CSRF, _method: 'DELETE' },
                success(res) {
                    if (res.success) {
                        $(`#cat-row-${id}`).remove();
                        rowIndex--;
                        $('#totalCount').text(rowIndex + ' categories');
                        toastr.success(res.message);
                    }
                },
                error(xhr) {
                    const msg = xhr.responseJSON?.message ?? 'Failed to delete.';
                    toastr.error(msg);
                }
            });
        }
    });
});
</script>
@endpush
