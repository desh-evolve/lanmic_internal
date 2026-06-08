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
    <div class="row">
        {{-- ── Main column ────────────────────────────────────────────── --}}
        <div class="col-md-9">

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

            {{-- Requisition info (compact single row) --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Requisition Information</h3></div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-sm-3"><strong>Requisition #:</strong><br>{{ $requisition->requisition_number }}</div>
                        <div class="col-sm-3"><strong>Requested By:</strong><br>{{ $requisition->user->name }}</div>
                        <div class="col-sm-3"><strong>Department:</strong><br>{{ $requisition->department->name ?? '-' }}</div>
                        @if($requisition->subDepartment)
                        <div class="col-sm-3"><strong>Sub-Department:</strong><br>{{ $requisition->subDepartment->name }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Items table ─────────────────────────────────────────── --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Items to Issue</h3></div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="issueItemsTable">

                        @foreach($requisition->items as $index => $item)
                        @php
                            $alreadyIssued = $item->issuedItems->sum('issued_quantity');
                            $remaining     = $item->quantity - $alreadyIssued;
                        @endphp
                        @if($remaining > 0)
                        @php
                            $qtyStock = 0;
                            $qtyRemaining = $remaining;
                            $qtyMax = 0;
                            if ($item->stock_quantity > 0) {
                                foreach ($item->locations as $location) {
                                    if ($item->location_code == $location['location_code']) {
                                        $qtyStock = $location['quantity'];
                                        break;
                                    }
                                }
                                $qtyMax = min($qtyRemaining, $qtyStock);
                            }
                        @endphp

                        {{-- One <tbody> per item so JS can use closest('tbody.item-card') --}}
                        <tbody class="item-card" data-item-index="{{ $index }}">

                            {{-- ── Row 1: Item header ───────────────────── --}}
                            <tr class="item-header-row table-secondary">
                                {{-- checkbox --}}
                                <td class="text-center align-middle" style="width:44px">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input item-checkbox"
                                               id="item-check-{{ $index }}">
                                        <label class="custom-control-label" for="item-check-{{ $index }}"></label>
                                    </div>
                                </td>
                                {{-- # --}}
                                <td class="align-middle text-center font-weight-bold" style="width:36px">
                                    {{ $index + 1 }}
                                </td>
                                {{-- item code + name --}}
                                <td class="align-middle">
                                    <strong>{{ $item->item_name }}</strong>
                                    <span class="badge badge-secondary ml-1">{{ $item->item_code }}</span>
                                    @if($item->specifications)
                                        <br><small class="text-muted">
                                            <i class="fas fa-info-circle"></i> {{ $item->specifications }}
                                        </small>
                                    @endif
                                </td>
                                {{-- qty badges --}}
                                <td class="align-middle text-center" style="width:100px">
                                    <div class="text-muted" style="font-size:10px">REQUESTED</div>
                                    <span class="badge badge-primary requested-qty"
                                          data-quantity="{{ $item->quantity }}">
                                        {{ $item->quantity }} {{ $item->unit }}
                                    </span>
                                </td>
                                <td class="align-middle text-center" style="width:100px">
                                    <div class="text-muted" style="font-size:10px">ISSUED</div>
                                    <span class="badge badge-info">{{ $alreadyIssued }} {{ $item->unit }}</span>
                                </td>
                                <td class="align-middle text-center" style="width:100px">
                                    <div class="text-muted" style="font-size:10px">REMAINING</div>
                                    <span class="badge badge-warning remaining-qty"
                                          data-quantity="{{ $remaining }}">
                                        {{ $remaining }} {{ $item->unit }}
                                    </span>
                                </td>
                                {{-- done button --}}
                                <td class="align-middle text-center" style="width:80px">
                                    @if($item->stock_quantity > 0)
                                    <button type="button"
                                            class="btn btn-xs btn-outline-success complete-item-btn"
                                            data-item-index="{{ $index }}"
                                            title="Mark this item as done">
                                        <i class="fas fa-check"></i> Done
                                    </button>
                                    @else
                                    <span class="badge badge-danger">No Stock</span>
                                    @endif
                                </td>
                            </tr>

                            @if($item->stock_quantity > 0)

                            {{-- ── Row 2: Add-entry input row ──────────── --}}
                            <tr class="item-body">
                                <td colspan="7" class="bg-light py-2 px-3">
                                    <div class="form-row align-items-end">
                                        <div class="col-md-3">
                                            <label class="mb-1 small font-weight-bold">
                                                Location <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control form-control-sm location-select-new"
                                                    data-item-index="{{ $index }}">
                                                <option value="">Select Location</option>
                                                @foreach($item->locations as $location)
                                                <option value="{{ $location['location_code'] }}"
                                                        data-quantity="{{ $location['quantity'] }}"
                                                        data-location-name="{{ $location['location_name'] }}"
                                                        @if($item->location_code == $location['location_code']) selected @endif>
                                                    {{ $location['location_name'] }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="mb-1 small font-weight-bold">
                                                Quantity <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" inputmode="decimal"
                                                       class="form-control quantity-input-new qty-text-input"
                                                       data-item-index="{{ $index }}"
                                                       data-max="{{ $qtyMax }}"
                                                       value="{{ $qtyMax }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">{{ $item->unit }}</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Max:&nbsp;<span class="qtyMax">{{ $qtyMax }}</span>&nbsp;&middot;
                                                Stock:&nbsp;<span class="qtyStock">{{ $qtyStock }}</span>&nbsp;&middot;
                                                Left:&nbsp;<span class="qtyRemaining">{{ $qtyRemaining }}</span>
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="mb-1 small font-weight-bold">Notes</label>
                                            <input type="text"
                                                   class="form-control form-control-sm notes-input-new"
                                                   data-item-index="{{ $index }}"
                                                   placeholder="Optional">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-success btn-block add-to-table-btn"
                                                    data-item-index="{{ $index }}"
                                                    data-item-code="{{ $item->item_code }}"
                                                    data-item-name="{{ $item->item_name }}"
                                                    data-item-category="{{ $item->item_category }}"
                                                    data-item-unit="{{ $item->unit }}"
                                                    data-requisition-item-id="{{ $item->id }}"
                                                    data-remaining="{{ $remaining }}">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- ── Row 3: Staged-entries nested table (hidden until first Add) --}}
                            <tr class="item-body issues-container-row" style="display:none">
                                <td colspan="7" class="p-0">
                                    <table class="table table-sm table-bordered mb-0 issues-table"
                                           data-item-index="{{ $index }}">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="25%">Location</th>
                                                <th width="18%">Issue Quantity</th>
                                                <th width="15%">Location Stock</th>
                                                <th width="30%">Notes</th>
                                                <th width="12%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="issues-tbody"
                                               data-remaining="{{ $remaining }}"
                                               data-item-unit="{{ $item->unit }}"
                                               data-original-remaining="{{ $remaining }}">
                                            {{-- rows injected by JS --}}
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr class="font-weight-bold">
                                                <td>Total Issuing:</td>
                                                <td colspan="4">
                                                    <span class="badge badge-lg badge-primary total-issuing">0</span>
                                                    {{ $item->unit }}
                                                    <span class="ml-2 text-muted">
                                                        of {{ $remaining }} {{ $item->unit }} remaining
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </td>
                            </tr>

                            @else

                            {{-- ── Row 2 (no-stock): alert row ─────────── --}}
                            <tr class="item-body">
                                <td colspan="7" class="py-2 px-3">
                                    <div class="alert alert-danger mb-0 py-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        No stock available. Please add stock via SAGE GRN.
                                    </div>
                                </td>
                            </tr>

                            @endif

                        </tbody>{{-- end item group --}}

                        @endif
                        @endforeach

                    </table>
                </div>{{-- card-body --}}
            </div>{{-- card --}}

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

        </div>{{-- col-md-9 --}}

        {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
        <div class="col-md-3">
            <div class="card card-primary sticky-top" style="top:20px">
                <div class="card-header"><h3 class="card-title">Issuance Summary</h3></div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Items</span>
                            <span class="info-box-number">{{ $requisition->items->count() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Fully Issued</span>
                            <span class="info-box-number text-success">
                                {{ $requisition->items->filter(fn($i) => $i->isFullyIssued())->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Issuance</span>
                            <span class="info-box-number text-warning">
                                {{ $requisition->items->filter(fn($i) => !$i->isFullyIssued())->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Done in This Batch</span>
                            <span class="info-box-number text-info" id="completedCount">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Instructions</h3></div>
                <div class="card-body">
                    <ol class="pl-3 mb-0">
                        <li>Review requested location &amp; quantity</li>
                        <li>Select a location from the dropdown</li>
                        <li>Adjust quantity if needed</li>
                        <li>Add optional notes</li>
                        <li>Click <strong>Add</strong> to stage the entry</li>
                        <li>Repeat for multiple locations if needed</li>
                        <li>Click <strong>Done</strong> when the item is complete</li>
                        <li>Click <strong>Issue All Items</strong> to submit</li>
                    </ol>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="icon fas fa-info"></i>
                        <small>Total quantity per item cannot exceed remaining or location stock.</small>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- row --}}
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const itemsData = @json($requisition->items);
    let rowCounter = 0;

    // Cache original location stocks per item index
    const locationStocks = {};
    itemsData.forEach((item, index) => {
        locationStocks[index] = {};
        if (item.locations) {
            item.locations.forEach(loc => {
                locationStocks[index][loc.location_code] = parseFloat(loc.quantity) || 0;
            });
        }
    });

    // ── Add entry button ────────────────────────────────────────────────
    $('.add-to-table-btn').on('click', function () {
        const itemIndex      = $(this).data('item-index');
        const card           = $(`tbody.item-card[data-item-index="${itemIndex}"]`);
        const locationSelect = card.find('.location-select-new');
        const quantityInput  = card.find('.quantity-input-new');
        const notesInput     = card.find('.notes-input-new');
        const tbody          = card.find('.issues-tbody');

        const locationCode  = locationSelect.val();
        const locationName  = locationSelect.find('option:selected').data('location-name');
        const quantity      = parseFloat(quantityInput.val()) || 0;
        const locationStock = parseFloat(locationSelect.find('option:selected').data('quantity')) || 0;
        const notes         = notesInput.val().trim();

        if (!locationCode) {
            alert('Please select a location');
            locationSelect.focus();
            return;
        }

        const locationExists = tbody.find(`tr[data-location-code="${locationCode}"]`).length > 0;
        if (locationExists) {
            alert('This location has already been added. You cannot add the same location twice for one item.');
            locationSelect.focus();
            return;
        }

        if (quantity <= 0) {
            alert('Please enter a valid quantity');
            quantityInput.focus();
            return;
        }

        if (quantity > locationStock) {
            alert(`Quantity cannot exceed available stock (${locationStock}) at this location`);
            quantityInput.focus();
            return;
        }

        const originalRemaining = parseFloat(tbody.data('original-remaining'));
        const currentTotal      = calculateTotal(tbody);

        if (currentTotal + quantity > originalRemaining) {
            alert(`Total quantity (${currentTotal + quantity}) would exceed remaining quantity (${originalRemaining})`);
            quantityInput.focus();
            return;
        }

        addTableRow(card, tbody, itemIndex, {
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
        locationSelect.val('');
        quantityInput.val('');
        notesInput.val('');

        updateTotal(tbody, itemIndex);
        recalculateAvailableQuantities(itemIndex);
    });

    // ── Remove staged row ───────────────────────────────────────────────
    $(document).on('click', '.remove-row-btn', function () {
        const row      = $(this).closest('tr');
        const tbody    = row.closest('tbody.issues-tbody');
        const itemIndex = tbody.closest('.issues-table').data('item-index');

        row.remove();
        updateTotal(tbody, itemIndex);
        recalculateAvailableQuantities(itemIndex);

        // Hide the entire entries container row when empty
        if (tbody.find('tr').length === 0) {
            tbody.closest('.issues-container-row').hide();
        }
    });

    // ── Location dropdown change ────────────────────────────────────────
    $(document).on('change', '.location-select-new', function () {
        const itemIndex = $(this).data('item-index');
        recalculateAvailableQuantities(itemIndex);
    });

    // ── Checkbox — collapse/expand item rows ───────────────────────────
    $('.item-checkbox').on('change', function () {
        const card      = $(this).closest('tbody.item-card');
        const itemBody  = card.find('tr.item-body');

        if ($(this).is(':checked')) {
            itemBody.hide();
            card.find('.item-header-row')
                .addClass('table-success')
                .removeClass('table-secondary');
        } else {
            // Show input/alert rows always
            card.find('tr.item-body').not('.issues-container-row').show();
            // Only re-show the staged-entries row if there are entries
            const hasEntries = card.find('.issues-tbody tr').length > 0;
            if (hasEntries) {
                card.find('.issues-container-row').show();
            }
            card.find('.item-header-row')
                .removeClass('table-success')
                .addClass('table-secondary');
        }

        updateCompletedCount();
    });

    // ── "Done" button — shortcut to tick the checkbox ──────────────────
    $('.complete-item-btn').on('click', function () {
        const itemIndex = $(this).data('item-index');
        const card      = $(`tbody.item-card[data-item-index="${itemIndex}"]`);
        const tbody     = card.find('.issues-tbody');

        if (tbody.find('tr').length === 0) {
            alert('Please add at least one issue entry before marking as done');
            return;
        }

        $(`#item-check-${itemIndex}`).prop('checked', true).trigger('change');
    });

    // ── Live qty editing on staged rows ────────────────────────────────
    $(document).on('change', '.row-qty-input', function () {
        const $input    = $(this);
        const tbody     = $input.closest('tbody.issues-tbody');
        const itemIndex = tbody.closest('.issues-table').data('item-index');
        const newQty    = parseFloat($input.val()) || 0;
        const maxStock  = parseFloat($input.data('max-stock')) || 0;
        const originalRemaining = parseFloat(tbody.data('original-remaining'));

        let otherTotal = 0;
        tbody.find('.row-qty-input').not($input).each(function () {
            otherTotal += parseFloat($(this).val()) || 0;
        });

        if (newQty <= 0) {
            alert('Quantity must be greater than 0');
            $input.val($input.data('prev') || 0.0001);
            return;
        }
        if (newQty > maxStock) {
            alert(`Quantity (${newQty}) cannot exceed location stock (${maxStock})`);
            $input.val(maxStock);
            return;
        }
        if (newQty + otherTotal > originalRemaining) {
            alert(`Total (${newQty + otherTotal}) would exceed remaining quantity (${originalRemaining})`);
            $input.val(originalRemaining - otherTotal);
            return;
        }

        $input.data('prev', parseFloat($input.val()));
        $input.closest('tr').data('quantity', parseFloat($input.val()));
        updateTotal(tbody, itemIndex);
        recalculateAvailableQuantities(itemIndex);

    }).on('focus', '.row-qty-input', function () {
        $(this).data('prev', parseFloat($(this).val()) || 0);
    });

    // ── Helper: build and append a staged entry row ─────────────────────
    function addTableRow(card, tbody, itemIndex, data) {
        rowCounter++;
        const itemUnit = tbody.data('item-unit');

        tbody.append(`
            <tr id="row_${rowCounter}" data-location-code="${data.locationCode}" data-quantity="${data.quantity}">
                <td>
                    <strong>${data.locationName}</strong>
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][location_code]"       value="${data.locationCode}">
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][requisition_item_id]" value="${data.requisitionItemId}">
                </td>
                <td>
                    <div class="input-group input-group-sm" style="width:130px">
                        <input type="text" inputmode="decimal"
                               class="form-control row-qty-input qty-text-input"
                               name="items[${itemIndex}][locations][${rowCounter}][issued_quantity]"
                               value="${data.quantity}"
                               data-max-stock="${data.locationStock}"
                               data-item-index="${itemIndex}">
                        <div class="input-group-append">
                            <span class="input-group-text">${itemUnit}</span>
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-info">${data.locationStock}</span> ${itemUnit}</td>
                <td>
                    ${data.notes ? `<small>${data.notes}</small>` : '<small class="text-muted">—</small>'}
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][notes]" value="${data.notes}">
                </td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger remove-row-btn">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </td>
            </tr>
        `);

        // Show the staged-entries container row
        card.find('.issues-container-row').show();
    }

    // ── Helper: sum all staged quantities for an item ───────────────────
    function calculateTotal(tbody) {
        let total = 0;
        tbody.find('.row-qty-input').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        return total;
    }

    function getLocationIssuedQuantity(tbody, locationCode) {
        let total = 0;
        tbody.find('tr').each(function () {
            if ($(this).data('location-code') === locationCode) {
                total += parseFloat($(this).find('.row-qty-input').val()) || 0;
            }
        });
        return total;
    }

    // ── Helper: update totals & remaining badge ─────────────────────────
    function updateTotal(tbody, itemIndex) {
        const total    = calculateTotal(tbody);
        const table    = $(`.issues-table[data-item-index="${itemIndex}"]`);
        const card     = $(`tbody.item-card[data-item-index="${itemIndex}"]`);
        const originalRemaining = parseFloat(tbody.data('original-remaining'));
        const itemUnit = tbody.data('item-unit');

        table.find('.total-issuing').text(total);
        table.find('tfoot .text-muted').text(`of ${originalRemaining} ${itemUnit} remaining`);

        card.find('.remaining-qty').text((originalRemaining - total) + ' ' + itemUnit)
                                   .data('quantity', originalRemaining - total);
    }

    // ── Helper: recalculate max/stock/remaining hints ───────────────────
    function recalculateAvailableQuantities(itemIndex) {
        const card           = $(`tbody.item-card[data-item-index="${itemIndex}"]`);
        const tbody          = card.find('.issues-tbody');
        const locationSelect = card.find('.location-select-new');
        const quantityInput  = card.find('.quantity-input-new');
        const selectedCode   = locationSelect.val();

        if (!selectedCode) {
            card.find('.qtyMax').text('0');
            card.find('.qtyStock').text('0');
            card.find('.qtyRemaining').text('0');
            quantityInput.val('');
            return;
        }

        const originalStock     = locationStocks[itemIndex][selectedCode] || 0;
        const issuedFromLoc     = getLocationIssuedQuantity(tbody, selectedCode);
        const availableStock    = originalStock - issuedFromLoc;
        const originalRemaining = parseFloat(tbody.data('original-remaining'));
        const totalIssued       = calculateTotal(tbody);
        const remainingToIssue  = originalRemaining - totalIssued;
        const qtyMax            = Math.min(availableStock, remainingToIssue);

        card.find('.qtyMax').text(qtyMax);
        card.find('.qtyStock').text(availableStock);
        card.find('.qtyRemaining').text(remainingToIssue);

        quantityInput.attr('max', qtyMax).val(qtyMax > 0 ? qtyMax : '');

        // Disable options for locations already staged
        locationSelect.find('option').each(function () {
            const locCode = $(this).val();
            if (!locCode) return;
            const inTable = tbody.find(`tr[data-location-code="${locCode}"]`).length > 0;
            $(this).prop('disabled', inTable);
        });
    }

    // ── Helper: update sidebar "Done in This Batch" counter ────────────
    function updateCompletedCount() {
        $('#completedCount').text($('.item-checkbox:checked').length);
    }

    // ── Qty text-input: block non-numeric keystrokes ────────────────────
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

    // ── Form submit validation ──────────────────────────────────────────
    $('#issueItemsForm').on('submit', function (e) {
        let hasItems = false;
        let isValid  = true;

        $('.issues-tbody').each(function () {
            const rowCount = $(this).find('tr').length;
            if (rowCount > 0) {
                hasItems = true;
                const itemIndex         = $(this).closest('.issues-table').data('item-index');
                const originalRemaining = parseFloat($(this).data('original-remaining'));
                const total             = calculateTotal($(this));

                if (total > originalRemaining) {
                    isValid = false;
                    alert(`Item #${itemIndex + 1}: Total quantity (${total}) exceeds remaining quantity (${originalRemaining})`);
                    return false;
                }
            }
        });

        if (!hasItems) {
            e.preventDefault();
            alert('Please add at least one item to issue');
            return false;
        }

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        $('#submitBtn').prop('disabled', true)
                       .html('<i class="fas fa-spinner fa-spin"></i> Processing…');
    });

});
</script>

<style>
/* ── Issue items table ─────────────────────────────────────────────── */
#issueItemsTable > tbody + tbody > tr:first-child > td {
    border-top: 2px solid #dee2e6;   /* visible separator between item groups */
}

#issueItemsTable .item-header-row td {
    vertical-align: middle;
}

#issueItemsTable .issues-table thead th,
#issueItemsTable .issues-table tbody td {
    padding: 0.4rem 0.5rem;
    vertical-align: middle;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.4rem 0.6rem;
}

.sticky-top {
    position: sticky;
}
</style>
@endpush
