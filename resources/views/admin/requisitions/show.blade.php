@extends('layouts.admin')

@section('title', 'Requisition Details')
@section('page-title', 'Requisition Details - Admin View')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.requisitions.index') }}">Requisition Approvals</a></li>
    <li class="breadcrumb-item active">{{ $requisition->requisition_number }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requisition Information</h3>
                <div class="card-tools">
                    @if($requisition->approve_status === 'pending')
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    @elseif($requisition->approve_status === 'approved' && $requisition->clear_status !== 'cleared')
                        <a href="{{ route('admin.requisitions.issue-items', $requisition->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-box"></i> Issue Items
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($requisition->approve_status === 'pending')
                <div class="alert alert-warning">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Action Required:</strong> This requisition is pending your approval.
                </div>
                @elseif($requisition->approve_status === 'approved' && $requisition->clear_status === 'pending')
                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i>
                    <strong>Approved:</strong> This requisition is approved. Items can be issued now.
                </div>
                @elseif($requisition->clear_status === 'cleared')
                <div class="alert alert-success">
                    <i class="icon fas fa-check-circle"></i>
                    <strong>Cleared:</strong> All items have been issued for this requisition.
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Requisition Number:</strong></div>
                    <div class="col-md-8">
                        <span class="badge badge-secondary badge-lg">{{ $requisition->requisition_number }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Approval Status:</strong></div>
                    <div class="col-md-8">
                        @if($requisition->approve_status === 'pending')
                            <span class="badge badge-warning badge-lg">Pending</span>
                        @elseif($requisition->approve_status === 'approved')
                            <span class="badge badge-success badge-lg">Approved</span>
                        @else
                            <span class="badge badge-danger badge-lg">Rejected</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Clear Status:</strong></div>
                    <div class="col-md-8">
                        @if($requisition->clear_status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @else
                            <span class="badge badge-success">Cleared</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Requested By:</strong></div>
                    <div class="col-md-8">
                        {{ $requisition->user->name }}
                        <br><small class="text-muted">{{ $requisition->user->email }}</small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Department:</strong></div>
                    <div class="col-md-8">
                        {{ $requisition->department->name ?? '-' }}
                        @if($requisition->department && $requisition->department->short_code)
                            <span class="badge badge-secondary">{{ $requisition->department->short_code }}</span>
                        @endif
                    </div>
                </div>
                @if($requisition->subDepartment)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Sub-Department:</strong></div>
                    <div class="col-md-8">
                        {{ $requisition->subDepartment->name }}
                        @if($requisition->subDepartment->short_code)
                            <span class="badge badge-secondary">{{ $requisition->subDepartment->short_code }}</span>
                        @endif
                    </div>
                </div>
                @endif
                @if($requisition->division)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Division:</strong></div>
                    <div class="col-md-8">
                        {{ $requisition->division->name }}
                        @if($requisition->division->short_code)
                            <span class="badge badge-secondary">{{ $requisition->division->short_code }}</span>
                        @endif
                    </div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Purpose:</strong></div>
                    <div class="col-md-8">{{ $requisition->purpose }}</div>
                </div>
                @if($requisition->notes)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Notes:</strong></div>
                    <div class="col-md-8">{{ $requisition->notes }}</div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Created At:</strong></div>
                    <div class="col-md-8">{{ $requisition->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
                @if($requisition->approve_status !== 'pending')
                <div class="row mb-3">
                    <div class="col-md-4"><strong>{{ $requisition->approve_status === 'approved' ? 'Approved' : 'Rejected' }} By:</strong></div>
                    <div class="col-md-8">{{ $requisition->approvedBy->name ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>{{ $requisition->approve_status === 'approved' ? 'Approved' : 'Rejected' }} At:</strong></div>
                    <div class="col-md-8">{{ $requisition->approved_at ? $requisition->approved_at->format('Y-m-d H:i:s') : '-' }}</div>
                </div>
                @endif
                @if($requisition->approve_status === 'rejected' && $requisition->rejection_reason)
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Rejection Reason:</strong></div>
                    <div class="col-md-8">
                        <div class="alert alert-danger">{{ $requisition->rejection_reason }}</div>
                    </div>
                </div>
                @endif
                @if($requisition->clear_status === 'cleared')
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Cleared By:</strong></div>
                    <div class="col-md-8">{{ $requisition->clearedBy->name ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Cleared At:</strong></div>
                    <div class="col-md-8">{{ $requisition->cleared_at ? $requisition->cleared_at->format('Y-m-d H:i:s') : '-' }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requisition Items</h3>
                @if($requisition->approve_status === 'pending' && Auth::user()->hasPermission('approve-requisitions'))
                <div class="card-tools">
                    <button type="button" class="btn btn-warning btn-sm" id="toggleEditBtn" onclick="toggleEditMode()">
                        <i class="fas fa-edit"></i> Edit Items
                    </button>
                </div>
                @endif
            </div>

            {{-- Read-only view --}}
            <div id="viewItemsSection" class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>UOM</th>
                            <th>Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisition->items as $index => $item)
                        @php
                            $issuedQty = $item->issuedItems->sum('issued_quantity');
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $item->item_code }}</strong></td>
                            <td>
                                {{ $item->item_name }}
                                @if($item->specifications)
                                    <br><small class="text-muted"><i class="fas fa-info-circle"></i> {{ $item->specifications }}</small>
                                @endif
                            </td>
                            <td>{{ $item->item_category ?? '-' }}</td>
                            <td>{{ number_format($item->quantity, 4) }}</td>
                            <td>{{ $item->unit ?? '-' }}</td>
                            <td>
                                @if($issuedQty > 0)
                                    <span class="badge badge-{{ $item->isFullyIssued() ? 'success' : 'info' }}">
                                        {{ $issuedQty }} / {{ $item->quantity }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">0 / {{ $item->quantity }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Editable form (approver, only when pending) --}}
            @if($requisition->approve_status === 'pending' && Auth::user()->hasPermission('approve-requisitions'))
            <div id="editItemsSection" style="display:none;" class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Editing Mode:</strong> You can change quantities, add or remove items. Click <strong>Save Changes</strong> when done.
                </div>
                <form action="{{ route('admin.requisitions.update-items', $requisition->id) }}" method="POST" id="editItemsForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered" id="editItemsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:20%">Item Code</th>
                                    <th style="width:30%">Item Name</th>
                                    <th style="width:10%">Category</th>
                                    <th style="width:12%">Qty</th>
                                    <th style="width:8%">UOM</th>
                                    <th style="width:15%">Location</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="editItemsBody">
                                @foreach($requisition->items as $index => $item)
                                <tr class="edit-item-row">
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="items[{{ $index }}][item_code]" value="{{ $item->item_code }}" readonly style="background:#f4f4f4;">
                                        <input type="hidden" name="items[{{ $index }}][item_name]" value="{{ $item->item_name }}">
                                        <input type="hidden" name="items[{{ $index }}][item_category]" value="{{ $item->item_category }}">
                                        <input type="hidden" name="items[{{ $index }}][unit]" value="{{ $item->unit }}">
                                        <input type="hidden" name="items[{{ $index }}][location_code]" value="{{ $item->location_code }}">
                                    </td>
                                    <td>
                                        <span class="text-sm">{{ $item->item_name }}</span>
                                    </td>
                                    <td><small>{{ $item->item_category ?? '-' }}</small></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm"
                                            name="items[{{ $index }}][quantity]"
                                            value="{{ $item->quantity }}"
                                            min="0.0001" step="0.0001" required>
                                    </td>
                                    <td><small>{{ $item->unit ?? '-' }}</small></td>
                                    <td><small>{{ $item->location_code }}</small></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-xs" onclick="removeEditRow(this)" title="Remove item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="addNewItemRow()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm mr-2" onclick="toggleEditMode()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>

        {{-- Add Item Modal (for approver edit mode) --}}
        @if($requisition->approve_status === 'pending' && Auth::user()->hasPermission('approve-requisitions'))
        <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title">Add Item to Requisition</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Item Code <span class="text-danger">*</span></label>
                            <input type="text" id="newItemCode" class="form-control" placeholder="Enter item code">
                        </div>
                        <div class="form-group">
                            <label>Item Name <span class="text-danger">*</span></label>
                            <input type="text" id="newItemName" class="form-control" placeholder="Enter item name">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Quantity <span class="text-danger">*</span></label>
                                    <input type="number" id="newItemQty" class="form-control" min="0.0001" step="0.0001" value="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>UOM</label>
                                    <input type="text" id="newItemUnit" class="form-control" placeholder="e.g. EA, PCS">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category</label>
                                    <input type="text" id="newItemCategory" class="form-control" placeholder="e.g. LOCAL, IMPORT">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Location Code <span class="text-danger">*</span></label>
                            <input type="text" id="newItemLocation" class="form-control" placeholder="Enter location code">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="confirmAddItem()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($requisition->purchaseOrderItems->count() > 0)
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Purchase Order Items (Not Available in Stock)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisition->purchaseOrderItems as $index => $poItem)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $poItem->item_code }}</strong></td>
                            <td>{{ $poItem->item_name }}</td>
                            <td>{{ $poItem->quantity }} {{ $poItem->unit }}</td>
                            <td>
                                <span class="badge badge-{{ $poItem->status === 'pending' ? 'warning' : 'success' }}">
                                    {{ ucfirst($poItem->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($requisition->issuedItems->count() > 0)
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Issued Items History</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Quantity Issued</th>
                            <th>Issued By</th>
                            <th>Issued At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisition->issuedItems as $index => $issuedItem)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $issuedItem->item_name }}</strong>
                                <br><small class="text-muted">{{ $issuedItem->item_code }}</small>
                            </td>
                            <td>{{ $issuedItem->issued_quantity }} {{ $issuedItem->unit }}</td>
                            <td>{{ $issuedItem->issuedBy->name ?? '-' }}</td>
                            <td>{{ $issuedItem->issued_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card-footer">
            <a href="{{ route('admin.requisitions.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($requisition->approve_status === 'pending')
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                    <i class="fas fa-times"></i> Reject
                </button>
            @elseif($requisition->approve_status === 'approved' && $requisition->clear_status !== 'cleared')
                <a href="{{ route('admin.requisitions.issue-items', $requisition->id) }}" class="btn btn-primary">
                    <i class="fas fa-box"></i> Issue Items
                </a>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Summary</h3>
            </div>
            <div class="card-body">
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Items</span>
                        <span class="info-box-number">{{ $requisition->items->count() }}</span>
                    </div>
                </div>
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Quantity</span>
                        <span class="info-box-number">{{ $requisition->items->sum('quantity') }}</span>
                    </div>
                </div>
                @if($requisition->approve_status === 'approved')
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Items Issued</span>
                        <span class="info-box-number text-success">
                            {{ $requisition->items->filter(fn($item) => $item->isFullyIssued())->count() }} / {{ $requisition->items->count() }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requester Information</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-user mr-1"></i> Name</strong>
                <p class="text-muted">{{ $requisition->user->name }}</p>
                <hr>
                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                <p class="text-muted">{{ $requisition->user->email }}</p>
                <hr>
                <strong><i class="fas fa-user-tag mr-1"></i> Roles</strong>
                <p class="text-muted">
                    @foreach($requisition->user->roles as $role)
                        <span class="badge badge-info">{{ $role->name }}</span>
                    @endforeach
                </p>
            </div>
        </div>
    </div>
</div>

@if($requisition->approve_status === 'pending' && Auth::user()->hasPermission('approve-requisitions'))
<script>
let editMode = false;
let newRowIndex = {{ $requisition->items->count() }};

function toggleEditMode() {
    editMode = !editMode;
    document.getElementById('viewItemsSection').style.display = editMode ? 'none' : 'block';
    document.getElementById('editItemsSection').style.display = editMode ? 'block' : 'none';
    document.getElementById('toggleEditBtn').innerHTML = editMode
        ? '<i class="fas fa-eye"></i> View Mode'
        : '<i class="fas fa-edit"></i> Edit Items';
}

function removeEditRow(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('#editItemsBody tr.edit-item-row').length <= 1) {
        alert('At least one item is required.');
        return;
    }
    row.remove();
    reindexEditRows();
}

function reindexEditRows() {
    document.querySelectorAll('#editItemsBody tr.edit-item-row').forEach((row, idx) => {
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/items\[\d+\]/, `items[${idx}]`);
        });
    });
    newRowIndex = document.querySelectorAll('#editItemsBody tr.edit-item-row').length;
}

function addNewItemRow() {
    $('#addItemModal').modal('show');
}

function confirmAddItem() {
    const code     = document.getElementById('newItemCode').value.trim();
    const name     = document.getElementById('newItemName').value.trim();
    const qty      = document.getElementById('newItemQty').value;
    const unit     = document.getElementById('newItemUnit').value.trim();
    const category = document.getElementById('newItemCategory').value.trim();
    const location = document.getElementById('newItemLocation').value.trim();

    if (!code || !name || !qty || !location) {
        alert('Item Code, Item Name, Quantity, and Location Code are required.');
        return;
    }

    const idx = newRowIndex++;
    const row = `
        <tr class="edit-item-row table-success">
            <input type="hidden" name="items[${idx}][id]" value="">
            <td>
                <input type="text" class="form-control form-control-sm" name="items[${idx}][item_code]" value="${code}" readonly style="background:#f4f4f4;">
                <input type="hidden" name="items[${idx}][item_name]" value="${name}">
                <input type="hidden" name="items[${idx}][item_category]" value="${category}">
                <input type="hidden" name="items[${idx}][unit]" value="${unit}">
                <input type="hidden" name="items[${idx}][location_code]" value="${location}">
            </td>
            <td><span class="text-sm">${name}</span></td>
            <td><small>${category || '-'}</small></td>
            <td>
                <input type="number" class="form-control form-control-sm"
                    name="items[${idx}][quantity]"
                    value="${qty}"
                    min="0.0001" step="0.0001" required>
            </td>
            <td><small>${unit || '-'}</small></td>
            <td><small>${location}</small></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs" onclick="removeEditRow(this)" title="Remove item">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;

    document.getElementById('editItemsBody').insertAdjacentHTML('beforeend', row);
    $('#addItemModal').modal('hide');
    // Clear modal fields
    ['newItemCode','newItemName','newItemQty','newItemUnit','newItemCategory','newItemLocation']
        .forEach(id => { const el = document.getElementById(id); if(id==='newItemQty') el.value='1'; else el.value=''; });
}
</script>
@endif

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.requisitions.approve', $requisition->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="approveModalLabel">Approve Requisition</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this requisition?</p>
                    <p><strong>Requisition #:</strong> {{ $requisition->requisition_number }}</p>
                    {{-- <p><strong>Total Amount:</strong> Rs.{{ number_format($requisition->items->sum('total_price'), 2) }}</p> --}}
                    
                    @if($requisition->purchaseOrderItems->count() > 0)
                        <div class="alert alert-warning">
                            <strong>Note:</strong> This requisition has {{ $requisition->purchaseOrderItems->count() }} item(s) that require purchase orders.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.requisitions.reject', $requisition->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Requisition</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this requisition:</p>
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Explain why this requisition is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection