@extends('layouts.admin')

@section('title', 'My Requisitions')
@section('page-title', 'My Requisitions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">My Requisitions</li>
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
                <h3 class="card-title">My Requisitions</h3>
                <div class="card-tools">
                    <a href="{{ route('requisitions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create New Requisition
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Requisition #</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th>Total Amount</th>
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
                            <td>
                                {{ $requisition->department->name ?? '-' }}
                                @if($requisition->subDepartment)
                                    <br><small class="text-muted">{{ $requisition->subDepartment->name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $requisition->items->count() }} items</span>
                            </td>
                            <td>Rs.{{ number_format($requisition->items->sum('total_price'), 2) }}</td>
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
                                @if($requisition->clear_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($requisition->clear_status === 'cleared')
                                    <span class="badge badge-success">Cleared</span>
                                @else
                                    <span class="badge badge-danger">Error</span>
                                @endif
                            </td>
                            <td>{{ $requisition->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('requisitions.show', $requisition->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($requisition->approve_status === 'pending')
                                {{-- <a href="{{ route('requisitions.edit', $requisition->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a> --}}
                                <form action="{{ route('requisitions.destroy', $requisition->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this requisition?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No requisitions found. <a href="{{ route('requisitions.create') }}">Create your first requisition</a></td>
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