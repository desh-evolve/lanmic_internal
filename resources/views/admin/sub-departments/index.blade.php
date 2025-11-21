@extends('layouts.admin')

@section('title', 'Sub-Department Management')
@section('page-title', 'Sub-Department Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item active">Sub-Departments</li>
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
                <h3 class="card-title">Sub-Departments List</h3>
                <div class="card-tools">
                    <a href="{{ route('sub-departments.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Sub-Department
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Short Code</th>
                            <th>Description</th>
                            <th>Departments</th>
                            <th>Divisions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subDepartments as $subDepartment)
                        <tr>
                            <td>{{ $subDepartment->id }}</td>
                            <td><strong>{{ $subDepartment->name }}</strong></td>
                            <td>
                                @if($subDepartment->short_code)
                                    <span class="badge badge-secondary">{{ $subDepartment->short_code }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ Str::limit($subDepartment->description, 50) ?? '-' }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $subDepartment->departments_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $subDepartment->divisions_count }}</span>
                            </td>
                            <td>
                                @if($subDepartment->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sub-departments.show', $subDepartment->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sub-departments.edit', $subDepartment->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sub-departments.destroy', $subDepartment->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this sub-department?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No sub-departments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $subDepartments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection