@extends('layouts.admin')

@section('title', 'Permission Details')
@section('page-title', 'Permission Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Information</h3>
                <div class="card-tools">
                    <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>ID:</strong></div>
                    <div class="col-md-9">{{ $permission->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Name:</strong></div>
                    <div class="col-md-9"><span class="badge badge-success badge-lg">{{ $permission->name }}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Description:</strong></div>
                    <div class="col-md-9">{{ $permission->description ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Created At:</strong></div>
                    <div class="col-md-9">{{ $permission->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Updated At:</strong></div>
                    <div class="col-md-9">{{ $permission->updated_at->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Roles with this Permission ({{ $permission->roles->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($permission->roles as $role)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                        <div>
                            <strong>{{ $role->name }}</strong>
                            @if($role->description)
                                <br>
                                <small class="text-muted">{{ $role->description }}</small>
                            @endif
                        </div>
                        <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                @empty
                    <p class="text-muted">This permission is not assigned to any roles</p>
                @endforelse
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('permissions.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@endsection