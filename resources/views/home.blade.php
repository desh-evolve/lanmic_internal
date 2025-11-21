@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            @permission('view-users')
            <a href="{{ route('users.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
            @else
            <div class="small-box-footer">&nbsp;</div>
            @endpermission
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \App\Models\Role::count() }}</h3>
                <p>Total Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tag"></i>
            </div>
            @permission('view-roles')
            <a href="{{ route('roles.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
            @else
            <div class="small-box-footer">&nbsp;</div>
            @endpermission
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Models\Permission::count() }}</h3>
                <p>Total Permissions</p>
            </div>
            <div class="icon">
                <i class="fas fa-lock"></i>
            </div>
            @permission('view-permissions')
            <a href="{{ route('permissions.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
            @else
            <div class="small-box-footer">&nbsp;</div>
            @endpermission
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ Auth::user()->roles->count() }}</h3>
                <p>My Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="small-box-footer">&nbsp;</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Welcome, {{ Auth::user()->name }}!</h3>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                <p><strong>Your Roles:</strong></p>
                @foreach(Auth::user()->roles as $role)
                    <span class="badge badge-info mr-1">{{ $role->name }}</span>
                @endforeach
                
                <p class="mt-3"><strong>Your Permissions:</strong></p>
                @php
                    $userPermissions = Auth::user()->roles->flatMap->permissions->unique('id');
                @endphp
                <div class="row">
                    @foreach($userPermissions as $permission)
                        <div class="col-md-6">
                            <small><i class="fas fa-check text-success"></i> {{ $permission->name }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Links</h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @permission('view-users')
                    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-users text-info"></i> Manage Users
                    </a>
                    @endpermission

                    @permission('view-roles')
                    <a href="{{ route('roles.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-tag text-success"></i> Manage Roles
                    </a>
                    @endpermission

                    @permission('view-permissions')
                    <a href="{{ route('permissions.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-lock text-warning"></i> Manage Permissions
                    </a>
                    @endpermission

                    @if(!Auth::user()->hasAnyPermission(['view-users', 'view-roles', 'view-permissions']))
                    <div class="list-group-item">
                        <p class="text-muted mb-0">No administrative access available</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection