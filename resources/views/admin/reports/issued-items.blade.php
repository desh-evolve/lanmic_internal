@extends('layouts.admin')

@section('title', 'Issued Items Report')
@section('page-title', 'Issued Items Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Issued Items</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
@include('admin.reports._issued-items-table', [
    'reportTitle' => 'Issued Items Report',
    'exportRoute' => 'reports.issued-items',
    'items'       => $items,
    'itemType'    => $itemType,
])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#issuedItemSelect').select2({
            theme: 'bootstrap',
            placeholder: 'Search by code or name…',
            allowClear: true,
            minimumInputLength: 0,
            matcher: function (params, data) {
                if (!params.term || params.term.trim() === '') return data;
                const term = params.term.toLowerCase();
                if (data.text && data.text.toLowerCase().includes(term)) return data;
                return null;
            }
        });
    });
</script>
@endpush
