@extends('layouts.admin')

@section('title', 'Return Details')
@section('page-title', 'Return Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Returns</a></li>
    <li class="breadcrumb-item active">Details</li>
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
                <h3 class="card-title">Return Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Requisition:</th>
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
                            @if($return->requisition->subDepartment)
                            <tr>
                                <th>Sub-Department:</th>
                                <td>{{ $return->requisition->subDepartment->name }}</td>
                            </tr>
                            @endif
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
                                        <span class="badge badge-warning">Pending Approval</span>
                                    @elseif($return->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($return->status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($return->status) }}</span>
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">Type</th>
                                <th width="10%">Code</th>
                                <th width="20%">Name</th>
                                <th width="10%">Category</th>
                                <th width="15%">Return Location</th>
                                <th width="8%">Qty</th>
                                <th width="7%">Unit</th>
                                <th width="15%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge badge-{{ $item->type_badge }}">
                                        {{ $item->getTypeLabel() }}
                                    </span>
                                </td>
                                <td>{{ $item->item_code }}</td>
                                <td>
                                    {{ $item->item_name }}
                                    @if($item->notes)
                                        <br><small class="text-muted"><i class="fas fa-sticky-note"></i> {{ $item->notes }}</small>
                                    @endif
                                    @if($item->admin_note)
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Admin: {{ $item->admin_note }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->item_category ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $item->location_code }}</span><br>
                                    <small>{{ $item->location_name }}</small>
                                </td>
                                <td><span class="badge badge-primary">{{ $item->quantity }}</span></td>
                                <td><span class="badge badge-secondary">{{ $item->unit }}</span></td>
                                <td>
                                    <span class="badge badge-{{ $item->status_badge }}">
                                        {{ ucfirst($item->approve_status) }}
                                    </span>
                                    @if($item->approved_at)
                                        <br><small class="text-muted">{{ $item->approved_at->format('Y-m-d') }}</small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($return->grnItems->count() > 0)
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title">GRN Items (Good Returns)</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->grnItems as $index => $grn)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $grn->item_code }}</td>
                                <td>{{ $grn->item_name }}</td>
                                <td><span class="badge badge-success">{{ $grn->grn_quantity }}</span></td>
                                <td>{{ $grn->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($return->scrapItems->count() > 0)
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title">Scrap Items</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->scrapItems as $index => $scrap)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $scrap->item_code }}</td>
                                <td>{{ $scrap->item_name }}</td>
                                <td><span class="badge badge-danger">{{ $scrap->scrap_quantity }}</span></td>
                                <td>{{ $scrap->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-footer">
                <a href="{{ route('returns.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                
                @if($return->status === 'pending')
                <form action="{{ route('returns.destroy', $return->id) }}" 
                      method="POST" 
                      class="d-inline float-right"
                      onsubmit="return confirm('Are you sure you want to delete this return?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Return
                    </button>
                </form>
                @endif
            </div>
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
                <div class="info-box bg-success">
                    <div class="info-box-content">
                        <span class="info-box-text">Same Condition</span>
                        <span class="info-box-number">{{ $return->same_items_count }}</span>
                    </div>
                </div>
                <div class="info-box bg-warning">
                    <div class="info-box-content">
                        <span class="info-box-text">Used Items</span>
                        <span class="info-box-number">{{ $return->used_items_count }}</span>
                    </div>
                </div>
                <div class="info-box bg-info">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Quantity</span>
                        <span class="info-box-number">{{ $return->total_quantity }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Approval Status</h3>
            </div>
            <div class="card-body">
                @php
                    $pendingCount = $return->items->where('approve_status', 'pending')->count();
                    $approvedCount = $return->items->where('approve_status', 'approved')->count();
                    $rejectedCount = $return->items->where('approve_status', 'rejected')->count();
                @endphp
                
                <table class="table table-sm">
                    <tr>
                        <td>Pending:</td>
                        <td class="text-right">
                            <span class="badge badge-warning">{{ $pendingCount }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Approved:</td>
                        <td class="text-right">
                            <span class="badge badge-success">{{ $approvedCount }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Rejected:</td>
                        <td class="text-right">
                            <span class="badge badge-danger">{{ $rejectedCount }}</span>
                        </td>
                    </tr>
                </table>

                @if($return->status === 'pending')
                <div class="alert alert-info mt-3 mb-0">
                    <i class="icon fas fa-info"></i>
                    <small>Your return is pending admin approval.</small>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Original Requisition</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th>Number:</th>
                        <td>
                            <a href="{{ route('requisitions.show', $return->requisition->id) }}" target="_blank">
                                {{ $return->requisition->requisition_number }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Department:</th>
                        <td>{{ $return->requisition->department->name }}</td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $return->requisition->created_at->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge badge-success">
                                {{ ucfirst($return->requisition->clear_status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection