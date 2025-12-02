{{-- resources/views/admin/reports/returns-summary.blade.php --}}
@extends('layouts.admin')

@section('title', 'Returns Summary Report')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Returns Summary Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Returns Summary</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button class="btn btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-undo-alt fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_returns']) }}</h3>
                    <small>Total Returns</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['pending']) }}</h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['cleared']) }}</h3>
                    <small>Cleared</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_items']) }}</h3>
                    <small>Total Items Returned</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.returns-summary') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Returned By</label>
                        <select name="user_id" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('reports.returns-summary') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Returns Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Return No</th>
                            <th>Return Date</th>
                            <th>Returned By</th>
                            <th class="text-center">Items Count</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Processed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $index => $return)
                            <tr>
                                <td>{{ $returns->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('returns.show', $return->id) }}">
                                        {{ $return->return_no ?? 'RET-' . str_pad($return->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($return->returned_at)->format('d M Y H:i') }}</td>
                                <td>{{ $return->returnedBy->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $return->items->count() }}</td>
                                <td>{{ Str::limit($return->reason, 50) }}</td>
                                <td>
                                    @if($return->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($return->status == 'cleared')
                                        <span class="badge bg-success">Cleared</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($return->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $return->processedBy->name ?? '-' }}</td>
                                <td>{{ $return->processed_at ? \Carbon\Carbon::parse($return->processed_at)->format('d M Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No returns found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $returns->firstItem() ?? 0 }} to {{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }} entries
                </div>
                {{ $returns->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection