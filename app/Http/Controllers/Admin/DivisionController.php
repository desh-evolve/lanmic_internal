<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DivisionController extends Controller
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
        $divisions = Division::withCount('subDepartments')->paginate(10);
        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subDepartments = SubDepartment::active()->get();
        return view('admin.divisions.create', compact('subDepartments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:divisions',
            'short_code' => 'nullable|string|max:50|unique:divisions',
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

        $division = Division::create([
            'name' => $request->name,
            'short_code' => $request->short_code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        if ($request->has('sub_departments')) {
            $division->subDepartments()->attach($request->sub_departments);
        }

        return redirect()->route('divisions.index')
            ->with('success', 'Division created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {
        $division->load('subDepartments.departments');
        return view('admin.divisions.show', compact('division'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Division $division)
    {
        $subDepartments = SubDepartment::active()->get();
        $divisionSubDepartments = $division->subDepartments->pluck('id')->toArray();
        return view('admin.divisions.edit', compact('division', 'subDepartments', 'divisionSubDepartments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Division $division)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:divisions,name,' . $division->id,
            'short_code' => 'nullable|string|max:50|unique:divisions,short_code,' . $division->id,
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

        $division->update([
            'name' => $request->name,
            'short_code' => $request->short_code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        $division->subDepartments()->sync($request->sub_departments ?? []);

        return redirect()->route('divisions.index')
            ->with('success', 'Division updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $division->subDepartments()->detach();
        $division->delete();

        return redirect()->route('divisions.index')
            ->with('success', 'Division deleted successfully.');
    }
}