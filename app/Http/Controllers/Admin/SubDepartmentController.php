<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubDepartment;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SubDepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
        // $this->middleware('permission:view-sub-departments')->only(['index', 'show']);
        // $this->middleware('permission:create-sub-departments')->only(['create', 'store']);
        // $this->middleware('permission:edit-sub-departments')->only(['edit', 'update']);
        // $this->middleware('permission:delete-sub-departments')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subDepartments = SubDepartment::active()->withCount(['departments', 'divisions'])->paginate(10);
        return view('admin.sub-departments.index', compact('subDepartments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::active()->get();
        $divisions = Division::active()->get();
        return view('admin.sub-departments.create', compact('departments', 'divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sub_departments',
            'short_code' => 'nullable|string|max:50|unique:sub_departments',
            'description' => 'nullable|string',
            'status' => 'string|max:11',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'divisions' => 'nullable|array',
            'divisions.*' => 'exists:divisions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $subDepartment = SubDepartment::create([
            'name' => $request->name,
            'short_code' => $request->short_code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        if ($request->has('departments')) {
            $subDepartment->departments()->attach($request->departments);
        }

        if ($request->has('divisions')) {
            $subDepartment->divisions()->attach($request->divisions);
        }

        return redirect()->route('sub-departments.index')
            ->with('success', 'Sub Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubDepartment $subDepartment)
    {
        $subDepartment->load('departments', 'divisions');
        return view('admin.sub-departments.show', compact('subDepartment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubDepartment $subDepartment)
    {
        $departments = Department::active()->get();
        $divisions = Division::active()->get();
        $subDepartmentDepartments = $subDepartment->departments->pluck('id')->toArray();
        $subDepartmentDivisions = $subDepartment->divisions->pluck('id')->toArray();
        return view('admin.sub-departments.edit', compact('subDepartment', 'departments', 'divisions', 'subDepartmentDepartments', 'subDepartmentDivisions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubDepartment $subDepartment)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sub_departments,name,' . $subDepartment->id,
            'short_code' => 'nullable|string|max:50|unique:sub_departments,short_code,' . $subDepartment->id,
            'description' => 'nullable|string',
            'status' => 'string|max:11',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'divisions' => 'nullable|array',
            'divisions.*' => 'exists:divisions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $subDepartment->update([
            'name' => $request->name,
            'short_code' => $request->short_code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        $subDepartment->departments()->sync($request->departments ?? []);
        $subDepartment->divisions()->sync($request->divisions ?? []);

        return redirect()->route('sub-departments.index')
            ->with('success', 'Sub Department updated successfully.');
    }

    public function destroy(Request $request, SubDepartment $subDepartment)
    {
        // Update status in the department_sub_department pivot table
        $subDepartment->departments()->updateExistingPivot(
            $subDepartment->departments()->pluck('departments.id'),
            ['status' => 'delete']
        );

        // Update status in the division_sub_department pivot table
        $subDepartment->divisions()->updateExistingPivot(
            $subDepartment->divisions()->pluck('divisions.id'),
            ['status' => 'delete']
        );

        // Update the sub_department itself
        $subDepartment->update(['status' => 'delete']);

        return redirect()->route('sub-departments.index')
            ->with('success', 'Sub Department deleted successfully.');
    }



    // public function destroy(Request $request, SubDepartment $subDepartment)
    // {
    //     DB::transaction(function () use ($subDepartment) {
    //         // Update all relationships in department_sub_department pivot
    //         DB::table('department_sub_department')
    //             ->where('sub_department_id', $subDepartment->id)
    //             ->update([
    //                 'status' => 'delete',
    //                 'updated_at' => now()
    //             ]);

    //         // Update all relationships in division_sub_department pivot
    //         DB::table('division_sub_department')
    //             ->where('sub_department_id', $subDepartment->id)
    //             ->update([
    //                 'status' => 'delete',
    //                 'updated_at' => now()
    //             ]);

    //         // Update the sub_department status
    //         $subDepartment->update(['status' => 'delete']);
    //     });

    //     return redirect()->route('sub-departments.index')
    //         ->with('success', 'Sub Department deleted successfully.');
    // }
}
