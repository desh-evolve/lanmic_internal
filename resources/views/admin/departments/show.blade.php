@extends('layouts.admin')

@section('title', 'Department Details')
@section('page-title', 'Department Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Department Information</h3>
                <div class="card-tools">
                    <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>ID:</strong></div>
                    <div class="col-md-9">{{ $department->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Name:</strong></div>
                    <div class="col-md-9">{{ $department->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Short Code:</strong></div>
                    <div class="col-md-9">
                        @if($department->short_code)
                            <span class="badge badge-secondary">{{ $department->short_code }}</span>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Description:</strong></div>
                    <div class="col-md-9">{{ $department->description ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Status:</strong></div>
                    <div class="col-md-9">
                        @if($department->status == 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Created At:</strong></div>
                    <div class="col-md-9">{{ $department->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Updated At:</strong></div>
                    <div class="col-md-9">{{ $department->updated_at->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sub-Departments ({{ $department->subDepartments->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($department->subDepartments as $subDepartment)
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
                            <small><strong>Divisions:</strong> {{ $subDepartment->divisions->count() }}</small>
                        </div>
                        <a href="{{ route('sub-departments.show', $subDepartment->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                @empty
                    <p class="text-muted">No sub-departments assigned to this department</p>
                @endforelse
            </div>
        </div>

        <div class="card-footer" style="display:flex; justify-content:right; align-items:right;">
            <a href="{{ route('departments.index') }}"  class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@endsection