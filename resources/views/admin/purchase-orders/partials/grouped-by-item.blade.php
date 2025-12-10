@forelse($poItems as $itemCode => $items)
    @php
        $firstItem = $items->first();
        $totalQuantity = $items->sum('quantity');
        $totalValue = $items->sum('total_price');
        $requisitionsCount = $items->unique('requisition_id')->count();
    @endphp
    <div class="card mb-3">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        Item Code: <strong>{{ $itemCode }}</strong>
                    </h5>
                    <small class="text-muted">
                        {{ $firstItem->item_name }} | 
                        Category: {{ $firstItem->item_category ?? '-' }} | 
                        From {{ $requisitionsCount }} requisition(s)
                    </small>
                </div>
                <div class="col-md-6 text-right">
                    <span class="badge badge-info">{{ $items->count() }} orders</span>
                    <span class="badge badge-primary">Total Qty: {{ $totalQuantity }}</span>
                    <span class="badge badge-success">Value: Rs.{{ number_format($totalValue, 2) }}</span>
                    @if($status === 'pending')
                        <button type="button" class="btn btn-success btn-sm ml-2" 
                                onclick="clearItemPO('{{ $itemCode }}')">
                            <i class="fas fa-check"></i> Clear All
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        @if($status === 'pending')
                        <th width="50">
                            <input type="checkbox" class="select-all-item" data-item="{{ $itemCode }}">
                        </th>
                        @endif
                        <th>Requisition #</th>
                        <th>Requested By</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        @if($status === 'cleared')
                        <th>Cleared By</th>
                        <th>Cleared At</th>
                        @endif
                        @if($status === 'pending')
                        <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        @if($status === 'pending')
                        <td>
                            <input type="checkbox" class="po-item-checkbox" value="{{ $item->id }}" data-item="{{ $itemCode }}">
                        </td>
                        @endif
                        <td>
                            <strong>{{ $item->requisition->requisition_number }}</strong>
                        </td>
                        <td>{{ $item->requisition->user->name }}</td>
                        <td>{{ $item->requisition->department->name ?? '-' }}</td>
                        <td>{{ $item->requisition->created_at->format('Y-m-d') }}</td>
                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                        <td>Rs.{{ number_format($item->unit_price, 2) }}</td>
                        <td><strong>Rs.{{ number_format($item->total_price, 2) }}</strong></td>
                        @if($status === 'cleared')
                        <td>{{ $item->clearedBy->name ?? '-' }}</td>
                        <td>{{ $item->cleared_at ? $item->cleared_at->format('Y-m-d H:i') : '-' }}</td>
                        @endif
                        @if($status === 'pending')
                        <td>
                            <button type="button" class="btn btn-success btn-xs" onclick="clearSinglePO({{ $item->id }})">
                                <i class="fas fa-check"></i> Clear
                            </button>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No purchase orders found.
    </div>
@endforelse

@if($status === 'pending' && $poItems->count() > 0)
<div class="fixed-bottom bg-white border-top p-3 shadow" style="z-index: 1000;">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span id="selectedCount" class="badge badge-primary">0</span> items selected
            </div>
            <div class="col-md-4 text-right">
                <button type="button" class="btn btn-success" id="bulkClearBtn" disabled onclick="bulkClearSelected()">
                    <i class="fas fa-check-double"></i> Clear Selected
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
// Select all for item
$('.select-all-item').change(function() {
    const itemCode = $(this).data('item');
    const isChecked = $(this).prop('checked');
    $(`.po-item-checkbox[data-item="${itemCode}"]`).prop('checked', isChecked);
    updateBulkClearButton();
});

// Individual checkbox
$('.po-item-checkbox').change(function() {
    updateBulkClearButton();
});

function updateBulkClearButton() {
    const selectedCount = $('.po-item-checkbox:checked').length;
    $('#selectedCount').text(selectedCount);
    $('#bulkClearBtn').prop('disabled', selectedCount === 0);
}

function clearSinglePO(poItemId) {
    if (confirm('Are you sure you want to clear this purchase order item?')) {
        submitClearForm([poItemId]);
    }
}

function clearItemPO(itemCode) {
    if (confirm('Are you sure you want to clear all purchase order items for this item?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.purchase-orders.bulk-clear") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'clear_item';
        form.appendChild(actionInput);
        
        const itemInput = document.createElement('input');
        itemInput.type = 'hidden';
        itemInput.name = 'item_code';
        itemInput.value = itemCode;
        form.appendChild(itemInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkClearSelected() {
    const selectedIds = $('.po-item-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one item');
        return;
    }
    
    if (confirm(`Are you sure you want to clear ${selectedIds.length} purchase order item(s)?`)) {
        submitClearForm(selectedIds);
    }
}

function submitClearForm(poItemIds) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.purchase-orders.clear") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    poItemIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'po_item_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush