<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::withCount('subDepartments')->paginate(10);
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subDepartments = SubDepartment::active()->get();
        return view('admin.departments.create', compact('subDepartments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments',
            'short_code' => 'nullable|string|max:50|unique:departments',
            'description' => 'nullable|string',
            'status' => 'string|max:11',
            'sub_departments' => 'nullable|array',
            'sub_departments.*' => 'exists:sub_departments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $department = Department::create([
            'name' => $request->name,
            'short_code' => $request->short_code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        if ($request->has('sub_departments')) {
            $department->subDepartments()->attach($request->sub_departments);
        }

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $department->load('subDepartments.divisions');
        return view('admin.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $subDepartments = SubDepartment::active()->get();
        $departmentSubDepartments = $department->subDepartments->pluck('id')->toArray();
        return view('admin.departments.edit', compact('department', 'subDepartments', 'departmentSubDepartments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'short_code' => 'nullable|string|max:50|unique:departments,short_code,' . $department->id,
            'description' => 'nullable|string',
            'status' => 'string|max:11',
            'sub_departments' => 'nullable|array',
            'sub_departments.*' => 'exists:sub_departments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $department->update([
            'name' => $request->name,
            'short_code' => $request->short_code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        $department->subDepartments()->sync($request->sub_departments ?? []);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->subDepartments()->detach();
        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}