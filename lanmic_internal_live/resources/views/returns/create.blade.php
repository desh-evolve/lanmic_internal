@extends('layouts.admin')

@section('title', 'Create Return')
@section('page-title', 'Create Return')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Returns</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
<form action="{{ route('returns.store') }}" method="POST" id="returnForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Return Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="requisition_id">Select Requisition <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('requisition_id') is-invalid @enderror" 
                                id="requisition_id" name="requisition_id" required style="width: 100%;">
                            <option value="">Select a requisition</option>
                            @foreach($requisitions as $req)
                                <option value="{{ $req->id }}" {{ old('requisition_id') == $req->id ? 'selected' : '' }}>
                                    {{ $req->requisition_number }} - {{ $req->department->name }} /  {{ $req->subDepartment->name }} / {{ $req->division->name }}
                                    ({{ $req->created_at->format('Y-m-d') }})
                                </option>
                            @endforeach
                        </select>
                        @error('requisition_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Only requisitions with issued items are shown</small>
                    </div>
                </div>
            </div>

            <div class="card" id="itemsCard" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">Add Items to Return</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Select items from the issued items list below. You can change the return type, location, and quantity.
                    </div>

                    <!-- Issued Items Selection -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label>Select Issued Item <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="issuedItemSelect" style="width: 100%;" disabled>
                                <option value="">Select requisition first</option>
                            </select>
                        </div>
                    </div>

                    <!-- Item Configuration -->
                    <div id="itemConfig" style="display: none;">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Return Type <span class="text-danger">*</span></label>
                                            <select class="form-control" id="returnType">
                                                <option value="same">Same Condition</option>
                                                <option value="used">Used</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Return Location <span class="text-danger">*</span></label>
                                            <select class="form-control" id="locationSelect">
                                                <option value="">Select location</option>
                                            </select>
                                            <small class="text-muted" id="locationHint"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="quantityInput" 
                                                   min="1" value="1">
                                            <small class="text-muted">
                                                Max: <span id="maxQty">0</span>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-success btn-block" id="addItemBtn">
                                                <i class="fas fa-plus"></i> Add to Return
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-0">
                                            <label>Notes (Optional)</label>
                                            <input type="text" class="form-control" id="notesInput" 
                                                   placeholder="Add any notes about this return">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Return Items Table -->
                    <div class="mt-4">
                        <h5>Items to Return</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10%">Type</th>
                                        <th width="10%">Code</th>
                                        <th width="20%">Name</th>
                                        <th width="10%">Category</th>
                                        <th width="15%">Return Location</th>
                                        <th width="10%">Qty</th>
                                        <th width="8%">Unit</th>
                                        <th width="17%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <tr id="emptyRow">
                                        <td colspan="8" class="text-center text-muted">No items added yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @error('items')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card" id="submitCard" style="display: none;">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Return
                    </button>
                    <a href="{{ route('returns.index') }}" class="btn btn-default">
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
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">Same Condition</span>
                            <span class="info-box-number" id="sameItemsCount">0</span>
                        </div>
                    </div>
                    <div class="info-box bg-warning">
                        <div class="info-box-content">
                            <span class="info-box-text">Used Items</span>
                            <span class="info-box-number" id="usedItemsCount">0</span>
                        </div>
                    </div>
                    <div class="info-box bg-info">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Quantity</span>
                            <span class="info-box-number" id="totalQuantity">0</span>
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
                        <li>Select the requisition you want to return items from</li>
                        <li>Choose an issued item from the dropdown</li>
                        <li>Select return type:
                            <ul>
                                <li><strong>Same:</strong> Item is in same condition</li>
                                <li><strong>Used:</strong> Item has been used</li>
                            </ul>
                        </li>
                        <li>Choose the location to return to</li>
                        <li>Enter quantity (cannot exceed issued quantity)</li>
                        <li>Add optional notes</li>
                        <li>Click "Add to Return"</li>
                        <li>Repeat for more items</li>
                        <li>Submit when complete</li>
                    </ol>
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Important Notes</h3>
                </div>
                <div class="card-body">
                    <ul class="pl-3 mb-0">
                        <li>You can only return items that have been issued</li>
                        <li>Return quantity cannot exceed issued quantity</li>
                        <li>Used items may be directed to different locations</li>
                        <li>Returns require admin approval</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/sage300.js') }}"></script>
<script>
let allReturnItems = [];
let issuedItemsData = [];
let allLocations = [];
let currentIssuedItem = null;

$(document).ready(function() {
    // Initialize Select2
    $('#requisition_id').select2({
        theme: 'bootstrap',
        placeholder: 'Select a requisition'
    });

    $('#issuedItemSelect').select2({
        theme: 'bootstrap',
        placeholder: 'Select an issued item'
    });

    // Load locations
    loadLocations();

    // Requisition change
    $('#requisition_id').change(function() {
        const requisitionId = $(this).val();
        
        if (requisitionId) {
            loadIssuedItems(requisitionId);
            $('#itemsCard').show();
            $('#submitCard').show();
        } else {
            $('#itemsCard').hide();
            $('#submitCard').hide();
            $('#issuedItemSelect').html('<option value="">Select requisition first</option>').prop('disabled', true);
        }
    });

    // Issued item selection
    $('#issuedItemSelect').change(function() {
        const itemId = $(this).val();
        
        if (itemId) {
            currentIssuedItem = issuedItemsData.find(item => item.id == itemId);
            if (currentIssuedItem) {
                setupItemConfig(currentIssuedItem);
                $('#itemConfig').show();
            }
        } else {
            $('#itemConfig').hide();
            currentIssuedItem = null;
        }
    });

    // Return type change
    $('#returnType').change(function() {
        updateLocationOptions();
    });

    // Location change
    $('#locationSelect').change(function() {
        updateLocationHint();
    });

    // Add item button
    $('#addItemBtn').click(function() {
        addItemToTable();
    });

    // Form submission
    $('#returnForm').submit(function(e) {
        if (allReturnItems.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the return');
            return false;
        }
    });
});

function loadLocations() {
    Sage300.getLocations()
        .done(function(response) {
            if (response.success && response.data) {
                allLocations = response.data.map(loc => ({
                    code: loc.LocationKey,
                    name: loc.Name,
                    type: loc.Name.toLowerCase().includes('used') ? 'used' : 'normal'
                }));
            }
        })
        .fail(function() {
            console.error('Failed to load locations');
        });
}

function loadIssuedItems(requisitionId) {
    $('#issuedItemSelect').html('<option value="">Loading...</option>').prop('disabled', true);
    
    $.get(`/api/requisitions/${requisitionId}/issued-items`)
        .done(function(data) {
            issuedItemsData = data;
            
            $('#issuedItemSelect').html('<option value="">Select an issued item</option>').prop('disabled', false);
            
            data.forEach(function(item) {
                // Calculate already returned quantity
                const alreadyReturned = getAllocatedQuantity(item.id);
                const availableToReturn = item.issued_quantity - alreadyReturned;
                
                $('#issuedItemSelect').append(
                    `<option value="${item.id}" data-item='${JSON.stringify(item)}'>
                        ${item.item_code} - ${item.item_name} 
                        (Issued: ${item.issued_quantity}, Available: ${availableToReturn})
                    </option>`
                );
            });
        })
        .fail(function() {
            alert('Failed to load issued items');
            $('#issuedItemSelect').html('<option value="">Error loading items</option>');
        });
}

function setupItemConfig(item) {
    // Calculate available quantity
    const alreadyAllocated = getAllocatedQuantity(item.id);
    const maxQuantity = item.issued_quantity - alreadyAllocated;
    
    $('#maxQty').text(maxQuantity);
    $('#quantityInput').attr('max', maxQuantity).val(Math.min(1, maxQuantity));
    
    // Set default return type
    $('#returnType').val('same').trigger('change');
    
    // Update locations
    updateLocationOptions();
}

function updateLocationOptions() {
    const returnType = $('#returnType').val();
    const locationSelect = $('#locationSelect');
    
    locationSelect.html('<option value="">Select location</option>');
    
    let filteredLocations = allLocations;
    if (returnType === 'used') {
        filteredLocations = allLocations.filter(loc => loc.type === 'used');
    }
    
    filteredLocations.forEach(function(location) {
        locationSelect.append(
            `<option value="${location.code}" data-name="${location.name}">
                ${location.name} (${location.code})
            </option>`
        );
    });
    
    // Set default to original location if available
    if (currentIssuedItem && currentIssuedItem.location_code) {
        const exists = filteredLocations.find(loc => loc.code === currentIssuedItem.location_code);
        if (exists) {
            locationSelect.val(currentIssuedItem.location_code);
        }
    }
    
    updateLocationHint();
}

function updateLocationHint() {
    const returnType = $('#returnType').val();
    const hint = returnType === 'used' 
        ? 'Only "used" locations are shown for used items'
        : 'All locations are available';
    $('#locationHint').text(hint);
}

function getAllocatedQuantity(issuedItemId) {
    return allReturnItems
        .filter(item => item.requisition_issued_item_id == issuedItemId)
        .reduce((sum, item) => sum + item.quantity, 0);
}

function addItemToTable() {
    if (!currentIssuedItem) {
        alert('Please select an issued item');
        return;
    }
    
    const returnType = $('#returnType').val();
    const locationCode = $('#locationSelect').val();
    const locationName = $('#locationSelect option:selected').data('name');
    const quantity = parseInt($('#quantityInput').val()) || 0;
    const notes = $('#notesInput').val();
    
    if (!locationCode) {
        alert('Please select a return location');
        return;
    }
    
    const alreadyAllocated = getAllocatedQuantity(currentIssuedItem.id);
    const maxQuantity = currentIssuedItem.issued_quantity - alreadyAllocated;
    
    if (quantity < 1) {
        alert('Quantity must be at least 1');
        return;
    }
    
    if (quantity > maxQuantity) {
        alert(`Quantity cannot exceed available quantity (${maxQuantity})`);
        return;
    }
    
    // Check for duplicate
    const exists = allReturnItems.findIndex(item => 
        item.requisition_issued_item_id == currentIssuedItem.id &&
        item.return_type === returnType &&
        item.location_code === locationCode
    );
    
    if (exists !== -1) {
        alert('This item with the same type and location is already added');
        return;
    }
    
    const itemData = {
        requisition_issued_item_id: currentIssuedItem.id,
        return_type: returnType,
        location_code: locationCode,
        location_name: locationName,
        item_code: currentIssuedItem.item_code,
        item_name: currentIssuedItem.item_name,
        item_category: currentIssuedItem.item_category,
        unit: currentIssuedItem.unit,
        quantity: quantity,
        notes: notes,
        issued_quantity: currentIssuedItem.issued_quantity
    };
    
    allReturnItems.push(itemData);
    renderTable();
    clearItemConfig();
    updateSummary();
}

function renderTable() {
    const tbody = $('#itemsTableBody');
    tbody.empty();
    
    if (allReturnItems.length === 0) {
        tbody.append('<tr id="emptyRow"><td colspan="8" class="text-center text-muted">No items added yet</td></tr>');
        return;
    }
    
    allReturnItems.forEach((item, index) => {
        const typeBadge = item.return_type === 'used' 
            ? '<span class="badge badge-warning">Used</span>' 
            : '<span class="badge badge-success">Same</span>';
        
        const row = `
            <tr>
                <td>${typeBadge}</td>
                <td>${item.item_code}</td>
                <td>
                    ${item.item_name}
                    ${item.notes ? `<br><small class="text-muted">${item.notes}</small>` : ''}
                    <input type="hidden" name="items[${index}][requisition_issued_item_id]" value="${item.requisition_issued_item_id}">
                    <input type="hidden" name="items[${index}][return_type]" value="${item.return_type}">
                    <input type="hidden" name="items[${index}][location_code]" value="${item.location_code}">
                    <input type="hidden" name="items[${index}][item_code]" value="${item.item_code}">
                    <input type="hidden" name="items[${index}][item_name]" value="${item.item_name}">
                    <input type="hidden" name="items[${index}][item_category]" value="${item.item_category}">
                    <input type="hidden" name="items[${index}][unit]" value="${item.unit}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${index}][notes]" value="${item.notes}">
                </td>
                <td>${item.item_category || 'N/A'}</td>
                <td>
                    <span class="badge badge-info">${item.location_code}</span><br>
                    <small>${item.location_name}</small>
                </td>
                <td><span class="badge badge-primary">${item.quantity}</span></td>
                <td><span class="badge badge-secondary">${item.unit}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem(${index})">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function deleteItem(index) {
    if (confirm('Are you sure you want to remove this item?')) {
        allReturnItems.splice(index, 1);
        renderTable();
        updateSummary();
        
        // Refresh issued items dropdown to update available quantities
        if (currentIssuedItem) {
            setupItemConfig(currentIssuedItem);
        }
    }
}

function clearItemConfig() {
    $('#issuedItemSelect').val('').trigger('change');
    $('#returnType').val('same');
    $('#locationSelect').val('');
    $('#quantityInput').val(1);
    $('#notesInput').val('');
    $('#itemConfig').hide();
    currentIssuedItem = null;
}

function updateSummary() {
    const total = allReturnItems.length;
    const same = allReturnItems.filter(item => item.return_type === 'same').length;
    const used = allReturnItems.filter(item => item.return_type === 'used').length;
    const totalQty = allReturnItems.reduce((sum, item) => sum + item.quantity, 0);
    
    $('#totalItemsCount').text(total);
    $('#sameItemsCount').text(same);
    $('#usedItemsCount').text(used);
    $('#totalQuantity').text(totalQty);
}
</script>
@endpush