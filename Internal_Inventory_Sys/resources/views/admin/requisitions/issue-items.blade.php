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
        <div class="col-md-9">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Requisition Information</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Requisition #:</strong></div>
                        <div class="col-md-8">{{ $requisition->requisition_number }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Requested By:</strong></div>
                        <div class="col-md-8">{{ $requisition->user->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Department:</strong></div>
                        <div class="col-md-8">{{ $requisition->department->name ?? '-' }}</div>
                    </div>
                    @if($requisition->subDepartment)
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Sub-Department:</strong></div>
                        <div class="col-md-8">{{ $requisition->subDepartment->name }}</div>
                    </div>
                    @endif
                </div>
            </div>

            @foreach($requisition->items as $index => $item)
            @php
                $alreadyIssued = $item->issuedItems->sum('issued_quantity');
                $remaining = $item->quantity - $alreadyIssued;
            @endphp
            
            @if($remaining > 0)
            <div class="card item-card" data-item-index="{{ $index }}">
                <div class="card-header bg-light d-flex justify-content-between">
                    <div class="w-100">
                        <h4 class="card-title mb-0 d-inline-block">
                            <strong>{{ $item->item_name }}</strong>
                            <span class="badge badge-secondary ml-2">{{ $item->item_code }}</span>
                        </h4>
                        @if($item->specifications)
                            <br>
                            <small class="text-muted ml-5 pl-2"><i class="fas fa-info-circle"></i> {{ $item->specifications }}</small>
                        @endif
                    </div>
                    <div class="custom-control custom-checkbox d-inline-block mr-3">
                        <input type="checkbox" class="custom-control-input item-checkbox" id="item-check-{{ $index }}">
                        <label class="custom-control-label" for="item-check-{{ $index }}"></label>
                    </div>
                </div>
                <div class="card-body item-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Requested Location:</strong><br>
                            <span class="badge badge-secondary">{{ $item->location_name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Requested Qty:</strong><br>
                            <span class="badge badge-primary requested-qty" data-quantity="{{ $item->quantity }}">{{ $item->quantity }} {{ $item->unit }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Already Issued:</strong><br>
                            <span class="badge badge-info">{{ $alreadyIssued }} {{ $item->unit }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Remaining:</strong><br>
                            <span class="badge badge-warning remaining-qty" data-quantity="{{ $remaining }}">{{ $remaining }} {{ $item->unit }}</span>
                        </div>
                    </div>

                    @if($item->stock_quantity > 0)
                        @php
                            $qtyStock = 0;
                            foreach($item->locations as $location){
                                if($item->location_code == $location['location_code']){
                                    $qtyStock = $location['quantity'];
                                    break;
                                }
                            }
                            $qtyRemaining = $remaining;
                            $qtyMax = min($qtyRemaining, $qtyStock);
                        @endphp
                        <!-- Add Location Form -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5 class="mb-3">Add Issue Entry</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Location <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm location-select-new" data-item-index="{{ $index }}">
                                                <option value="">Select Location</option>
                                                @foreach($item->locations as $location)
                                                <option value="{{ $location['location_code'] }}" 
                                                    data-quantity="{{ $location['quantity'] }}"
                                                    data-location-name="{{ $location['location_name'] }}"
                                                    @if($item->location_code == $location['location_code']) selected @endif
                                                >
                                                    {{ $location['location_name'] }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Quantity <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control quantity-input-new" 
                                                    data-item-index="{{ $index }}" min="1" max="{{ $qtyMax }}" value="{{ $qtyMax }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">{{ $item->unit }}</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Max: <span class="qtyMax">{{ $qtyMax }}</span><br>
                                                Location Stock: <span class="qtyStock">{{ $qtyStock }}</span><br>
                                                Issue Remaining: <span class="qtyRemaining">{{ $qtyRemaining }}</span>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Notes</label>
                                            <input type="text" class="form-control form-control-sm notes-input-new" 
                                                data-item-index="{{ $index }}" placeholder="Enter notes (optional)">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="mb-1">&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-success btn-block add-to-table-btn" 
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
                                </div>
                            </div>
                        </div>

                        <!-- Issues Table -->
                        <table class="table table-sm table-bordered issues-table" data-item-index="{{ $index }}">
                            <thead class="thead-light">
                                <tr>
                                    <th width="20%">Location</th>
                                    <th width="15%">Issue Quantity</th>
                                    <th width="15%">Location Stock</th>
                                    <th width="35%">Notes</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody class="issues-tbody" data-remaining="{{ $remaining }}" data-item-unit="{{ $item->unit }}" data-original-remaining="{{ $remaining }}">
                                <!-- Rows will be added here dynamically -->
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="font-weight-bold">
                                    <td>Total Issuing:</td>
                                    <td colspan="4">
                                        <span class="badge badge-lg badge-primary total-issuing">0</span> {{ $item->unit }}
                                        <span class="ml-2 text-muted">of {{ $remaining }} {{ $item->unit }} remaining</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="mt-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-primary complete-item-btn" data-item-index="{{ $index }}">
                                <i class="fas fa-check"></i> Complete This Item
                            </button>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> No stock available. Please add stock via SAGE GRN.
                        </div>
                    @endif
                </div>
            </div>
            @endif
            @endforeach

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fas fa-box"></i> Issue All Items
                    </button>
                    <a href="{{ route('admin.requisitions.show', $requisition->id) }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-primary sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h3 class="card-title">Issuance Summary</h3>
                </div>
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
                                {{ $requisition->items->filter(fn($item) => $item->isFullyIssued())->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Issuance</span>
                            <span class="info-box-number text-warning">
                                {{ $requisition->items->filter(fn($item) => !$item->isFullyIssued())->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Completed in Form</span>
                            <span class="info-box-number text-info" id="completedCount">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Instructions</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3">
                        <li>Review requested location and quantity</li>
                        <li>Select location (can be same or different)</li>
                        <li>Enter quantity to issue</li>
                        <li>Add notes if needed</li>
                        <li>Click "Add" to add entry to table</li>
                        <li>Repeat for multiple locations if needed</li>
                        <li>Check the box when item is complete</li>
                        <li>Click "Issue All Items" when done</li>
                    </ol>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="icon fas fa-info"></i>
                        <small>
                            <strong>Note:</strong> Total quantity cannot exceed remaining quantity or available stock per location.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const itemsData = @json($requisition->items);
    let rowCounter = 0;
    
    // Store original location stocks for each item
    const locationStocks = {};
    itemsData.forEach((item, index) => {
        locationStocks[index] = {};
        if (item.locations) {
            item.locations.forEach(loc => {
                locationStocks[index][loc.location_code] = parseFloat(loc.quantity);
            });
        }
    });
    
    // Add to table button click
    $('.add-to-table-btn').on('click', function() {
        const itemIndex = $(this).data('item-index');
        const item = itemsData[itemIndex];
        
        const card = $(`.item-card[data-item-index="${itemIndex}"]`);
        const locationSelect = card.find('.location-select-new');
        const quantityInput = card.find('.quantity-input-new');
        const notesInput = card.find('.notes-input-new');
        const tbody = card.find('.issues-tbody'); // MOVED HERE - Define tbody first
        
        const locationCode = locationSelect.val();
        const locationName = locationSelect.find('option:selected').data('location-name');
        const quantity = parseInt(quantityInput.val()) || 0;
        const locationStock = parseInt(locationSelect.find('option:selected').data('quantity')) || 0;
        const notes = notesInput.val().trim();
        
        // Validation
        if (!locationCode) {
            alert('Please select a location');
            locationSelect.focus();
            return;
        }
        
        // Check if location already exists in the table
        const locationExists = tbody.find('tr[data-location-code="' + locationCode + '"]').length > 0;
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
        const currentTotal = calculateTotal(tbody);
        
        if (currentTotal + quantity > originalRemaining) {
            alert(`Total quantity (${currentTotal + quantity}) would exceed remaining quantity (${originalRemaining})`);
            quantityInput.focus();
            return;
        }
        
        // Add row
        addTableRow(card, tbody, itemIndex, {
            locationCode: locationCode,
            locationName: locationName,
            quantity: quantity,
            locationStock: locationStock,
            notes: notes,
            itemCode: $(this).data('item-code'),
            itemName: $(this).data('item-name'),
            itemCategory: $(this).data('item-category'),
            itemUnit: $(this).data('item-unit'),
            requisitionItemId: $(this).data('requisition-item-id')
        });
        
        // Reset inputs
        locationSelect.val('');
        quantityInput.val('');
        notesInput.val('');
        
        // Update totals and recalculate available quantities
        updateTotal(tbody, itemIndex);
        recalculateAvailableQuantities(itemIndex);
    });
    
    // Remove row button (delegated)
    $(document).on('click', '.remove-row-btn', function() {
        const row = $(this).closest('tr');
        const tbody = row.closest('tbody');
        const itemIndex = tbody.closest('.issues-table').data('item-index');
        
        row.remove();
        updateTotal(tbody, itemIndex);
        recalculateAvailableQuantities(itemIndex);
    });
    
    // Location select change
    $(document).on('change', '.location-select-new', function() {
        const itemIndex = $(this).data('item-index');
        recalculateAvailableQuantities(itemIndex);
    });
    
    // Item checkbox change
    $('.item-checkbox').on('change', function() {
        const card = $(this).closest('.item-card');
        const itemBody = card.find('.item-body');
        const cardFooter = card.find('.card-footer');
        
        if ($(this).is(':checked')) {
            itemBody.slideUp();
            cardFooter.slideUp();
            card.addClass('border-success');
            card.find('.card-header').addClass('bg-success text-white');
        } else {
            itemBody.slideDown();
            cardFooter.slideDown();
            card.removeClass('border-success');
            card.find('.card-header').removeClass('bg-success text-white');
        }
        
        updateCompletedCount();
    });
    
    // Complete item button
    $('.complete-item-btn').on('click', function() {
        const itemIndex = $(this).data('item-index');
        const card = $(`.item-card[data-item-index="${itemIndex}"]`);
        const tbody = card.find('.issues-tbody');
        const rowCount = tbody.find('tr').length;
        
        if (rowCount === 0) {
            alert('Please add at least one issue entry before marking as complete');
            return;
        }
        
        const checkbox = $(`#item-check-${itemIndex}`);
        checkbox.prop('checked', true).trigger('change');
    });
    
    function addTableRow(card, tbody, itemIndex, data) {
        rowCounter++;
        const rowId = `row_${rowCounter}`;
        const itemUnit = tbody.data('item-unit');
        
        const row = `
            <tr id="${rowId}" data-location-code="${data.locationCode}" data-quantity="${data.quantity}">
                <td>
                    <strong>${data.locationName}</strong>
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][location_code]" value="${data.locationCode}">
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][requisition_item_id]" value="${data.requisitionItemId}">
                </td>
                <td>
                    <span class="badge badge-primary">${data.quantity}</span> ${itemUnit}
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][issued_quantity]" value="${data.quantity}">
                </td>
                <td>
                    <span class="badge badge-info">${data.locationStock}</span> ${itemUnit}
                </td>
                <td>
                    ${data.notes ? `<small>${data.notes}</small>` : '<small class="text-muted">-</small>'}
                    <input type="hidden" name="items[${itemIndex}][locations][${rowCounter}][notes]" value="${data.notes}">
                </td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger remove-row-btn" title="Remove">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </td>
            </tr>
        `;
        
        tbody.append(row);
    }
    
    function calculateTotal(tbody) {
        let total = 0;
        tbody.find('input[name*="[issued_quantity]"]').each(function() {
            total += parseInt($(this).val()) || 0;
        });
        return total;
    }
    
    function getLocationIssuedQuantity(tbody, locationCode) {
        let total = 0;
        tbody.find('tr').each(function() {
            if ($(this).data('location-code') === locationCode) {
                total += parseInt($(this).data('quantity')) || 0;
            }
        });
        return total;
    }
    
    function recalculateAvailableQuantities(itemIndex) {
        const card = $(`.item-card[data-item-index="${itemIndex}"]`);
        const tbody = card.find('.issues-tbody');
        const locationSelect = card.find('.location-select-new');
        const quantityInput = card.find('.quantity-input-new');
        
        const selectedLocationCode = locationSelect.val();
        
        if (!selectedLocationCode) {
            // Reset if no location selected
            card.find('.qtyMax').text('0');
            card.find('.qtyStock').text('0');
            card.find('.qtyRemaining').text('0');
            quantityInput.val('');
            return;
        }
        
        // Get original stock for selected location
        const originalLocationStock = locationStocks[itemIndex][selectedLocationCode] || 0;
        
        // Get how much has been issued from this location already
        const issuedFromLocation = getLocationIssuedQuantity(tbody, selectedLocationCode);
        
        // Calculate available stock at this location
        const availableLocationStock = originalLocationStock - issuedFromLocation;
        
        // Get original remaining quantity
        const originalRemaining = parseFloat(tbody.data('original-remaining'));
        
        // Get total already issued
        const totalIssued = calculateTotal(tbody);
        
        // Calculate remaining to issue
        const remainingToIssue = originalRemaining - totalIssued;
        
        // Max quantity is the minimum of available location stock and remaining to issue
        const qtyMax = Math.min(availableLocationStock, remainingToIssue);
        
        // Update UI
        card.find('.qtyMax').text(qtyMax);
        card.find('.qtyStock').text(availableLocationStock);
        card.find('.qtyRemaining').text(remainingToIssue);
        
        // Update input
        quantityInput.attr('max', qtyMax);
        quantityInput.val(qtyMax > 0 ? qtyMax : '');
        
        // Update location dropdown display
        locationSelect.find('option').each(function() {
            const locCode = $(this).val();
            if (locCode && locationStocks[itemIndex][locCode]) {
                const origStock = locationStocks[itemIndex][locCode];
                const issued = getLocationIssuedQuantity(tbody, locCode);
                const available = origStock - issued;
                const locName = $(this).data('location-name');
                const unit = tbody.data('item-unit');
                
                // Update the displayed quantity in dropdown
                $(this).text(`${locName}`);
                $(this).data('quantity', available);
                
                // Disable option if location already added to table or no stock available
                const locationInTable = tbody.find('tr[data-location-code="' + locCode + '"]').length > 0;
                if (locationInTable) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            }
        });
    }
    
    function updateTotal(tbody, itemIndex) {
        const total = calculateTotal(tbody);
        const table = $(`.issues-table[data-item-index="${itemIndex}"]`);
        table.find('.total-issuing').text(total);
        
        // Update remaining badge
        const card = $(`.item-card[data-item-index="${itemIndex}"]`);
        const originalRemaining = parseFloat(tbody.data('original-remaining'));
        const newRemaining = originalRemaining - total;
        const itemUnit = tbody.data('item-unit');
        
        card.find('.remaining-qty').text(newRemaining + ' ' + itemUnit);
        card.find('.remaining-qty').data('quantity', newRemaining);
        
        // Update footer text
        table.find('tfoot .text-muted').text(`of ${originalRemaining} ${itemUnit} remaining`);
    }
    
    function updateCompletedCount() {
        const count = $('.item-checkbox:checked').length;
        $('#completedCount').text(count);
    }
    
    // Form validation
    $('#issueItemsForm').on('submit', function(e) {
        let hasItems = false;
        let isValid = true;
        
        $('.issues-tbody').each(function() {
            const rowCount = $(this).find('tr').length;
            if (rowCount > 0) {
                hasItems = true;
                
                // Check if total doesn't exceed remaining
                const itemIndex = $(this).closest('.issues-table').data('item-index');
                const originalRemaining = parseFloat($(this).data('original-remaining'));
                const total = calculateTotal($(this));
                
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
        
        // Show loading state
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    });
});
</script>

<style>
.issues-table tbody tr {
    vertical-align: middle;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
}

.item-card {
    transition: all 0.3s ease;
}

.item-card.border-success {
    border: 2px solid #28a745 !important;
}

.item-card .card-header.bg-success {
    background-color: #28a745 !important;
}

.custom-control-label {
    cursor: pointer;
}

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
}

.sticky-top {
    position: sticky;
}

.card-footer {
    background-color: #f8f9fa;
}

.item-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
}
</style>
@endpush