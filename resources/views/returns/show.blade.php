@extends('layouts.admin')

@section('title', 'Return Details')
@section('page-title', 'Return Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Returns</a></li>
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
            </div>
            <div class="card-body">
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
                            <span class="badge badge-warning badge-lg">Pending Approval</span>
                        @elseif($return->status === 'cleared')
                            <span class="badge badge-success badge-lg">Cleared</span>
                        @else
                            <span class="badge badge-secondary badge-lg">{{ ucfirst($return->status) }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Returned By:</strong></div>
                    <div class="col-md-8">{{ $return->returnedBy->name }}</div>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $index => $item)
                        @php
                            $location = \App\Models\Location::find($item->return_location_id);
                        @endphp
                        <tr>
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
                                @if($location)
                                    {{ $location['name'] }}
                                    <br><small class="text-muted">{{ $location['code'] }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item->return_quantity }} {{ $item->unit }}</td>
                            <td>
                                @if($item->approve_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($item->approve_status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
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
                <h3 class="card-title">Approved Items (GRN)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Quantity</th>
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
                <h3 class="card-title">Rejected Items (Scrap)</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Quantity</th>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card-footer">
            <a href="{{ route('returns.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($return->status === 'pending' && $return->returned_by === Auth::id())
                <form action="{{ route('returns.destroy', $return->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this return?')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
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
    </div>
</div>
@endsection