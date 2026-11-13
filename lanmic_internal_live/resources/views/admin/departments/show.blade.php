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
                <div class="card-header" style="background-color: rgb(192, 189, 189)">
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
                            @if ($department->short_code)
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
                            @if ($department->status == 'active')
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

            <div class="card" >
                <div class="card-header" style="background-color: rgb(192, 189, 189)">
                    <h3 class="card-title">Sub-Departments ({{ $department->subDepartments->count() }})</h3>
                </div>

                <div class="card-body">
                    @forelse($department->subDepartments as $subDepartment)

                        <div class="p-3 mb-2 border rounded bg-white shadow-sm">

                            <!-- Sub Department Title -->
                            <div class="mb-4 d-flex justify-content-between" style="height:26px;">
                                <div>
                                    <strong class="h6">{{ $subDepartment->name }}</strong>
                                    @if ($subDepartment->short_code)
                                        <span class="badge badge-secondary">{{ $subDepartment->short_code }}</span>
                                    @endif

                                    @if ($subDepartment->description)
                                        <p class="text-muted mt-1 mb-1">
                                            {{ Str::limit($subDepartment->description, 120) }}
                                        </p>
                                    @endif
                                </div>

                                <a href="{{ route('sub-departments.show', $subDepartment->id) }}"
                                    class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>

                            <!-- Divisions Section -->
                            @if ($subDepartment->divisions->count())
                                <div class="mt-3 ml-4"> {{-- <<< indentation here --}}
                                    <strong class="text-primary d-block mb-2">Divisions:</strong>

                                    @foreach ($subDepartment->divisions as $division)
                                        <div class="mb-2 pl-3"> {{-- <<< more indentation --}}
                                            <strong>{{ $division->name }}</strong>

                                            @if ($division->short_code)
                                                <span class="badge badge-secondary">{{ $division->short_code }}</span>
                                            @endif

                                            <br>
                                            <small class="text-muted">
                                                {{ $division->description ?? '-' }}
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted ml-4">No divisions assigned</p>
                            @endif
                        </div>

                    @empty
                        <p class="text-muted">No sub-departments assigned to this department</p>
                    @endforelse
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end"
                style="position: sticky; bottom: 0; background: white; z-index: 10;">
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
@endsection
