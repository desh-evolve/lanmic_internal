{{-- resources/views/admin/reports/returns-summary.blade.php --}}
@extends('layouts.admin')

@section('title', 'Returns Summary Report')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Returns Summary Report</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Returns Summary</li>
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
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-undo-alt fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_returns']) }}</h3>
                    <small>Total Returns</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['pending']) }}</h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['cleared']) }}</h3>
                    <small>Cleared</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_items']) }}</h3>
                    <small>Total Items Returned</small>
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
            <form method="GET" action="{{ route('reports.returns-summary') }}">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Item Description</label>
                        <input type="text" name="item_name" class="form-control" placeholder="Item name..." value="{{ request('item_name') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Returned By</label>
                        <select name="user_id" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        <a href="{{ route('reports.returns-summary') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Returns Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Returned Date</th>
                            <th>Return Number</th>
                            <th>Requisition Number</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th class="text-center">UOM</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Price</th>
                            <th>Department</th>
                            <th>Sub Department</th>
                            <th>Remark</th>
                            <th>Returned By</th>
                            <th>Accepted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $index => $returnItem)
                            @php $ret = $returnItem->return; @endphp
                            <tr>
                                <td>{{ $returns->firstItem() + $index }}</td>
                                <td>{{ $ret ? \Carbon\Carbon::parse($ret->returned_at)->format('d M Y H:i') : '—' }}</td>
                                <td>
                                    @if($ret)
                                        <a href="{{ route('returns.show', $ret->id) }}">
                                            RET-{{ str_pad($ret->id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @else —
                                    @endif
                                </td>
                                <td>
                                    @if($ret?->requisition)
                                        <a href="{{ route('requisitions.show', $ret->requisition_id) }}">
                                            {{ $ret->requisition->requisition_number ?? 'REQ-' . str_pad($ret->requisition_id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @else —
                                    @endif
                                </td>
                                <td><code>{{ $returnItem->item_code }}</code></td>
                                <td>{{ $returnItem->item_name }}</td>
                                <td class="text-center">{{ $returnItem->unit ?? '—' }}</td>
                                <td class="text-center">{{ number_format($returnItem->quantity, 4) }}</td>
                                <td class="text-end">{{ $returnItem->issuedItem ? number_format($returnItem->issuedItem->unit_price, 2) : '—' }}</td>
                                <td class="text-end">{{ $returnItem->issuedItem ? number_format($returnItem->issuedItem->unit_price * $returnItem->quantity, 2) : '—' }}</td>
                                <td>{{ $ret?->requisition?->department?->name ?? '—' }}</td>
                                <td>{{ $ret?->requisition?->subDepartment?->name ?? '—' }}</td>
                                <td>{{ $returnItem->admin_note ?? '—' }}</td>
                                <td>{{ $ret?->returnedBy?->name ?? 'N/A' }}</td>
                                <td>{{ $returnItem->approvedBy?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No return items found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($returns->count() > 0)
                    <tfoot class="table-secondary font-weight-bold">
                        <tr>
                            <td colspan="7" class="text-right">Page Total:</td>
                            <td class="text-center">{{ number_format($returns->sum('quantity'), 4) }}</td>
                            <td></td>
                            <td class="text-end">
                                {{ number_format($returns->sum(fn($ri) => $ri->issuedItem ? $ri->issuedItem->unit_price * $ri->quantity : 0), 2) }}
                            </td>
                            <td colspan="5"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $returns->firstItem() ?? 0 }} to {{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }} items
                </div>
                {{ $returns->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- Department-wise Totals --}}
    @if(isset($deptTotals) && $deptTotals->count() > 0)
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-building mr-2"></i>Department-wise Returned Value Summary</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Department</th>
                        <th class="text-center">Total Qty Returned</th>
                        <th class="text-end">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deptTotals as $i => $dt)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $dt->dept_name }}</td>
                        <td class="text-center">{{ number_format($dt->total_qty, 4) }}</td>
                        <td class="text-end">{{ number_format($dt->total_value, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-dark text-white font-weight-bold">
                    <tr>
                        <td colspan="2" class="text-right">Grand Total</td>
                        <td class="text-center">{{ number_format($deptTotals->sum('total_qty'), 4) }}</td>
                        <td class="text-end">{{ number_format($deptTotals->sum('total_value'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
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