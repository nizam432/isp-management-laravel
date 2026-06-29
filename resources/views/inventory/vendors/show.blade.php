@extends('adminlte::page')
@section('title', 'Vendor Profile')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-handshake mr-2 text-primary"></i>{{ $vendor->name }}
                <small class="text-muted" style="font-size:14px;">{{ $vendor->vendor_no }}</small>
            </h4>
            <small class="text-muted">Vendor profile &amp; purchase history</small>
        </div>
        <div>
            <a href="{{ route('inventory.vendors.ledger', $vendor) }}" class="btn btn-info btn-sm px-3 mr-1">
                <i class="fas fa-book mr-1"></i> Ledger
            </a>
            <a href="{{ route('inventory.vendors.edit', $vendor) }}" class="btn btn-warning btn-sm px-3 mr-1">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('inventory.vendors.index') }}" class="btn btn-secondary btn-sm px-3">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')

@include('inventory._partials.alerts')

<div class="row">
    {{-- Left Column --}}
    <div class="col-md-4">

        {{-- Basic Info --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-user-tie mr-1"></i> Basic Info
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="small text-muted pl-3" style="width:40%">Owner</td>
                            <td class="pr-3">{{ $vendor->owner_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Phone</td>
                            <td class="pr-3">{{ $vendor->phone }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Alt Phone</td>
                            <td class="pr-3">{{ $vendor->alternate_phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Email</td>
                            <td class="pr-3">{{ $vendor->email ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Address</td>
                            <td class="pr-3">{{ $vendor->address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Type</td>
                            <td class="pr-3">
                                <span class="badge badge-light border">{{ ucfirst($vendor->vendor_type) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Status</td>
                            <td class="pr-3">
                                <span class="badge badge-{{ $vendor->status == 'active' ? 'success' : ($vendor->status == 'blacklisted' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($vendor->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="small text-muted pl-3">Total Due</td>
                            <td class="pr-3 font-weight-bold text-danger">
                                ৳{{ number_format($vendor->total_due, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Contact Persons --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-address-book mr-1"></i> Contact Persons
                </h6>
                <button class="btn btn-primary btn-xs px-2" data-toggle="modal" data-target="#addContactModal">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($vendor->contacts as $contact)
                        <tr>
                            <td class="pl-3">
                                <span class="font-weight-bold">{{ $contact->name }}</span>
                                @if($contact->is_primary)
                                    <span class="badge badge-primary ml-1">Primary</span>
                                @endif
                                <br>
                                <small class="text-muted">
                                    {{ $contact->designation }}
                                    @if($contact->designation && $contact->phone) | @endif
                                    {{ $contact->phone }}
                                </small>
                            </td>
                            <td class="text-right pr-3" style="width:40px;">
                                <form action="{{ route('inventory.vendors.contacts.destroy', [$vendor, $contact]) }}"
                                      method="POST" onsubmit="return confirm('Delete this contact?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger px-2">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center text-muted py-3 small">No contacts added</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Documents --}}
        <div class="card shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                <h6 class="m-0 font-weight-bold text-muted">
                    <i class="fas fa-file-alt mr-1"></i> Documents
                </h6>
                <button class="btn btn-primary btn-xs px-2" data-toggle="modal" data-target="#addDocModal">
                    <i class="fas fa-upload"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($vendor->documents as $doc)
                        <tr>
                            <td class="pl-3">
                                <a href="{{ $doc->file_url }}" target="_blank">{{ $doc->document_type }}</a>
                                @if($doc->is_expired)
                                    <span class="badge badge-danger ml-1">Expired</span>
                                @endif
                                <br>
                                <small class="text-muted">{{ $doc->expiry_date?->format('d M Y') }}</small>
                            </td>
                            <td class="text-right pr-3" style="width:40px;">
                                <form action="{{ route('inventory.vendors.documents.destroy', [$vendor, $doc]) }}"
                                      method="POST" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger px-2">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center text-muted py-3 small">No documents uploaded</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Right Column: Purchase History --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#1a237e 0%,#283593 100%);">
                <h6 class="m-0 text-white font-weight-bold">
                    <i class="fas fa-shopping-cart mr-1"></i> Purchase History
                </h6>
                <a href="{{ route('inventory.purchases.create') }}" class="btn btn-light btn-sm px-3">
                    <i class="fas fa-plus mr-1"></i> New Purchase
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                                <th class="small text-uppercase" style="font-size:11px;color:#555;padding:10px 12px;">Purchase No</th>
                                <th class="small text-uppercase" style="font-size:11px;color:#555;padding:10px 12px;">Date</th>
                                <th class="small text-uppercase text-right" style="font-size:11px;color:#555;padding:10px 12px;">Amount</th>
                                <th class="small text-uppercase text-right" style="font-size:11px;color:#555;padding:10px 12px;">Paid</th>
                                <th class="small text-uppercase text-right" style="font-size:11px;color:#555;padding:10px 12px;">Due</th>
                                <th class="small text-uppercase text-center" style="font-size:11px;color:#555;padding:10px 12px;">Status</th>
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendor->purchases as $purchase)
                            <tr>
                                <td style="padding:10px 12px;" class="font-weight-bold">{{ $purchase->purchase_no }}</td>
                                <td style="padding:10px 12px;" class="text-muted small">{{ $purchase->purchase_date->format('d M Y') }}</td>
                                <td style="padding:10px 12px;" class="text-right font-weight-bold">৳{{ number_format($purchase->total_amount, 2) }}</td>
                                <td style="padding:10px 12px;" class="text-right text-success">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                                <td style="padding:10px 12px;" class="text-right {{ $purchase->due_amount > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                    ৳{{ number_format($purchase->due_amount, 2) }}
                                </td>
                                <td style="padding:10px 12px;" class="text-center">
                                    <span class="badge badge-{{ $purchase->status == 'received' ? 'success' : ($purchase->status == 'draft' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                                <td style="padding:10px 12px;" class="text-center">
                                    <a href="{{ route('inventory.purchases.show', $purchase) }}"
                                       class="btn btn-sm btn-info px-2" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-shopping-cart fa-3x mb-3 d-block" style="opacity:.2;"></i>
                                    No purchases yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Contact Modal --}}
<div class="modal fade" id="addContactModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('inventory.vendors.contacts.store', $vendor) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Add Contact Person</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Designation</label>
                        <input type="text" name="designation" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="isPrimary" name="is_primary" value="1">
                        <label class="custom-control-label" for="isPrimary">Set as Primary Contact</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Document Modal --}}
<div class="modal fade" id="addDocModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('inventory.vendors.documents.store', $vendor) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Upload Document</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Document Type <span class="text-danger">*</span></label>
                        <input type="text" name="document_type" class="form-control"
                               placeholder="Trade License, NID, Agreement..." required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control-file"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Accepted: PDF, JPG, PNG</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .card-header h6 { font-size: 13px; letter-spacing: .3px; }
    .btn-xs { padding: 2px 6px; font-size: 11px; }
    .table tbody td { vertical-align: middle; }
</style>
@stop
