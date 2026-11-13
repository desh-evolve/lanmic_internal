{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Reports Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>Reports Dashboard
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Requisition Reports --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-alt fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Requisition Summary</h5>
                                            <p class="card-text small">View all requisitions with status and filters</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.requisition-summary') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Item Requisition Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-boxes fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Item Requisition</h5>
                                            <p class="card-text small">Detailed item-wise requisition analysis</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.item-requisition') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Issued Items Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-hand-holding fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Issued Items</h5>
                                            <p class="card-text small">Track all issued items and quantities</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.issued-items') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Purchase Order Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-shopping-cart fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Purchase Orders</h5>
                                            <p class="card-text small">PO items and procurement status</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.purchase-order') }}" class="btn btn-dark btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Returns Summary Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-undo-alt fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Returns Summary</h5>
                                            <p class="card-text small">All returned items and status</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.returns-summary') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- GRN Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-secondary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-clipboard-check fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">GRN Report</h5>
                                            <p class="card-text small">Goods received notes analysis</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.grn') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Scrap Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-dark text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-trash-alt fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Scrap Report</h5>
                                            <p class="card-text small">Scrapped items and disposal tracking</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.scrap') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Department Activity Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-purple text-white h-100" style="background-color: #6f42c1;">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-building fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Department Activity</h5>
                                            <p class="card-text small">Department-wise activity analysis</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.department-activity') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- User Activity Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-teal text-white h-100" style="background-color: #20c997;">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-users fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">User Activity</h5>
                                            <p class="card-text small">User-wise activity tracking</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.user-activity') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Monthly Summary Report --}}
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card bg-orange text-white h-100" style="background-color: #fd7e14;">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-calendar-alt fa-3x opacity-75 mr-3"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="card-title">Monthly Summary</h5>
                                            <p class="card-text small">Monthly trends and statistics</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('reports.monthly-summary') }}" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-right"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection