@extends('layouts.admin')

@section('title', 'Create Division')
@section('page-title', 'Create Division')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('divisions.index') }}">Divisions</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Division Information</h3>
            </div>
            <form action="{{ route('divisions.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Division Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Sales Team A">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="short_code">Short Code <small class="text-muted">(Optional)</small></label>
                        <input type="text" class="form-control @error('short_code') is-invalid @enderror" 
                               id="short_code" name="short_code" value="{{ old('short_code') }}" placeholder="e.g., STA">
                        @error('short_code')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" placeholder="Brief description of the division">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input 
                                type="checkbox" 
                                class="custom-control-input" 
                                id="status" 
                                name="status" 
                                value="active"
                                {{ old('status', $division->status ?? 'active') === 'active' ? 'checked' : '' }}
                            >
                            <label class="custom-control-label" for="status">Active</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Assign to Sub-Departments</label>
                        @error('sub_departments')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="row">
                            @forelse($subDepartments as $subDepartment)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sub_departments[]" 
                                           value="{{ $subDepartment->id }}" id="sub_dept{{ $subDepartment->id }}"
                                           {{ in_array($subDepartment->id, old('sub_departments', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sub_dept{{ $subDepartment->id }}">
                                        {{ $subDepartment->name }}
                                        @if($subDepartment->short_code)
                                            <span class="badge badge-secondary">{{ $subDepartment->short_code }}</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted">No sub-departments available. <a href="{{ route('sub-departments.create') }}">Create one</a></p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Division
                    </button>
                    <a href="{{ route('divisions.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection