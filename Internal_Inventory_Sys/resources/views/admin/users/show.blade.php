@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Information</h3>
                <div class="card-tools">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>ID:</strong></div>
                    <div class="col-md-9">{{ $user->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Name:</strong></div>
                    <div class="col-md-9">{{ $user->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Email:</strong></div>
                    <div class="col-md-9">{{ $user->email }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Created At:</strong></div>
                    <div class="col-md-9">{{ $user->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Updated At:</strong></div>
                    <div class="col-md-9">{{ $user->updated_at->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assigned Roles</h3>
            </div>
            <div class="card-body">
                @forelse($user->roles as $role)
                    <span class="badge badge-info badge-lg mr-2 mb-2">{{ $role->name }}</span>
                @empty
                    <p class="text-muted">No roles assigned</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permissions (via Roles)</h3>
            </div>
            <div class="card-body">
                @php
                    $allPermissions = $user->roles->flatMap->permissions->unique('id');
                @endphp
                @forelse($allPermissions as $permission)
                    <span class="badge badge-success mr-2 mb-2">{{ $permission->name }}</span>
                @empty
                    <p class="text-muted">No permissions assigned</p>
                @endforelse
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('users.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@endsection