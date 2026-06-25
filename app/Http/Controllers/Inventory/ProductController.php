<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductCategory;
use App\Models\Inventory\StockTransaction;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
                    ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
                    ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
                    ->when($request->low_stock, fn($q) => $q->lowStock())
                    ->latest()
                    ->paginate(20);

        $categories = ProductCategory::all();

        return view('inventory.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = ProductCategory::all();
        return view('inventory.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'     => 'required|exists:inventory_product_categories,id',
            'name'            => 'required|string|max:255',
            'model'           => 'nullable|string|max:255',
            'unit'            => 'required|in:pcs,meter,roll,box',
            'meter_per_roll'  => 'nullable|integer|min:1',
            'low_stock_alert' => 'required|integer|min:0',
            'purchase_price'  => 'nullable|numeric|min:0',
            'sell_price'      => 'nullable|numeric|min:0',
        ]);

        Product::create([
            ...$request->only([
                'category_id', 'name', 'model', 'unit',
                'meter_per_roll', 'low_stock_alert',
                'purchase_price', 'sell_price',
            ]),
            'stock_quantity' => 0,
            'created_by'     => auth()->id(),
        ]);

        return redirect()->route('inventory.products.index')
                         ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'locationStocks.location');

        $transactions = StockTransaction::with(['location', 'createdBy'])
                            ->where('product_id', $product->id)
                            ->latest()
                            ->paginate(20);

        return view('inventory.products.show', compact('product', 'transactions'));
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::all();
        return view('inventory.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id'     => 'required|exists:inventory_product_categories,id',
            'name'            => 'required|string|max:255',
            'model'           => 'nullable|string|max:255',
            'unit'            => 'required|in:pcs,meter,roll,box',
            'meter_per_roll'  => 'nullable|integer|min:1',
            'low_stock_alert' => 'required|integer|min:0',
            'purchase_price'  => 'nullable|numeric|min:0',
            'sell_price'      => 'nullable|numeric|min:0',
        ]);

        $product->update($request->only([
            'category_id', 'name', 'model', 'unit',
            'meter_per_roll', 'low_stock_alert',
            'purchase_price', 'sell_price',
        ]));

        return redirect()->route('inventory.products.index')
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if (!$product->isDeletable()) {
            return back()->with('error', 'This product has transactions and cannot be deleted.');
        }

        $product->delete();

        return redirect()->route('inventory.products.index')
                         ->with('success', 'Product deleted successfully.');
    }
}
