@extends('layouts.admin')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">Purchase Orders</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
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

        <!-- Statistics Cards -->
        <div class="row mb-3">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $statistics['pending_count'] }}</h3>
                        <p>Pending PO Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $statistics['pending_quantity'] }}</h3>
                        <p>Pending Quantity</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $statistics['cleared_count'] }}</h3>
                        <p>Cleared PO Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
                           href="{{ route('admin.purchase-orders.index', ['status' => 'pending', 'group_by' => $groupBy]) }}">
                            <i class="fas fa-clock"></i> Pending 
                            <span class="badge badge-warning">{{ $statistics['pending_count'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'cleared' ? 'active' : '' }}" 
                           href="{{ route('admin.purchase-orders.index', ['status' => 'cleared', 'group_by' => $groupBy]) }}">
                            <i class="fas fa-check"></i> Cleared 
                            <span class="badge badge-success">{{ $statistics['cleared_count'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <!-- Filters and Group By -->
                <form method="GET" action="{{ route('admin.purchase-orders.index') }}" class="mb-3">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Group By</label>
                                <select name="group_by" class="form-control" onchange="this.form.submit()">
                                    <option value="item" {{ $groupBy === 'item' ? 'selected' : '' }}>Item</option>
                                    <option value="requisition" {{ $groupBy === 'requisition' ? 'selected' : '' }}>Requisition</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Item Code</label>
                                <input type="text" name="item_code" class="form-control" value="{{ request('item_code') }}" placeholder="Search by code">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.purchase-orders.index', ['status' => $status, 'group_by' => $groupBy]) }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </form>

                @if($groupBy === 'requisition')
                    @include('admin.purchase-orders.partials.grouped-by-requisition')
                @else
                    @include('admin.purchase-orders.partials.grouped-by-item')
                @endif
            </div>
        </div>
    </div>
</div>
@endsection