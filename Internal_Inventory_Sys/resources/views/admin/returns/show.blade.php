@extends('layouts.admin')

@section('title', 'Return Details')
@section('page-title', 'Return Details - Admin View')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.returns.index') }}">Return Approvals</a></li>
    <li class="breadcrumb-item active">#{{ $return->id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Return Information</h3>
                <div class="card-tools">
                    @if($return->status === 'pending')
                        <a href="{{ route('admin.returns.approve-items', $return->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-check-double"></i> Process Items
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($return->status === 'pending')
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Action Required:</strong> This return is pending approval. Process items individually.
                </div>
                @elseif($return->status === 'cleared')
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Cleared:</strong> All items have been processed for this return.
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Return ID:</th>
                                <td><span class="badge badge-secondary badge-lg">#{{ $return->id }}</span></td>
                            </tr>
                            <tr>
                                <th>Requisition:</th>
                                <td>
                                    <a href="{{ route('requisitions.show', $return->requisition->id) }}" target="_blank">
                                        {{ $return->requisition->requisition_number }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td>{{ $return->requisition->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Returned By:</th>
                                <td>{{ $return->returnedBy->name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Returned At:</th>
                                <td>{{ $return->returned_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($return->status === 'pending')
                                        <span class="badge badge-warning badge-lg">Pending</span>
                                    @else
                                        <span class="badge badge-success badge-lg">Cleared</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Total Items:</th>
                                <td><span class="badge badge-info">{{ $return->items->count() }}</span></td>
                            </tr>
                            <tr>
                                <th>Total Quantity:</th>
                                <td><span class="badge badge-primary">{{ $return->total_quantity }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Return Items</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $index => $item)
                        <tr class="{{ $item->approve_status === 'approved' ? 'table-success' : ($item->approve_status === 'rejected' ? 'table-danger' : ($item->approve_status === 'partial' ? 'table-warning' : '')) }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->item_name }}</strong>
                                <br><small class="text-muted">{{ $item->item_code }}</small>
                                @if($item->notes)
                                    <br><small><i class="fas fa-info-circle"></i> {{ $item->notes }}</small>
                                @endif
                                @if($item->admin_note)
                                    <br><small class="text-primary"><i class="fas fa-comment"></i> Admin: {{ $item->admin_note }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->type_badge }}">
                                    {{ $item->getTypeLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $item->location_code }}</span><br>
                                <small>{{ $item->location_name }}</small>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $item->quantity }} {{ $item->unit }}</span>
                            </td>
                            <td>
                                @if($item->approve_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($item->approve_status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($item->approve_status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @elseif($item->approve_status === 'partial')
                                    <span class="badge badge-warning">Partial</span>
                                @endif
                                @if($item->approved_at)
                                    <br><small class="text-muted">{{ $item->approved_at->format('Y-m-d H:i') }}</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($return->grnItems->count() > 0)
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">GRN Items (Posted to SAGE)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>SAGE Ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->grnItems as $index => $grn)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $grn->item_name }}</strong>
                                <br><small class="text-muted">{{ $grn->item_code }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $grn->location_code }}</span>
                            </td>
                            <td><span class="badge badge-success">{{ $grn->grn_quantity }} {{ $grn->unit }}</span></td>
                            <td>Rs. {{ number_format($grn->unit_price, 2) }}</td>
                            <td>Rs. {{ number_format($grn->total_price, 2) }}</td>
                            <td>
                                @if($grn->reference_number_1)
                                    <small>{{ $grn->reference_number_1 }}</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($return->scrapItems->count() > 0)
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Scrap Items (DB Only)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->scrapItems as $index => $scrap)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $scrap->item_name }}</strong>
                                <br><small class="text-muted">{{ $scrap->item_code }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $scrap->location_code }}</span>
                            </td>
                            <td><span class="badge badge-danger">{{ $scrap->scrap_quantity }} {{ $scrap->unit }}</span></td>
                            <td>Rs. {{ number_format($scrap->unit_price, 2) }}</td>
                            <td>Rs. {{ number_format($scrap->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card-footer">
            <a href="{{ route('admin.returns.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($return->status === 'pending' && $return->items->where('approve_status', 'pending')->count() > 0)
                <a href="{{ route('admin.returns.approve-items', $return->id) }}" class="btn btn-primary">
                    <i class="fas fa-check-double"></i> Process Items
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
                        <span class="info-box-number">{{ $return->items->count() }}</span>
                    </div>
                </div>
                <div class="info-box bg-warning">
                    <div class="info-box-content">
                        <span class="info-box-text">Pending</span>
                        <span class="info-box-number">{{ $return->items->where('approve_status', 'pending')->count() }}</span>
                    </div>
                </div>
                <div class="info-box bg-success">
                    <div class="info-box-content">
                        <span class="info-box-text">Approved</span>
                        <span class="info-box-number">{{ $return->items->where('approve_status', 'approved')->count() }}</span>
                    </div>
                </div>
                <div class="info-box bg-danger">
                    <div class="info-box-content">
                        <span class="info-box-text">Rejected</span>
                        <span class="info-box-number">{{ $return->items->where('approve_status', 'rejected')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requester Info</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-user"></i> Name</strong>
                <p class="text-muted">{{ $return->returnedBy->name }}</p>
                <hr>
                <strong><i class="fas fa-envelope"></i> Email</strong>
                <p class="text-muted">{{ $return->returnedBy->email }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Original Requisition</h3>
            </div>
            <div class="card-body">
                <strong>Number:</strong>
                <p>
                    <a href="{{ route('requisitions.show', $return->requisition->id) }}" target="_blank">
                        {{ $return->requisition->requisition_number }}
                    </a>
                </p>
                <hr>
                <strong>Department:</strong>
                <p>{{ $return->requisition->department->name }}</p>
                <hr>
                <strong>Created:</strong>
                <p>{{ $return->requisition->created_at->format('Y-m-d') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection