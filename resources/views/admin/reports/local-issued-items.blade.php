@extends('layouts.admin')

@section('title', 'Local Items Issuing Report')
@section('page-title', 'Local Items Issuing Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Local Issued Items</li>
@endsection

@section('content')
@include('admin.reports._issued-items-table', [
    'reportTitle'    => 'Local Items Issuing Report',
    'exportRoute'    => 'reports.local-issued-items',
])
@endsection
