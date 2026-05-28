@extends('layouts.admin')

@section('title', 'Edit Requisition')
@section('page-title', 'Edit Requisition')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisitions</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requisitions.show', $requisition->id) }}">{{ $requisition->requisition_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
<form action="{{ route('requisitions.update', $requisition->id) }}" method="POST" id="requisitionEditForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-8">
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Edit Requisition
                        <span class="badge badge-secondary ml-2">{{ $requisition->requisition_number }}</span>
                        <span class="badge badge-warning ml-1">Pending</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Department <span class="text-danger">*</span></label>
                                <select class="form-control @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ (old('department_id', $requisition->department_id) == $department->id) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                            @if($department->short_code) ({{ $department->short_code }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sub-Department</label>
                                <select class="form-control" id="sub_department_id" name="sub_department_id">
                                    <option value="">Select Sub-Department</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Division</label>
                                <select class="form-control" id="division_id" name="division_id">
                                    <option value="">Select Division</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Any additional information">{{ old('notes', $requisition->notes) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Items</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle"></i>
                        You can change quantities, remove items, or add new items. Click <strong>Save Changes</strong> when done.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:18%">Item Code</th>
                                    <th style="width:30%">Item Name</th>
                                    <th style="width:10%">Category</th>
                                    <th style="width:12%">Qty</th>
                                    <th style="width:8%">UOM</th>
                                    <th style="width:17%">Specifications</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @foreach($requisition->items as $index => $item)
                                <tr class="item-row">
                                    <input type="hidden" name="requisition_items[{{ $index }}][item_code]"     value="{{ $item->item_code }}">
                                    <input type="hidden" name="requisition_items[{{ $index }}][item_name]"     value="{{ $item->item_name }}">
                                    <input type="hidden" name="requisition_items[{{ $index }}][item_category]" value="{{ $item->item_category }}">
                                    <input type="hidden" name="requisition_items[{{ $index }}][unit]"          value="{{ $item->unit }}">
                                    <input type="hidden" name="requisition_items[{{ $index }}][location_code]" value="{{ $item->location_code }}">
                                    <td><code>{{ $item->item_code }}</code></td>
                                    <td>{{ $item->item_name }}</td>
                                    <td><small>{{ $item->item_category ?? '-' }}</small></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm"
                                            name="requisition_items[{{ $index }}][quantity]"
                                            value="{{ old('requisition_items.'.$index.'.quantity', $item->quantity) }}"
                                            min="0.0001" step="0.0001" required>
                                    </td>
                                    <td><small>{{ $item->unit ?? '-' }}</small></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm"
                                            name="requisition_items[{{ $index }}][specifications]"
                                            value="{{ old('requisition_items.'.$index.'.specifications', $item->specifications) }}"
                                            placeholder="Optional specs">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-xs" onclick="removeRow(this)" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Add New Item --}}
                    <div class="mt-3 p-3 border rounded bg-light">
                        <h6><i class="fas fa-plus-circle text-success"></i> Add New Item</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small">Item</label>
                                    <select id="newItemSelect" class="form-control form-control-sm" style="width:100%">
                                        <option value="">Search item...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="small">Qty</label>
                                    <input type="number" id="newItemQty" class="form-control form-control-sm" value="1" min="0.0001" step="0.0001">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="small">Location</label>
                                    <select id="newItemLocation" class="form-control form-control-sm">
                                        <option value="">Select location</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end pb-2">
                                <button type="button" class="btn btn-success btn-sm w-100" onclick="addNewItem()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small">Specifications (optional)</label>
                                    <input type="text" id="newItemSpec" class="form-control form-control-sm" placeholder="Optional specifications">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light mb-3">
                        <div class="info-box-content">
                            <span class="info-box-text">Items in Requisition</span>
                            <span class="info-box-number" id="itemCountDisplay">{{ $requisition->items->count() }}</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('requisitions.show', $requisition->id) }}" class="btn btn-secondary btn-block mt-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Note</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Editing is only allowed while the requisition is <strong>Pending</strong> approval.
                    </p>
                    <p class="text-sm mb-0">
                        <i class="fas fa-info-circle text-info"></i>
                        Once the approver approves or rejects, editing will be locked.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// All items from Sage 300
const allItems = @json($items);
let rowIndex = {{ $requisition->items->count() }};

// Pre-load sub-departments and divisions based on current selection
const initialDeptId   = {{ $requisition->department_id ?? 'null' }};
const initialSubDeptId = {{ $requisition->sub_department_id ?? 'null' }};
const initialDivId    = {{ $requisition->division_id ?? 'null' }};

