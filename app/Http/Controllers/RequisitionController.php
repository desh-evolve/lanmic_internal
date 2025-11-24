<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the user's requisitions.
     */
    public function index()
    {
        $requisitions = Requisition::where('user_id', Auth::id())
            ->with(['department', 'subDepartment', 'division', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('requisitions.index', compact('requisitions'));
    }

    /**
     * Show the form for creating a new requisition.
     */
    public function create()
    {
        $departments = Department::active()->get();
        $items = $this->getItemsFromJson();
        
        return view('requisitions.create', compact('departments', 'items'));
    }

    /**
     * Store a newly created requisition in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'nullable|exists:sub_departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'purpose' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required|string',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create requisition
            $requisition = Requisition::create([
                'requisition_number' => Requisition::generateRequisitionNumber(),
                'user_id' => Auth::id(),
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'division_id' => $request->division_id,
                'purpose' => $request->purpose,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Create requisition items
            foreach ($request->items as $item) {
                $unitPrice = $item['unit_price'] ?? 0;
                $quantity = $item['quantity'];
                $totalPrice = $unitPrice * $quantity;

                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'item_category' => $item['item_category'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'specifications' => $item['specifications'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('requisitions.show', $requisition->id)
                ->with('success', 'Requisition created successfully. Requisition Number: ' . $requisition->requisition_number);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create requisition. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified requisition.
     */
    public function show(Requisition $requisition)
    {
        // Check if user owns this requisition or is admin
        if ($requisition->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $requisition->load(['department', 'subDepartment', 'division', 'items', 'user', 'approvedBy']);
        
        return view('requisitions.show', compact('requisition'));
    }

    /**
     * Show the form for editing the specified requisition.
     */
    public function edit(Requisition $requisition)
    {
        // Only allow editing if requisition is pending and user owns it
        if ($requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->status !== 'pending') {
            return redirect()->route('requisitions.show', $requisition->id)
                ->with('error', 'Cannot edit a requisition that has been ' . $requisition->status);
        }

        $departments = Department::active()->get();
        $subDepartments = SubDepartment::active()->get();
        $divisions = Division::active()->get();
        $items = $this->getItemsFromJson();
        
        return view('requisitions.edit', compact('requisition', 'departments', 'subDepartments', 'divisions', 'items'));
    }

    /**
     * Update the specified requisition in storage.
     */
    public function update(Request $request, Requisition $requisition)
    {
        // Only allow editing if requisition is pending and user owns it
        if ($requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->status !== 'pending') {
            return redirect()->route('requisitions.show', $requisition->id)
                ->with('error', 'Cannot edit a requisition that has been ' . $requisition->status);
        }

        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'nullable|exists:sub_departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'purpose' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required|string',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update requisition
            $requisition->update([
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'division_id' => $request->division_id,
                'purpose' => $request->purpose,
                'notes' => $request->notes,
            ]);

            // Delete old items and create new ones
            $requisition->items()->delete();

            foreach ($request->items as $item) {
                $unitPrice = $item['unit_price'] ?? 0;
                $quantity = $item['quantity'];
                $totalPrice = $unitPrice * $quantity;

                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'item_category' => $item['item_category'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'specifications' => $item['specifications'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('requisitions.show', $requisition->id)
                ->with('success', 'Requisition updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update requisition. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified requisition from storage.
     */
    public function destroy(Requisition $requisition)
    {
        // Only allow deletion if requisition is pending and user owns it
        if ($requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->status !== 'pending') {
            return redirect()->route('requisitions.index')
                ->with('error', 'Cannot delete a requisition that has been ' . $requisition->status);
        }

        $requisition->delete();

        return redirect()->route('requisitions.index')
            ->with('success', 'Requisition deleted successfully.');
    }

    /**
     * Get sub-departments based on department.
     */

    public function getSubDepartments($departmentId)
    {
        $subDepartments = SubDepartment::query()
            ->where('status', 'active')  // only active sub-departments
            ->whereHas('departments', function ($query) use ($departmentId) {
                $query->where('departments.id', $departmentId)
                    ->where('department_sub_department.status', 'active'); // pivot status
            })
            ->select('sub_departments.id', 'sub_departments.name', 'sub_departments.short_code')
            ->get();

        return response()->json($subDepartments);
    }

    /**
     * Get divisions based on sub-department.
     */
    public function getDivisions($subDepartmentId)
    {
        $divisions = Division::query()
            ->where('status', 'active')
            ->whereHas('subDepartments', function ($query) use ($subDepartmentId) {
                $query->where('sub_departments.id', $subDepartmentId)
                      ->where('division_sub_department.status', 'active'); // pivot table status
            })
            ->select('divisions.id', 'divisions.name', 'divisions.short_code')
            ->get();
    
        return response()->json($divisions);
    }

    /**
     * Get items from JSON file.
     */
    private function getItemsFromJson()
    {
        if (Storage::exists('ex_items.json')) {
            $json = Storage::get('ex_items.json');
            return json_decode($json, true);
        }
        
        return [];
    }
}