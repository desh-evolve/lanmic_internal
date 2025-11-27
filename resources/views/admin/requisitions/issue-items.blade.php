@extends('layouts.admin')

@section('title', 'Issue Items')
@section('page-title', 'Issue Items')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.requisitions.index') }}">Requisitions</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.requisitions.show', $requisition->id) }}">{{ $requisition->requisition_number }}</a></li>
    <li class="breadcrumb-item active">Issue Items</li>
@endsection

@section('content')
<form action="{{ route('admin.requisitions.issue-items.store', $requisition->id) }}" method="POST" id="issueItemsForm">
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
                    <h3 class="card-title">Requisition Information</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Requisition #:</strong></div>
                        <div class="col-md-8">{{ $requisition->requisition_number }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Requested By:</strong></div>
                        <div class="col-md-8">{{ $requisition->user->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Department:</strong></div>
                        <div class="col-md-8">{{ $requisition->department->name ?? '-' }}</div>
                    </div>
                    @if($requisition->subDepartment)
                    <div class="row mb-2">
                        <div class="col-md-4"><strong>Sub-Department:</strong></div>
                        <div class="col-md-8">{{ $requisition->subDepartment->name }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Issue Items</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Requested Qty</th>
                                <th>Already Issued</th>
                                <th>Remaining</th>
                                <th>Issue Now</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requisition->items as $item)
                            @php
                                $alreadyIssued = $item->issuedItems->sum('issued_quantity');
                                $remaining = $item->quantity - $alreadyIssued;
                            @endphp
                            <tr class="{{ $remaining <= 0 ? 'table-success' : '' }}">
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $item->item_code }}</small>
                                    @if($item->specifications)
                                        <br><small><i class="fas fa-info-circle"></i> {{ $item->specifications }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $alreadyIssued }}</span>
                                </td>
                                <td>
                                    @if($remaining > 0)
                                        <span class="badge badge-warning">{{ $remaining }}</span>
                                    @else
                                        <span class="badge badge-success">Fully Issued</span>
                                    @endif
                                </td>
                                <td>
                                    @if($remaining > 0)
                                        <input type="hidden" name="items[{{ $loop->index }}][requisition_item_id]" value="{{ $item->id }}">
                                        <div class="input-group input-group-sm">
                                            <input type="number" 
                                                   class="form-control issue-quantity" 
                                                   name="items[{{ $loop->index }}][issued_quantity]" 
                                                   min="1" 
                                                   max="{{ $remaining }}" 
                                                   value="{{ $remaining }}"
                                                   data-remaining="{{ $remaining }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text">{{ $item->unit }}</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Max: {{ $remaining }}</small>
                                    @else
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Complete</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @error('items')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-box"></i> Issue Selected Items
                    </button>
                    <a href="{{ route('admin.requisitions.show', $requisition->id) }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Issuance Summary</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Items</span>
                            <span class="info-box-number">{{ $requisition->items->count() }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Fully Issued</span>
                            <span class="info-box-number text-success">
                                {{ $requisition->items->filter(fn($item) => $item->isFullyIssued())->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Issuance</span>
                            <span class="info-box-number text-warning">
                                {{ $requisition->items->filter(fn($item) => !$item->isFullyIssued())->count() }}
                            </span>
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
                        <li>Review each item and its remaining quantity</li>
                        <li>Enter the quantity to issue (up to the remaining amount)</li>
                        <li>Click "Issue Selected Items" to complete</li>
                    </ol>
                    <div class="alert alert-info mt-3">
                        <i class="icon fas fa-info"></i>
                        <small>
                            You can issue partial quantities. Items will remain in the requisition until fully issued.
                        </small>
                    </div>
                </div>
            </div>

            @if($requisition->purchaseOrderItems->count() > 0)
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Purchase Orders Required</h3>
                </div>
                <div class="card-body">
                    <p><strong>Items needing PO:</strong></p>
                    <ul>
                        @foreach($requisition->purchaseOrderItems as $poItem)
                        <li>
                            <strong>{{ $poItem->item_name }}</strong>
                            <br>
                            <small>Qty: {{ $poItem->quantity }} {{ $poItem->unit }}</small>
                            <br>
                            <span class="badge badge-{{ $poItem->status === 'pending' ? 'warning' : 'success' }}">
                                {{ ucfirst($poItem->status) }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Validate quantities on input
    $('.issue-quantity').on('input', function() {
        const max = parseInt($(this).data('remaining'));
        const value = parseInt($(this).val());
        
        if (value > max) {
            $(this).val(max);
            alert('Cannot issue more than remaining quantity: ' + max);
        }
        
        if (value < 1) {
            $(this).val(1);
        }
    });

    // Form validation
    $('#issueItemsForm').on('submit', function(e) {
        let hasValue = false;
        $('.issue-quantity').each(function() {
            if ($(this).val() > 0) {
                hasValue = true;
                return false;
            }
        });

        if (!hasValue) {
            e.preventDefault();
            alert('Please enter at least one item quantity to issue');
        }
    });
});
</script>
@endpush