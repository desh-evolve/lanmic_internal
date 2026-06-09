@extends('layouts.admin')

@section('title', 'Issued Items Report')
@section('page-title', 'Issued Items Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Issued Items</li>
@endsection

@section('content')
@include('admin.reports._issued-items-table', [
    'reportTitle'    => 'Issued Items Report',
    'exportRoute'    => 'reports.issued-items',
])
@endsection
