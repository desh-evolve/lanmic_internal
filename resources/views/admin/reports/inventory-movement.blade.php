{{-- resources/views/admin/reports/inventory-movement.blade.php --}}
@extends('layouts.admin')

@section('title', 'Inventory Movement Report')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
<style>
    /* ── Screen styles ─────────────────────────────────────────────── */
    .report-header-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 14px 18px;
        margin-bottom: 20px;
        font-size: 0.85rem;
    }
    .report-header-box table td { padding: 2px 12px 2px 0; }
    .report-header-box .label { color: #6c757d; font-weight: 600; white-space: nowrap; }

    .item-block { margin-bottom: 28px; }
    .item-heading {
        background: #343a40;
        color: #fff;
        padding: 6px 10px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 4px 4px 0 0;
    }
    .item-heading .costing { font-size: 0.78rem; opacity: 0.85; }

    .movement-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .movement-table th {
        background: #495057;
        color: #fff;
        padding: 5px 8px;
        text-align: center;
        border: 1px solid #6c757d;
        white-space: nowrap;
    }
    .movement-table td {
        padding: 4px 8px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    .movement-table .section-header {
        background: #e9ecef;
        font-size: 0.78rem;
        font-weight: 600;
        color: #495057;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    .movement-table tr.issue-row    { background: #fff8f0; }
    .movement-table tr.grn-row     { background: #f0fff4; }
    .movement-table tr.po-grn-row  { background: #e8f4fd; }
    .movement-table tr.balance-row {
        background: #fffde7;
        font-weight: 600;
        font-style: italic;
        color: #5d4037;
    }
    .movement-table tr.closing-row {
        background: #e3f2fd;
        font-weight: 700;
        color: #1565c0;
        border-top: 2px solid #1565c0;
    }
    .movement-table tr.total-row  {
        background: #f8f9fa;
        font-weight: 700;
        border-top: 2px solid #495057;
    }
    .qty-in   { color: #155724; font-weight: 600; }
    .qty-out  { color: #721c24; font-weight: 600; }
    .cost-in  { color: #155724; }
    .cost-out { color: #721c24; }

    /* ── Print styles ──────────────────────────────────────────────── */
    @media print {
        .no-print { display: none !important; }
        body { font-size: 10pt; }
        .container-fluid { padding: 0; }
        .print-header { display: block !important; }
        .item-heading { background: #000 !important; -webkit-print-color-adjust: exact; }
        .movement-table th { background: #000 !important; -webkit-print-color-adjust: exact; }
        .item-block { page-break-inside: avoid; }
    }
    .print-header { display: none; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="row mb-3 no-print">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">I/C Inventory Movement Report</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Inventory Movement</li>
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

    {{-- Filters --}}
    <div class="card mb-3 no-print">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5></div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.inventory-movement') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item</label>
                    <select name="item_code" id="itemCodeSelect" class="form-control" style="width:100%">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item['code'] }}"
                                {{ $itemCode === $item['code'] ? 'selected' : '' }}>
                                {{ $item['code'] }} — {{ $item['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $deptId == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('reports.inventory-movement') }}" class="btn btn-secondary w-100"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Print header (hidden on screen) --}}
    <div class="print-header mb-3">
        <div style="display:flex; justify-content:space-between; font-size:10pt;">
            <div><strong>Lanka Minerals &amp; Chemicals (Pvt) Ltd.</strong></div>
            <div>{{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div style="font-size:12pt; font-weight:bold; text-align:center; margin:6px 0;">
            I/C Inventory Movement Report
        </div>
        <table style="font-size:9pt; margin-bottom:8px;">
            <tr><td class="label">From Date</td><td>{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} To {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</td></tr>
            @if($itemCode)
                @php $selectedItem = collect($items)->firstWhere('code', $itemCode); @endphp
                <tr><td class="label">Item Filter</td><td>{{ $itemCode }}{{ $selectedItem ? ' — ' . $selectedItem['name'] : '' }}</td></tr>
            @endif
        </table>
    </div>

    {{-- Report parameter summary (screen) --}}
    <div class="report-header-box no-print">
        <table>
            <tr>
                <td class="label">From Date</td>
                <td>{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &nbsp;To&nbsp; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</td>
                <td width="40"></td>
                <td class="label">Item Filter</td>
                <td>
                    @if($itemCode)
                        @php $selectedItem = collect($items)->firstWhere('code', $itemCode); @endphp
                        {{ $itemCode }}{{ $selectedItem ? ' — ' . $selectedItem['name'] : '' }}
                    @else
                        All Items
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Department Filter</td>
                <td colspan="4">{{ $departments->firstWhere('id', $deptId)?->name ?? 'All Departments' }}</td>
            </tr>
        </table>
    </div>

    @if($grouped->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No movements found for the selected filters and date range.
        </div>
    @else
        @foreach($grouped as $itemCode => $rows)
            @php
                $firstRow     = $rows->first();
                $totalQtyIn   = $rows->sum('qty_in');
                $totalCostIn  = $rows->sum('cost_in');
                $totalQtyOut  = $rows->sum('qty_out');
                $totalCostOut = $rows->sum('cost_out');
                $netQty       = $totalQtyIn - $totalQtyOut;
                $openingQty   = $openingBalances[$itemCode] ?? null;
                $closingQty   = $openingQty !== null ? $openingQty + $totalQtyIn - $totalQtyOut : null;
            @endphp

            <div class="item-block">
                {{-- Item heading --}}
                <div class="item-heading">
                    <span>{{ $itemCode }} &nbsp; {{ $firstRow['item_name'] }}</span>
                    <span class="costing">Unit: {{ $firstRow['unit'] }}</span>
                </div>

                <div class="table-responsive">
                    <table class="movement-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Date</th>
                                <th rowspan="2">Document Number</th>
                                <th rowspan="2">Type</th>
                                <th rowspan="2">Invoice No</th>
                                <th rowspan="2">Vendor No</th>
                                <th rowspan="2">Vendor Name</th>
                                <th rowspan="2">Department</th>
                                <th rowspan="2">Sub-Dept</th>
                                <th rowspan="2">Unit</th>
                                <th colspan="2" class="section-header" style="background:#d4edda;color:#155724;">&#9650; Inventory In</th>
                                <th colspan="2" class="section-header" style="background:#f8d7da;color:#721c24;">&#9660; Inventory Out</th>
                                <th rowspan="2">Remarks</th>
                            </tr>
                            <tr>
                                <th style="background:#d4edda;color:#155724;">Quantity</th>
                                <th style="background:#d4edda;color:#155724;">Extended Cost</th>
                                <th style="background:#f8d7da;color:#721c24;">Quantity</th>
                                <th style="background:#f8d7da;color:#721c24;">Extended Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Opening Balance row --}}
                            @if($openingQty !== null)
                            <tr class="balance-row">
                                <td><small>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</small></td>
                                <td colspan="8"><em>Opening Balance</em></td>
                                <td class="text-right" style="color:#5d4037;">
                                    <strong>{{ number_format($openingQty, 4) }}</strong>
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            @endif

                            @foreach($rows as $row)
                                @php
                                    $rowClass = match($row['type']) {
                                        'RETURN GRN'   => 'grn-row',
                                        'Purchase GRN' => 'po-grn-row',
                                        default        => 'issue-row',
                                    };
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                    <td><strong>{{ $row['document_no'] }}</strong></td>
                                    <td>
                                        @if($row['type'] === 'RETURN GRN')
                                            <span class="badge" style="background:#28a745;">RETURN GRN</span>
                                        @elseif($row['type'] === 'Purchase GRN')
                                            <span class="badge" style="background:#0d6efd;">Purchase GRN</span>
                                        @else
                                            <span class="badge" style="background:#6c757d;">{{ $row['type'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['invoice_no'] }}</td>
                                    <td>{{ $row['vendor_no'] }}</td>
                                    <td>{{ $row['vendor_name'] }}</td>
                                    <td>{{ $row['department'] }}</td>
                                    <td>{{ $row['sub_dept'] ?: '—' }}</td>
                                    <td class="text-center">{{ $row['unit'] }}</td>
                                    <td class="text-right qty-in">
                                        {{ $row['qty_in'] > 0 ? number_format($row['qty_in'], 4) : '' }}
                                    </td>
                                    <td class="text-right cost-in">
                                        {{ $row['cost_in'] > 0 ? number_format($row['cost_in'], 2) : '' }}
                                    </td>
                                    <td class="text-right qty-out">
                                        {{ $row['qty_out'] > 0 ? number_format($row['qty_out'], 4) : '' }}
                                    </td>
                                    <td class="text-right cost-out">
                                        {{ $row['cost_out'] > 0 ? number_format($row['cost_out'], 2) : '' }}
                                    </td>
                                    <td>{{ $row['remarks'] }}</td>
                                </tr>
                            @endforeach

                            {{-- Item Total --}}
                            <tr class="total-row">
                                <td colspan="9" class="text-right pr-2" style="font-size:0.8rem;">Item Total:</td>
                                <td class="text-right qty-in">{{ $totalQtyIn > 0 ? number_format($totalQtyIn, 4) : '' }}</td>
                                <td class="text-right cost-in">{{ $totalCostIn > 0 ? number_format($totalCostIn, 2) : '' }}</td>
                                <td class="text-right qty-out">{{ $totalQtyOut > 0 ? number_format($totalQtyOut, 4) : '' }}</td>
                                <td class="text-right cost-out">{{ $totalCostOut > 0 ? number_format($totalCostOut, 2) : '' }}</td>
                                <td></td>
                            </tr>
                            {{-- Net Movement --}}
                            <tr class="total-row" style="background:#e2e8f0;">
                                <td colspan="9" class="text-right pr-2" style="font-size:0.8rem;">Net Movement (In − Out):</td>
                                <td class="text-right" colspan="2">
                                    <span class="{{ $netQty >= 0 ? 'qty-in' : 'qty-out' }}">
                                        {{ number_format(abs($netQty), 4) }} {{ $netQty >= 0 ? '▲' : '▼' }}
                                    </span>
                                </td>
                                <td class="text-right" colspan="2">
                                    @php $netCost = $totalCostIn - $totalCostOut; @endphp
                                    <span class="{{ $netCost >= 0 ? 'cost-in' : 'cost-out' }}">
                                        {{ number_format(abs($netCost), 2) }} {{ $netCost >= 0 ? '▲' : '▼' }}
                                    </span>
                                </td>
                                <td></td>
                            </tr>

                            {{-- Closing Balance row --}}
                            @if($closingQty !== null)
                            <tr class="closing-row">
                                <td><small>{{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</small></td>
                                <td colspan="8"><em>Closing Balance</em></td>
                                <td class="text-right">
                                    <strong>{{ number_format($closingQty, 4) }}</strong>
                                </td>
                                <td colspan="4"></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        {{-- Grand Total --}}
        @php
            $grandQtyIn   = $grouped->flatten(1)->sum('qty_in');
            $grandCostIn  = $grouped->flatten(1)->sum('cost_in');
            $grandQtyOut  = $grouped->flatten(1)->sum('qty_out');
            $grandCostOut = $grouped->flatten(1)->sum('cost_out');
        @endphp
        <div class="card mt-2">
            <div class="card-header bg-dark text-white">
                <strong><i class="fas fa-sigma"></i> Grand Total — {{ $grouped->count() }} item(s), {{ $grouped->flatten(1)->count() }} movement(s)</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 text-center" style="font-size:0.9rem;">
                    <thead class="table-secondary">
                        <tr>
                            <th></th>
                            <th class="text-success">Total Qty In</th>
                            <th class="text-success">Total Cost In</th>
                            <th class="text-danger">Total Qty Out</th>
                            <th class="text-danger">Total Cost Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Grand Total</strong></td>
                            <td class="text-success"><strong>{{ number_format($grandQtyIn, 4) }}</strong></td>
                            <td class="text-success"><strong>{{ number_format($grandCostIn, 2) }}</strong></td>
                            <td class="text-danger"><strong>{{ number_format($grandQtyOut, 4) }}</strong></td>
                            <td class="text-danger"><strong>{{ number_format($grandCostOut, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#itemCodeSelect').select2({
            theme: 'bootstrap',
            placeholder: 'Type to search by code or name...',
            allowClear: true,
            minimumInputLength: 0,
            matcher: function (params, data) {
                if (!params.term || params.term.trim() === '') return data;
                const term = params.term.toLowerCase();
                if (data.text && data.text.toLowerCase().includes(term)) return data;
                return null;
            }
        });
    });

    function exportToExcel() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('export', 'excel');
        window.location.href = window.location.pathname + '?' + urlParams.toString();
    }
</script>
@endpush
