{{-- resources/views/incomes/categories.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Income Categories')
@section('page_actions')
    <a href="{{ route('incomes.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Income
    </a>
    <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#addIncomeCategoryModal">
        <i class="fas fa-plus mr-1"></i> Add Category
    </button>
@endsection
@section('page_content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Income Categories</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Name</th>
                    <th>Badge</th>
                    <th>Icon</th>
                    <th>Description</th>
                    <th class="text-center">Entries</th>
                    <th class="text-center">Status</th>
                    <th style="width:90px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $i => $cat)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="font-weight-bold">{{ $cat->name }}</td>
                    <td><span class="badge" style="{{ $cat->badgeStyle }}">{{ $cat->name }}</span></td>
                    <td>
                        @if($cat->icon)
                            <i class="{{ $cat->icon }}"></i>
                            <small class="text-muted ml-1">{{ $cat->icon }}</small>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td>{{ Str::limit($cat->description, 45) ?? '—' }}</td>
                    <td class="text-center"><span class="badge badge-secondary">{{ $cat->incomes_count }}</span></td>
                    <td class="text-center">
                        @if($cat->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-xs btn-warning btn-edit-income-cat"
                                data-id="{{ $cat->id }}"
                                data-name="{{ $cat->name }}"
                                data-color="{{ $cat->color }}"
                                data-icon="{{ $cat->icon }}"
                                data-description="{{ $cat->description }}"
                                data-active="{{ $cat->is_active ? 1 : 0 }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if($cat->incomes_count == 0)
                        <form action="{{ route('income-categories.destroy', $cat) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No categories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addIncomeCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('income-categories.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus mr-1"></i> Add Income Category</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Connection Fee">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Color</label>
                                <input type="color" name="color" class="form-control" value="#185FA5" style="height:38px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Icon (FontAwesome)</label>
                                <input type="text" name="icon" class="form-control" placeholder="fas fa-coins">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Description</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Short description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save mr-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editIncomeCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editIncomeCatForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editIncomeCatName" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Color</label>
                                <input type="color" name="color" id="editIncomeCatColor" class="form-control" style="height:38px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Icon</label>
                                <input type="text" name="icon" id="editIncomeCatIcon" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Description</label>
                        <textarea name="description" id="editIncomeCatDesc" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="custom-control-input" id="editIncomeCatActive" name="is_active" value="1">
                            <label class="custom-control-label" for="editIncomeCatActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-save mr-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
document.querySelectorAll('.btn-edit-income-cat').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var d = this.dataset;
        document.getElementById('editIncomeCatForm').action = '/income-categories/' + d.id;
        document.getElementById('editIncomeCatName').value  = d.name;
        document.getElementById('editIncomeCatColor').value = d.color;
        document.getElementById('editIncomeCatIcon').value  = d.icon;
        document.getElementById('editIncomeCatDesc').value  = d.description;
        document.getElementById('editIncomeCatActive').checked = d.active == '1';
        $('#editIncomeCategoryModal').modal('show');
    });
});
</script>
@endpush

@endsection
