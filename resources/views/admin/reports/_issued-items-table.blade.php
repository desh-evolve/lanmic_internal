{{--
    Shared partial for issued-items report.
    Expects:
        $groupedItems       — Collection keyed by department name
        $statistics         — ['total_issued', 'total_quantity', 'total_value']
        $departments        — Collection of active Department models
        $reportTitle        — string
        $exportRoute        — named route string
        $items              — optional: Collection of Sage300Item for searchable dropdown
        $itemType           — optional: 'all'|'local'|'import' (default 'all')
        $requestedByUsers   — optional: Collection of User models for Requested By filter
--}}
@php $itemType = $itemType ?? 'all'; @endphp

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<div class="card mb-3 no-print">
    <div class="card-header py-2">
        <h6 class="mb-0"><i class="fas fa-filter mr-1"></i> Filters</h6>
    </div>
    <div class="card-body py-2">
        <form method="GET" action="{{ route($exportRoute) }}">
            <div class="row align-items-end">

                <div class="col-md-2">
                    <label class="small mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="small mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}">
                </div>

                {{-- Item type filter --}}
                <div class="col-md-1">
                    <label class="small mb-1">Type</label>
                    <select name="item_type" class="form-control form-control-sm">
                        <option value="all"    {{ $itemType === 'all'    ? 'selected' : '' }}>All</option>
                        <option value="local"  {{ $itemType === 'local'  ? 'selected' : '' }}>Local</option>
                        <option value="import" {{ $itemType === 'import' ? 'selected' : '' }}>Import</option>
                    </select>
                </div>

                {{-- Searchable item dropdown --}}
                @if(isset($items) && $items->isNotEmpty())
                <div class="col-md-3">
                    <label class="small mb-1">Item</label>
                    <select name="item_code" id="issuedItemSelect" class="form-control form-control-sm" style="width:100%">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->item_code }}"
                                {{ request('item_code') === $item->item_code ? 'selected' : '' }}>
                                {{ $item->item_code }} — {{ $item->description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="col-md-2">
                    <label class="small mb-1">Item Code</label>
                    <input type="text" name="item_code" class="form-control form-control-sm"
                           placeholder="Item code…" value="{{ request('item_code') }}">
                </div>
                <div class="col-md-1">
                    <label class="small mb-1">Item Name</label>
                    <input type="text" name="item_name" class="form-control form-control-sm"
                           placeholder="Name…" value="{{ request('item_name') }}">
                </div>
                @endif

                <div class="col-md-2">
                    <label class="small mb-1">Department</label>
                    <select name="department_id" class="form-control form-control-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(isset($requestedByUsers) && $requestedByUsers->isNotEmpty())
                <div class="col-md-3">
                    <label class="small mb-1">Requested By</label>
                    <select name="requested_by[]" id="requestedBySelect" class="form-control form-control-sm" multiple style="width:100%">
                        @foreach($requestedByUsers as $user)
                            <option value="{{ $user->id }}"
                                {{ in_array($user->id, (array) request('requested_by', [])) ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2 d-flex" style="gap:4px">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route($exportRoute) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- ── Summary chips ────────────────────────────────────────────────────── --}}
<div class="row mb-3 no-print">
    <div class="col-md-4">
        <div class="info-box mb-0">
            <span class="info-box-icon bg-info"><i class="fas fa-hand-holding"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Records</span>
                <span class="info-box-number">{{ number_format($statistics['total_issued']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-0">
            <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Quantity</span>
                <span class="info-box-number">{{ number_format($statistics['total_quantity'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-0">
            <span class="info-box-icon bg-primary"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Value</span>
                <span class="info-box-number">{{ number_format($statistics['total_value'], 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Report table ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center no-print">
        <h5 class="mb-0">
            {{ $reportTitle }}
            @if($itemType !== 'all')
                <span class="badge badge-{{ $itemType === 'local' ? 'success' : 'primary' }} ml-2">
                    {{ ucfirst($itemType) }} Items
                </span>
            @endif
        </h5>
        <div>
            <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-secondary btn-sm ml-1" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        @if($groupedItems->isEmpty())
            <div class="p-4 text-center text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                No issued items found for the selected filters.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" id="reportTable">
                <thead>
                    <tr class="thead-dark">
                        <th style="min-width:110px">Document No</th>
                        <th style="width:85px">Date</th>
                        <th style="min-width:140px">Requested By</th>
                        <th style="min-width:180px">Item</th>
                        <th style="width:65px">Type</th>
                        <th style="width:45px">Loc</th>
                        <th style="width:45px">UOM</th>
                        <th style="width:65px" class="text-right">Qty</th>
                        <th style="width:90px" class="text-right">Unit Price</th>
                        <th style="width:100px" class="text-right">Total Cost</th>
                        <th style="min-width:130px">Sub-Dept</th>
                        <th style="min-width:90px">Job Card</th>
                        <th style="min-width:140px">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedItems as $deptName => $items)

                    <tr class="report-group-header">
                        <td colspan="13"><strong>{{ strtoupper($deptName) }}</strong></td>
                    </tr>

                    @foreach($items as $item)
                    @php
                        $isImport = str_starts_with($item->item_code, 'ENI-');
                    @endphp
                    <tr>
                        <td>{{ $item->requisition->requisition_number ?? '—' }}</td>
                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($item->issued_at)->format('d/m/Y') }}</td>
                        <td>{{ $item->requisition->user->name ?? '—' }}</td>
                        <td>
                            {{ $item->item_name }}
                            <br><small class="text-muted">{{ $item->item_code }}</small>
                        </td>
                        <td class="text-center">
                            @if($isImport)
                                <span class="badge badge-primary" style="font-size:0.72rem;">Import</span>
                            @else
                                <span class="badge badge-success" style="font-size:0.72rem;">Local</span>
                            @endif
                        </td>
                        <td>{{ $item->location_code ?? '—' }}</td>
                        <td>{{ $item->unit ?? '—' }}</td>
                        <td class="text-right">{{ number_format($item->issued_quantity, 2) }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                        <td>{{ $item->requisition->subDepartment->name ?? '—' }}</td>
                        <td>{{ $item->reference_number_1 ?? '—' }}</td>
                        <td>{{ $item->requisition->notes ?? $item->notes ?? '—' }}</td>
                    </tr>
                    @endforeach

                    <tr class="report-group-subtotal">
                        <td colspan="7"></td>
                        <td class="text-right">
                            <strong>{{ number_format($items->sum('issued_quantity'), 2) }}</strong>
                        </td>
                        <td></td>
                        <td class="text-right">
                            <strong>{{ number_format($items->sum('total_price'), 2) }}</strong>
                        </td>
                        <td colspan="3"></td>
                    </tr>

                    <tr class="report-group-spacer"><td colspan="13"></td></tr>

                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="report-grand-total">
                        <td colspan="6"><strong>TOTAL ISSUING COST</strong></td>
                        <td></td>
                        <td class="text-right">
                            <strong>{{ number_format($statistics['total_quantity'], 2) }}</strong>
                        </td>
                        <td></td>
                        <td class="text-right">
                            <strong>{{ number_format($statistics['total_value'], 2) }}</strong>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
function exportToExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.location.href = window.location.pathname + '?' + params.toString();
}
</script>

<style>
#reportTable th, #reportTable td {
    padding: 4px 6px;
    vertical-align: middle;
    font-size: 0.82rem;
}
.report-group-header td {
    background-color: #e9ecef !important;
    font-size: 0.85rem;
    letter-spacing: 0.03em;
}
.report-group-subtotal td {
    border-top: 2px solid #343a40 !important;
    background-color: #f8f9fa;
}
.report-group-spacer td {
    padding: 3px !important;
    background-color: transparent !important;
    border-left: none !important;
    border-right: none !important;
}
.report-grand-total td {
    background-color: #fff59d !important;
    font-size: 0.88rem;
}
@media print {
    .no-print          { display: none !important; }
    .main-sidebar, .main-header, .content-header, .main-footer { display: none !important; }
    .content-wrapper   { margin: 0 !important; padding: 0 !important; }
    .card              { border: none !important; box-shadow: none !important; }
    .card-header       { display: none !important; }
    #reportTable       { font-size: 8pt; width: 100%; }
    #reportTable th, #reportTable td { padding: 2px 4px; }
}
</style>
