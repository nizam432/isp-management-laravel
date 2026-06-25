<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ClientDeviceAssignment;
use App\Models\Inventory\Product;
use App\Models\Inventory\StoreLocation;
use App\Models\Inventory\LocationStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientDeviceAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $assignments = ClientDeviceAssignment::with('client', 'product', 'location')
                            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
                            ->when($request->status === 'active', fn($q) => $q->active())
                            ->when($request->status === 'returned', fn($q) => $q->returned())
                            ->latest()
                            ->paginate(20);

        return view('inventory.assignments.index', compact('assignments'));
    }

    public function create()
    {
        $clients   = Customer::active()->get();
        $products  = Product::with('category')->inStock()->get();
        $locations = StoreLocation::active()->get();

        return view('inventory.assignments.create', compact('clients', 'products', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'     => 'required|exists:customers,id',
            'product_id'    => 'required|exists:inventory_products,id',
            'location_id'   => 'required|exists:inventory_store_locations,id',
            'serial_no'     => 'nullable|string|max:255',
            'assigned_date' => 'required|date',
            'note'          => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock_quantity < 1) {
            return back()->with('error', 'Insufficient stock for this product.');
        }

        DB::transaction(function () use ($request, $product) {
            ClientDeviceAssignment::create([
                ...$request->only(['client_id', 'product_id', 'location_id', 'serial_no', 'assigned_date', 'note']),
                'assigned_by' => auth()->id(),
            ]);

            // Stock কমাও
            $product->decrement('stock_quantity', 1);

            LocationStock::where('product_id', $request->product_id)
                         ->where('location_id', $request->location_id)
                         ->decrement('quantity', 1);

            // Stock transaction
            StockTransaction::create([
                'product_id'     => $request->product_id,
                'location_id'    => $request->location_id,
                'type'           => 'out',
                'reason'         => 'sale',
                'reference_type' => 'client_assignment',
                'quantity'       => 1,
                'note'           => 'Device Assigned to Client #' . $request->client_id,
                'created_by'     => auth()->id(),
            ]);
        });

        return redirect()->route('inventory.assignments.index')
                         ->with('success', 'Device assigned successfully.');
    }

    public function show(ClientDeviceAssignment $assignment)
    {
        $assignment->load('client', 'product', 'location', 'assignedBy', 'returnedTo');

        return view('inventory.assignments.show', compact('assignment'));
    }

    // Device Return (Stock বাড়বে)
    public function return(Request $request, ClientDeviceAssignment $assignment)
    {
        $request->validate([
            'return_date' => 'required|date',
            'note'        => 'nullable|string',
        ]);

        if ($assignment->isReturned()) {
            return back()->with('error', 'This device is already returned.');
        }

        DB::transaction(function () use ($request, $assignment) {
            $assignment->update([
                'return_date' => $request->return_date,
                'returned_to' => auth()->id(),
                'note'        => $request->note ?? $assignment->note,
            ]);

            // Stock বাড়াও
            $assignment->product->increment('stock_quantity', 1);

            LocationStock::updateOrCreate(
                ['product_id' => $assignment->product_id, 'location_id' => $assignment->location_id],
                ['quantity'   => DB::raw('quantity + 1')]
            );

            // Stock transaction
            StockTransaction::create([
                'product_id'     => $assignment->product_id,
                'location_id'    => $assignment->location_id,
                'type'           => 'in',
                'reason'         => 'return',
                'reference_type' => 'client_assignment',
                'reference_id'   => $assignment->id,
                'quantity'       => 1,
                'note'           => 'Device Returned from Client #' . $assignment->client_id,
                'created_by'     => auth()->id(),
            ]);
        });

        return redirect()->route('inventory.assignments.show', $assignment)
                         ->with('success', 'Device returned. Stock updated.');
    }
}
