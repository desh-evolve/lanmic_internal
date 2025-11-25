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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="background-color: rgb(192, 189, 189)">
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
                            @if ($division->short_code)
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
                            @if ($division->status == 'active')
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
                <div class="card-header" style="background-color: rgb(192, 189, 189)">
                    <h3 class="card-title">Sub-Departments ({{ $division->subDepartments->count() }})</h3>
                </div>
                {{-- <div class="card-body">
                    @forelse($division->subDepartments as $subDepartment)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                            <div>
                                <strong>{{ $subDepartment->name }}</strong>
                                @if ($subDepartment->short_code)
                                    <span class="badge badge-secondary">{{ $subDepartment->short_code }}</span>
                                @endif
                                @if ($subDepartment->description)
                                    <br>
                                    <small class="text-muted">{{ Str::limit($subDepartment->description, 100) }}</small>
                                @endif
                                <br>
                                <small><strong>Parent Departments:</strong>
                                    {{ $subDepartment->departments->count() }}</small>
                            </div>
                            <a href="{{ route('sub-departments.show', $subDepartment->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    @empty
                        <p class="text-muted">Not assigned to any sub-departments</p>
                    @endforelse
                </div> --}}

                <div class="card-body">
                    @forelse($division->subDepartments as $subDepartment)

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

                                <a href="{{ route('divisions.show', $subDepartment->id) }}"
                                    class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>

                            <!-- Divisions Section -->
                            @if ($subDepartment->departments->count())
                                <div class="mt-3 ml-4"> {{-- <<< indentation here --}}
                                    <strong class="text-primary d-block mb-2">Divisions:</strong>

                                    @foreach ($subDepartment->departments as $department)
                                        <div class="mb-2 pl-3"> {{-- <<< more indentation --}}
                                            <strong>{{ $department->name }}</strong>

                                            @if ($department->short_code)
                                                <span class="badge badge-secondary">{{ $department->short_code }}</span>
                                            @endif

                                            <br>
                                            <small class="text-muted">
                                                {{ $department->description ?? '-' }}
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted ml-4">No department assigned</p>
                            @endif
                        </div>

                    @empty
                        <p class="text-muted">No sub-departments assigned to this department</p>
                    @endforelse
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end"
                style="position: sticky; bottom: 0; background: white; z-index: 10;">
                <a href="{{ route('divisions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
@endsection
