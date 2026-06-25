<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')
                        ->latest()
                        ->paginate(20);

        return view('inventory.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('inventory.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:inventory_product_categories,name',
            'description' => 'nullable|string',
        ]);

        ProductCategory::create([
            'name'        => $request->name,
            'description' => $request->description,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->route('inventory.categories.index')
                         ->with('success', 'Category created successfully.');
    }

    public function edit(ProductCategory $category)
    {
        return view('inventory.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:inventory_product_categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('inventory.categories.index')
                         ->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category)
    {
        if (!$category->isDeletable()) {
            return back()->with('error', 'This category has products and cannot be deleted.');
        }

        $category->delete();

        return redirect()->route('inventory.categories.index')
                         ->with('success', 'Category deleted successfully.');
    }
}
