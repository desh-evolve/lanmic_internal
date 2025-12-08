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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="itemQuantity" min="1" value="1">
                                <small class="text-muted">
                                    Available: <span id="availableQty" class="font-weight-bold">-</span>
                                    <br>
                                    Pending Approval: <span id="pendingQty" class="font-weight-bold">-</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Specifications</label>
                                <input type="text" class="form-control" id="itemSpecifications" placeholder="Optional">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center pb-2">
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
                                        <th width="8%">Qty</th>
                                        <th width="8%">Unit</th>
                                        <th width="10%">Price</th>
                                        <th width="10%">Total</th>
                                        <th width="12%">Availability</th>
                                        <th width="12%">Actions</th>
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
                <div class="card-body row">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <div class="info-box-content">
                                <span class="info-box-text">Available Items</span>
                                <span class="info-box-number" id="availableItemsCount">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-warning">
                            <div class="info-box-content">
                                <span class="info-box-text">Purchase Order Items</span>
                                <span class="info-box-number" id="poItemsCount">0</span>
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
                                <span class="info-box-text">Total Amount</span>
                                <span class="info-box-number" id="totalAmount">Rs. 0.00</span>
                            </div>
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
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="poItemsTableBody">
                                <tr id="poEmptyRow">
                                    <td colspan="3" class="text-center text-muted">No PO items</td>
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Stock Available</label>
                            <input type="text" class="form-control" id="editStockAvailable" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pending Approval</label>
                            <input type="text" class="form-control" id="editPendingQty" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="editQuantity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Specifications</label>
                    <textarea class="form-control" id="editSpecifications" rows="3"></textarea>
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
let allRequestedItems = []; // All items (both available and PO)
let allItems = [];
let pendingApprovals = {}; // Track pending quantities per item code

