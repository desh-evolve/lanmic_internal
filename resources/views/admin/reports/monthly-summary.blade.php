{{-- resources/views/admin/reports/monthly-summary.blade.php --}}
@extends('layouts.admin')

@section('title', 'Monthly Summary Report')

@section('styles')
<style>
    .month-card {
        transition: transform 0.2s;
    }
    .month-card:hover {
        transform: scale(1.02);
    }
    .chart-container {
        position: relative;
        height: 400px;
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
                    <h1 class="h3 mb-0">Monthly Summary Report - {{ $year }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Monthly Summary</li>
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

    {{-- Year Filter --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calendar"></i> Select Year</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.monthly-summary') }}" class="row">
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-control" onchange="this.form.submit()">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Yearly Summary Cards --}}
    <div class="row mb-4">
        @php
            $yearlyRequisitions = collect($months)->sum('requisitions_count');
            $yearlyApproved = collect($months)->sum('requisitions_approved');
            $yearlyReturns = collect($months)->sum('returns_count');
            $yearlyIssued = collect($months)->sum('issued_items');
            $yearlyGRN = collect($months)->sum('grn_items');
        @endphp
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($yearlyRequisitions) }}</h4>
                    <small>Total Requisitions</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($yearlyApproved) }}</h4>
                    <small>Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($yearlyReturns) }}</h4>
                    <small>Returns</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($yearlyIssued) }}</h4>
                    <small>Items Issued</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($yearlyGRN) }}</h4>
                    <small>GRN Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ $yearlyRequisitions > 0 ? round(($yearlyApproved / $yearlyRequisitions) * 100) : 0 }}%</h4>
                    <small>Approval Rate</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Monthly Trends</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Yearly Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Cards Grid --}}
    <div class="row mb-4">
        @foreach($months as $index => $month)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card month-card h-100 {{ $month['requisitions_count'] > 0 ? '' : 'bg-light' }}">
                    <div class="card-header bg-gradient text-center" 
                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0">{{ $month['month'] }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td><i class="fas fa-file-alt text-primary"></i> Requisitions</td>
                                <td class="text-end"><strong>{{ $month['requisitions_count'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-check-circle text-success"></i> Approved</td>
                                <td class="text-end"><strong>{{ $month['requisitions_approved'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-undo-alt text-warning"></i> Returns</td>
                                <td class="text-end"><strong>{{ $month['returns_count'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-check text-info"></i> Cleared</td>
                                <td class="text-end"><strong>{{ $month['returns_cleared'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-hand-holding text-secondary"></i> Issued</td>
                                <td class="text-end"><strong>{{ number_format($month['issued_items']) }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-clipboard-check text-dark"></i> GRN</td>
                                <td class="text-end"><strong>{{ number_format($month['grn_items']) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    @if($month['requisitions_count'] > 0)
                        <div class="card-footer bg-light text-center">
                            <small class="text-muted">
                                Approval Rate: 
                                <strong class="{{ ($month['requisitions_approved'] / max($month['requisitions_count'], 1)) * 100 >= 50 ? 'text-success' : 'text-danger' }}">
                                    {{ round(($month['requisitions_approved'] / max($month['requisitions_count'], 1)) * 100) }}%
                                </strong>
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Monthly Data Table</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Requisitions</th>
                            <th class="text-center">Approved</th>
                            <th class="text-center">Approval Rate</th>
                            <th class="text-center">Returns</th>
                            <th class="text-center">Cleared</th>
                            <th class="text-center">Items Issued</th>
                            <th class="text-center">GRN Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $month)
                            <tr>
                                <td><strong>{{ $month['month'] }}</strong></td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill">{{ $month['requisitions_count'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success rounded-pill">{{ $month['requisitions_approved'] }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $rate = $month['requisitions_count'] > 0 
                                            ? round(($month['requisitions_approved'] / $month['requisitions_count']) * 100) 
                                            : 0;
                                    @endphp
                                    <div class="progress" style="height: 20px; min-width: 60px;">
                                        <div class="progress-bar bg-{{ $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $rate }}%">
                                            {{ $rate }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark rounded-pill">{{ $month['returns_count'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info rounded-pill">{{ $month['returns_cleared'] }}</span>
                                </td>
                                <td class="text-center">{{ number_format($month['issued_items']) }}</td>
                                <td class="text-center">{{ number_format($month['grn_items']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th>Total</th>
                            <th class="text-center">{{ $yearlyRequisitions }}</th>
                            <th class="text-center">{{ $yearlyApproved }}</th>
                            <th class="text-center">
                                {{ $yearlyRequisitions > 0 ? round(($yearlyApproved / $yearlyRequisitions) * 100) : 0 }}%
                            </th>
                            <th class="text-center">{{ $yearlyReturns }}</th>
                            <th class="text-center">{{ collect($months)->sum('returns_cleared') }}</th>
                            <th class="text-center">{{ number_format($yearlyIssued) }}</th>
                            <th class="text-center">{{ number_format($yearlyGRN) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Trends Chart
    const ctx1 = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($months)->pluck('month')) !!},
            datasets: [
                {
                    label: 'Requisitions',
                    data: {!! json_encode(collect($months)->pluck('requisitions_count')) !!},
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Approved',
                    data: {!! json_encode(collect($months)->pluck('requisitions_approved')) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Returns',
                    data: {!! json_encode(collect($months)->pluck('returns_count')) !!},
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Items Issued',
                    data: {!! json_encode(collect($months)->pluck('issued_items')) !!},
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
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

    // Distribution Chart
    const ctx2 = document.getElementById('distributionChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Requisitions', 'Approved', 'Returns', 'GRN Items'],
            datasets: [{
                data: [
                    {{ $yearlyRequisitions }},
                    {{ $yearlyApproved }},
                    {{ $yearlyReturns }},
                    {{ $yearlyGRN }}
                ],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    function exportToExcel() {
        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('export', 'excel');
        
        // Redirect with export parameter
        window.location.href = window.location.pathname + '?' + urlParams.toString();
    }
</script>
@endsection