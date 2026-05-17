<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a paginated list of all inventory items.
     * Also shows a count of items that have fallen below minimum stock level.
     */
    public function index()
    {
        $items = InventoryItem::withCount('transactions')
                              ->latest()
                              ->paginate(20);

        // Count items that are at or below their minimum stock threshold
        $lowStock = InventoryItem::lowStock()->count();

        return view('inventory.index', compact('items', 'lowStock'));
    }

    /**
     * Store a newly created inventory item in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'category'   => 'required|in:router,cable,onu,switch,splitter,other',
            'unit'       => 'required|string|max:20',  // e.g. pcs, meter, roll
            'unit_price' => 'required|numeric|min:0',
            'min_stock'  => 'required|integer|min:0',  // alert threshold
        ]);

        InventoryItem::create($request->all());

        return back()->with('success', 'Item added successfully.');
    }

    /**
     * Update the specified inventory item.
     */
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'unit_price' => 'required|numeric|min:0',
            'min_stock'  => 'required|integer|min:0',
        ]);

        $inventoryItem->update($request->all());

        return back()->with('success', 'Item updated successfully.');
    }

    /**
     * Delete the specified inventory item.
     */
    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return back()->with('success', 'Item deleted successfully.');
    }

    /**
     * Record a stock-in transaction (items received / purchased).
     * The model's booted() method automatically increments stock_quantity.
     */
    public function stockIn(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'quantity'  => 'required|integer|min:1',
            'reference' => 'nullable|string|max:100', // e.g. purchase order number
        ]);

        // Creating the transaction triggers stock increment via model observer
        InventoryTransaction::create([
            'item_id'   => $inventoryItem->id,
            'type'      => 'in',
            'quantity'  => $request->quantity,
            'reference' => $request->reference,
            'user_id'   => auth()->id(),
        ]);

        return back()->with('success', "{$request->quantity} unit(s) added to stock.");
    }

    /**
     * Record a stock-out transaction (items used or given to a customer).
     * The model's booted() method automatically decrements stock_quantity.
     * Will reject if requested quantity exceeds available stock.
     */
    public function stockOut(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'quantity'    => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id', // which customer received the item
            'reference'   => 'nullable|string|max:100',      // e.g. work order reference
        ]);

        // Block transaction if stock is insufficient
        if ($inventoryItem->stock_quantity < $request->quantity) {
            return back()->with('error',
                "Insufficient stock. Available: {$inventoryItem->stock_quantity} {$inventoryItem->unit}."
            );
        }

        // Creating the transaction triggers stock decrement via model observer
        InventoryTransaction::create([
            'item_id'     => $inventoryItem->id,
            'type'        => 'out',
            'quantity'    => $request->quantity,
            'reference'   => $request->reference,
            'customer_id' => $request->customer_id,
            'user_id'     => auth()->id(),
        ]);

        return back()->with('success', "{$request->quantity} unit(s) removed from stock.");
    }
}
