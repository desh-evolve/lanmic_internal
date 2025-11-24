@extends('layouts.admin')

@section('title', 'Edit Requisition')
@section('page-title', 'Edit Requisition')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisitions</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <form action="{{ route('requisitions.update', $requisition->id) }}" method="POST" id="requisitionForm">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Requisition - {{ $requisition->requisition_number }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id">Department <span class="text-danger">*</span></label>
                                    <select class="form-control @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id" required>
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ old('department_id', $requisition->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                                @if ($department->short_code)
                                                    ({{ $department->short_code }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sub_department_id">Sub-Department</label>
                                    <select class="form-control @error('sub_department_id') is-invalid @enderror"
                                        id="sub_department_id" name="sub_department_id">
                                        <option value="">Select Sub-Department</option>
                                        @foreach ($subDepartments as $subDepartment)
                                            <option value="{{ $subDepartment->id }}"
                                                {{ old('sub_department_id', $requisition->sub_department_id) == $subDepartment->id ? 'selected' : '' }}>
                                                {{ $subDepartment->name }}
                                                @if ($subDepartment->short_code)
                                                    ({{ $subDepartment->short_code }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sub_department_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="division_id">Division</label>
                            <select class="form-control @error('division_id') is-invalid @enderror" id="division_id"
                                name="division_id">
                                <option value="">Select Division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ old('division_id', $requisition->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                        @if ($division->short_code)
                                            ({{ $division->short_code }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="purpose">Purpose <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('purpose') is-invalid @enderror" id="purpose" name="purpose" rows="3"
                                required>{{ old('purpose', $requisition->purpose) }}</textarea>
                            @error('purpose')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $requisition->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Items</h3>
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
                            <i class="fas fa-save"></i> Update Requisition
                        </button>
                        <a href="{{ route('requisitions.show', $requisition->id) }}" class="btn btn-default">
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
                                <span class="info-box-text">Total Amount</span>
                                <span class="info-box-number" id="totalAmount">$0.00</span>
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
        const availableItems = @json($items);
        const existingItems = @json($requisition->items);

        $(document).ready(function() {
            // Department change event
            $('#department_id').change(function() {
                const departmentId = $(this).val();
                $('#sub_department_id').html('<option value="">Select Sub-Department</option>').prop(
                    'disabled', true);
                $('#division_id').html('<option value="">Select Division</option>').prop('disabled', true);

                if (departmentId) {
                    $.get(`/api/departments/${departmentId}/sub-departments`, function(data) {
                        if (data.length > 0) {
                            $('#sub_department_id').prop('disabled', false);
                            data.forEach(function(subDept) {
                                const text = subDept.short_code ?
                                    `${subDept.name} (${subDept.short_code})` :
                                    subDept.name;
                                const selected = subDept.id ==
                                    {{ old('sub_department_id', $requisition->sub_department_id ?? 'null') }} ?
                                    'selected' : '';
                                $('#sub_department_id').append(
                                    `<option value="${subDept.id}" ${selected}>${text}</option>`
                                    );
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
                                const selected = division.id ==
                                    {{ old('division_id', $requisition->division_id ?? 'null') }} ?
                                    'selected' : '';
                                $('#division_id').append(
                                    `<option value="${division.id}" ${selected}>${text}</option>`
                                    );
                            });
                        }
                    });
                }
            });

            // Add item button
            $('#addItemBtn').click(function() {
                addItemRow();
            });

            // Load existing items
            existingItems.forEach(function(item) {
                addItemRow(item);
            });

            // Trigger department change to load sub-departments if department is selected
            if ($('#department_id').val()) {
                $('#department_id').trigger('change');
            }
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
                            <label>Select Item <span class="text-danger">*</span></label>
                            <select class="form-control item-select" name="items[${itemIndex}][item_code]" required>
                                <option value="">Select an item</option>
                                ${availableItems.map(item => `
                                        <option value="${item.code}" 
                                                data-name="${item.name}"
                                                data-category="${item.category || ''}"
                                                data-unit="${item.unit || ''}"
                                                data-price="${item.unit_price || 0}"
                                                ${existingItem && existingItem.item_code === item.code ? 'selected' : ''}>
                                            ${item.code} - ${item.name} (${item.category || 'N/A'})
                                        </option>
                                    `).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control item-name" name="items[${itemIndex}][item_name]" 
                                   value="${existingItem ? existingItem.item_name : ''}" readonly required>
                            <input type="hidden" class="item-category" name="items[${itemIndex}][item_category]" 
                                   value="${existingItem ? existingItem.item_category || '' : ''}">
                            <input type="hidden" class="item-unit" name="items[${itemIndex}][unit]" 
                                   value="${existingItem ? existingItem.unit || '' : ''}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" 
                                   min="1" value="${existingItem ? existingItem.quantity : 1}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Unit Price</label>
                            <input type="number" class="form-control item-price" name="items[${itemIndex}][unit_price]" 
                                   step="0.01" min="0" value="${existingItem ? existingItem.unit_price : 0}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Total</label>
                            <input type="text" class="form-control item-total" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="text-right">
                                <span class="badge badge-info item-unit-badge">${existingItem ? existingItem.unit || '' : ''}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Specifications</label>
                    <textarea class="form-control" name="items[${itemIndex}][specifications]" rows="2">${existingItem ? existingItem.specifications || '' : ''}</textarea>
                    </div>
        </div>
    </div>
`;

            $('#itemsContainer').append(itemHtml);

            const currentRow = $(`.item-row[data-index="${itemIndex}"]`);

            // Calculate total for existing items
            if (existingItem) {
                calculateItemTotal(currentRow);
            }

            // Item select change event
            currentRow.find('.item-select').change(function() {
                const selectedOption = $(this).find('option:selected');
                const row = $(this).closest('.item-row');

                row.find('.item-name').val(selectedOption.data('name'));
                row.find('.item-category').val(selectedOption.data('category'));
                row.find('.item-unit').val(selectedOption.data('unit'));
                row.find('.item-price').val(selectedOption.data('price'));
                row.find('.item-unit-badge').text(selectedOption.data('unit'));

                calculateItemTotal(row);
            });

            // Quantity change event
            currentRow.find('.item-quantity').on('input', function() {
                const row = $(this).closest('.item-row');
                calculateItemTotal(row);
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

        function calculateItemTotal(row) {
            const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            const price = parseFloat(row.find('.item-price').val()) || 0;
            const total = quantity * price;
            row.find('.item-total').val('$' + total.toFixed(2));
            updateSummary();
        }

        function updateSummary() {
            const itemCount = $('.item-row').length;
            let totalAmount = 0;
            $('.item-row').each(function() {
                const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
                const price = parseFloat($(this).find('.item-price').val()) || 0;
                totalAmount += (quantity * price);
            });

            $('#totalItemsCount').text(itemCount);
            $('#totalAmount').text('$' + totalAmount.toFixed(2));
        }
    </script>
@endpush
