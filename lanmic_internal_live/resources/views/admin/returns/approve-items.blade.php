@extends('layouts.admin')

@section('title', 'Process Return Items')
@section('page-title', 'Process Return Items')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.returns.index') }}">Returns</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.returns.show', $return->id) }}">#{{ $return->id }}</a></li>
    <li class="breadcrumb-item active">Process Items</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
<form action="{{ route('admin.returns.approve-items.store', $return->id) }}" method="POST" id="approveItemsForm">
    @csrf
    <div class="row">
        <div class="col-md-9">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Return Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Return ID:</th>
                                    <td>#{{ $return->id }}</td>
                                </tr>
                                <tr>
                                    <th>Requisition:</th>
                                    <td>
                                        <a href="{{ route('requisitions.show', $return->requisition->id) }}" target="_blank">
                                            {{ $return->requisition->requisition_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Returned By:</th>
                                    <td>{{ $return->returnedBy->name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Returned At:</th>
                                    <td>{{ $return->returned_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Total Items:</th>
                                    <td><span class="badge badge-info">{{ $return->items->count() }}</span></td>
                                </tr>
                                <tr>
                                    <th>Pending:</th>
                                    <td><span class="badge badge-warning">{{ $return->items->where('approve_status', 'pending')->count() }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($return->items->where('approve_status', 'pending') as $index => $item)
            <div class="card item-card" data-item-index="{{ $index }}">
                <div class="card-header bg-light item-header" style="cursor: pointer;" data-toggle="collapse" data-target="#item-{{ $index }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <i class="fas fa-chevron-down toggle-icon mr-2"></i>
                            <h5 class="d-inline mb-0">
                                {{ $item->item_name }}
                                <small class="text-muted">({{ $item->item_code }})</small>
                                <span class="badge badge-primary ml-2">{{ $item->quantity }} {{ $item->unit }}</span>
                                <span class="badge badge-{{ $item->type_badge }} ml-1">{{ $item->getTypeLabel() }}</span>
                            </h5>
                        </div>
                        <div class="custom-control custom-checkbox" onclick="event.stopPropagation();">
                            <input type="checkbox" class="custom-control-input item-checkbox" id="check-{{ $index }}">
                            <label class="custom-control-label" for="check-{{ $index }}">
                                <span class="badge badge-success check-badge" style="display: none;">✓ Checked</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="collapse show" id="item-{{ $index }}">
                    <div class="card-body">
                        <input type="hidden" name="items[{{ $index }}][return_item_id]" value="{{ $item->id }}">
                        
                        @if($item->notes)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>User Notes:</strong> {{ $item->notes }}
                        </div>
                        @endif

                        <!-- Original/Current Values Display -->
                        <div class="card bg-light mb-3">
                            <div class="card-body py-2">
                                <h6 class="mb-2"><i class="fas fa-user"></i> User Submitted Values:</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Type:</small><br>
                                        <strong>{{ $item->getTypeLabel() }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Location:</small><br>
                                        <strong>{{ $item->location_name }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Item:</small><br>
                                        <strong>{{ $item->item_code }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Quantity:</small><br>
                                        <strong>{{ $item->quantity }} {{ $item->unit }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3"><i class="fas fa-edit"></i> Admin Processing (You can modify):</h6>

                        <!-- Item Selection and Details -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Return Type <span class="text-danger">*</span></label>
                                    <select class="form-control return-type-select" name="items[{{ $index }}][return_type]" required>
                                        <option value="same" {{ $item->return_type === 'same' ? 'selected' : '' }}>Same Condition</option>
                                        <option value="used" {{ $item->return_type === 'used' ? 'selected' : '' }}>Used</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Item <span class="text-danger">*</span></label>
                                    <select class="form-control item-select select2" 
                                            name="items[{{ $index }}][item_code]" 
                                            data-index="{{ $index }}"
                                            data-original-code="{{ $item->item_code }}"
                                            required>
                                        <option value="">Loading items...</option>
                                    </select>
                                    <small class="text-muted">You can change the item if needed</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Location <span class="text-danger">*</span></label>
                                    <select class="form-control location-select select2" 
                                            name="items[{{ $index }}][location_code]" 
                                            data-index="{{ $index }}"
                                            data-original-location="{{ $item->location_code }}"
                                            required>
                                        <option value="">Loading locations...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Unit Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control unit-price-input" 
                                           name="items[{{ $index }}][unit_price]" 
                                           value="0" 
                                           min="0"
                                           readonly
                                           required>
                                    <small class="text-muted">Auto-calculated from item average cost</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-check-circle text-success"></i>
                                        GRN Quantity <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control grn-quantity" 
                                           name="items[{{ $index }}][grn_quantity]" 
                                           min="0" 
                                           max="{{ $item->quantity }}" 
                                           value="{{ $item->quantity }}"
                                           data-max="{{ $item->quantity }}"
                                           required>
                                    <small class="text-muted">Max: {{ $item->quantity }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-times-circle text-danger"></i>
                                        Scrap Quantity <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control scrap-quantity" 
                                           name="items[{{ $index }}][scrap_quantity]" 
                                           min="0" 
                                           max="{{ $item->quantity }}" 
                                           value="0"
                                           data-max="{{ $item->quantity }}"
                                           required>
                                    <small class="text-muted">Max: {{ $item->quantity }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-success quick-btn" data-action="all-grn">
                                        <i class="fas fa-check-double"></i> All to GRN
                                    </button>
                                    <button type="button" class="btn btn-outline-danger quick-btn" data-action="all-scrap">
                                        <i class="fas fa-trash-alt"></i> All to Scrap
                                    </button>
                                    <button type="button" class="btn btn-outline-warning quick-btn" data-action="split-half">
                                        <i class="fas fa-divide"></i> Split 50/50
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Warning -->
                        <div class="alert alert-warning quantity-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> Total must equal {{ $item->quantity }}. 
                            Current total: <span class="current-total">{{ $item->quantity }}</span>
                        </div>

                        <!-- Admin Note -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-comment-alt text-info"></i>
                                Admin Note <small class="text-muted">(Optional)</small>
                            </label>
                            <textarea class="form-control admin-note" 
                                      name="items[{{ $index }}][admin_note]" 
                                      rows="2" 
                                      placeholder="Add a note for this item"></textarea>
                            <div class="mt-2">
                                <small class="text-muted">Quick Notes:</small>
                                <div class="btn-group btn-group-sm flex-wrap" role="group">
                                    <button type="button" class="btn btn-outline-success quick-note-btn" 
                                            data-note="Item in good condition, approved for inventory.">
                                        <i class="fas fa-thumbs-up"></i> Good
                                    </button>
                                    <button type="button" class="btn btn-outline-warning quick-note-btn" 
                                            data-note="Item shows signs of wear, partial approval.">
                                        <i class="fas fa-exclamation"></i> Wear
                                    </button>
                                    <button type="button" class="btn btn-outline-danger quick-note-btn" 
                                            data-note="Item damaged beyond use, sent to scrap.">
                                        <i class="fas fa-times"></i> Damaged
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary quick-note-btn" data-note="">
                                        <i class="fas fa-eraser"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-info btn-sm mark-checked-btn">
                                <i class="fas fa-check"></i> Mark as Checked
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            @if($return->items->where('approve_status', 'pending')->count() === 0)
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                All items have been processed.
            </div>
            @endif

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-success" id="submitBtn" 
                            {{ $return->items->where('approve_status', 'pending')->count() === 0 ? 'disabled' : '' }}>
                        <i class="fas fa-check"></i> Process Items
                    </button>
                    <a href="{{ route('admin.returns.show', $return->id) }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-primary sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Items</span>
                            <span class="info-box-number">{{ $return->items->count() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-warning">
                        <div class="info-box-content">
                            <span class="info-box-text">Pending</span>
                            <span class="info-box-number">{{ $return->items->where('approve_status', 'pending')->count() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-success">
                        <div class="info-box-content">
                            <span class="info-box-text">Checked</span>
                            <span class="info-box-number" id="checkedCount">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Instructions</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3 small">
                        <li>Review user submitted values (gray box)</li>
                        <li>Modify return type, item, location, or price if needed</li>
                        <li>Split quantity between GRN (approved) and Scrap (rejected)</li>
                        <li>GRN + Scrap must equal total quantity</li>
                        <li>Add admin note (optional)</li>
                        <li>Check the checkbox when done</li>
                        <li>Submit to process all</li>
                    </ol>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Legend</h3>
                </div>
                <div class="card-body small">
                    <p><strong><i class="fas fa-check-circle text-success"></i> GRN:</strong> Posts to SAGE (BothIncrease)</p>
                    <p><strong><i class="fas fa-times-circle text-danger"></i> Scrap:</strong> DB only, no SAGE posting</p>
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
let allItems = [];
let allLocations = [];

$(document).ready(function() {
    // Load items and locations from SAGE
    loadItems();
    loadLocations();

    // Initialize Select2 after data loads
    setTimeout(function() {
        $('.select2').select2({
            theme: 'bootstrap',
            width: '100%'
        });
    }, 1000);

    // Toggle collapse
    $('.item-header').on('click', function(e) {
        if (!$(e.target).is('input, label')) {
            const target = $(this).data('target');
            $(target).collapse('toggle');
        }
    });

    $('.collapse').on('shown.bs.collapse', function() {
        $(this).prev().find('.toggle-icon').removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }).on('hidden.bs.collapse', function() {
        $(this).prev().find('.toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-right');
    });

    // Item selection change
    $(document).on('change', '.item-select', function() {
        const itemCode = $(this).val();
        const card = $(this).closest('.item-card');
        const locationCode = card.find('.location-select').val();
        
        if (itemCode) {
            // Get item details for fallback
            const item = allItems.find(i => i.code === itemCode);
            if (item) {
                // Set default price from item average cost
                const avgCost = item.average_cost || 0;
                card.find('.unit-price-input').val(avgCost.toFixed(2));
            }
            
            // If location is also selected, get location-specific price
            if (locationCode) {
                loadItemLocationPrice(itemCode, locationCode, card);
            }
        }
    });

    // Location selection change
    $(document).on('change', '.location-select', function() {
        const locationCode = $(this).val();
        const card = $(this).closest('.item-card');
        const itemCode = card.find('.item-select').val();
        
        // If both item and location are selected, get location-specific price
        if (itemCode && locationCode) {
            loadItemLocationPrice(itemCode, locationCode, card);
        }
    });

    // Checkbox change
    $('.item-checkbox').on('change', function() {
        const card = $(this).closest('.item-card');
        const index = card.data('item-index');
        const collapse = $('#item-' + index);
        const badge = $(this).siblings('label').find('.check-badge');

        if ($(this).is(':checked')) {
            card.addClass('border-success');
            badge.show();
            collapse.collapse('hide');
        } else {
            card.removeClass('border-success');
            badge.hide();
            collapse.collapse('show');
        }
        
        updateCheckedCount();
    });

    // Mark as checked button
    $('.mark-checked-btn').on('click', function() {
        const card = $(this).closest('.item-card');
        const index = card.data('item-index');
        const checkbox = $('#check-' + index);
        
        checkbox.prop('checked', true).trigger('change');
    });

    // Quantity validation
    $('.grn-quantity, .scrap-quantity').on('input', function() {
        const card = $(this).closest('.item-card');
        validateQuantities(card);
    });

    // Quick action buttons
    $(document).on('click', '.quick-btn', function() {
        const action = $(this).data('action');
        const card = $(this).closest('.item-card');
        const maxQty = parseInt(card.find('.grn-quantity').data('max'));
        const grnInput = card.find('.grn-quantity');
        const scrapInput = card.find('.scrap-quantity');
        
        if (action === 'all-grn') {
            grnInput.val(maxQty);
            scrapInput.val(0);
        } else if (action === 'all-scrap') {
            grnInput.val(0);
            scrapInput.val(maxQty);
        } else if (action === 'split-half') {
            const half = Math.floor(maxQty / 2);
            const remainder = maxQty - half;
            grnInput.val(half);
            scrapInput.val(remainder);
        }
        
        validateQuantities(card);
    });

    // Quick note buttons
    $(document).on('click', '.quick-note-btn', function() {
        const note = $(this).data('note');
        const card = $(this).closest('.item-card');
        const noteInput = card.find('.admin-note');
        
        noteInput.val(note);
    });

    // Form validation
    $('#approveItemsForm').submit(function(e) {
        let valid = true;
        let errorMessage = '';
        
        $('.item-card').each(function() {
            const card = $(this);
            const grnQty = parseInt(card.find('.grn-quantity').val()) || 0;
            const scrapQty = parseInt(card.find('.scrap-quantity').val()) || 0;
            const maxQty = parseInt(card.find('.grn-quantity').data('max'));
            const itemName = card.find('h5').text().trim().split('(')[0].trim();
            
            if (grnQty + scrapQty !== maxQty) {
                valid = false;
                errorMessage += `\n- ${itemName}: Total must equal ${maxQty}`;
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Please fix the following errors:' + errorMessage);
        } else {
            // Show loading
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        }
    });

    // Initialize validation
    $('.item-card').each(function() {
        validateQuantities($(this));
    });
    
    // Load initial prices for items that have both item and location selected
    setTimeout(function() {
        $('.item-card').each(function() {
            const card = $(this);
            const itemCode = card.find('.item-select').val();
            const locationCode = card.find('.location-select').val();
            
            if (itemCode && locationCode) {
                loadItemLocationPrice(itemCode, locationCode, card);
            }
        });
    }, 1500);
});

function loadItems() {
    Sage300.getItems()
        .done(function(response) {
            if (response.success && response.data) {
                allItems = response.data.map(item => ({
                    code: item.UnformattedItemNumber,
                    name: item.Description,
                    category: item.Category || 'N/A',
                    unit: item.StockingUnitOfMeasure,
                    average_cost: parseFloat(item.AverageCost || 0)
                }));
                
                // Populate all item selects
                $('.item-select').each(function() {
                    const select = $(this);
                    const originalCode = select.data('original-code');
                    
                    select.html('<option value="">Select an item</option>');
                    
                    allItems.forEach(function(item) {
                        const selected = item.code === originalCode ? 'selected' : '';
                        select.append(
                            `<option value="${item.code}" ${selected}>
                                ${item.code} - ${item.name} (${item.category})
                            </option>`
                        );
                    });
                    
                    // Set initial price for original item
                    if (originalCode) {
                        const item = allItems.find(i => i.code === originalCode);
                        if (item) {
                            const card = select.closest('.item-card');
                            card.find('.unit-price-input').val(item.average_cost.toFixed(2));
                        }
                    }
                });
            }
        })
        .fail(function() {
            console.error('Failed to load items');
            alert('Failed to load items from SAGE300. Please refresh the page.');
        });
}

function loadLocations() {
    Sage300.getLocations()
        .done(function(response) {
            if (response.success && response.data) {
                allLocations = response.data.map(loc => ({
                    code: loc.LocationKey,
                    name: loc.Name,
                    description: loc.Description || ''
                }));
                
                // Loop through each select element
                $('.location-select').each(function() {
                    const select = $(this);
                    const originalLocation = select.data('original-location');
                    
                    select.html('<option value="">Select a location</option>');
                    
                    allLocations.forEach(function(loc) {
                        const selected = loc.code === originalLocation ? 'selected' : '';
                        select.append(
                            `<option value="${loc.code}" ${selected}>
                                ${loc.name} (${loc.code})
                            </option>`
                        );
                    });
                });
            }
        })
        .fail(function() {
            console.error('Failed to load locations');
            alert('Failed to load locations from SAGE300. Please refresh the page.');
        });
}

function loadItemLocationPrice(itemCode, locationCode, card) {
    const priceInput = card.find('.unit-price-input');
    
    // Show loading state
    priceInput.prop('readonly', true).val('Loading...');
    
    Sage300.getItemLocations(itemCode)
        .done(function(response) {
            if (response.success && response.data && response.data.length > 0) {
                // Find the specific location
                const locationData = response.data.find(loc => loc.location_code === locationCode);
                
                if (locationData && locationData.average_cost) {
                    // Use location-specific unit cost
                    priceInput.val(parseFloat(locationData.average_cost ?? 0).toFixed(2));
                } else {
                    // Fallback to item average cost
                    const item = allItems.find(i => i.code === itemCode);
                    if (item) {
                        priceInput.val(item.average_cost.toFixed(2));
                    }
                }
            } else {
                // Fallback to item average cost
                const item = allItems.find(i => i.code === itemCode);
                if (item) {
                    priceInput.val(item.average_cost.toFixed(2));
                }
            }
            
            // Allow admin to edit
            priceInput.prop('readonly', false);
        })
        .fail(function() {
            console.error('Failed to load item location price');
            
            // Fallback to item average cost
            const item = allItems.find(i => i.code === itemCode);
            if (item) {
                priceInput.val(item.average_cost.toFixed(2));
            } else {
                priceInput.val('0.00');
            }
            
            // Allow admin to edit
            priceInput.prop('readonly', false);
        });
}

function validateQuantities(card) {
    const grnQty = parseInt(card.find('.grn-quantity').val()) || 0;
    const scrapQty = parseInt(card.find('.scrap-quantity').val()) || 0;
    const maxQty = parseInt(card.find('.grn-quantity').data('max'));
    const totalQty = grnQty + scrapQty;
    
    const warning = card.find('.quantity-warning');
    const currentTotal = warning.find('.current-total');
    
    currentTotal.text(totalQty);
    
    if (totalQty !== maxQty) {
        warning.show();
        card.find('.grn-quantity, .scrap-quantity').addClass('is-invalid');
    } else {
        warning.hide();
        card.find('.grn-quantity, .scrap-quantity').removeClass('is-invalid');
    }
}

function updateCheckedCount() {
    const count = $('.item-checkbox:checked').length;
    $('#checkedCount').text(count);
}
</script>

<style>
.item-header {
    transition: background-color 0.2s;
}
.item-header:hover {
    background-color: #e9ecef !important;
}
.toggle-icon {
    transition: transform 0.3s;
}
.item-card .border-success {
    border: 2px solid #28a745 !important;
}
.sticky-top {
    position: sticky;
}
</style>
@endpush