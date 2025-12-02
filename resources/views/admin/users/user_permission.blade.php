@extends('layouts.admin')

@section('title', 'User Permission')
@section('page-title', 'User Permission')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">User Permission</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Permissions for: <strong>{{ $user->name }}</strong></h3>
                <div class="card-tools">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>

            <form action="{{ route('users.update_permission', $user->id) }}" method="POST">
                @csrf

                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-success" id="checkAll">
                            <i class="fas fa-check-square"></i> Check All
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" id="uncheckAll">
                            <i class="fas fa-square"></i> Uncheck All
                        </button>
                    </div>

                    @forelse($permissions as $module => $modulePermissions)
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-folder mr-2"></i>
                                    {{ ucfirst($module) }} Module
                                </h5>
                                <div>
                                    <button type="button" class="btn btn-sm btn-light check-module" data-module="{{ $module }}">
                                        <i class="fas fa-check"></i> Check All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-light uncheck-module" data-module="{{ $module }}">
                                        <i class="fas fa-times"></i> Uncheck All
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th width="30%">Permission</th>
                                        <th width="45%">Description</th>
                                        <th width="15%" class="text-center">Active</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modulePermissions as $permission)
                                    <tr>
                                        <td>{{ $permission->id }}</td>
                                        <td>
                                            <i class="fas fa-key text-muted mr-1"></i>
                                            {{ $permission->name }}
                                        </td>
                                        <td>{{ $permission->description ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input 
                                                    type="checkbox" 
                                                    class="custom-control-input permission-checkbox module-{{ $module }}" 
                                                    name="permissions[]" 
                                                    value="{{ $permission->id }}" 
                                                    id="permission_{{ $permission->id }}"
                                                    {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}
                                                >
                                                <label class="custom-control-label" for="permission_{{ $permission->id }}"></label>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No permissions found.
                    </div>
                    @endforelse
                </div>

                <div class="card-footer d-flex justify-content-end" style="position: sticky; bottom: 0; background: white; z-index: 10;">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save"></i> Save Permissions
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Check All (Global)
    document.getElementById('checkAll').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    // Uncheck All (Global)
    document.getElementById('uncheckAll').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Check All per Module
    document.querySelectorAll('.check-module').forEach(button => {
        button.addEventListener('click', function() {
            const module = this.getAttribute('data-module');
            document.querySelectorAll('.module-' + module).forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    });

    // Uncheck All per Module
    document.querySelectorAll('.uncheck-module').forEach(button => {
        button.addEventListener('click', function() {
            const module = this.getAttribute('data-module');
            document.querySelectorAll('.module-' + module).forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    });
</script>
@endpush