$(document).ready(function () {

    // Init Select2 for new item search
    $('#newItemSelect').select2({
        placeholder: 'Search by code or name...',
        allowClear: true,
        data: allItems.map(i => ({
            id: i.ItemNumber,
            text: i.ItemNumber + ' — ' + i.Description,
            item: i
        })),
        matcher: function(params, data) {
            if (!params.term) return data;
            const t = params.term.toUpperCase();
            if (data.text && data.text.toUpperCase().includes(t)) return data;
            return null;
        }
    }).on('change', function () {
        const selected = $(this).select2('data')[0];
        if (selected && selected.item) {
            loadNewItemLocations(selected.item.ItemNumber);
        } else {
            $('#newItemLocation').html('<option value="">Select location</option>');
        }
    });

    // Load sub-departments on dept change
    $('#department_id').change(function () {
        loadSubDepartments($(this).val(), null);
    });

    // Load divisions on sub-dept change
    $('#sub_department_id').change(function () {
        loadDivisions($(this).val(), null);
    });

    // Pre-populate sub-departments & divisions
    if (initialDeptId) {
        loadSubDepartments(initialDeptId, initialSubDeptId, function() {
            if (initialSubDeptId) loadDivisions(initialSubDeptId, initialDivId);
        });
    }
});

function loadSubDepartments(deptId, preselectId, callback) {
    if (!deptId) { $('#sub_department_id').html('<option value="">Select Sub-Department</option>'); return; }
    $.get('/api/departments/' + deptId + '/sub-departments', function(data) {
        let opts = '<option value="">Select Sub-Department</option>';
        data.forEach(sd => { opts += `<option value="${sd.id}" ${sd.id == preselectId ? 'selected' : ''}>${sd.name}</option>`; });
        $('#sub_department_id').html(opts);
        if (callback) callback();
    });
}

function loadDivisions(subDeptId, preselectId) {
    if (!subDeptId) { $('#division_id').html('<option value="">Select Division</option>'); return; }
    $.get('/api/sub-departments/' + subDeptId + '/divisions', function(data) {
        let opts = '<option value="">Select Division</option>';
        data.forEach(d => { opts += `<option value="${d.id}" ${d.id == preselectId ? 'selected' : ''}>${d.name}</option>`; });
        $('#division_id').html(opts);
    });
}

function loadNewItemLocations(itemCode) {
    $('#newItemLocation').html('<option value="">Loading...</option>');
    $.get('/admin/sage300/api/items/' + encodeURIComponent(itemCode) + '/locations', function(data) {
        let opts = '<option value="">Select location</option>';
        (data || []).forEach(loc => {
            opts += `<option value="${loc.location}">${loc.location} (Qty: ${loc.quantity})</option>`;
        });
        $('#newItemLocation').html(opts);
    }).fail(function() {
        $('#newItemLocation').html('<option value="">No locations found</option>');
    });
}

function removeRow(btn) {
    if (document.querySelectorAll('#itemsBody tr.item-row').length <= 1) {
        alert('At least one item is required.');
        return;
    }
    btn.closest('tr').remove();
    reindexRows();
    updateItemCount();
}

function reindexRows() {
    document.querySelectorAll('#itemsBody tr.item-row').forEach((row, idx) => {
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/requisition_items\[\d+\]/, `requisition_items[${idx}]`);
        });
    });
    rowIndex = document.querySelectorAll('#itemsBody tr.item-row').length;
}

function addNewItem() {
    const selectedData = $('#newItemSelect').select2('data')[0];
    if (!selectedData || !selectedData.item) {
        alert('Please select an item.');
        return;
    }
    const item     = selectedData.item;
    const qty      = parseFloat(document.getElementById('newItemQty').value);
    const location = document.getElementById('newItemLocation').value;
    const spec     = document.getElementById('newItemSpec').value.trim();

    if (!qty || qty <= 0) { alert('Please enter a valid quantity.'); return; }
    if (!location) { alert('Please select a location.'); return; }

    const idx = rowIndex++;
    const row = `
        <tr class="item-row table-success">
            <input type="hidden" name="requisition_items[${idx}][item_code]"     value="${item.ItemNumber}">
            <input type="hidden" name="requisition_items[${idx}][item_name]"     value="${item.Description}">
            <input type="hidden" name="requisition_items[${idx}][item_category]" value="${item.CategoryCode || ''}">
            <input type="hidden" name="requisition_items[${idx}][unit]"          value="${item.UnitOfMeasure || ''}">
            <input type="hidden" name="requisition_items[${idx}][location_code]" value="${location}">
            <td><code>${item.ItemNumber}</code></td>
            <td>${item.Description}</td>
            <td><small>${item.CategoryCode || '-'}</small></td>
            <td>
                <input type="number" class="form-control form-control-sm"
                    name="requisition_items[${idx}][quantity]"
                    value="${qty}" min="0.0001" step="0.0001" required>
            </td>
            <td><small>${item.UnitOfMeasure || '-'}</small></td>
            <td>
                <input type="text" class="form-control form-control-sm"
                    name="requisition_items[${idx}][specifications]"
                    value="${spec}" placeholder="Optional specs">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs" onclick="removeRow(this)" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;

    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);
    updateItemCount();

    // Reset add form
    $('#newItemSelect').val(null).trigger('change');
    document.getElementById('newItemQty').value = '1';
    document.getElementById('newItemSpec').value = '';
    $('#newItemLocation').html('<option value="">Select location</option>');
}

function updateItemCount() {
    document.getElementById('itemCountDisplay').textContent =
        document.querySelectorAll('#itemsBody tr.item-row').length;
}
</script>
@endpush
