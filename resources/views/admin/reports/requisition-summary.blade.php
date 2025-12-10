{{-- resources/views/admin/reports/requisition-summary.blade.php --}}
@extends('layouts.admin')

@section('title', 'Requisition Summary Report')

@section('styles')
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .stat-card.primary { border-left-color: #007bff; }
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.danger { border-left-color: #dc3545; }
    .stat-card.info { border-left-color: #17a2b8; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Requisition Summary Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Requisition Summary</li>
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
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card primary h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase font-weight-bold">Total Requisitions</div>
                    <div class="h2 font-weight-bold mb-0">{{ number_format($statistics['total_requisitions']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card info h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase font-weight-bold">Total Items</div>
                    <div class="h2 font-weight-bold mb-0">{{ number_format($statistics['total_items']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card success h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase font-weight-bold">Total Value</div>
                    <div class="h2 font-weight-bold mb-0">{{ number_format($statistics['total_value'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card warning h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase font-weight-bold">Pending</div>
                    <div class="h2 font-weight-bold mb-0">{{ number_format($statistics['pending']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card success h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase font-weight-bold">Approved</div>
                    <div class="h2 font-weight-bold mb-0">{{ number_format($statistics['approved']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card danger h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase font-weight-bold">Rejected</div>
                    <div class="h2 font-weight-bold mb-0">{{ number_format($statistics['rejected']) }}</div>
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
            <form method="GET" action="{{ route('reports.requisition-summary') }}">
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
                        <select name="approve_status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('approve_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('approve_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approve_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('reports.requisition-summary') }}" class="btn btn-secondary">
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
            <h5 class="mb-0"><i class="fas fa-table"></i> Requisition Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="reportTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Requisition No</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Department</th>
                            <th>Items Count</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Approved Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $index => $requisition)
                            <tr>
                                <td>{{ $requisitions->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('requisitions.show', $requisition->id) }}">
                                        {{ $requisition->requisition_no ?? 'REQ-' . str_pad($requisition->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td>{{ $requisition->created_at->format('d M Y') }}</td>
                                <td>{{ $requisition->user->name ?? 'N/A' }}</td>
                                <td>{{ $requisition->department->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $requisition->items->count() }}</td>
                                <td class="text-end">{{ number_format($requisition->items->sum('total_price'), 2) }}</td>
                                <td>
                                    @if($requisition->approve_status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($requisition->approve_status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($requisition->approve_status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($requisition->approve_status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $requisition->approvedBy->name ?? '-' }}</td>
                                <td>{{ $requisition->approved_at ? \Carbon\Carbon::parse($requisition->approved_at)->format('d M Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No requisitions found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $requisitions->firstItem() ?? 0 }} to {{ $requisitions->lastItem() ?? 0 }} of {{ $requisitions->total() }} entries
                </div>
                {{ $requisitions->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    function exportToExcel() {
        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('export', 'excel');
        
        // Redirect with export parameter
        window.location.href = window.location.pathname + '?' + urlParams.toString();
    }
</script>
@endsection