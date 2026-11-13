@extends('layouts.admin')

@section('title', 'Create Requisition')
@section('page-title', 'Create Requisition')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisitions</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
<form action="{{ route('requisitions.store') }}" method="POST" id="requisitionForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Requisition Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department_id">Department <span class="text-danger">*</span></label>
                                <select class="form-control @error('department_id') is-invalid @enderror" 
                                        id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                            @if($department->short_code) ({{ $department->short_code }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sub_department_id">Sub-Department</label>
                                <select class="form-control @error('sub_department_id') is-invalid @enderror" 
                                        id="sub_department_id" name="sub_department_id">
                                    <option value="">Select Sub-Department</option>
                                </select>
                                @error('sub_department_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_id">Division</label>
                                <select class="form-control @error('division_id') is-invalid @enderror" 
                                        id="division_id" name="division_id">
                                    <option value="">Select Division</option>
                                </select>
                                @error('division_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2" placeholder="Any additional information">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Items</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-info" id="refreshItemsBtn">
                            <i class="fas fa-sync"></i> Refresh Items
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Item Entry Form -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Select Item <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="itemSelect" style="width: 100%;">
                                    <option value="">Search and select an item</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Location <span class="text-danger">*</span></label>
                                <select class="form-control" id="locationSelect" disabled>
                                    <option value="">Select item first</option>
                                </select>
                                <small class="text-muted">
                                    Available: <span id="locationAvailableQty" class="font-weight-bold text-success">-</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="itemQuantity" min="1" value="1" disabled>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center pb-2">
                            <button type="button" class="btn btn-success" id="addItemBtn">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>

                    <!-- All Requested Items Table -->
                    <div class="mt-4">
                        <h5>Requested Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10%">Code</th>
                                        <th width="20%">Name</th>
                                        <th width="10%">Category</th>
                                        <th width="12%">Location</th>
                                        <th width="8%">Qty</th>
                                        <th width="8%">Unit</th>
                                        <th width="10%">Available</th>
                                        <th width="12%">Status</th>
                                        <th width="10%">Actions</th>
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
                        <i class="fas fa-paper-plane"></i> Submit Requisition
                    </button>
                    <a href="{{ route('requisitions.index') }}" class="btn btn-default">
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
                            <span class="info-box-text">Available Items</span>
                            <span class="info-box-number" id="availableItemsCount">0</span>
                        </div>
                    </div>
                    <div class="info-box bg-warning">
                        <div class="info-box-content">
                            <span class="info-box-text">PO Items</span>
                            <span class="info-box-number" id="poItemsCount">0</span>
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

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Purchase Order Items</h3>
                    <div class="card-tools">
                        <span class="badge badge-warning" id="poItemsBadge">0</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>PO Qty</th>
                                </tr>
                            </thead>
                            <tbody id="poItemsTableBody">
                                <tr id="poEmptyRow">
                                    <td colspan="4" class="text-center text-muted">No PO items</td>
                                </tr>
                            </tbody>
                        </table>
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
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndex">
                <div class="form-group">
                    <label>Item Code</label>
                    <input type="text" class="form-control" id="editItemCode" readonly>
                </div>
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" class="form-control" id="editItemName" readonly>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" class="form-control" id="editLocation" readonly>
                </div>
                <div class="form-group">
                    <label>Available Quantity</label>
                    <input type="text" class="form-control" id="editAvailableQty" readonly>
                </div>
                <div class="form-group">
                    <label>Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="editQuantity" min="1" required>
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
<script src="{{ asset('js/sage300.js') }}"></script>
<script>
let allRequestedItems = [];
let allItems = [];
let itemLocations = {};
let pendingApprovals = {}; // Track pending quantities per item-location combination

$(document).ready(function() {
    loadItems();
    loadPendingApprovals();

    $('#department_id').change(function() {
        const departmentId = $(this).val();
        $('#sub_department_id').html('<option value="">Select Sub-Department</option>').prop('disabled', true);
        $('#division_id').html('<option value="">Select Division</option>').prop('disabled', true);

        if (departmentId) {
            $.get(`/api/departments/${departmentId}/sub-departments`, function(data) {
                if (data.length > 0) {
                    $('#sub_department_id').prop('disabled', false);
                    data.forEach(function(subDept) {
                        const text = subDept.short_code ? 
                            `${subDept.name} (${subDept.short_code})` : 
                            subDept.name;
                        $('#sub_department_id').append(`<option value="${subDept.id}">${text}</option>`);
                    });
                }
            });
        }
    });

    $('#sub_department_id').change(function() {
        const subDepartmentId = $(this).val();
        $('#division_id').html('<option value="">Select Division</option>').prop('disabled', true);

        if (subDepartmentId) {
            $.get(`/api/sub-departments/${subDepartmentId}/divisions`, function(data) {
                if (data.length > 0) {
                    $('#division_id').prop('disabled', false);
                    data.forEach(function(division) {
                        const text = division.short_code ? 
                            `${division.name} (${division.short_code})` : 
                            division.name;
                        $('#division_id').append(`<option value="${division.id}">${text}</option>`);
                    });
                }
            });
        }
    });

    $('#itemSelect').on('select2:select', function(e) {
        const data = e.params.data;
        if (data && data.item) {
            loadItemLocations(data.item.code);
        }
    });

    $('#locationSelect').change(function() {
        const selectedLocation = $(this).val();
        const selectedItem = $('#itemSelect').select2('data')[0];
        
        if (selectedLocation && selectedItem) {
            const locationData = JSON.parse($(this).find('option:selected').attr('data-location'));
            const itemCode = selectedItem.item.code;
            const locationCode = locationData.location_code;
            
            // Get pending quantity for this item-location combination
            const pendingKey = `${itemCode}_${locationCode}`;
            const pendingQty = pendingApprovals[pendingKey] || 0;
            
            // Calculate actual available quantity
            const stockQty = locationData.quantity;
            const actualAvailable = Math.max(0, stockQty - pendingQty);
            
            $('#locationAvailableQty').html(`
                <span class="text-success">${actualAvailable}</span>
                ${pendingQty > 0 ? `<br><small class="text-warning">(${pendingQty} pending approval)</small>` : ''}
            `);
            $('#itemQuantity').prop('disabled', false).val(1);
        } else {
            $('#locationAvailableQty').text('-');
            $('#itemQuantity').prop('disabled', true).val(1);
        }
    });

    $('#addItemBtn').click(function() {
        addItemToTable();
    });

    $('#saveEditBtn').click(function() {
        saveEdit();
    });

    $('#requisitionForm').submit(function(e) {
        if (allRequestedItems.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the requisition');
            return false;
        }
    });

    $('#refreshItemsBtn').click(function() {
        const $btn = $(this);
        $btn.prop('disabled', true).find('i').addClass('fa-spin');
        loadItems();
        loadPendingApprovals();
        setTimeout(function() {
            $btn.prop('disabled', false).find('i').removeClass('fa-spin');
        }, 1000);
    });
});

function loadItems() {
    $('#itemSelect').html('<option value="">Loading items from Sage300...</option>');

    Sage300.getItems()
        .done(function(response) {
            if (response.success && response.data) {
                allItems = response.data.map(item => ({
                    code: item.UnformattedItemNumber,
                    name: item.Description,
                    category: item.Category || 'N/A',
                    unit: item.StockingUnitOfMeasure
                }));
                initializeSelect2();
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to load items from Sage300:', error);
            alert('Failed to load items. Please refresh the page.');
        });
}

function loadPendingApprovals() {
    // Load pending approval quantities from API
    $.get('/api/requisitions/pending-items', function(data) {
        pendingApprovals = {};
        data.forEach(function(item) {
            const key = `${item.item_code}_${item.location_code}`;
            pendingApprovals[key] = parseInt(item.total_quantity) || 0;
        });
    }).fail(function() {
        console.log('Could not load pending approvals');
        pendingApprovals = {};
    });
}

function initializeSelect2() {
    const normalizedItems = allItems.map(item => {
        return {
            code: item.code || '',
            name: item.name || '',
            category: item.category || 'N/A',
            unit: item.unit || 'pcs'
        };
    }).filter(item => item.code && item.name);

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

function loadItemLocations(itemCode) {
    $('#locationSelect').html('<option value="">Loading locations...</option>').prop('disabled', true);
    $('#locationAvailableQty').text('-');
    $('#itemQuantity').prop('disabled', true).val(1);

    Sage300.getItemLocations(itemCode)
        .done(function(response) {
            if (response.success && response.data && response.data.length > 0) {
                itemLocations[itemCode] = response.data;
                
                $('#locationSelect').html('<option value="">Select Location</option>').prop('disabled', false);
                
                response.data.forEach(function(location) {
                    // Calculate actual available considering pending approvals
                    const pendingKey = `${itemCode}_${location.location_code}`;
                    const pendingQty = pendingApprovals[pendingKey] || 0;
                    const actualAvailable = Math.max(0, location.quantity - pendingQty);
                    
                    const locationText = `${location.location_code} - ${location.location_name} (Available: ${actualAvailable}${pendingQty > 0 ? `, Pending: ${pendingQty}` : ''})`;
                    $('#locationSelect').append(
                        `<option value="${location.location_code}" data-location='${JSON.stringify(location)}'>${locationText}</option>`
                    );
                });
            } else {
                $('#locationSelect').html('<option value="">No locations available</option>');
                alert('No locations found for this item');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to load item locations:', error);
            $('#locationSelect').html('<option value="">Error loading locations</option>');
            alert('Failed to load locations. Please try again.');
        });
}

function addItemToTable() {
    const selectedOption = $('#itemSelect').select2('data')[0];
    if (!selectedOption || !selectedOption.item) {
        alert('Please select an item');
        return;
    }

    const locationSelect = $('#locationSelect');
    const selectedLocation = locationSelect.val();
    if (!selectedLocation) {
        alert('Please select a location');
        return;
    }

    const item = selectedOption.item;
    const locationData = JSON.parse(locationSelect.find('option:selected').attr('data-location'));
    const requestedQty = parseInt($('#itemQuantity').val()) || 1;

    if (requestedQty < 1) {
        alert('Quantity must be at least 1');
        return;
    }

    // Check if same item-location combination already exists
    const existingIndex = allRequestedItems.findIndex(i => 
        i.code === item.code && i.location_code === locationData.location_code
    );
    
    if (existingIndex !== -1) {
        alert(`This item from location "${locationData.location_name}" is already added. Please edit the existing entry.`);
        return;
    }

    // Calculate available quantity considering pending approvals
    const pendingKey = `${item.code}_${locationData.location_code}`;
    const pendingQty = pendingApprovals[pendingKey] || 0;
    const stockQty = locationData.quantity;
    const actualAvailable = Math.max(0, stockQty - pendingQty);
    
    // Calculate requisition and PO quantities
    const needsPO = requestedQty > actualAvailable;
    const requisitionQty = requestedQty;
    const poQty = Math.max(0, requestedQty - actualAvailable);

    const itemData = {
        code: item.code,
        name: item.name,
        category: item.category || 'N/A',
        unit: item.unit || 'pcs',
        location_code: locationData.location_code,
        location_name: locationData.location_name,
        quantity: requestedQty,
        stock_qty: stockQty,
        pending_qty: pendingQty,
        available_qty: actualAvailable,
        needsPO: needsPO,
        requisition_qty: requisitionQty,
        po_qty: poQty
    };

    allRequestedItems.push(itemData);
    renderTable();
    clearForm();
    updateSummary();
}

function renderTable() {
    const tbody = $('#itemsTableBody');
    tbody.empty();

    if (allRequestedItems.length === 0) {
        tbody.append('<tr id="emptyRow"><td colspan="9" class="text-center text-muted">No items added yet</td></tr>');
        renderPOSummary();
        return;
    }

    allRequestedItems.forEach((item, index) => {
        let statusBadge = '';
        let availableDisplay = '';
        
        if (item.needsPO) {
            statusBadge = `
                <span class="badge badge-success">${item.available_qty} Available</span><br>
                <span class="badge badge-warning">${item.po_qty} PO Required</span>
            `;
            availableDisplay = `
                <span class="badge badge-success">${item.available_qty}</span>
                ${item.pending_qty > 0 ? `<br><small class="text-warning">${item.pending_qty} pending</small>` : ''}
            `;
        } else {
            statusBadge = `<span class="badge badge-success">Fully Available</span>`;
            availableDisplay = `
                <span class="badge badge-success">${item.available_qty}</span>
                ${item.pending_qty > 0 ? `<br><small class="text-warning">${item.pending_qty} pending</small>` : ''}
            `;
        }

        const row = `
            <tr>
                <td>${item.code}</td>
                <td>
                    ${item.name}
                    <!-- Requisition Items -->
                    <input type="hidden" name="requisition_items[${index}][item_code]" value="${item.code}">
                    <input type="hidden" name="requisition_items[${index}][item_name]" value="${item.name}">
                    <input type="hidden" name="requisition_items[${index}][item_category]" value="${item.category}">
                    <input type="hidden" name="requisition_items[${index}][unit]" value="${item.unit}">
                    <input type="hidden" name="requisition_items[${index}][location_code]" value="${item.location_code}">
                    <input type="hidden" name="requisition_items[${index}][location_name]" value="${item.location_name}">
                    <input type="hidden" name="requisition_items[${index}][quantity]" value="${item.requisition_qty}">
                    ${item.needsPO ? `
                    <!-- Purchase Order Items -->
                    <input type="hidden" name="purchase_order_items[${index}][item_code]" value="${item.code}">
                    <input type="hidden" name="purchase_order_items[${index}][item_name]" value="${item.name}">
                    <input type="hidden" name="purchase_order_items[${index}][item_category]" value="${item.category}">
                    <input type="hidden" name="purchase_order_items[${index}][unit]" value="${item.unit}">
                    <input type="hidden" name="purchase_order_items[${index}][location_code]" value="${item.location_code}">
                    <input type="hidden" name="purchase_order_items[${index}][location_name]" value="${item.location_name}">
                    <input type="hidden" name="purchase_order_items[${index}][quantity]" value="${item.po_qty}">
                    ` : ''}
                </td>
                <td>${item.category}</td>
                <td>
                    <span class="badge badge-info">${item.location_code}</span>
                    <br><small>${item.location_name}</small>
                </td>
                <td><span class="badge badge-primary">${item.quantity}</span></td>
                <td><span class="badge badge-secondary">${item.unit}</span></td>
                <td>${availableDisplay}</td>
                <td>${statusBadge}</td>
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

    renderPOSummary();
}

function renderPOSummary() {
    const tbody = $('#poItemsTableBody');
    tbody.empty();

    const poItems = allRequestedItems.filter(item => item.needsPO);

    if (poItems.length === 0) {
        tbody.append('<tr id="poEmptyRow"><td colspan="4" class="text-center text-muted">No PO items</td></tr>');
        $('#poItemsBadge').text('0');
        return;
    }

    $('#poItemsBadge').text(poItems.length);

    poItems.forEach((item) => {
        const row = `
            <tr>
                <td><small>${item.code}</small></td>
                <td><small>${item.name}</small></td>
                <td><small>${item.location_code}</small></td>
                <td><span class="badge badge-warning">${item.po_qty}</span></td>
            </tr>
        `;
        tbody.append(row);
    });
}

function editItem(index) {
    const item = allRequestedItems[index];
    $('#editIndex').val(index);
    $('#editItemCode').val(item.code);
    $('#editItemName').val(item.name);
    $('#editLocation').val(`${item.location_code} - ${item.location_name}`);
    $('#editAvailableQty').val(`${item.available_qty}${item.pending_qty > 0 ? ` (${item.pending_qty} pending)` : ''}`);
    $('#editQuantity').val(item.quantity);
    $('#editModal').modal('show');
}

function saveEdit() {
    const index = parseInt($('#editIndex').val());
    const quantity = parseInt($('#editQuantity').val());

    if (quantity < 1) {
        alert('Quantity must be at least 1');
        return;
    }

    const item = allRequestedItems[index];
    
    // Recalculate PO needs with actual available (considering pending)
    const actualAvailable = item.available_qty;
    const needsPO = quantity > actualAvailable;
    const requisitionQty = Math.min(quantity, actualAvailable);
    const poQty = Math.max(0, quantity - actualAvailable);

    item.quantity = quantity;
    item.needsPO = needsPO;
    item.requisition_qty = requisitionQty;
    item.po_qty = poQty;

    $('#editModal').modal('hide');
    renderTable();
    updateSummary();
}

function deleteItem(index) {
    if (confirm('Are you sure you want to remove this item?')) {
        allRequestedItems.splice(index, 1);
        renderTable();
        updateSummary();
    }
}

function clearForm() {
    $('#itemSelect').val(null).trigger('change');
    $('#locationSelect').html('<option value="">Select item first</option>').prop('disabled', true);
    $('#itemQuantity').val(1).prop('disabled', true);
    $('#locationAvailableQty').text('-');
}

function updateSummary() {
    const totalItems = allRequestedItems.length;
    const totalQty = allRequestedItems.reduce((sum, item) => sum + item.quantity, 0);
    const availableItems = allRequestedItems.filter(item => !item.needsPO).length;
    const poItems = allRequestedItems.filter(item => item.needsPO).length;

    $('#totalItemsCount').text(totalItems);
    $('#totalQuantity').text(totalQty);
    $('#availableItemsCount').text(availableItems);
    $('#poItemsCount').text(poItems);
}
</script>
@endpush