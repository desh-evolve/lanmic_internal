@extends('layouts.admin')

@section('title', 'Purchase Order Details')
@section('page-title', 'Purchase Order Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">Purchase Orders</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice"></i> Purchase Order Item Details
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Status Badge -->
                <div class="row mb-3">
                    <div class="col-12">
                        @if($poItem->status === 'pending')
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        @else
                            <span class="badge badge-success badge-lg">
                                <i class="fas fa-check"></i> Cleared
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Item Information -->
                <h5 class="border-bottom pb-2 mb-3">Item Information</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="150">Item Code:</th>
                                <td><strong>{{ $poItem->item_code }}</strong></td>
                            </tr>
                            <tr>
                                <th>Item Name:</th>
                                <td>{{ $poItem->item_name }}</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>{{ $poItem->item_category ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="150">Quantity:</th>
                                <td><strong>{{ $poItem->quantity }} {{ $poItem->unit }}</strong></td>
                            </tr>
                            <tr>
                                <th>Unit Price:</th>
                                <td>Rs.{{ number_format($poItem->unit_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Price:</th>
                                <td><strong class="text-success">Rs.{{ number_format($poItem->total_price, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Requisition Information -->
                <h5 class="border-bottom pb-2 mb-3">Requisition Information</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="150">Requisition #:</th>
                                <td><strong>{{ $poItem->requisition->requisition_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Requested By:</th>
                                <td>{{ $poItem->requisition->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td>{{ $poItem->requisition->department->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="150">Request Date:</th>
                                <td>{{ $poItem->requisition->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($poItem->requisition->status) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Clearing Information (if cleared) -->
                @if($poItem->status === 'cleared')
                <h5 class="border-bottom pb-2 mb-3">Clearing Information</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="150">Cleared By:</th>
                                <td>{{ $poItem->clearedBy->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Cleared At:</th>
                                <td>{{ $poItem->cleared_at ? $poItem->cleared_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Additional Notes -->
                @if($poItem->notes)
                <h5 class="border-bottom pb-2 mb-3">Notes</h5>
                <div class="row mb-4">
                    <div class="col-12">
                        <p>{{ $poItem->notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        @if($poItem->status === 'pending')
                            <button type="button" class="btn btn-success" onclick="clearPurchaseOrder()">
                                <i class="fas fa-check"></i> Clear Purchase Order
                            </button>
                        @endif
                        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($poItem->status === 'pending')
<form id="clearForm" method="POST" action="{{ route('admin.purchase-orders.clear') }}" style="display: none;">
    @csrf
    <input type="hidden" name="po_item_ids[]" value="{{ $poItem->id }}">
</form>
@endif
@endsection

@push('scripts')
<script>
function clearPurchaseOrder() {
    if (confirm('Are you sure you want to clear this purchase order item?')) {
        document.getElementById('clearForm').submit();
    }
}
</script>
@endpush