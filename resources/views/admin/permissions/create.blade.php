@extends('layouts.admin')

@section('title', 'Create Permission')
@section('page-title', 'Create Permission')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Information</h3>
            </div>
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., view-reports, edit-settings">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Use lowercase with hyphens (e.g., create-user, delete-post)</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" placeholder="Brief description of what this permission allows">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="icon fas fa-info"></i>
                        <strong>Note:</strong> After creating a permission, you need to assign it to roles in the Role Management section.
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Permission
                    </button>
                    <a href="{{ route('permissions.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Permission Naming Guide</h3>
            </div>
            <div class="card-body">
                <p><strong>Common Patterns:</strong></p>
                <ul>
                    <li><code>view-[resource]</code> - View access</li>
                    <li><code>create-[resource]</code> - Create new items</li>
                    <li><code>edit-[resource]</code> - Edit existing items</li>
                    <li><code>delete-[resource]</code> - Delete items</li>
                    <li><code>manage-[resource]</code> - Full control</li>
                </ul>
                <hr>
                <p><strong>Examples:</strong></p>
                <ul class="list-unstyled">
                    <li>✓ <code>view-dashboard</code></li>
                    <li>✓ <code>manage-users</code></li>
                    <li>✓ <code>create-reports</code></li>
                    <li>✓ <code>edit-settings</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection