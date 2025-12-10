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
                    <h3 class="card-title">Add Items to Return</h3>
                </div>
                <div class="card-body">
                    <!-- Item Entry Form -->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Return Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="returnType">
                                    <option value="">Select Type</option>
                                    <option value="used">Used</option>
                                    <option value="same">Same</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Location <span class="text-danger">*</span></label>
                                <select class="form-control" id="returnLocation" disabled>
                                    <option value="">Select Location</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Select Item <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="itemSelect" style="width: 100%;" disabled>
                                    <option value="">Search and select an item</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="itemQuantity" min="1" value="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Notes</label>
                                <input type="text" class="form-control" id="itemNotes" placeholder="Optional">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-center pb-2">
                            <button type="button" class="btn btn-success" id="addItemBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- All Return Items Table -->
                    <div class="mt-4">
                        <h5>Items to Return</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10%">Type</th>
                                        <th width="12%">Location</th>
                                        <th width="10%">Code</th>
                                        <th width="18%">Name</th>
                                        <th width="10%">Category</th>
                                        <th width="8%">Qty</th>
                                        <th width="8%">Unit</th>
                                        <th width="10%">Price</th>
                                        <th width="14%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <tr id="emptyRow">
                                        <td colspan="9" class="text-center text-muted">No items added yet</td>
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

            <div class="card">
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
                <div class="card-body row">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <div class="info-box-content">
                                <span class="info-box-text">Same Condition</span>
                                <span class="info-box-number" id="sameItemsCount">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-warning">
                            <div class="info-box-content">
                                <span class="info-box-text">Used Items</span>
                                <span class="info-box-number" id="usedItemsCount">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Total Items</span>
                                <span class="info-box-number" id="totalItemsCount">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Total Quantity</span>
                                <span class="info-box-number" id="totalQuantity">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Return by Location</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Items</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="locationSummaryBody">
                                <tr id="locationEmptyRow">
                                    <td colspan="3" class="text-center text-muted">No items</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Instructions</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3">
                        <li>Select return type (Used or Same)</li>
                        <li>Choose appropriate location</li>
                        <li>Search and select the item to return</li>
                        <li>Enter the quantity</li>
                        <li>Add optional notes</li>
                        <li>Click + to add item to list</li>
                        <li>Submit for approval</li>
                    </ol>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Return Information</h3>
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
        </div>
    </div>
</form>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Return Item</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndex">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Return Type</label>
                            <select class="form-control" id="editReturnType">
                                <option value="used">Used</option>
                                <option value="same">Same</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control" id="editLocation">
                                <option value="">Select Location</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Item Code</label>
                            <input type="text" class="form-control" id="editItemCode" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Item Name</label>
                            <input type="text" class="form-control" id="editItemName" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="editQuantity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" id="editNotes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let allReturnItems = [];
let allItems = @json($items);
let allLocations = @json($locations);

