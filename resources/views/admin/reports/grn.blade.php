{{-- resources/views/admin/reports/grn.blade.php --}}
@extends('layouts.admin')

@section('title', 'GRN Report')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">GRN Report (Goods Received Notes)</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">GRN</li>
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
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_grn_items']) }}</h3>
                    <small>Total GRN Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_quantity']) }}</h3>
                    <small>Total Quantity</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_value'], 2) }}</h3>
                    <small>Total Value</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Top GRN Items --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Top 10 GRN Items by Quantity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-center">GRN Count</th>
                                    <th class="text-end">Total Quantity</th>
                                    <th class="text-end">Total Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemStats as $index => $stat)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><code>{{ $stat->item_code }}</code></td>
                                        <td>{{ $stat->item_name }}</td>
                                        <td class="text-center">{{ number_format($stat->grn_count) }}</td>
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
            <form method="GET" action="{{ route('reports.grn') }}">
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
                        <a href="{{ route('reports.grn') }}" class="btn btn-secondary">
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
            <h5 class="mb-0"><i class="fas fa-table"></i> GRN Items Details</h5>
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
                            <th class="text-center">GRN Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Price</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grnItems as $index => $item)
                            <tr>
                                <td>{{ $grnItems->firstItem() + $index }}</td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($item->return)
                                        <a href="{{ route('returns.show', $item->return_id) }}">
                                            {{ $item->return->return_no ?? 'RET-' . str_pad($item->return_id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td><code>{{ $item->item_code }}</code></td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->return->returnedBy->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ number_format($item->grn_quantity) }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                                <td>{{ Str::limit($item->remarks, 30) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No GRN items found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $grnItems->firstItem() ?? 0 }} to {{ $grnItems->lastItem() ?? 0 }} of {{ $grnItems->total() }} entries
                </div>
                {{ $grnItems->appends(request()->query())->links() }}
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