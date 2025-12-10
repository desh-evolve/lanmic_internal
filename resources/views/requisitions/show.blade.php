@extends('layouts.admin')

@section('title', 'Requisition Details')
@section('page-title', 'Requisition Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisitions</a></li>
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
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Requisition Number:</strong></div>
                    <div class="col-md-8">
                        <span class="badge badge-secondary badge-lg">{{ $requisition->requisition_number }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Approve Status:</strong></div>
                    <div class="col-md-8">
                        @if($requisition->approve_status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($requisition->approve_status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @else
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Clear Status:</strong></div>
                    <div class="col-md-8">
                        @if($requisition->clear_status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($requisition->clear_status === 'cleared')
                            <span class="badge badge-success">Cleared</span>
                        @else
                            <span class="badge badge-danger">Error</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Requested By:</strong></div>
                    <div class="col-md-8">{{ $requisition->user->name }}</div>
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
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisition->items as $item)
                        <tr>
                            <td><strong>{{ $item->item_code }}</strong></td>
                            <td>
                                {{ $item->item_name }}
                                @if($item->specifications)
                                    <br><small class="text-muted">{{ $item->specifications }}</small>
                                @endif
                            </td>
                            <td>{{ $item->location_name ?? '-' }}</td>
                            <td>{{ $item->item_category ?? '-' }}</td>
                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('requisitions.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
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
            </div>
        </div>
    </div>
</div>
@endsection