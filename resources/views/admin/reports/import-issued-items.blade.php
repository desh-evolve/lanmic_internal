@extends('layouts.admin')

@section('title', 'Import Items Issuing Report')
@section('page-title', 'Import Items Issuing Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Import Issued Items</li>
@endsection

@section('content')
@include('admin.reports._issued-items-table', [
    'reportTitle'    => 'Import Items Issuing Report',
    'exportRoute'    => 'reports.import-issued-items',
])
@endsection