$(document).ready(function() {
    // Load items and pending approvals
    loadItems();
    loadPendingApprovals();

    // Department change event
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

    // Sub-department change event
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

    // Item select change
    $('#itemSelect').on('select2:select', function(e) {
        const data = e.params.data;
        if (data && data.item) {
            updateAvailabilityDisplay(data.item);
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

    // Form submission
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
        $('#availableQty').text('0');
        $('#pendingQty').text('0');
        
        loadItems();
        loadPendingApprovals();
        
        setTimeout(function() {
            $btn.prop('disabled', false).find('i').removeClass('fa-spin');
        }, 1000);
    });
});

/*
function loadItems() {
    $('#itemSelect').html('<option value="">Loading items...</option>');

    $.get('/api/items', function(data) {
        allItems = data;
        initializeSelect2();
    }).fail(function() {
        allItems = @json($items ?? []);
        initializeSelect2();
    });
}
*/

function loadItems() {
    $('#itemSelect').html('<option value="">Loading items from Sage300...</option>');

    // Load items from Sage300 API
    Sage300.getItems()
        .done(function(response) {
            if (response.success && response.data) {
                // Transform Sage300 data to your format
                allItems = response.data.map(item => ({
                    code: item.ItemNumber || item.UnformattedItemNumber,
                    name: item.Description,
                    category: item.Category || 'N/A',
                    unit: item.StockingUnitOfMeasure,
                    unit_price: 0, // Will be fetched separately
                    available_qty: item.QuantityAvailable || 0
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
            const code = item.item_code;
            if (!pendingApprovals[code]) {
                pendingApprovals[code] = 0;
            }
            pendingApprovals[code] += parseInt(item.quantity) || 0;
        });
    }).fail(function() {
        console.log('Could not load pending approvals');
        pendingApprovals = {};
    });
}

function initializeSelect2() {
    // Normalize items data structure
    const normalizedItems = allItems.map(item => {
        const normalizedItem = {
            code: item.code || item['code'] || item.id || item['id'] || '',
            name: item.name || item['name'] || '',
            category: item.category || item['category'] || 'N/A',
            unit: item.unit || item['unit'] || 'pcs',
            unit_price: parseFloat(item.unit_price || item['unit_price'] || 0),
            available_qty: parseInt(item.available_qty || item['available_qty'] || 0)
        };
        return normalizedItem;
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

/*
function updateAvailabilityDisplay(item) {
    const stockQty = item.available_qty || 0;
    const pendingQty = pendingApprovals[item.code] || 0;
    const actualAvailable = Math.max(0, stockQty - pendingQty);

    $('#availableQty').text(actualAvailable);
    if (pendingQty > 0) {
        $('#pendingQty').text(`${pendingQty}`);
    } else {
        $('#pendingQty').text('');
    }
}
*/

function updateAvailabilityDisplay(item) {
    // Show loading state
    $('#availableQty').html('<i class="fas fa-spinner fa-spin"></i>');
    $('#pendingQty').text(pendingApprovals[item.code] || 0);

    // Fetch fresh quantity and price from Sage300
    Sage300.getItemDetails(item.code)
        .done(function(response) {
            if (response.success && response.data) {
                const data = response.data;
                const stockQty = data.available_qty || 0;
                const pendingQty = pendingApprovals[item.code] || 0;
                const actualAvailable = Math.max(0, stockQty - pendingQty);

                // Update display
                $('#availableQty').text(actualAvailable);
                
                // Update item data with fresh info
                item.available_qty = stockQty;
                item.unit_price = data.unit_price || 0;
            }
        })
        .fail(function() {
            $('#availableQty').text(item.available_qty || 0);
            console.error('Failed to fetch item details');
        });
}

/*
function addItemToTable() {
    const selectedOption = $('#itemSelect').select2('data')[0];
    if (!selectedOption || !selectedOption.item) {
        alert('Please select an item');
        return;
    }

    const item = selectedOption.item;
    const requestedQty = parseInt($('#itemQuantity').val()) || 1;
    const specifications = $('#itemSpecifications').val();
    
    // Calculate availability considering pending approvals
    const stockQty = item.available_qty || 0;
    const pendingQty = pendingApprovals[item.code] || 0;
    const actualAvailable = Math.max(0, stockQty - pendingQty);

    // Check if item already exists
    const existsIndex = allRequestedItems.findIndex(i => i.code === item.code);
    if (existsIndex !== -1) {
        alert('This item is already added. You can edit it from the table.');
        return;
    }

    const unitPrice = item.unit_price || 0;
    const total = requestedQty * unitPrice;

    // Determine if item needs PO
    const needsPO = requestedQty > actualAvailable;
    const availableForRequisition = Math.min(requestedQty, actualAvailable);
    const needsForPO = Math.max(0, requestedQty - actualAvailable);

    const itemData = {
        code: item.code,
        name: item.name,
        category: item.category || 'N/A',
        unit: item.unit || 'pcs',
        quantity: requestedQty,
        unitPrice: unitPrice,
        total: total,
        specifications: specifications,
        stockAvailable: actualAvailable,
        pendingQty: pendingQty,
        needsPO: needsPO,
        availableQty: availableForRequisition,
        poQty: needsForPO
    };

    allRequestedItems.push(itemData);
    renderTables();
    clearForm();
    updateSummary();
}
*/

function addItemToTable() {
    const selectedOption = $('#itemSelect').select2('data')[0];
    if (!selectedOption || !selectedOption.item) {
        alert('Please select an item');
        return;
    }

    const item = selectedOption.item;
    const requestedQty = parseInt($('#itemQuantity').val()) || 1;
    const specifications = $('#itemSpecifications').val();

    // Show loading state
    const $addBtn = $('#addItemBtn');
    $addBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

    // Fetch latest price and quantity from Sage300
    Sage300.getItemDetails(item.code)
        .done(function(response) {
            if (response.success && response.data) {
                const freshData = response.data;
                
                // Calculate availability
                const stockQty = freshData.available_qty || 0;
                const pendingQty = pendingApprovals[item.code] || 0;
                const actualAvailable = Math.max(0, stockQty - pendingQty);

                // Check if already exists
                const existsIndex = allRequestedItems.findIndex(i => i.code === item.code);
                if (existsIndex !== -1) {
                    alert('This item is already added. You can edit it from the table.');
                    $addBtn.prop('disabled', false).html('<i class="fas fa-plus"></i> Add Item');
                    return;
                }

                const unitPrice = freshData.unit_price || 0;
                const total = requestedQty * unitPrice;

                // Determine PO needs
                const needsPO = requestedQty > actualAvailable;
                const availableForRequisition = Math.min(requestedQty, actualAvailable);
                const needsForPO = Math.max(0, requestedQty - actualAvailable);

                const itemData = {
                    code: item.code,
                    name: freshData.name || item.name,
                    category: freshData.category || item.category || 'N/A',
                    unit: freshData.unit || item.unit || 'pcs',
                    quantity: requestedQty,
                    unitPrice: unitPrice,
                    total: total,
                    specifications: specifications,
                    stockAvailable: actualAvailable,
                    pendingQty: pendingQty,
                    needsPO: needsPO,
                    availableQty: availableForRequisition,
                    poQty: needsForPO
                };

                allRequestedItems.push(itemData);
                renderTables();
                clearForm();
                updateSummary();
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to fetch item details:', error);
            alert('Failed to fetch item details. Please try again.');
        })
        .always(function() {
            $addBtn.prop('disabled', false).html('<i class="fas fa-plus"></i> Add Item');
        });
}

function renderTables() {
    const tbody = $('#itemsTableBody');
    tbody.empty();

    if (allRequestedItems.length === 0) {
        tbody.append('<tr id="emptyRow"><td colspan="9" class="text-center text-muted">No items added yet</td></tr>');
        renderPOSummary();
        return;
    }

    allRequestedItems.forEach((item, index) => {
        let availabilityBadge = '';
        if (item.needsPO) {
            availabilityBadge = `
                <span class="badge badge-success">${item.availableQty}</span>
                <span class="badge badge-warning">${item.poQty} PO</span>
            `;
        } else {
            availabilityBadge = `<span class="badge badge-success">Available</span>`;
        }

        const row = `
            <tr>
                <td>${item.code}</td>
                <td>
                    ${item.name}
                    ${item.specifications ? `<br><small class="text-muted">${item.specifications}</small>` : ''}
                    <!-- Requisition Items Hidden Inputs -->
                    <input type="hidden" name="requisition_items[${index}][item_code]" value="${item.code}">
                    <input type="hidden" name="requisition_items[${index}][item_name]" value="${item.name}">
                    <input type="hidden" name="requisition_items[${index}][item_category]" value="${item.category}">
                    <input type="hidden" name="requisition_items[${index}][unit]" value="${item.unit}">
                    <input type="hidden" name="requisition_items[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="requisition_items[${index}][unit_price]" value="${item.unitPrice}">
                    <input type="hidden" name="requisition_items[${index}][total_price]" value="${item.total}">
                    <input type="hidden" name="requisition_items[${index}][specifications]" value="${item.specifications}">
                    ${item.needsPO ? `
                    <!-- Purchase Order Items Hidden Inputs -->
                    <input type="hidden" name="purchase_order_items[${index}][item_code]" value="${item.code}">
                    <input type="hidden" name="purchase_order_items[${index}][item_name]" value="${item.name}">
                    <input type="hidden" name="purchase_order_items[${index}][item_category]" value="${item.category}">
                    <input type="hidden" name="purchase_order_items[${index}][unit]" value="${item.unit}">
                    <input type="hidden" name="purchase_order_items[${index}][quantity]" value="${item.poQty}">
                    <input type="hidden" name="purchase_order_items[${index}][unit_price]" value="${item.unitPrice}">
                    <input type="hidden" name="purchase_order_items[${index}][total_price]" value="${item.poQty * item.unitPrice}">
                    ` : ''}
                </td>
                <td>${item.category}</td>
                <td><span class="badge badge-primary">${item.quantity}</span></td>
                <td><span class="badge badge-info">${item.unit}</span></td>
                <td>Rs. ${item.unitPrice.toFixed(2)}</td>
                <td><strong>Rs. ${item.total.toFixed(2)}</strong></td>
                <td>${availabilityBadge}</td>
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
        tbody.append('<tr id="poEmptyRow"><td colspan="3" class="text-center text-muted">No PO items</td></tr>');
        $('#poItemsBadge').text('0');
        return;
    }

    $('#poItemsBadge').text(poItems.length);

    poItems.forEach((item) => {
        const row = `
            <tr>
                <td><small>${item.code}</small></td>
                <td><small>${item.name}</small></td>
                <td><span class="badge badge-warning">${item.poQty}</span></td>
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
    $('#editStockAvailable').val(item.stockAvailable);
    $('#editPendingQty').val(item.pendingQty);
    $('#editQuantity').val(item.quantity);
    $('#editSpecifications').val(item.specifications);
    $('#editModal').modal('show');
}

function saveEdit() {
    const index = parseInt($('#editIndex').val());
    const quantity = parseInt($('#editQuantity').val());
    const specifications = $('#editSpecifications').val();

    if (quantity < 1) {
        alert('Quantity must be at least 1');
        return;
    }

    const item = allRequestedItems[index];
    item.quantity = quantity;
    item.specifications = specifications;
    item.total = quantity * item.unitPrice;

    // Recalculate PO needs
    const actualAvailable = item.stockAvailable;
    item.needsPO = quantity > actualAvailable;
    item.availableQty = Math.min(quantity, actualAvailable);
    item.poQty = Math.max(0, quantity - actualAvailable);

    $('#editModal').modal('hide');
    renderTables();
    updateSummary();
}

function deleteItem(index) {
    if (confirm('Are you sure you want to remove this item?')) {
        allRequestedItems.splice(index, 1);
        renderTables();
        updateSummary();
    }
}

function clearForm() {
    $('#itemSelect').val(null).trigger('change');
    $('#itemQuantity').val(1);
    $('#itemSpecifications').val('');
    $('#availableQty').text('-');
    $('#pendingQty').text('');
}

function updateSummary() {
    const totalItems = allRequestedItems.length;
    const availableItems = allRequestedItems.filter(item => !item.needsPO).length;
    const poItems = allRequestedItems.filter(item => item.needsPO).length;
    
    let totalAmount = 0;
    allRequestedItems.forEach(item => {
        totalAmount += item.total;
    });

    $('#totalItemsCount').text(totalItems);
    $('#availableItemsCount').text(availableItems);
    $('#poItemsCount').text(poItems);
    $('#totalAmount').text('Rs. ' + totalAmount.toFixed(2));
}
</script>
@endpush