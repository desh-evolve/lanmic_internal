@extends('layouts.admin')

@section('title', 'Create Sub-Department')
@section('page-title', 'Create Sub-Department')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sub-departments.index') }}">Sub-Departments</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sub-Department Information</h3>
            </div>
            <form action="{{ route('sub-departments.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Sub-Department Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Recruitment">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="short_code">Short Code <small class="text-muted">(Optional)</small></label>
                        <input type="text" class="form-control @error('short_code') is-invalid @enderror" 
                               id="short_code" name="short_code" value="{{ old('short_code') }}" placeholder="e.g., REC">
                        @error('short_code')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" placeholder="Brief description of the sub-department">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>Assign to Departments</label>
                        @error('departments')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="row">
                            @forelse($departments as $department)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="departments[]" 
                                           value="{{ $department->id }}" id="dept{{ $department->id }}"
                                           {{ in_array($department->id, old('departments', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dept{{ $department->id }}">
                                        {{ $department->name }}
                                        @if($department->short_code)
                                            <span class="badge badge-secondary">{{ $department->short_code }}</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted">No departments available. <a href="{{ route('departments.create') }}">Create one</a></p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>Assign Divisions</label>
                        @error('divisions')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="row">
                            @forelse($divisions as $division)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="divisions[]" 
                                           value="{{ $division->id }}" id="div{{ $division->id }}"
                                           {{ in_array($division->id, old('divisions', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="div{{ $division->id }}">
                                        {{ $division->name }}
                                        @if($division->short_code)
                                            <span class="badge badge-secondary">{{ $division->short_code }}</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted">No divisions available. <a href="{{ route('divisions.create') }}">Create one</a></p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Sub-Department
                    </button>
                    <a href="{{ route('sub-departments.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection