@extends('adminlte::page')
@section('title', 'Product Categories')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-tags mr-2 text-primary"></i>Product Categories
            </h4>
            <small class="text-muted">Manage inventory categories</small>
        </div>
        <a href="{{ route('inventory.categories.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-plus mr-1"></i> Add Category
        </a>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="card shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center"
         style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
        <h6 class="m-0 text-white font-weight-bold">
            <i class="fas fa-list mr-1"></i> Category List
        </h6>
        <input type="text" id="searchInput" class="form-control form-control-sm"
               placeholder="Quick search..." style="width:220px; border-radius:20px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="categoryTable">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <th class="text-center" style="width:50px;">#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Products</th>
                        <th class="text-center" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    @forelse($categories as $category)
                    <tr>
                        <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                        <td class="font-weight-bold">{{ $category->name }}</td>
                        <td class="text-muted">{{ $category->description ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge badge-secondary">{{ $category->products_count }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('inventory.categories.edit', $category) }}"
                               class="btn btn-sm btn-warning px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($category->isDeletable())
                            <form action="{{ route('inventory.categories.destroy', $category) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger px-2" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-tags fa-3x mb-3 d-block" style="opacity:.2;"></i>
                            No categories found. Click <strong>+ Add Category</strong> to add one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($categories->hasPages())
    <div class="card-footer bg-light py-2">
        {{ $categories->links() }}
    </div>
    @endif
</div>

@endsection

@section('css')
<style>
    #categoryTable thead th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #555;
        padding: 10px 12px;
    }
    #categoryTable tbody td { padding: 10px 12px; vertical-align: middle; }
    #categoryTable tbody tr:hover { background:#f0f4ff !important; }
    #searchInput:focus { box-shadow: 0 0 0 3px rgba(26,35,126,.15); border-color:#1a237e; }
</style>
@stop

@section('js')
@parent
<script>
$(function () {
    $('#searchInput').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('#categoryTableBody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });
});
</script>
@stop
