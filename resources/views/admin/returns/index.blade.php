@extends('layouts.admin')

@section('title', 'Return Approvals')
@section('page-title', 'Return Approvals')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">Return Approvals</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
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

        <!-- Statistics Cards -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \App\Models\ReturnModel::pending()->count() }}</h3>
                        <p>Pending Returns</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\ReturnModel::cleared()->count() }}</h3>
                        <p>Cleared Returns</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\GrnItem::where('status', 'active')->count() }}</h3>
                        <p>GRN Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\ScrapItem::where('status', 'active')->count() }}</h3>
                        <p>Scrap Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-trash"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Returns</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('admin.returns.index') }}" class="form-inline">
                        <div class="input-group input-group-sm">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="cleared" {{ request('status') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                            </select>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Returned By</th>
                            <th>Returned At</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr>
                            <td><strong>#{{ $return->id }}</strong></td>
                            <td>{{ $return->returnedBy->name }}</td>
                            <td>{{ $return->returned_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge badge-info">{{ $return->items->count() }} items</span>
                                <br>
                                <small>
                                    <span class="badge badge-warning">{{ $return->items->where('approve_status', 'pending')->count() }} pending</span>
                                    <span class="badge badge-success">{{ $return->items->where('approve_status', 'approved')->count() }} approved</span>
                                    <span class="badge badge-danger">{{ $return->items->where('approve_status', 'rejected')->count() }} rejected</span>
                                </small>
                            </td>
                            <td>
                                @if($return->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($return->status === 'cleared')
                                    <span class="badge badge-success">Cleared</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.returns.show', $return->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No returns found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
</div>
@endsection