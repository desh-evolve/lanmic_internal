{{-- resources/views/admin/reports/scrap.blade.php --}}
@extends('layouts.admin')

@section('title', 'Return Reject Report')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Return Reject Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Return Reject</li>
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
            <div class="card bg-dark text-white">
                <div class="card-body text-center">
                    <i class="fas fa-ban fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_reject_items']) }}</h3>
                    <small>Total Rejected Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_quantity']) }}</h3>
                    <small>Total Quantity Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_value'], 2) }}</h3>
                    <small>Total Value Lost</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Rejected Items --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Top 10 Most Rejected Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-center">Reject Count</th>
                                    <th class="text-end">Total Quantity</th>
                                    <th class="text-end">Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($itemStats as $index => $stat)
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <span class="badge bg-danger">1</span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td><code>{{ $stat->item_code }}</code></td>
                                        <td>{{ $stat->item_name }}</td>
                                        <td class="text-center">{{ number_format($stat->reject_count) }}</td>
                                        <td class="text-end">{{ number_format($stat->total_quantity) }}</td>
                                        <td class="text-end text-danger">{{ number_format($stat->total_value, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">No rejected items found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 no-print">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.scrap') }}">
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
                        <a href="{{ route('reports.scrap') }}" class="btn btn-secondary">
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
            <h5 class="mb-0"><i class="fas fa-table"></i> Rejected Item Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Return No</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Returned By</th>
                            <th>Type</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Value</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rejectItems as $index => $item)
                            @php
                                $scrap = $item->scrapItem;
                                $quantity = $scrap->scrap_quantity ?? $item->quantity;
                                $unitPrice = $scrap->unit_price ?? ($item->issuedItem->unit_price ?? 0);
                                $totalPrice = $scrap->total_price ?? ($unitPrice * $quantity);
                            @endphp
                            <tr>
                                <td>{{ $rejectItems->firstItem() + $index }}</td>
                                <td>{{ $item->approved_at ? $item->approved_at->format('d M Y') : '—' }}</td>
                                <td>
                                    @if($item->return)
                                        <a href="{{ route('returns.show', $item->return_id) }}">
                                            RET-{{ str_pad($item->return_id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td><code>{{ $item->item_code }}</code></td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->return->returnedBy->name ?? 'N/A' }}</td>
                                <td>
                                    @if($scrap)
                                        <span class="badge badge-secondary">Scrapped</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($quantity) }}</td>
                                <td class="text-end">{{ number_format($unitPrice, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($totalPrice, 2) }}</td>
                                <td>{{ Str::limit($item->admin_note ?? '—', 40) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No rejected items found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $rejectItems->firstItem() ?? 0 }} to {{ $rejectItems->lastItem() ?? 0 }} of {{ $rejectItems->total() }} entries
                </div>
                {{ $rejectItems->appends(request()->query())->links() }}
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
