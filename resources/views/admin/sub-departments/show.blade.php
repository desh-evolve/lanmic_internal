@extends('layouts.admin')

@section('title', 'Sub-Department Details')
@section('page-title', 'Sub-Department Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sub-departments.index') }}">Sub-Departments</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sub-Department Information</h3>
                <div class="card-tools">
                    <a href="{{ route('sub-departments.edit', $subDepartment->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>ID:</strong></div>
                    <div class="col-md-9">{{ $subDepartment->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Name:</strong></div>
                    <div class="col-md-9">{{ $subDepartment->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Short Code:</strong></div>
                    <div class="col-md-9">
                        @if($subDepartment->short_code)
                            <span class="badge badge-secondary">{{ $subDepartment->short_code }}</span>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Description:</strong></div>
                    <div class="col-md-9">{{ $subDepartment->description ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Status:</strong></div>
                    <div class="col-md-9">
                        @if($subDepartment->status == 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Created At:</strong></div>
                    <div class="col-md-9">{{ $subDepartment->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Updated At:</strong></div>
                    <div class="col-md-9">{{ $subDepartment->updated_at->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Parent Departments ({{ $subDepartment->departments->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($subDepartment->departments as $department)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <strong>{{ $department->name }}</strong>
                            @if($department->short_code)
                                <span class="badge badge-secondary">{{ $department->short_code }}</span>
                            @endif
                            @if($department->description)
                                <br>
                                <small class="text-muted">{{ Str::limit($department->description, 100) }}</small>
                            @endif
                        </div>
                        <a href="{{ route('departments.show', $department->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                @empty
                    <p class="text-muted">Not assigned to any departments</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Divisions ({{ $subDepartment->divisions->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($subDepartment->divisions as $division)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <strong>{{ $division->name }}</strong>
                            @if($division->short_code)
                                <span class="badge badge-secondary">{{ $division->short_code }}</span>
                            @endif
                            @if($division->description)
                                <br>
                                <small class="text-muted">{{ Str::limit($division->description, 100) }}</small>
                            @endif
                        </div>
                        <a href="{{ route('divisions.show', $division->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                @empty
                    <p class="text-muted">No divisions assigned to this sub-department</p>
                @endforelse
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('sub-departments.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@endsection