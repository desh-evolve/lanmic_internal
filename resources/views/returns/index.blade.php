@extends('layouts.admin')

@section('title', 'My Returns')
@section('page-title', 'My Returns')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">My Returns</li>
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
                <h3 class="card-title">My Returns</h3>
                <div class="card-tools">
                    <a href="{{ route('returns.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create New Return
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Returned At</th>
                            <th>Items</th>
                            <th>Total Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr>
                            <td><strong>#{{ $return->id }}</strong></td>
                            <td>{{ $return->returned_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge badge-info">{{ $return->items->count() }} items</span>
                            </td>
                            <td>{{ $return->items->sum('return_quantity') }}</td>
                            <td>
                                @if($return->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($return->status === 'cleared')
                                    <span class="badge badge-success">Cleared</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($return->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('returns.show', $return->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($return->status === 'pending')
                                <form action="{{ route('returns.destroy', $return->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this return?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No returns found. <a href="{{ route('returns.create') }}">Create your first return</a></td>
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