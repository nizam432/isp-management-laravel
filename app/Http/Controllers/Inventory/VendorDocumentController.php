<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Vendor;
use App\Models\Inventory\VendorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorDocumentController extends Controller
{
    public function store(Request $request, Vendor $vendor)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expiry_date'   => 'nullable|date',
            'note'          => 'nullable|string',
        ]);

        $path = $request->file('file')->store('inventory/vendor-documents', 'public');

        $vendor->documents()->create([
            'document_type' => $request->document_type,
            'file_path'     => $path,
            'expiry_date'   => $request->expiry_date,
            'note'          => $request->note,
            'created_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Vendor $vendor, VendorDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
