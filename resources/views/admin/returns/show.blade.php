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
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Action Required:</strong> This return is pending approval. Process items individually.
                </div>
                @elseif($return->status === 'cleared')
                <div class="alert alert-success">
                    <i class="icon fas fa-check-circle"></i>
                    <strong>Cleared:</strong> All items have been processed for this return.
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Return ID:</strong></div>
                    <div class="col-md-8">
                        <span class="badge badge-secondary badge-lg">#{{ $return->id }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Status:</strong></div>
                    <div class="col-md-8">
                        @if($return->status === 'pending')
                            <span class="badge badge-warning badge-lg">Pending</span>
                        @else
                            <span class="badge badge-success badge-lg">Cleared</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Returned By:</strong></div>
                    <div class="col-md-8">
                        {{ $return->returnedBy->name }}
                        <br><small class="text-muted">{{ $return->returnedBy->email }}</small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Returned At:</strong></div>
                    <div class="col-md-8">{{ $return->returned_at->format('Y-m-d H:i:s') }}</div>
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
                            <th>Admin Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $index => $item)
                        <tr class="{{ $item->approve_status === 'approved' ? 'table-success' : ($item->approve_status === 'rejected' ? 'table-danger' : '') }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->item_name }}</strong>
                                <br><small class="text-muted">{{ $item->item_code }}</small>
                                @if($item->notes)
                                    <br><small><i class="fas fa-info-circle"></i> {{ $item->notes }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->return_type === 'used' ? 'warning' : 'info' }}">
                                    {{ ucfirst($item->return_type) }}
                                </span>
                            </td>
                            <td>
                                @if($item->location)
                                    {{ $item->location['name'] }}
                                    <br><small class="text-muted">{{ $item->location['code'] }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->return_quantity }} {{ $item->unit }}</td>
                            <td>
                                @if($item->approve_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($item->approve_status === 'approved')
                                    <span class="badge badge-success">Approved → GRN</span>
                                @else
                                    <span class="badge badge-danger">Rejected → Scrap</span>
                                @endif
                            </td>
                            <td>
                                @if($item->admin_note)
                                    {{ $item->admin_note }}
                                @else
                                    -
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
                <h3 class="card-title">GRN Items (Approved)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->grnItems as $index => $grnItem)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $grnItem->item_name }}</strong>
                                <br><small class="text-muted">{{ $grnItem->item_code }}</small>
                            </td>
                            <td>{{ $grnItem->grn_quantity }} {{ $grnItem->unit }}</td>
                            <td>{{ $grnItem->created_at->format('Y-m-d H:i') }}</td>
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
                <h3 class="card-title">Scrap Items (Rejected)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->scrapItems as $index => $scrapItem)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $scrapItem->item_name }}</strong>
                                <br><small class="text-muted">{{ $scrapItem->item_code }}</small>
                            </td>
                            <td>{{ $scrapItem->scrap_quantity }} {{ $scrapItem->unit }}</td>
                            <td>{{ $scrapItem->created_at->format('Y-m-d H:i') }}</td>
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
            @if($return->status === 'pending')
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
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Quantity</span>
                        <span class="info-box-number">{{ $return->items->sum('return_quantity') }}</span>
                    </div>
                </div>
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Pending</span>
                        <span class="info-box-number text-warning">
                            {{ $return->items->where('approve_status', 'pending')->count() }}
                        </span>
                    </div>
                </div>
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Approved</span>
                        <span class="info-box-number text-success">
                            {{ $return->items->where('approve_status', 'approved')->count() }}
                        </span>
                    </div>
                </div>
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text">Rejected</span>
                        <span class="info-box-number text-danger">
                            {{ $return->items->where('approve_status', 'rejected')->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requester Information</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-user mr-1"></i> Name</strong>
                <p class="text-muted">{{ $return->returnedBy->name }}</p>
                <hr>
                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                <p class="text-muted">{{ $return->returnedBy->email }}</p>
                <hr>
                <strong><i class="fas fa-user-tag mr-1"></i> Roles</strong>
                <p class="text-muted">
@foreach($return->returnedBy->roles as $role)
<span class="badge badge-info">{{ $role->name }}</span>
@endforeach
</p>
</div>
</div>
</div>
</div>
@endsection