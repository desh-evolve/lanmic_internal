@extends('layouts.admin')

@section('title', 'Edit Requisition')
@section('page-title', 'Edit Requisition')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisitions</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
    under development
@endsection
