@extends('layouts.admin')

@section('title', 'Requisition Approvals')
@section('page-title', 'Requisition Approvals')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">Requisition Approvals</li>
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
                        <h3>{{ \App\Models\Requisition::pending()->count() }}</h3>
                        <p>Pending Requisitions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\Requisition::approved()->count() }}</h3>
                        <p>Approved Requisitions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\Requisition::rejected()->count() }}</h3>
                        <p>Rejected Requisitions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\Requisition::count() }}</h3>
                        <p>Total Requisitions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Requisitions</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('admin.requisitions.index') }}" class="form-inline">
                        <div class="input-group input-group-sm" style="width: 350px;">
                            <select name="approve_status" class="form-control form-control-sm">
                                <option value="">All Approve Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <select name="clear_status" class="form-control form-control-sm">
                                <option value="">All Clear Status</option>
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
                            <th>Requisition #</th>
                            <th>Requested By</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th>Approve Status</th>
                            <th>Clear Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $requisition)
                        <tr>
                            <td><strong>{{ $requisition->requisition_number }}</strong></td>
                            <td>{{ $requisition->user->name }}</td>
                            <td>
                                {{ $requisition->department->name ?? '-' }}
                                @if($requisition->subDepartment)
                                    <br><small class="text-muted">{{ $requisition->subDepartment->name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $requisition->items->count() }} items</span>
                            </td>
                            <td>
                                @if($requisition->approve_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($requisition->approve_status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($requisition->approve_status !== 'rejected')
                                    @if($requisition->clear_status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($requisition->clear_status === 'cleared')
                                        <span class="badge badge-success">Cleared</span>
                                    @else
                                        <span class="badge badge-danger">Error</span>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $requisition->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.requisitions.show', $requisition->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No requisitions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $requisitions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection