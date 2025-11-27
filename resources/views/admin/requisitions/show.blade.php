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
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach($requisition->items as $index => $item)
                        @php 
                            $grandTotal += $item->total_price; 
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
                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
                            <td><strong>${{ number_format($item->total_price, 2) }}</strong></td>
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
                        <tr class="bg-light">
                            <td colspan="6" class="text-right"><strong>Grand Total:</strong></td>
                            <td colspan="2"><strong class="text-primary">${{ number_format($grandTotal, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

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
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Amount</span>
                        <span class="info-box-number">${{ number_format($requisition->items->sum('total_price'), 2) }}</span>
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
                    <p><strong>Total Amount:</strong> ${{ number_format($requisition->items->sum('total_price'), 2) }}</p>
                    
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