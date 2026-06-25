@extends('layouts.app')
@section('title', 'Vendor Profile')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ $vendor->name }} <small class="text-muted fs-6">{{ $vendor->vendor_no }}</small></h4>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.vendors.ledger', $vendor) }}" class="btn btn-outline-info btn-sm">Ledger</a>
            <a href="{{ route('inventory.vendors.edit', $vendor) }}" class="btn btn-outline-primary btn-sm">Edit</a>
            <a href="{{ route('inventory.vendors.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
        </div>
    </div>
    @include('inventory._partials.alerts')
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Basic Info</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Owner</td><td>{{ $vendor->owner_name ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Phone</td><td>{{ $vendor->phone }}</td></tr>
                        <tr><td class="text-muted">Alt Phone</td><td>{{ $vendor->alternate_phone ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Email</td><td>{{ $vendor->email ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Address</td><td>{{ $vendor->address ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Type</td><td>{{ ucfirst($vendor->vendor_type) }}</td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $vendor->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($vendor->status) }}</span></td></tr>
                        <tr><td class="text-muted">Total Due</td><td class="fw-bold text-danger">৳{{ number_format($vendor->total_due, 2) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Contact Persons --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    Contact Persons
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">+ Add</button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse($vendor->contacts as $contact)
                            <tr>
                                <td>
                                    {{ $contact->name }}
                                    @if($contact->is_primary) <span class="badge bg-primary">Primary</span> @endif
                                    <br><small class="text-muted">{{ $contact->designation }} | {{ $contact->phone }}</small>
                                </td>
                                <td>
                                    <form action="{{ route('inventory.vendors.contacts.destroy', [$vendor, $contact]) }}" method="POST"
                                          onsubmit="return confirm('Delete?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">✕</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td class="text-center text-muted py-2">No contacts</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Documents --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    Documents
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addDocModal">+ Upload</button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse($vendor->documents as $doc)
                            <tr>
                                <td>
                                    <a href="{{ $doc->file_url }}" target="_blank">{{ $doc->document_type }}</a>
                                    @if($doc->is_expired) <span class="badge bg-danger">Expired</span> @endif
                                    <br><small class="text-muted">{{ $doc->expiry_date?->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <form action="{{ route('inventory.vendors.documents.destroy', [$vendor, $doc]) }}" method="POST"
                                          onsubmit="return confirm('Delete?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">✕</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td class="text-center text-muted py-2">No documents</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Purchase History --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                    Purchase History
                    <a href="{{ route('inventory.purchases.create') }}" class="btn btn-sm btn-outline-primary">New Purchase</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Purchase No</th><th>Date</th><th>Amount</th><th>Paid</th><th>Due</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($vendor->purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->purchase_no }}</td>
                                <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                                <td>৳{{ number_format($purchase->total_amount, 2) }}</td>
                                <td>৳{{ number_format($purchase->paid_amount, 2) }}</td>
                                <td class="{{ $purchase->due_amount > 0 ? 'text-danger' : '' }}">৳{{ number_format($purchase->due_amount, 2) }}</td>
                                <td><span class="badge bg-{{ $purchase->status == 'received' ? 'success' : ($purchase->status == 'draft' ? 'warning' : 'secondary') }}">{{ ucfirst($purchase->status) }}</span></td>
                                <td><a href="{{ route('inventory.purchases.show', $purchase) }}" class="btn btn-sm btn-outline-info">View</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No purchases</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Contact Modal --}}
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.vendors.contacts.store', $vendor) }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Contact Person</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Phone *</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_primary" value="1"><label class="form-check-label">Primary Contact</label></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Add Document Modal --}}
<div class="modal fade" id="addDocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.vendors.documents.store', $vendor) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Document Type *</label><input type="text" name="document_type" class="form-control" placeholder="Trade License, NID, Agreement..." required></div>
                    <div class="mb-3"><label class="form-label">File *</label><input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required></div>
                    <div class="mb-3"><label class="form-label">Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Note</label><textarea name="note" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
