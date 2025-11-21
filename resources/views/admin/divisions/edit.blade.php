@extends('layouts.admin')

@section('title', 'Edit Division')
@section('page-title', 'Edit Division')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('divisions.index') }}">Divisions</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Division Information</h3>
            </div>
            <form action="{{ route('divisions.update', $division->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Division Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $division->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="short_code">Short Code <small class="text-muted">(Optional)</small></label>
                        <input type="text" class="form-control @error('short_code') is-invalid @enderror" 
                               id="short_code" name="short_code" value="{{ old('short_code', $division->short_code) }}">
                        @error('short_code')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4">{{ old('description', $division->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', $division->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
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
                                           {{ in_array($subDepartment->id, old('sub_departments', $divisionSubDepartments)) ? 'checked' : '' }}>
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
                                <p class="text-muted">No sub-departments available.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Division
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