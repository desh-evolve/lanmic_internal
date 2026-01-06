@extends('layouts.admin')

@section('title', 'Manage User Permissions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Manage Permissions for {{ $user->name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                            <li class="breadcrumb-item active">Permissions</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Roles:</strong>
                        @foreach($user->roles as $role)
                            <span class="badge bg-info">{{ $role->name }}</span>
                        @endforeach
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Legend</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><span class="badge bg-success">Green</span> = From Role (pre-ticked)</p>
                    <p class="mb-0"><span class="badge bg-primary">Blue</span> = Direct Permission</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Assign Direct Permissions</h5>
                    <small class="text-muted">Green checkboxes are from roles and cannot be unchecked. Add or remove direct permissions below.</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.permissions.update', $user) }}" method="POST">
                        @csrf

                        @foreach($permissions as $module => $modulePermissions)
                            <div class="mb-4">
                                <h6 class="text-uppercase text-primary border-bottom pb-2">
                                    <i class="fas fa-cube"></i> {{ ucfirst($module) }} Module
                                </h6>
                                <div class="row">
                                    @foreach($modulePermissions as $permission)
                                        @php
                                            $isFromRole = in_array($permission->id, $rolePermissions);
                                            $isDirectPermission = in_array($permission->id, $userPermissions);
                                            $isChecked = $isFromRole || $isDirectPermission;
                                        @endphp
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    id="perm_{{ $permission->id }}"
                                                    {{ $isChecked ? 'checked' : '' }}>
                                                <label class="form-check-label {{ $isFromRole ? 'text-success fw-bold' : '' }}" for="perm_{{ $permission->id }}">
                                                    {{ $permission->description }}
                                                    @if($isFromRole)
                                                        <small class="badge bg-success ms-1">Role</small>
                                                    @endif
                                                    @if($isDirectPermission)
                                                        <small class="badge bg-primary ms-1">Direct</small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Permissions
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
