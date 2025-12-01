@extends('layouts.admin')

@section('title', 'Process Return Items')
@section('page-title', 'Process Return Items')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.returns.index') }}">Returns</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.returns.show', $return->id) }}">#{{ $return->id }}</a></li>
    <li class="breadcrumb-item active">Process Items</li>
@endsection

@section('content')
<form action="{{ route('admin.returns.approve-items.store', $return->id) }}" method="POST" id="approveItemsForm">
    @csrf
    <div class="row">
        <div class="col-md-8">
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
                    <h3 class="card-title">Return Information</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Return ID:</strong></div>
                        <div class="col-md-8">#{{ $return->id }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Returned By:</strong></div>
                        <div class="col-md-8">{{ $return->returnedBy->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Returned At:</strong></div>
                        <div class="col-md-8">{{ $return->returned_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Process Return Items</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="icon fas fa-info"></i>
                        <strong>Instructions:</strong><br>
                        - Review each item and its quantity<br>
                        - Split quantity between GRN (approved) and Scrap (rejected)<br>
                        - GRN Qty + Scrap Qty must equal Total Return Qty<br>
                        - You can send all to GRN, all to Scrap, or split between both<br>
                        - You can change the return type and location before processing
                    </div>

                    @foreach($return->items->where('approve_status', 'pending') as $index => $item)
                    @php
                        $location = \App\Models\Location::find($item->return_location_id);
                    @endphp
                    <div class="card mb-3 item-card" data-item-index="{{ $index }}">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                {{ $item->item_name }} 
                                <small class="text-muted">({{ $item->item_code }})</small>
                                <span class="badge badge-primary float-right">Total: {{ $item->return_quantity }} {{ $item->unit }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="items[{{ $index }}][return_item_id]" value="{{ $item->id }}">
                            
                            @if($item->notes)
                                <div class="alert alert-secondary">
                                    <i class="fas fa-info-circle"></i> <strong>Notes:</strong> {{ $item->notes }}
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Current Type</label>
                                        <input type="text" class="form-control" value="{{ ucfirst($item->return_type) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Current Location</label>
                                        <input type="text" class="form-control" value="{{ $location ? $location['name'] . ' (' . $location['code'] . ')' : '-' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Return Type <span class="text-danger">*</span></label>
                                        <select class="form-control return-type-select" name="items[{{ $index }}][return_type]" required>
                                            <option value="used" {{ $item->return_type === 'used' ? 'selected' : '' }}>Used</option>
                                            <option value="same" {{ $item->return_type === 'same' ? 'selected' : '' }}>Same</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Location <span class="text-danger">*</span></label>
                                        <select class="form-control location-select" name="items[{{ $index }}][return_location_id]" data-current-type="{{ $item->return_type }}" data-current-location="{{ $item->return_location_id }}" required>
                                            <option value="">Select Location</option>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc['id'] }}" data-type="{{ $loc['type'] }}" {{ $item->return_location_id == $loc['id'] ? 'selected' : '' }}>
                                                    {{ $loc['name'] }} ({{ $loc['code'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-check-circle text-success"></i> 
                                            GRN Quantity (Approved) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control grn-quantity" 
                                               name="items[{{ $index }}][grn_quantity]" 
                                               min="0" 
                                               max="{{ $item->return_quantity }}" 
                                               value="{{ $item->return_quantity }}"
                                               data-max="{{ $item->return_quantity }}"
                                               required>
                                        <small class="form-text text-muted">Max: {{ $item->return_quantity }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-times-circle text-danger"></i> 
                                            Scrap Quantity (Rejected) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control scrap-quantity" 
                                               name="items[{{ $index }}][scrap_quantity]" 
                                               min="0" 
                                               max="{{ $item->return_quantity }}" 
                                               value="0"
                                               data-max="{{ $item->return_quantity }}"
                                               required>
                                        <small class="form-text text-muted">Max: {{ $item->return_quantity }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-warning quantity-warning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>Warning:</strong> Total must equal {{ $item->return_quantity }}. 
                                        Current total: <span class="current-total">{{ $item->return_quantity }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-sm btn-success quick-btn" data-action="all-grn">
                                            <i class="fas fa-check-double"></i> All to GRN
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger quick-btn" data-action="all-scrap">
                                            <i class="fas fa-trash-alt"></i> All to Scrap
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning quick-btn" data-action="split-half">
                                            <i class="fas fa-divide"></i> Split 50/50
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @if($return->items->where('approve_status', 'pending')->count() === 0)
                        <div class="alert alert-success">
                            <i class="icon fas fa-check-circle"></i>
                            All items have been processed.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-success" id="submitBtn" {{ $return->items->where('approve_status', 'pending')->count() === 0 ? 'disabled' : '' }}>
                        <i class="fas fa-check"></i> Process Items
                    </button>
                    <a href="{{ route('admin.returns.show', $return->id) }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Processing Summary</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Items</span>
                            <span class="info-box-number">{{ $return->items->count() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Pending</span>
                            <span class="info-box-number text-warning">
                                {{ $return->items->where('approve_status', 'pending')->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Already Processed</span>
                            <span class="info-box-number text-success">
                                {{ $return->items->where('approve_status', '!=', 'pending')->count() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Per Item:</strong></p>
                    <ul class="pl-3">
                        <li><strong>All to GRN:</strong> Send entire quantity to approved</li>
                        <li><strong>All to Scrap:</strong> Send entire quantity to rejected</li>
                        <li><strong>Split 50/50:</strong> Divide equally between GRN and Scrap</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Legend</h3>
                </div>
                <div class="card-body">
                    <p><strong><i class="fas fa-check-circle text-success"></i> GRN (Goods Received Note):</strong> Approved items added to inventory</p>
                    <hr>
                    <p><strong><i class="fas fa-times-circle text-danger"></i> Scrap:</strong> Rejected items sent to scrap</p>
                    <hr>
                    <p><strong>Return Types:</strong></p>
                    <ul>
                        <li><strong>Used:</strong> Item has been used</li>
                        <li><strong>Same:</strong> Item is in same/new condition</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Filter locations based on return type
    $('.return-type-select').change(function() {
        const card = $(this).closest('.item-card');
        const returnType = $(this).val();
        const locationSelect = card.find('.location-select');
        
        filterLocations(locationSelect, returnType);
    });

    // Initialize location filters on page load
    $('.location-select').each(function() {
        const card = $(this).closest('.item-card');
        const returnType = card.find('.return-type-select').val();
        filterLocations($(this), returnType);
    });

    function filterLocations(locationSelect, returnType) {
        const currentLocation = locationSelect.data('current-location');
        
        locationSelect.find('option').each(function() {
            if ($(this).val() === '') return; // Skip the empty option
            
            const locationType = $(this).data('type');
            
            if (returnType === 'used') {
                // For 'used' type, only show used locations
                if (locationType === 'used') {
                    $(this).show();
                } else {
                    $(this).hide();
                    if ($(this).is(':selected')) {
                        locationSelect.val('');
                    }
                }
            } else {
                // For 'same' type, show all locations
                $(this).show();
            }
        });
    }

    // Quantity validation
    $('.grn-quantity, .scrap-quantity').on('input', function() {
        const card = $(this).closest('.item-card');
        validateQuantities(card);
    });

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

    // Quick action buttons
    $('.quick-btn').click(function() {
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

    // Form validation
    $('#approveItemsForm').submit(function(e) {
        let valid = true;
        let errorMessage = '';
        
        $('.item-card').each(function() {
            const card = $(this);
            const grnQty = parseInt(card.find('.grn-quantity').val()) || 0;
            const scrapQty = parseInt(card.find('.scrap-quantity').val()) || 0;
            const maxQty = parseInt(card.find('.grn-quantity').data('max'));
            const itemName = card.find('h5').text().trim();
            
            if (grnQty + scrapQty !== maxQty) {
                valid = false;
                errorMessage += `\n- ${itemName}: Total must equal ${maxQty}`;
            }
            
            if (grnQty < 0 || scrapQty < 0) {
                valid = false;
                errorMessage += `\n- ${itemName}: Quantities cannot be negative`;
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Please fix the following errors:' + errorMessage);
        }
    });

    // Initialize validation on page load
    $('.item-card').each(function() {
        validateQuantities($(this));
    });
});
</script>
@endpush