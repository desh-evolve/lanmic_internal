@extends('layouts.admin')

@section('title', 'Issue Items')
@section('page-title', 'Issue Items')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.requisitions.index') }}">Requisitions</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.requisitions.show', $requisition->id) }}">{{ $requisition->requisition_number }}</a></li>
    <li class="breadcrumb-item active">Issue Items</li>
@endsection

@section('content')
<form action="{{ route('admin.requisitions.issue-items.store', $requisition->id) }}" method="POST" id="issueItemsForm">
    @csrf

    {{-- ── Flash messages ──────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    {{-- ── Requisition info bar ─────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-body py-2">
            <div class="row">
                <div class="col-sm-3">
                    <strong>Requisition #:</strong><br>
                    <span class="badge badge-secondary">{{ $requisition->requisition_number }}</span>
                </div>
                <div class="col-sm-3">
                    <strong>Requested By:</strong><br>
                    {{ $requisition->user->name }}
                </div>
                <div class="col-sm-3">
                    <strong>Department:</strong><br>
                    {{ $requisition->department->name ?? '-' }}
                </div>
                @if($requisition->subDepartment)
                <div class="col-sm-3">
                    <strong>Sub-Department:</strong><br>
                    {{ $requisition->subDepartment->name }}
                </div>
                @endif
                @if($requisition->notes)
                <div class="col-sm-3">
                    <strong>Notes:</strong><br>
                    {{ $requisition->notes }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Two-panel layout ────────────────────────────────────────────── --}}
    <div class="row">

        {{-- ════════════════════════════════════════════════════════════════
             LEFT  —  Items to Issue (one row per item, inline controls)
        ═══════════════════════════════════════════════════════════════════ --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Items to Issue</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0" id="itemsTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:32px">#</th>
                                <th>Item</th>
                                <th style="width:90px">Remaining</th>
                                <th style="width:150px">Location</th>
                                <th style="width:95px">Qty</th>
                                <th>Notes</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowNum = 0; @endphp
                            @foreach($requisition->items as $index => $item)
                            @php
                                $alreadyIssued = $item->issuedItems->sum('issued_quantity');
                                $remaining     = $item->quantity - $alreadyIssued;
                            @endphp
                            @if($remaining > 0)
                            @php
                                $rowNum++;
                                $qtyStock = 0;
                                $qtyMax   = 0;
                                if ($item->stock_quantity > 0) {
                                    foreach ($item->locations as $loc) {
                                        if ($item->location_code == $loc['location_code']) {
                                            $qtyStock = $loc['quantity'];
                                            break;
                                        }
                                    }
                                    $qtyMax = min($remaining, $qtyStock);
                                }
                            @endphp
                            <tr class="item-row"
                                data-item-index="{{ $index }}"
                                data-original-remaining="{{ $remaining }}"
                                data-item-unit="{{ $item->unit }}">

                                <td class="text-center align-middle">{{ $rowNum }}</td>

                                {{-- Item name + code --}}
                                <td class="align-middle">
                                    <strong>{{ $item->item_code }}</strong><br>
                                    <small class="text-muted">{{ $item->item_name }}</small>
                                    @if($item->specifications)
                                        <br><small class="text-muted">
                                            <i class="fas fa-info-circle"></i> {{ $item->specifications }}
                                        </small>
                                    @endif
                                </td>

                                {{-- Remaining badge (updated live) --}}
                                <td class="text-center align-middle">
                                    <span class="badge badge-warning remaining-qty"
                                          data-quantity="{{ $remaining }}">
                                        {{ $remaining }}<br>{{ $item->unit }}
                                    </span>
                                </td>

                                @if($item->stock_quantity > 0)
                                {{-- Location --}}
                                <td class="align-middle">
                                    <select class="form-control form-control-sm location-select-new"
                                            data-item-index="{{ $index }}">
                                        <option value="">Select</option>
                                        @foreach($item->locations as $loc)
                                        <option value="{{ $loc['location_code'] }}"
                                                data-quantity="{{ $loc['quantity'] }}"
                                                data-location-name="{{ $loc['location_name'] }}"
                                                @if($item->location_code == $loc['location_code']) selected @endif>
                                            {{ $loc['location_name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Qty --}}
                                <td class="align-middle">
                                    <input type="text" inputmode="decimal"
                                           class="form-control form-control-sm quantity-input-new qty-text-input"
                                           data-item-index="{{ $index }}"
                                           data-max="{{ $qtyMax }}"
                                           value="{{ $qtyMax }}">
                                    <small class="text-muted">
                                        Max:&nbsp;<span class="qtyMax">{{ $qtyMax }}</span>
                                    </small>
                                </td>

                                {{-- Notes --}}
                                <td class="align-middle">
                                    <input type="text"
                                           class="form-control form-control-sm notes-input-new"
                                           data-item-index="{{ $index }}"
                                           placeholder="Optional">
                                </td>

                                {{-- Add button --}}
                                <td class="text-center align-middle">
                                    <button type="button"
                                            class="btn btn-sm btn-success add-to-table-btn"
                                            data-item-index="{{ $index }}"
                                            data-item-code="{{ $item->item_code }}"
                                            data-item-name="{{ $item->item_name }}"
                                            data-item-category="{{ $item->item_category }}"
                                            data-item-unit="{{ $item->unit }}"
                                            data-requisition-item-id="{{ $item->id }}"
                                            data-remaining="{{ $remaining }}"
                                            title="Add to issue list">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>

                                @else
                                {{-- No stock --}}
                                <td colspan="4" class="align-middle text-center">
                                    <span class="badge badge-danger">
                                        <i class="fas fa-exclamation-circle"></i> No Stock
                                    </span>
                                </td>
                                @endif

                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>{{-- card-body --}}
            </div>{{-- card --}}
        </div>{{-- col-md-7 --}}

        {{-- ════════════════════════════════════════════════════════════════
             RIGHT  —  Issue List (staged entries, one row per Add click)
        ═══════════════════════════════════════════════════════════════════ --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Issue List</h3>
                    <span class="badge badge-primary" id="stagedCount">0 entries</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0" id="stagedTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Item</th>
                                <th style="width:110px">Location</th>
                                <th style="width:80px">Qty</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="stagedTbody">
                            <tr id="emptyRow">
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Add items from the left table
                                </td>
                            </tr>
                        </tbody>
                        {{-- Running total footer --}}
                        <tfoot class="bg-light" id="stagedFooter" style="display:none">
                            <tr>
                                <td colspan="4" class="text-right">
                                    <small class="text-muted" id="totalEntriesInfo"></small>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>{{-- card-body --}}
            </div>{{-- card --}}
        </div>{{-- col-md-5 --}}

    </div>{{-- row --}}

    {{-- ── Submit bar ───────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-footer">
            <button type="submit" class="btn btn-success" id="submitBtn">
                <i class="fas fa-box"></i> Issue All Items
            </button>
            <a href="{{ route('admin.requisitions.show', $requisition->id) }}" class="btn btn-default ml-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

</form>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    function escHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    const itemsData = @json($requisition->items);
    let rowCounter  = 0;

    // Cache original location stocks: { itemIndex: { locationCode: qty } }
    const locationStocks = {};
    itemsData.forEach((item, index) => {
        locationStocks[index] = {};
        if (item.locations) {
            item.locations.forEach(loc => {
                locationStocks[index][loc.location_code] = parseFloat(loc.quantity) || 0;
            });
        }
    });

    // ── Add button ──────────────────────────────────────────────────────
    $(document).on('click', '.add-to-table-btn', function () {
        const itemIndex      = $(this).data('item-index');
        const itemRow        = $(`tr.item-row[data-item-index="${itemIndex}"]`);
        const locationSelect = itemRow.find('.location-select-new');
        const quantityInput  = itemRow.find('.quantity-input-new');
        const notesInput     = itemRow.find('.notes-input-new');

        const locationCode  = locationSelect.val();
        const locationName  = locationSelect.find('option:selected').data('location-name') || locationCode;
        const quantity      = parseFloat(quantityInput.val()) || 0;
        const locationStock = parseFloat(locationSelect.find('option:selected').data('quantity')) || 0;
        const notes         = notesInput.val().trim();

        // ── Validation ────────────────────────────────────────────────
        if (!locationCode) {
            alert('Please select a location.');
            locationSelect.focus();
            return;
        }

        const alreadyAdded = $(`#stagedTbody tr[data-item-index="${itemIndex}"][data-location-code="${locationCode}"]`).length > 0;
        if (alreadyAdded) {
            alert('This location is already in the issue list for this item.');
            locationSelect.focus();
            return;
        }

        if (quantity <= 0) {
            alert('Please enter a valid quantity.');
            quantityInput.focus();
            return;
        }

        if (quantity > locationStock) {
            alert(`Quantity cannot exceed available stock (${locationStock}) at this location.`);
            quantityInput.focus();
            return;
        }

        const originalRemaining = parseFloat(itemRow.data('original-remaining'));
        const currentTotal      = calculateTotalForItem(itemIndex);

        if (currentTotal + quantity > originalRemaining) {
            alert(`Total (${currentTotal + quantity}) would exceed the remaining quantity (${originalRemaining}).`);
            quantityInput.focus();
            return;
        }

        // ── Append to right table ─────────────────────────────────────
        addStagedRow(itemIndex, {
            locationCode,
            locationName,
            quantity,
            locationStock,
            notes,
            itemCode:          $(this).data('item-code'),
            itemName:          $(this).data('item-name'),
            itemCategory:      $(this).data('item-category'),
            itemUnit:          $(this).data('item-unit'),
            requisitionItemId: $(this).data('requisition-item-id')
        });

        // Reset inputs
        locationSelect.val('').trigger('change');
        quantityInput.val('');
        notesInput.val('');

        updateItemRemaining(itemIndex);
        recalcItem(itemIndex);
        updateStagedCount();
    });

    // ── Remove button ───────────────────────────────────────────────────
    $(document).on('click', '.remove-row-btn', function () {
        const row       = $(this).closest('tr');
        const itemIndex = parseInt(row.data('item-index'));

        row.remove();

        if ($('#stagedTbody tr:not(#emptyRow)').length === 0) {
            $('#emptyRow').show();
            $('#stagedFooter').hide();
        }

        updateItemRemaining(itemIndex);
        recalcItem(itemIndex);
        updateStagedCount();
    });

    // ── Location dropdown change ─────────────────────────────────────────
    $(document).on('change', '.location-select-new', function () {
        recalcItem($(this).data('item-index'));
    });

    // ── Build and append a staged row ───────────────────────────────────
    function addStagedRow(itemIndex, data) {
        rowCounter++;
        $('#emptyRow').hide();
        $('#stagedFooter').show();

        $('#stagedTbody').append(`
            <tr data-item-index="${itemIndex}"
                data-location-code="${escHtml(data.locationCode)}"
                data-quantity="${escHtml(data.quantity)}">
                <td>
                    <strong>${escHtml(data.itemCode)}</strong>
                    <br><small class="text-muted">${escHtml(data.itemName)}</small>
                    ${data.notes ? `<br><small class="text-muted"><i class="fas fa-comment-alt fa-xs"></i> ${escHtml(data.notes)}</small>` : ''}
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][requisition_item_id]" value="${escHtml(data.requisitionItemId)}">
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][location_code]"       value="${escHtml(data.locationCode)}">
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][notes]"               value="${escHtml(data.notes)}">
                </td>
                <td class="align-middle">
                    <small>${escHtml(data.locationName)}</small>
                </td>
                <td class="align-middle">
                    <strong>${escHtml(data.quantity)}</strong> <small class="text-muted">${escHtml(data.itemUnit)}</small>
                    <input type="hidden"
                           name="items[${itemIndex}][locations][${rowCounter}][issued_quantity]"
                           value="${escHtml(data.quantity)}">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-xs btn-danger remove-row-btn" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `);
    }

    // ── Sum staged qty for one item (reads data-quantity on each staged <tr>) ─
    function calculateTotalForItem(itemIndex) {
        let total = 0;
        $(`#stagedTbody tr[data-item-index="${itemIndex}"]`).each(function () {
            total += parseFloat($(this).data('quantity')) || 0;
        });
        return total;
    }

    function getLocationIssuedQty(itemIndex, locationCode) {
        let total = 0;
        $(`#stagedTbody tr[data-item-index="${itemIndex}"][data-location-code="${locationCode}"]`).each(function () {
            total += parseFloat($(this).data('quantity')) || 0;
        });
        return total;
    }

    // ── Update the Remaining badge in the left table ─────────────────────
    function updateItemRemaining(itemIndex) {
        const itemRow   = $(`tr.item-row[data-item-index="${itemIndex}"]`);
        const original  = parseFloat(itemRow.data('original-remaining'));
        const issued    = calculateTotalForItem(itemIndex);
        const left      = original - issued;
        const unit      = itemRow.data('item-unit');

        itemRow.find('.remaining-qty')
               .text(left + '\n' + unit)
               .data('quantity', left);

        // Disable Add button if nothing left to issue
        itemRow.find('.add-to-table-btn').prop('disabled', left <= 0);
    }

    // ── Recalc max qty hint and disable staged locations ─────────────────
    function recalcItem(itemIndex) {
        const itemRow        = $(`tr.item-row[data-item-index="${itemIndex}"]`);
        const locationSelect = itemRow.find('.location-select-new');
        const quantityInput  = itemRow.find('.quantity-input-new');
        const selectedCode   = locationSelect.val();

        if (!selectedCode) {
            itemRow.find('.qtyMax').text('—');
            quantityInput.val('');
            return;
        }

        const originalStock     = locationStocks[itemIndex][selectedCode] || 0;
        const issuedFromLoc     = getLocationIssuedQty(itemIndex, selectedCode);
        const availableStock    = originalStock - issuedFromLoc;
        const original          = parseFloat(itemRow.data('original-remaining'));
        const alreadyIssued     = calculateTotalForItem(itemIndex);
        const remainingToIssue  = original - alreadyIssued;
        const qtyMax            = Math.min(availableStock, remainingToIssue);

        itemRow.find('.qtyMax').text(qtyMax > 0 ? qtyMax : 0);
        quantityInput.val(qtyMax > 0 ? qtyMax : '');

        // Disable location options already staged
        locationSelect.find('option').each(function () {
            const code = $(this).val();
            if (!code) return;
            const staged = $(`#stagedTbody tr[data-item-index="${itemIndex}"][data-location-code="${code}"]`).length > 0;
            $(this).prop('disabled', staged);
        });
    }

    // ── Update the "N entries" counter in the right card header ─────────
    function updateStagedCount() {
        const n = $('#stagedTbody tr:not(#emptyRow)').length;
        $('#stagedCount').text(n + (n === 1 ? ' entry' : ' entries'));

        // Summary line in footer
        if (n > 0) {
            let parts = [];
            $('tr.item-row').each(function () {
                const idx   = parseInt($(this).data('item-index'));
                const total = calculateTotalForItem(idx);
                if (total > 0) {
                    const unit = $(this).data('item-unit');
                    const code = $(this).find('td:nth-child(2) strong').text();
                    parts.push(`${code}: ${total} ${unit}`);
                }
            });
            $('#totalEntriesInfo').text(parts.join('  ·  '));
        }
    }

    // ── Qty text-input: allow only digits and one decimal point ─────────
    $(document).on('keydown', '.qty-text-input', function (e) {
        if ([8,9,13,27,46,35,36,37,38,39,40].includes(e.keyCode)) return;
        if ((e.ctrlKey || e.metaKey) && [65,67,86,88,90].includes(e.keyCode)) return;
        if ((e.keyCode === 190 || e.keyCode === 110) && !$(this).val().includes('.')) return;
        if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) return;
        e.preventDefault();
    });
    $(document).on('paste', '.qty-text-input', function (e) {
        const text = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        if (!/^\d*\.?\d*$/.test(text)) e.preventDefault();
    });

    // ── Form submit validation ───────────────────────────────────────────
    $('#issueItemsForm').on('submit', function (e) {
        const stagedRows = $('#stagedTbody tr:not(#emptyRow)');

        if (stagedRows.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the issue list before submitting.');
            return false;
        }

        // Check per-item totals don't exceed remaining
        let valid = true;
        const seen = new Set();

        stagedRows.each(function () {
            const idx = parseInt($(this).data('item-index'));
            if (seen.has(idx)) return;
            seen.add(idx);

            const itemRow = $(`tr.item-row[data-item-index="${idx}"]`);
            const cap     = parseFloat(itemRow.data('original-remaining'));
            const total   = calculateTotalForItem(idx);

            if (total > cap) {
                valid = false;
                const code = itemRow.find('td:nth-child(2) strong').text();
                alert(`${code}: total to issue (${total}) exceeds remaining (${cap}).`);
                return false; // break each
            }
        });

        if (!valid) {
            e.preventDefault();
            return false;
        }

        $('#submitBtn').prop('disabled', true)
                       .html('<i class="fas fa-spinner fa-spin"></i> Processing…');
    });

});
</script>

<style>
/* tighten table cells */
#itemsTable td, #itemsTable th,
#stagedTable td, #stagedTable th {
    vertical-align: middle;
    padding: 0.35rem 0.5rem;
}

/* keep remaining badge text centered on two lines */
.remaining-qty {
    white-space: pre-line;
    display: inline-block;
    line-height: 1.2;
    text-align: center;
}
</style>
@endpush
