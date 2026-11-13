@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    .stat-card {
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .stat-card .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 700;
    }
    .stat-card .stat-label {
        color: #6c757d;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-card .stat-change {
        font-size: 12px;
        font-weight: 600;
    }
    .stat-change.positive { color: #28a745; }
    .stat-change.negative { color: #dc3545; }

    .info-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .info-card .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f0f0;
        font-weight: 600;
    }

    .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .activity-item:last-child {
        border-bottom: none;
    }
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .quick-action-btn {
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .quick-action-btn i {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .pending-item {
        padding: 15px;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 10px;
        transition: background 0.2s;
    }
    .pending-item:hover {
        background: #e9ecef;
    }

    .chart-container {
        position: relative;
        height: 250px;
    }

    .welcome-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 30px;
        color: white;
        margin-bottom: 25px;
    }
    .welcome-banner h2 {
        margin-bottom: 5px;
    }
    .welcome-banner p {
        opacity: 0.9;
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>Welcome back, {{ Auth::user()->name }}!</h2>
                <p>{{ now()->format('l, F j, Y') }} | Here's what's happening with your inventory system today.</p>
            </div>
            <div class="col-md-4 text-md-right">
                <a href="{{ route('requisitions.create') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus-circle"></i> New Requisition
                </a>
            </div>
        </div>
    </div>

    {{-- Main Statistics Cards --}}
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Total Requisitions</p>
                            <h3 class="stat-value mb-0">{{ number_format($stats['total_requisitions']) }}</h3>
                            <span class="stat-change {{ $changes['requisitions'] >= 0 ? 'positive' : 'negative' }}">
                                <i class="fas fa-{{ $changes['requisitions'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($changes['requisitions']) }}% from last month
                            </span>
                        </div>
                        <div class="stat-icon bg-primary text-white">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Pending Approval</p>
                            <h3 class="stat-value mb-0 text-warning">{{ number_format($stats['pending_requisitions']) }}</h3>
                            <span class="text-muted" style="font-size: 12px;">
                                Awaiting review
                            </span>
                        </div>
                        <div class="stat-icon bg-warning text-white">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Approved</p>
                            <h3 class="stat-value mb-0 text-success">{{ number_format($stats['approved_requisitions']) }}</h3>
                            <span class="stat-change {{ $changes['approved'] >= 0 ? 'positive' : 'negative' }}">
                                <i class="fas fa-{{ $changes['approved'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($changes['approved']) }}% from last month
                            </span>
                        </div>
                        <div class="stat-icon bg-success text-white">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label mb-1">Total Returns</p>
                            <h3 class="stat-value mb-0 text-info">{{ number_format($stats['total_returns']) }}</h3>
                            <span class="stat-change {{ $changes['returns'] >= 0 ? 'positive' : 'negative' }}">
                                <i class="fas fa-{{ $changes['returns'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($changes['returns']) }}% from last month
                            </span>
                        </div>
                        <div class="stat-icon bg-info text-white">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row">
        {{-- Monthly Trends Chart --}}
        <div class="col-xl-8 mb-4">
            <div class="card info-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line text-primary mr-2"></i> Monthly Trends</span>
                    <span class="badge badge-light">Last 6 Months</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Distribution --}}
        <div class="col-xl-4 mb-4">
            <div class="card info-card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-pie text-primary mr-2"></i> Requisition Status
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
    {{-- Admin Section: Pending Items --}}
    <div class="row">
        {{-- Pending Requisitions --}}
        <div class="col-xl-6 mb-4">
            <div class="card info-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clipboard-list text-warning mr-2"></i> Pending Requisitions</span>
                    <a href="{{ route('admin.requisitions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse($pendingRequisitions as $req)
                        <div class="pending-item mx-3 {{ $loop->first ? 'mt-3' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-primary">{{ $req->requisition_number }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> {{ $req->user->name ?? 'N/A' }}
                                        @if($req->department)
                                            | <i class="fas fa-building"></i> {{ $req->department->name }}
                                        @endif
                                    </small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-info">{{ $req->items->count() }} items</span>
                                    <br>
                                    <small class="text-muted">{{ $req->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('admin.requisitions.show', $req->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>No pending requisitions!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending Returns --}}
        <div class="col-xl-6 mb-4">
            <div class="card info-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-undo text-info mr-2"></i> Pending Returns</span>
                    <a href="{{ route('returns.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse($pendingReturns as $return)
                        <div class="pending-item mx-3 {{ $loop->first ? 'mt-3' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-info">{{ $return->return_no ?? 'RET-' . str_pad($return->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> {{ $return->returnedBy->name ?? 'N/A' }}
                                    </small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-warning">{{ $return->items->count() }} items</span>
                                    <br>
                                    <small class="text-muted">{{ $return->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('returns.show', $return->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>No pending returns!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bottom Row: Activity, Top Items, Quick Actions --}}
    <div class="row">
        {{-- Recent Activity --}}
        <div class="col-xl-4 mb-4">
            <div class="card info-card h-100">
                <div class="card-header">
                    <i class="fas fa-history text-primary mr-2"></i> Recent Activity
                </div>
                <div class="card-body p-0">
                    <div class="px-3">
                        @forelse($recentActivity as $activity)
                            <div class="activity-item d-flex align-items-center">
                                <div class="activity-icon bg-{{ $activity['color'] }} text-white mr-3">
                                    <i class="fas {{ $activity['icon'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ $activity['title'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $activity['description'] }}</small>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <p>No recent activity</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Requested Items --}}
        <div class="col-xl-4 mb-4">
            <div class="card info-card h-100">
                <div class="card-header">
                    <i class="fas fa-fire text-danger mr-2"></i> Top Items This Month
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Requests</th>
                                <th class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topItems as $index => $item)
                                <tr>
                                    <td>
                                        @if($index == 0)
                                            <i class="fas fa-trophy text-warning"></i>
                                        @endif
                                        <strong>{{ Str::limit($item->item_name, 20) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $item->item_code }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $item->request_count }}</span>
                                    </td>
                                    <td class="text-right">{{ number_format($item->total_qty) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No items requested this month
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Actions / My Stats --}}
        <div class="col-xl-4 mb-4">
            <div class="card info-card h-100">
                <div class="card-header">
                    <i class="fas fa-bolt text-warning mr-2"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('requisitions.create') }}" class="quick-action-btn d-block bg-primary text-white">
                                <i class="fas fa-plus-circle d-block"></i>
                                <span>New Requisition</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('requisitions.index') }}" class="quick-action-btn d-block bg-info text-white">
                                <i class="fas fa-list d-block"></i>
                                <span>My Requisitions</span>
                            </a>
                        </div>
                        @if($isAdmin)
                        <div class="col-6 mb-3">
                            <a href="{{ route('admin.requisitions.index') }}" class="quick-action-btn d-block bg-warning text-white">
                                <i class="fas fa-clipboard-check d-block"></i>
                                <span>Approvals</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('reports.index') }}" class="quick-action-btn d-block bg-success text-white">
                                <i class="fas fa-chart-bar d-block"></i>
                                <span>Reports</span>
                            </a>
                        </div>
                        @else
                        <div class="col-6 mb-3">
                            <a href="{{ route('returns.create') }}" class="quick-action-btn d-block bg-warning text-dark">
                                <i class="fas fa-undo d-block"></i>
                                <span>Return Items</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('returns.index') }}" class="quick-action-btn d-block bg-secondary text-white">
                                <i class="fas fa-history d-block"></i>
                                <span>My Returns</span>
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- My Stats Summary --}}
                    <hr>
                    <h6 class="text-muted mb-3">My Statistics</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Requisitions</span>
                        <strong>{{ $userStats['my_requisitions'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending</span>
                        <span class="badge badge-warning">{{ $userStats['my_pending'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Approved</span>
                        <span class="badge badge-success">{{ $userStats['my_approved'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Rejected</span>
                        <span class="badge badge-danger">{{ $userStats['my_rejected'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
    {{-- Department Statistics --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card info-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-building text-primary mr-2"></i> Department-wise Requisitions</span>
                    <a href="{{ route('reports.department-activity') }}" class="btn btn-sm btn-outline-primary">View Report</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($departmentStats as $dept)
                            <div class="col-md-4 col-lg-2 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="text-primary mb-1">{{ $dept->total }}</h4>
                                    <small class="text-muted">{{ $dept->department->name ?? 'Unknown' }}</small>
                                </div>
                            </div>
                        @endforeach
                        @if($departmentStats->isEmpty())
                            <div class="col-12 text-center text-muted py-4">
                                No department data available
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- System Overview Cards --}}
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">{{ $stats['total_users'] }}</h5>
                            <small>Total Users</small>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">{{ $stats['total_departments'] }}</h5>
                            <small>Departments</small>
                        </div>
                        <i class="fas fa-building fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">{{ $stats['pending_returns'] }}</h5>
                            <small>Pending Returns</small>
                        </div>
                        <i class="fas fa-undo fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">{{ number_format($thisMonthStats['issued_items']) }}</h5>
                            <small>Items Issued (This Month)</small>
                        </div>
                        <i class="fas fa-hand-holding fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($monthlyTrends)->pluck('month')) !!},
            datasets: [
                {
                    label: 'Requisitions',
                    data: {!! json_encode(collect($monthlyTrends)->pluck('requisitions')) !!},
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Approved',
                    data: {!! json_encode(collect($monthlyTrends)->pluck('approved')) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Returns',
                    data: {!! json_encode(collect($monthlyTrends)->pluck('returns')) !!},
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $statusDistribution['pending'] }},
                    {{ $statusDistribution['approved'] }},
                    {{ $statusDistribution['rejected'] }}
                ],
                backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '60%'
        }
    });
</script>
@endpush
