{{-- resources/views/admin/reports/department-activity.blade.php --}}
@extends('layouts.admin')

@section('title', 'Department Activity Report')

@section('styles')
<style>
    .progress-bar-animated {
        animation: progress-bar-stripes 1s linear infinite;
    }
    .dept-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dept-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
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
                    <h1 class="h3 mb-0">Department Activity Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Department Activity</li>
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
            <form method="GET" action="{{ route('reports.department-activity') }}">
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
                        <a href="{{ route('reports.department-activity') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Department Cards --}}
    <div class="row mb-4">
        @php
            $maxRequisitions = collect($reportData)->max('total_requisitions') ?: 1;
            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
        @endphp
        
        @foreach($reportData as $index => $data)
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="card dept-card h-100">
                    <div class="card-header bg-{{ $colors[$index % count($colors)] }} text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2"></i>{{ $data['department']->name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Requisitions Overview --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Total Requisitions</span>
                                <strong>{{ number_format($data['total_requisitions']) }}</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-{{ $colors[$index % count($colors)] }}" 
                                     style="width: {{ ($data['total_requisitions'] / $maxRequisitions) * 100 }}%">
                                </div>
                            </div>
                        </div>

                        {{-- Status Breakdown --}}
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h5 mb-0 text-warning">{{ $data['pending_requisitions'] }}</div>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h5 mb-0 text-success">{{ $data['approved_requisitions'] }}</div>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h5 mb-0 text-danger">{{ $data['rejected_requisitions'] }}</div>
                                    <small class="text-muted">Rejected</small>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Stats --}}
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Items Requested</span>
                                <strong>{{ number_format($data['total_items']) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Value</span>
                                <strong class="text-success">{{ number_format($data['total_value'], 2) }}</strong>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-light">
                        <a href="{{ route('reports.requisition-summary', ['department_id' => $data['department']->id]) }}" 
                           class="btn btn-sm btn-outline-{{ $colors[$index % count($colors)] }}">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Summary Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Department Summary Table</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Department</th>
                            <th class="text-center">Total Requisitions</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Approved</th>
                            <th class="text-center">Rejected</th>
                            <th class="text-end">Total Items</th>
                            <th class="text-end">Total Value</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; $grandItems = 0; @endphp
                        @forelse($reportData as $index => $data)
                            @php 
                                $grandTotal += $data['total_value']; 
                                $grandItems += $data['total_items'];
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <i class="fas fa-building me-1 text-muted"></i>
                                    {{ $data['department']->name }}
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
                                    <span class="badge bg-danger rounded-pill">{{ $data['rejected_requisitions'] }}</span>
                                </td>
                                <td class="text-end">{{ number_format($data['total_items']) }}</td>
                                <td class="text-end">{{ number_format($data['total_value'], 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('reports.requisition-summary', ['department_id' => $data['department']->id]) }}" 
                                       class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No department data found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="6" class="text-end">Grand Total:</th>
                            <th class="text-end">{{ number_format($grandItems) }}</th>
                            <th class="text-end">{{ number_format($grandTotal, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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