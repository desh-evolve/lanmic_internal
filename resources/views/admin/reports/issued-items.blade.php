{{-- resources/views/admin/reports/issued-items.blade.php --}}
@extends('layouts.admin')

@section('title', 'Issued Items Report')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Issued Items Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Issued Items</li>
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
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Issued Records</h6>
                            <h2 class="mb-0">{{ number_format($statistics['total_issued']) }}</h2>
                        </div>
                        <i class="fas fa-hand-holding fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Quantity Issued</h6>
                            <h2 class="mb-0">{{ number_format($statistics['total_quantity']) }}</h2>
                        </div>
                        <i class="fas fa-boxes fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Value</h6>
                            <h2 class="mb-0">{{ number_format($statistics['total_value'], 2) }}</h2>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                    </div>
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
            <form method="GET" action="{{ route('reports.issued-items') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="item_code" class="form-control" placeholder="Search item code..." value="{{ request('item_code') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('reports.issued-items') }}" class="btn btn-secondary">
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
            <h5 class="mb-0"><i class="fas fa-table"></i> Issued Items Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Issue Date</th>
                            <th>Requisition No</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Issued To</th>
                            <th class="text-center">Issued Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Price</th>
                            <th>Issued By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuedItems as $index => $item)
                            <tr>
                                <td>{{ $issuedItems->firstItem() + $index }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->issued_at)->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('requisitions.show', $item->requisition_id) }}">
                                        {{ $item->requisition->requisition_no ?? 'REQ-' . str_pad($item->requisition_id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td><code>{{ $item->item_code }}</code></td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->requisition->user->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ number_format($item->issued_quantity) }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                                <td>{{ $item->issuedBy->name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No issued items found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $issuedItems->firstItem() ?? 0 }} to {{ $issuedItems->lastItem() ?? 0 }} of {{ $issuedItems->total() }} entries
                </div>
                {{ $issuedItems->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection