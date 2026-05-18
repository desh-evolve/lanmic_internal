@extends('layouts.admin')

@section('title', 'Edit Return')
@section('page-title', 'Edit Return')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Returns</a></li>
    <li class="breadcrumb-item active">Edit #{{ $return->id }}</li>
@endsection

@section('content')
<form action="{{ route('returns.update', $return->id) }}" method="POST" id="returnForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Return #{{ $return->id }}</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="icon fas fa-info"></i>
                        <strong>Return Types:</strong><br>
                        <strong>Used:</strong> Items that have been used and can only be returned to used locations.<br>
                        <strong>Same:</strong> Items in same condition, can be returned to any location.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Items to Return</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="itemsContainer">
                        <!-- Items will be added here dynamically -->
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Return
                    </button>
                    <a href="{{ route('returns.show', $return->id) }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Items</span>
                            <span class="info-box-number" id="totalItemsCount">0</span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Quantity</span>
                            <span class="info-box-number" id="totalQuantity">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let itemIndex = 0;
const allItems = @json($items);
const allLocations = @json($locations);
const existingItems = @json($return->items);

$(document).ready(function() {
    // Add item button
    $('#addItemBtn').click(function() {
        addItemRow();
    });

    // Load existing items
    existingItems.forEach(function(item) {
        addItemRow(item);
    });
});

function addItemRow(existingItem = null) {
    const itemHtml = `
        <div class="card item-row" data-index="${itemIndex}">
            <div class="card-body">
                <button type="button" class="close remove-item" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Return Type <span class="text-danger">*</span></label>
                            <select class="form-control return-type" name="items[${itemIndex}][return_type]" required>
                                <option value="">Select Type</option>
                                <option value="used" ${existingItem && existingItem.return_type === 'used' ? 'selected' : ''}>Used</option>
                                <option value="same" ${existingItem && existingItem.return_type === 'same' ? 'selected' : ''}>Same</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Return Location <span class="text-danger">*</span></label>
                            <select class="form-control return-location" name="items[${itemIndex}][return_location_id]" required ${existingItem ? '' : 'disabled'}>
                                <option value="">Select Location</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Select Item <span class="text-danger">*</span></label>
                            <select class="form-control item-select" name="items[${itemIndex}][item_code]" required ${existingItem ? '' : 'disabled'}>
                                <option value="">Select an item</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control item-name" name="items[${itemIndex}][item_name]" value="${existingItem ? existingItem.item_name : ''}" readonly required>
                            <input type="hidden" class="item-category" name="items[${itemIndex}][item_category]" value="${existingItem ? existingItem.item_category || '' : ''}">
                            <input type="hidden" class="item-unit" name="items[${itemIndex}][unit]" value="${existingItem ? existingItem.unit || '' : ''}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control item-quantity" name="items[${itemIndex}][return_quantity]" min="1" value="${existingItem ? existingItem.return_quantity : 1}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Unit Price</label>
                            <input type="number" class="form-control item-price" name="items[${itemIndex}][unit_price]" step="0.01" min="0" value="${existingItem ? existingItem.unit_price : 0}" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" name="items[${itemIndex}][notes]" rows="2">${existingItem ? existingItem.notes || '' : ''}</textarea>
                </div>
            </div>
        </div>
    `;

    $('#itemsContainer').append(itemHtml);

    const currentRow = $(`.item-row[data-index="${itemIndex}"]`);

    // If existing item, populate locations and items
    if (existingItem) {
        populateLocationsForType(currentRow, existingItem.return_type, existingItem.return_location_id);
        populateItems(currentRow, existingItem.item_code);
    }

    // Return type change event
    currentRow.find('.return-type').change(function() {
        const returnType = $(this).val();
        const row = $(this).closest('.item-row');
        populateLocationsForType(row, returnType);
    });

    // Location change event
    currentRow.find('.return-location').change(function() {
        const row = $(this).closest('.item-row');
        if ($(this).val()) {
            populateItems(row);
        }
    });

    // Item select change event
    currentRow.find('.item-select').change(function() {
        const selectedOption = $(this).find('option:selected');
        const row = $(this).closest('.item-row');
        
        row.find('.item-name').val(selectedOption.data('name'));
        row.find('.item-category').val(selectedOption.data('category'));
        row.find('.item-unit').val(selectedOption.data('unit'));
        row.find('.item-price').val(selectedOption.data('price'));
    });

    // Quantity change event
    currentRow.find('.item-quantity').on('input', function() {
        updateSummary();
    });

    // Remove item event
    currentRow.find('.remove-item').click(function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
            updateSummary();
        } else {
            alert('At least one item is required');
        }
    });

    itemIndex++;
    updateSummary();
}

function populateLocationsForType(row, returnType, selectedLocationId = null) {
    const locationSelect = row.find('.return-location');
    const itemSelect = row.find('.item-select');
    
    locationSelect.html('<option value="">Select Location</option>');
    itemSelect.html('<option value="">Select an item</option>');
    
    if (returnType) {
        locationSelect.prop('disabled', false);
        
        let filteredLocations;
        if (returnType === 'used') {
            filteredLocations = allLocations.filter(loc => loc.type === 'used');
        } else {
            filteredLocations = allLocations;
        }
        
        filteredLocations.forEach(function(location) {
            const selected = selectedLocationId && location.id == selectedLocationId ? 'selected' : '';
            locationSelect.append(`<option value="${location.id}" ${selected}>${location.name} (${location.code})</option>`);
        });
    } else {
        locationSelect.prop('disabled', true);
        itemSelect.prop('disabled', true);
    }
}

function populateItems(row, selectedItemCode = null) {
    const itemSelect = row.find('.item-select');
    
    itemSelect.prop('disabled', false);
    itemSelect.html('<option value="">Select an item</option>');
    
    allItems.forEach(function(item) {
        const selected = selectedItemCode && item.code === selectedItemCode ? 'selected' : '';
        itemSelect.append(`
            <option value="${item.code}" 
                    data-name="${item.name}"
                    data-category="${item.category || ''}"
                    data-unit="${item.unit || ''}"
                    data-price="${item.unit_price || 0}"
                    ${selected}>
                ${item.code} - ${item.name} (${item.category || 'N/A'})
            </option>
        `);
    });
}

function updateSummary() {
    const itemCount = $('.item-row').length;
    let totalQuantity = 0;

    $('.item-row').each(function() {
        const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
        totalQuantity += quantity;
    });

    $('#totalItemsCount').text(itemCount);
    $('#totalQuantity').text(totalQuantity);
}
</script>
@endpush