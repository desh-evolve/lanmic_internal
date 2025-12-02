{{-- resources/views/admin/reports/item-requisition.blade.php --}}
@extends('layouts.admin')

@section('title', 'Item Requisition Report')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Item Requisition Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Item Requisition</li>
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

    {{-- Top Requested Items --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Most Requested Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-center">Request Count</th>
                                    <th class="text-end">Total Quantity</th>
                                    <th class="text-end">Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemStats as $index => $stat)
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> 1</span>
                                            @elseif($index == 1)
                                                <span class="badge bg-secondary">2</span>
                                            @elseif($index == 2)
                                                <span class="badge bg-danger">3</span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td><code>{{ $stat->item_code }}</code></td>
                                        <td>{{ $stat->item_name }}</td>
                                        <td class="text-center">{{ number_format($stat->request_count) }}</td>
                                        <td class="text-end">{{ number_format($stat->total_quantity) }}</td>
                                        <td class="text-end">{{ number_format($stat->total_value, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
            <form method="GET" action="{{ route('reports.item-requisition') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="item_code" class="form-control" placeholder="Search item code..." value="{{ request('item_code') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="item_name" class="form-control" placeholder="Search item name..." value="{{ request('item_name') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('reports.item-requisition') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> Item Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Requisition No</th>
                            <th>Date</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Requested By</th>
                            <th>Department</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td>{{ $items->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('requisitions.show', $item->requisition_id) }}">
                                        {{ $item->requisition->requisition_no ?? 'REQ-' . str_pad($item->requisition_id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td><code>{{ $item->item_code }}</code></td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->requisition->user->name ?? 'N/A' }}</td>
                                <td>{{ $item->requisition->department->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ number_format($item->quantity) }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No items found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
                </div>
                {{ $items->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection