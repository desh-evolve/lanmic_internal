@extends('layouts.admin')

@section('title', 'Sage 300 Items')
@section('page-title', 'Sage 300 Item Catalogue')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sage300.index') }}">Sage 300</a></li>
    <li class="breadcrumb-item active">Items</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endpush

@section('content')

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">
            <i class="fas fa-boxes"></i> All Items
            @if($fileExists)
                <span class="badge badge-success ml-2">{{ number_format($total) }} items</span>
                <small class="text-muted ml-2" style="font-size:0.8rem;">Last updated: {{ $lastUpdated }}</small>
            @else
                <span class="badge badge-warning ml-2">Cache not built</span>
            @endif
        </h3>
        <div>
            <a href="{{ route('sage300.index') }}" class="btn btn-sm btn-secondary mr-1">
                <i class="fas fa-cogs"></i> API Explorer
            </a>
            <button id="refreshBtn" class="btn btn-sm btn-warning">
                <i class="fas fa-sync-alt"></i> Refresh Cache
            </button>
        </div>
    </div>

    <div class="card-body">
        @if(!$fileExists)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Item catalogue has not been built yet. Click <strong>Refresh Cache</strong> to fetch all items from Sage 300.
            </div>
        @endif

        <div id="refreshMsg" class="alert" style="display:none;"></div>

        <table id="itemsTable" class="table table-bordered table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Qty On Hand</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {

    const table = $('#itemsTable').DataTable({
        processing: true,
        ajax: {
            url: '{{ url("admin/sage300/api/items") }}',
            dataSrc: 'data',
            error: function () {
                $('#refreshMsg')
                    .removeClass('alert-success').addClass('alert alert-danger')
                    .html('<i class="fas fa-exclamation-circle"></i> Failed to load items. Cache may not be built yet.')
                    .show();
            }
        },
        columns: [
            { data: 'UnformattedItemNumber', defaultContent: '' },
            { data: 'Description',           defaultContent: '' },
            { data: 'Category',              defaultContent: '<span class="text-muted">—</span>' },
            { data: 'StockingUnitOfMeasure', defaultContent: '' },
            {
                data: 'QuantityOnHand',
                defaultContent: '0',
                className: 'text-right',
                render: function (data) {
                    return data != null ? parseFloat(data).toLocaleString() : '0';
                }
            },
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading items...',
            emptyTable: 'No items found. Refresh the cache to fetch from Sage 300.',
        }
    });

    // Refresh cache button
    $('#refreshBtn').on('click', function () {
        const $btn = $(this);
        if (!confirm('This will re-fetch all items from Sage 300. It may take a minute. Continue?')) return;

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
        $('#refreshMsg').hide();

        $.ajax({
            url: '{{ route("sage300.api.items.refresh-cache") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#refreshMsg')
                    .removeClass('alert-danger').addClass('alert alert-success')
                    .html('<i class="fas fa-check-circle"></i> ' + response.message)
                    .show();
                table.ajax.reload();
            },
            error: function () {
                $('#refreshMsg')
                    .removeClass('alert-success').addClass('alert alert-danger')
                    .html('<i class="fas fa-exclamation-circle"></i> Refresh failed. Please try again.')
                    .show();
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Refresh Cache');
            }
        });
    });

});
</script>
@endpush