$(document).ready(function() {
    // Initialize Select2
    initializeSelect2();

    // Return type change event
    $('#returnType').change(function() {
        const returnType = $(this).val();
        const locationSelect = $('#returnLocation');
        const itemSelect = $('#itemSelect');
        
        locationSelect.html('<option value="">Select Location</option>');
        
        if (returnType) {
            locationSelect.prop('disabled', false);
            
            let filteredLocations;
            if (returnType === 'used') {
                filteredLocations = allLocations.filter(loc => loc.type === 'used');
            } else {
                filteredLocations = allLocations;
            }
            
            filteredLocations.forEach(function(location) {
                locationSelect.append(`<option value="${location.id}" data-name="${location.name}" data-code="${location.code}">${location.name} (${location.code})</option>`);
            });
        } else {
            locationSelect.prop('disabled', true);
            itemSelect.prop('disabled', true);
        }
    });

    // Location change event
    $('#returnLocation').change(function() {
        const itemSelect = $('#itemSelect');
        
        if ($(this).val()) {
            itemSelect.prop('disabled', false);
        } else {
            itemSelect.prop('disabled', true);
        }
    });

    // Add item button
    $('#addItemBtn').click(function() {
        addItemToTable();
    });

    // Save edit button
    $('#saveEditBtn').click(function() {
        saveEdit();
    });

    // Edit return type change
    $('#editReturnType').change(function() {
        const returnType = $(this).val();
        const locationSelect = $('#editLocation');
        
        locationSelect.html('<option value="">Select Location</option>');
        
        let filteredLocations;
        if (returnType === 'used') {
            filteredLocations = allLocations.filter(loc => loc.type === 'used');
        } else {
            filteredLocations = allLocations;
        }
        
        filteredLocations.forEach(function(location) {
            locationSelect.append(`<option value="${location.id}" data-name="${location.name}" data-code="${location.code}">${location.name} (${location.code})</option>`);
        });
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

function initializeSelect2() {
    const normalizedItems = allItems.map(item => ({
        code: item.code || item.id || '',
        name: item.name || '',
        category: item.category || 'N/A',
        unit: item.unit || 'pcs',
        unit_price: parseFloat(item.unit_price || 0)
    })).filter(item => item.code && item.name);

    $('#itemSelect').select2({
        theme: 'bootstrap',
        placeholder: 'Search for an item by code or name',
        allowClear: true,
        data: normalizedItems.map(item => ({
            id: item.code,
            text: `${item.code} - ${item.name} (${item.category})`,
            item: item
        })),
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }

            if (!data.item) {
                return null;
            }

            const term = params.term.toLowerCase();
            const item = data.item;
            
            if (item.code && item.code.toString().toLowerCase().indexOf(term) > -1) {
                return data;
            }
            if (item.name && item.name.toLowerCase().indexOf(term) > -1) {
                return data;
            }
            if (item.category && item.category.toLowerCase().indexOf(term) > -1) {
                return data;
            }

            return null;
        }
    });
}

function addItemToTable() {
    const returnType = $('#returnType').val();
    const locationId = $('#returnLocation').val();
    const locationOption = $('#returnLocation option:selected');
    const locationName = locationOption.data('name');
    const locationCode = locationOption.data('code');
    
    const selectedOption = $('#itemSelect').select2('data')[0];
    
    if (!returnType) {
        alert('Please select a return type');
        return;
    }
    
    if (!locationId) {
        alert('Please select a location');
        return;
    }
    
    if (!selectedOption || !selectedOption.item) {
        alert('Please select an item');
        return;
    }

    const item = selectedOption.item;
    const quantity = parseInt($('#itemQuantity').val()) || 1;
    const notes = $('#itemNotes').val();

    // Check if same item with same location and type already exists
    const existsIndex = allReturnItems.findIndex(i => 
        i.code === item.code && 
        i.locationId === locationId && 
        i.returnType === returnType
    );
    
    if (existsIndex !== -1) {
        alert('This item with the same type and location is already added. You can edit it from the table.');
        return;
    }

    const itemData = {
        returnType: returnType,
        returnTypeLabel: returnType === 'used' ? 'Used' : 'Same',
        locationId: locationId,
        locationName: locationName,
        locationCode: locationCode,
        code: item.code,
        name: item.name,
        category: item.category || 'N/A',
        unit: item.unit || 'pcs',
        quantity: quantity,
        unitPrice: item.unit_price || 0,
        notes: notes
    };

    allReturnItems.push(itemData);
    renderTable();
    clearForm();
    updateSummary();
}

function renderTable() {
    const tbody = $('#itemsTableBody');
    tbody.empty();

    if (allReturnItems.length === 0) {
        tbody.append('<tr id="emptyRow"><td colspan="9" class="text-center text-muted">No items added yet</td></tr>');
        renderLocationSummary();
        return;
    }

    allReturnItems.forEach((item, index) => {
        const typeBadge = item.returnType === 'used' 
            ? '<span class="badge badge-warning">Used</span>' 
            : '<span class="badge badge-success">Same</span>';

        const row = `
            <tr>
                <td>${typeBadge}</td>
                <td><small>${item.locationName}</small></td>
                <td>${item.code}</td>
                <td>
                    ${item.name}
                    ${item.notes ? `<br><small class="text-muted">${item.notes}</small>` : ''}
                    <!-- Hidden Inputs -->
                    <input type="hidden" name="items[${index}][return_type]" value="${item.returnType}">
                    <input type="hidden" name="items[${index}][return_location_id]" value="${item.locationId}">
                    <input type="hidden" name="items[${index}][item_code]" value="${item.code}">
                    <input type="hidden" name="items[${index}][item_name]" value="${item.name}">
                    <input type="hidden" name="items[${index}][item_category]" value="${item.category}">
                    <input type="hidden" name="items[${index}][unit]" value="${item.unit}">
                    <input type="hidden" name="items[${index}][return_quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${index}][unit_price]" value="${item.unitPrice}">
                    <input type="hidden" name="items[${index}][notes]" value="${item.notes}">
                </td>
                <td>${item.category}</td>
                <td><span class="badge badge-primary">${item.quantity}</span></td>
                <td><span class="badge badge-info">${item.unit}</span></td>
                <td>Rs. ${item.unitPrice.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="editItem(${index})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    renderLocationSummary();
}

function renderLocationSummary() {
    const tbody = $('#locationSummaryBody');
    tbody.empty();

    if (allReturnItems.length === 0) {
        tbody.append('<tr id="locationEmptyRow"><td colspan="3" class="text-center text-muted">No items</td></tr>');
        return;
    }

    // Group by location
    const locationGroups = {};
    allReturnItems.forEach(item => {
        if (!locationGroups[item.locationId]) {
            locationGroups[item.locationId] = {
                name: item.locationName,
                items: 0,
                quantity: 0
            };
        }
        locationGroups[item.locationId].items++;
        locationGroups[item.locationId].quantity += item.quantity;
    });

    Object.keys(locationGroups).forEach(locationId => {
        const group = locationGroups[locationId];
        const row = `
            <tr>
                <td><small>${group.name}</small></td>
                <td><span class="badge badge-info">${group.items}</span></td>
                <td><span class="badge badge-primary">${group.quantity}</span></td>
            </tr>
        `;
        tbody.append(row);
    });
}

function editItem(index) {
    const item = allReturnItems[index];
    
    $('#editIndex').val(index);
    $('#editReturnType').val(item.returnType).trigger('change');
    
    // Wait for locations to populate, then set the value
    setTimeout(() => {
        $('#editLocation').val(item.locationId);
    }, 100);
    
    $('#editItemCode').val(item.code);
    $('#editItemName').val(item.name);
    $('#editQuantity').val(item.quantity);
    $('#editNotes').val(item.notes);
    
    $('#editModal').modal('show');
}

function saveEdit() {
    const index = parseInt($('#editIndex').val());
    const returnType = $('#editReturnType').val();
    const locationId = $('#editLocation').val();
    const locationOption = $('#editLocation option:selected');
    const quantity = parseInt($('#editQuantity').val());
    const notes = $('#editNotes').val();

    if (!returnType) {
        alert('Please select a return type');
        return;
    }

    if (!locationId) {
        alert('Please select a location');
        return;
    }

    if (quantity < 1) {
        alert('Quantity must be at least 1');
        return;
    }

    const item = allReturnItems[index];
    
    // Check for duplicates (excluding current item)
    const duplicateIndex = allReturnItems.findIndex((i, idx) => 
        idx !== index &&
        i.code === item.code && 
        i.locationId === locationId && 
        i.returnType === returnType
    );
    
    if (duplicateIndex !== -1) {
        alert('An item with the same code, type, and location already exists.');
        return;
    }

    item.returnType = returnType;
    item.returnTypeLabel = returnType === 'used' ? 'Used' : 'Same';
    item.locationId = locationId;
    item.locationName = locationOption.data('name');
    item.locationCode = locationOption.data('code');
    item.quantity = quantity;
    item.notes = notes;

    $('#editModal').modal('hide');
    renderTable();
    updateSummary();
}

function deleteItem(index) {
    if (confirm('Are you sure you want to remove this item?')) {
        allReturnItems.splice(index, 1);
        renderTable();
        updateSummary();
    }
}

function clearForm() {
    $('#returnType').val('');
    $('#returnLocation').html('<option value="">Select Location</option>').prop('disabled', true);
    $('#itemSelect').val(null).trigger('change').prop('disabled', true);
    $('#itemQuantity').val(1);
    $('#itemNotes').val('');
}

function updateSummary() {
    const totalItems = allReturnItems.length;
    const sameItems = allReturnItems.filter(item => item.returnType === 'same').length;
    const usedItems = allReturnItems.filter(item => item.returnType === 'used').length;
    
    let totalQuantity = 0;
    allReturnItems.forEach(item => {
        totalQuantity += item.quantity;
    });

    $('#totalItemsCount').text(totalItems);
    $('#sameItemsCount').text(sameItems);
    $('#usedItemsCount').text(usedItems);
    $('#totalQuantity').text(totalQuantity);
}
</script>
@endpush