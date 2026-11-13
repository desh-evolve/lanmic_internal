@extends('layouts.admin')

@section('title', 'My Returns')
@section('page-title', 'My Returns')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">Returns</li>
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

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Returns List</h3>
                <div class="card-tools">
                    <a href="{{ route('returns.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create New Return
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Requisition</th>
                                <th width="15%">Returned At</th>
                                <th width="10%">Total Items</th>
                                <th width="10%">Total Qty</th>
                                <th width="10%">Same</th>
                                <th width="10%">Used</th>
                                <th width="10%">Status</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $return)
                            <tr>
                                <td>{{ $loop->iteration + ($returns->currentPage() - 1) * $returns->perPage() }}</td>
                                <td>
                                    <strong>{{ $return->requisition->requisition_number }}</strong><br>
                                    <small class="text-muted">{{ $return->requisition->department->name ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $return->returned_at->format('Y-m-d H:i') }}</td>
                                <td><span class="badge badge-info">{{ $return->total_items }}</span></td>
                                <td><span class="badge badge-primary">{{ $return->total_quantity }}</span></td>
                                <td><span class="badge badge-success">{{ $return->same_items_count }}</span></td>
                                <td><span class="badge badge-warning">{{ $return->used_items_count }}</span></td>
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
                                <td>
                                    <a href="{{ route('returns.show', $return->id) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($return->status === 'pending')
                                    <form action="{{ route('returns.destroy', $return->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this return?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    No returns found. <a href="{{ route('returns.create') }}">Create your first return</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($returns->hasPages())
            <div class="card-footer clearfix">
                {{ $returns->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection