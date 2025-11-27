@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row">
        <!-- My Requisitions -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ Auth::user()->requisitions()->count() }}</h3>
                    <p>My Requisitions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <a href="{{ route('requisitions.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Pending Requisitions -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ Auth::user()->requisitions()->pending()->count() }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('requisitions.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Approved Requisitions -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ Auth::user()->requisitions()->approved()->count() }}</h3>
                    <p>Approved</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
                <a href="{{ route('requisitions.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        @if (Auth::user()->hasRole('admin'))
            <!-- Admin: Pending Approvals -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\Requisition::pending()->count() }}</h3>
                        <p>Need Approval</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <a href="{{ route('admin.requisitions.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @else
            <!-- Regular User: Rejected -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ Auth::user()->requisitions()->rejected()->count() }}</h3>
                        <p>Rejected</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                    <a href="{{ route('requisitions.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endif
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
                    @foreach (Auth::user()->roles as $role)
                        <span class="badge badge-info mr-1">{{ $role->name }}</span>
                    @endforeach

                    <p class="mt-3"><strong>Your Permissions:</strong></p>
                    @php
                        $userPermissions = Auth::user()->roles->flatMap->permissions->unique('id');
                    @endphp
                    <div class="row">
                        @foreach ($userPermissions as $permission)
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
                        <a href="{{ route('requisitions.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle text-primary"></i> Create New Requisition
                        </a>
                        <a href="{{ route('requisitions.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt text-info"></i> My Requisitions
                        </a>

                        @if (Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.requisitions.index') }}"
                                class="list-group-item list-group-item-action">
                                <i class="fas fa-clipboard-check text-warning"></i> Requisition Approvals
                            </a>
                        @endif

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
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (Auth::user()->hasRole('admin'))
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Requisitions Needing Approval</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Requisition #</th>
                                    <th>Requested By</th>
                                    <th>Department</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $pendingRequisitions = \App\Models\Requisition::pending()
                                        ->with(['user', 'department', 'items'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                @forelse($pendingRequisitions as $req)
                                    <tr>
                                        <td><strong>{{ $req->requisition_number }}</strong></td>
                                        <td>{{ $req->user->name }}</td>
                                        <td>{{ $req->department->name ?? '-' }}</td>
                                        <td><span class="badge badge-info">{{ $req->items->count() }}</span></td>
                                        <td>${{ number_format($req->items->sum('total_price'), 2) }}</td>
                                        <td>{{ $req->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="{{ route('admin.requisitions.show', $req->id) }}"
                                                class="btn btn-sm btn-primary">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No pending requisitions</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($pendingRequisitions->count() > 0)
                        <div class="card-footer">
                            <a href="{{ route('admin.requisitions.index') }}" class="btn btn-primary">View All
                                Requisitions</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
