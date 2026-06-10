{{-- resources/views/expenses/categories.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Expense Categories')
@section('page_actions')
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Expenses
    </a>
    <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#addModal">
        <i class="fas fa-plus mr-1"></i> Add Category
    </button>
@endsection
@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Categories</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>নাম</th>
                    <th>Badge Preview</th>
                    <th>Icon</th>
                    <th>বিবরণ</th>
                    <th class="text-center">Expenses</th>
                    <th class="text-center">Status</th>
                    <th style="width:90px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $i => $cat)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="font-weight-bold">{{ $cat->name }}</td>
                    <td>
                        <span class="badge" style="{{ $cat->badgeStyle }}">
                            {{ $cat->name }}
                        </span>
                    </td>
                    <td>
                        @if($cat->icon)
                            <i class="{{ $cat->icon }}"></i>
                            <small class="text-muted">{{ $cat->icon }}</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($cat->description, 50) ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $cat->expenses_count }}</span>
                    </td>
                    <td class="text-center">
                        @if($cat->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-xs btn-warning btn-edit"
                                data-id="{{ $cat->id }}"
                                data-name="{{ $cat->name }}"
                                data-color="{{ $cat->color }}"
                                data-icon="{{ $cat->icon }}"
                                data-description="{{ $cat->description }}"
                                data-active="{{ $cat->is_active ? 1 : 0 }}"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if($cat->expenses_count == 0)
                            <form action="{{ route('expense-categories.destroy', $cat) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete করবেন?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">কোনো category নেই।</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('expense-categories.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus mr-1"></i> নতুন Category</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               placeholder="e.g. Office Supplies">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Color</label>
                                <input type="color" name="color" class="form-control"
                                       value="#185FA5" style="height:38px">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Icon (FontAwesome class)</label>
                                <input type="text" name="icon" class="form-control"
                                       placeholder="fas fa-box">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">বিবরণ</label>
                        <textarea name="description" rows="2" class="form-control"
                                  placeholder="Short description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Color</label>
                                <input type="color" name="color" id="editColor" class="form-control"
                                       style="height:38px">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Icon</label>
                                <input type="text" name="icon" id="editIcon" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">বিবরণ</label>
                        <textarea name="description" id="editDescription" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="editActive"
                                   name="is_active" value="1">
                            <label class="custom-control-label" for="editActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
document.querySelectorAll('.btn-edit').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var d = this.dataset;
        document.getElementById('editForm').action = '/expense-categories/' + d.id;
        document.getElementById('editName').value        = d.name;
        document.getElementById('editColor').value       = d.color;
        document.getElementById('editIcon').value        = d.icon;
        document.getElementById('editDescription').value = d.description;
        document.getElementById('editActive').checked    = d.active == '1';
        $('#editModal').modal('show');
    });
});
</script>
@endpush

@endsection
