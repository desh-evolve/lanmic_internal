{{-- resources/views/admin/reports/user-activity.blade.php --}}
@extends('layouts.admin')

@section('title', 'User Activity Report')

@section('styles')
<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .user-avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
    .activity-badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">User Activity Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">User Activity</li>
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

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.user-activity') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('reports.user-activity') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="row mb-4">
        @php
            $totalUsers = count($reportData);
            $totalRequisitions = collect($reportData)->sum('total_requisitions');
            $totalReturns = collect($reportData)->sum('total_returns');
            $activeUsers = collect($reportData)->filter(fn($d) => $d['total_requisitions'] > 0 || $d['total_returns'] > 0)->count();
        @endphp
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $totalUsers }}</h3>
                    <small>Total Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $activeUsers }}</h3>
                    <small>Active Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $totalRequisitions }}</h3>
                    <small>Total Requisitions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-undo-alt fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $totalReturns }}</h3>
                    <small>Total Returns</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Users Cards --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 5 Users by Requisitions</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach(collect($reportData)->sortByDesc('total_requisitions')->take(5) as $index => $data)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }} me-2">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="user-avatar-placeholder me-2">
                                        {{ strtoupper(substr($data['user']->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $data['user']->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $data['user']->email }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded-pill">{{ $data['total_requisitions'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-undo"></i> Top 5 Users by Returns</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach(collect($reportData)->sortByDesc('total_returns')->take(5) as $index => $data)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }} me-2">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="user-avatar-placeholder me-2">
                                        {{ strtoupper(substr($data['user']->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $data['user']->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $data['user']->email }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark rounded-pill">{{ $data['total_returns'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> All Users Activity</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="userActivityTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role(s)</th>
                            <th class="text-center">Total Requisitions</th>
                            <th class="text-center">Pending Req.</th>
                            <th class="text-center">Approved Req.</th>
                            <th class="text-center">Total Returns</th>
                            <th class="text-center">Pending Returns</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $index => $data)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-placeholder me-2">
                                            {{ strtoupper(substr($data['user']->name, 0, 1)) }}
                                        </div>
                                        <strong>{{ $data['user']->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $data['user']->email }}</td>
                                <td>
                                    @if($data['user']->roles && $data['user']->roles->count() > 0)
                                        @foreach($data['user']->roles as $role)
                                            <span class="badge bg-secondary">{{ $role->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No role</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill">{{ $data['total_requisitions'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark rounded-pill">{{ $data['pending_requisitions'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success rounded-pill">{{ $data['approved_requisitions'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info rounded-pill">{{ $data['total_returns'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger rounded-pill">{{ $data['pending_returns'] }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('reports.requisition-summary', ['user_id' => $data['user']->id]) }}" 
                                       class="btn btn-sm btn-info" title="View Requisitions">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                    <a href="{{ route('reports.returns-summary', ['user_id' => $data['user']->id]) }}" 
                                       class="btn btn-sm btn-warning" title="View Returns">
                                        <i class="fas fa-undo-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No user data found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection