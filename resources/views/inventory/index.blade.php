{{-- resources/views/inventory/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Inventory')
@section('page_actions')
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addItemModal">
        <i class="fas fa-plus"></i> Add Item
    </button>
@endsection
@section('page_content')

@if($lowStock > 0)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>{{ $lowStock }}</strong> item(s) are below minimum stock level!
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr><th>Name</th><th>Category</th><th>Stock</th><th>Min Stock</th><th>Unit</th><th>Unit Price</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr class="{{ $item->is_low_stock ? 'table-warning' : '' }}">
                    <td>{{ $item->name }} @if($item->is_low_stock) <span class="badge badge-warning">Low Stock</span> @endif</td>
                    <td>{{ ucfirst($item->category) }}</td>
                    <td><strong>{{ $item->stock_quantity }}</strong> {{ $item->unit }}</td>
                    <td>{{ $item->min_stock }} {{ $item->unit }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ number_format($item->unit_price) }} BDT</td>
                    <td>
                        <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#stockIn{{ $item->id }}">
                            <i class="fas fa-plus"></i> In
                        </button>
                        <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#stockOut{{ $item->id }}">
                            <i class="fas fa-minus"></i> Out
                        </button>
                        <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>

                {{-- Stock In Modal --}}
                <div class="modal fade" id="stockIn{{ $item->id }}">
                    <div class="modal-dialog">
                        <form action="{{ route('inventory.stock-in', $item) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header"><h5>Stock In — {{ $item->name }}</h5></div>
                                <div class="modal-body">
                                    <div class="form-group"><label>Quantity</label><input type="number" name="quantity" class="form-control" min="1" required></div>
                                    <div class="form-group"><label>Reference</label><input type="text" name="reference" class="form-control" placeholder="Purchase order / note"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Add Stock</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Stock Out Modal --}}
                <div class="modal fade" id="stockOut{{ $item->id }}">
                    <div class="modal-dialog">
                        <form action="{{ route('inventory.stock-out', $item) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header"><h5>Stock Out — {{ $item->name }} (Available: {{ $item->stock_quantity }})</h5></div>
                                <div class="modal-body">
                                    <div class="form-group"><label>Quantity</label><input type="number" name="quantity" class="form-control" min="1" max="{{ $item->stock_quantity }}" required></div>
                                    <div class="form-group"><label>Reference</label><input type="text" name="reference" class="form-control" placeholder="Work order / note"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Remove Stock</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="7" class="text-center text-muted">No items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $items->links() }}</div>
</div>

{{-- Add Item Modal --}}
<div class="modal fade" id="addItemModal">
    <div class="modal-dialog">
        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>Add Inventory Item</h5></div>
                <div class="modal-body">
                    <div class="form-group"><label>Item Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            @foreach(['router','cable','onu','switch','splitter','other'] as $cat)
                                <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Unit</label><input type="text" name="unit" class="form-control" placeholder="pcs / meter / roll" required></div>
                    <div class="form-group"><label>Unit Price (BDT)</label><input type="number" name="unit_price" class="form-control" value="0" required></div>
                    <div class="form-group"><label>Minimum Stock Alert</label><input type="number" name="min_stock" class="form-control" value="5" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
