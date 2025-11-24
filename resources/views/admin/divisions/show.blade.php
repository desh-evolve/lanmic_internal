@extends('layouts.admin')

@section('title', 'Division Details')
@section('page-title', 'Division Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('divisions.index') }}">Divisions</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Division Information</h3>
                <div class="card-tools">
                    <a href="{{ route('divisions.edit', $division->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>ID:</strong></div>
                    <div class="col-md-9">{{ $division->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Name:</strong></div>
                    <div class="col-md-9">{{ $division->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Short Code:</strong></div>
                    <div class="col-md-9">
                        @if($division->short_code)
                            <span class="badge badge-secondary">{{ $division->short_code }}</span>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Description:</strong></div>
                    <div class="col-md-9">{{ $division->description ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Status:</strong></div>
                    <div class="col-md-9">
                        @if($division->status == 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Created At:</strong></div>
                    <div class="col-md-9">{{ $division->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Updated At:</strong></div>
                    <div class="col-md-9">{{ $division->updated_at->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sub-Departments ({{ $division->subDepartments->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($division->subDepartments as $subDepartment)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <strong>{{ $subDepartment->name }}</strong>
                            @if($subDepartment->short_code)
                                <span class="badge badge-secondary">{{ $subDepartment->short_code }}</span>
                            @endif
                            @if($subDepartment->description)
                                <br>
                                <small class="text-muted">{{ Str::limit($subDepartment->description, 100) }}</small>
                            @endif
                            <br>
                            <small><strong>Parent Departments:</strong> {{ $subDepartment->departments->count() }}</small>
                        </div>
                        <a href="{{ route('sub-departments.show', $subDepartment->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Not assigned to any sub-departments</p>
                @endforelse
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('divisions.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@endsection