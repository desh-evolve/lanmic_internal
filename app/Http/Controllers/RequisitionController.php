<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\PurchaseOrderItem;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\Division;
use App\Services\ItemAvailabilityService;
use App\Services\Sage300Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequisitionController extends Controller
{
    protected $itemAvailabilityService;

    protected Sage300Service $sage300;

    public function __construct(ItemAvailabilityService $itemAvailabilityService, Sage300Service $sage300)
    {
        $this->middleware('auth');
        $this->itemAvailabilityService = $itemAvailabilityService;
        $this->sage300 = $sage300;
    }

    /**
     * Display requisitions created by the current user or by any colleague
     * who shares a department with them.
     */
    public function index()
    {
        $colleagueIds = Auth::user()->departmentColleagueIds();

        $requisitions = Requisition::whereIn('user_id', $colleagueIds)
            ->where('status', 'active')
            ->with(['department', 'subDepartment', 'division', 'items', 'user'])
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
        $items = $this->sage300->getItems();
        
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
            'notes' => 'nullable|string',
            'requisition_items' => 'required|array|min:1',
            'requisition_items.*.item_code' => 'required|string',
            'requisition_items.*.item_name' => 'required|string',
            'requisition_items.*.quantity' => ['required', 'numeric', 'min:0.0001', 'regex:/^\d+(\.\d{1,4})?$/'],
            'requisition_items.*.location_code' => 'required',
            'requisition_items.*.specifications' => 'nullable|string',
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
                'notes' => $request->notes,
                'approve_status' => 'pending',
                'clear_status' => 'pending',
                'status' => 'active',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Process each item
            foreach ($request->requisition_items as $item) {

                // Insert into requisition_items (all items)
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'item_category' => $item['item_category'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'quantity' => $item['quantity'],
                    'location_code' => $item['location_code'],
                    'specifications' => $item['specifications'] ?? null,
                    'status' => 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            if(!empty($request->purchase_order_items)){
                foreach ($request->purchase_order_items as $item) {
                    PurchaseOrderItem::create([
                        'requisition_id' => $requisition->id,
                        'item_code' => $item['item_code'],
                        'item_name' => $item['item_name'],
                        'item_category' => $item['item_category'] ?? null,
                        'unit' => $item['unit'] ?? null,
                        'location_code' => $item['location_code'],
                        'quantity' => $item['quantity'],
                        'status' => 'pending',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('requisitions.show', $requisition->id)
                ->with('success', 'Requisition created successfully. Requisition Number: ' . $requisition->requisition_number);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create requisition: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * True if the current user may VIEW a requisition: their own, admin role,
     * or one created by a colleague who shares a department with them.
     */
    private function canViewRequisition(Requisition $requisition): bool
    {
        $user = Auth::user();
        if ($user->hasPermission('full-access')) return true;
        return $user->departmentColleagueIds()->contains($requisition->user_id);
    }

    /**
     * Display the specified requisition.
     */
    public function show(Requisition $requisition)
    {
        if (!$this->canViewRequisition($requisition)) {
            abort(403, 'Unauthorized action.');
        }

        $requisition->load(['department', 'subDepartment', 'division', 'items', 'purchaseOrderItems', 'user', 'approvedBy']);
        
        foreach($requisition->items as $item){
            $location = $this->sage300->getLocation($item->location_code);
            $item->location_name = $location['Name'] ?? '-';
        }
        
        return view('requisitions.show', compact('requisition'));
    }


    /**
     * Show the form for editing a pending requisition.
     */
    public function edit(Requisition $requisition)
    {
        if ((int)$requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->approve_status !== 'pending') {
            return redirect()->route('requisitions.show', $requisition->id)
                ->with('error', 'Only pending requisitions can be edited.');
        }

        $requisition->load(['department', 'subDepartment', 'division', 'items']);
        $departments = \App\Models\Department::active()->get();
        $items = $this->sage300->getItems();

        return view('requisitions.edit', compact('requisition', 'departments', 'items'));
    }

    /**
     * Update a pending requisition.
     */
    public function update(Request $request, Requisition $requisition)
    {
        if ((int)$requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->approve_status !== 'pending') {
            return redirect()->route('requisitions.show', $requisition->id)
                ->with('error', 'Only pending requisitions can be edited.');
        }

        $validator = Validator::make($request->all(), [
            'department_id'                       => 'required|exists:departments,id',
            'sub_department_id'                   => 'nullable|exists:sub_departments,id',
            'division_id'                         => 'nullable|exists:divisions,id',
            'notes'                               => 'nullable|string',
            'requisition_items'                   => 'required|array|min:1',
            'requisition_items.*.item_code'       => 'required|string',
            'requisition_items.*.item_name'       => 'required|string',
            'requisition_items.*.quantity'        => ['required', 'numeric', 'min:0.0001', 'regex:/^\d+(\.\d{1,4})?$/'],
            'requisition_items.*.location_code'   => 'required',
            'requisition_items.*.specifications'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $requisition->update([
                'department_id'     => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'division_id'       => $request->division_id,
                'notes'             => $request->notes,
                'updated_by'        => Auth::id(),
            ]);

            // Remove existing items
            $requisition->allItems()->update(['status' => 'delete', 'updated_by' => Auth::id()]);

            // Re-create items
            foreach ($request->requisition_items as $itemData) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_code'      => $itemData['item_code'],
                    'item_name'      => $itemData['item_name'],
                    'item_category'  => $itemData['item_category'] ?? null,
                    'unit'           => $itemData['unit'] ?? null,
                    'quantity'       => $itemData['quantity'],
                    'location_code'  => $itemData['location_code'],
                    'specifications' => $itemData['specifications'] ?? null,
                    'status'         => 'active',
                    'created_by'     => Auth::id(),
                    'updated_by'     => Auth::id(),
                ]);
            }

            DB::commit();
            return redirect()->route('requisitions.show', $requisition->id)
                ->with('success', 'Requisition updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update requisition: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified requisition from storage.
     */
    public function destroy(Requisition $requisition)
    {
        // Only allow deletion if requisition is pending and user owns it
        if ((int)$requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->approve_status !== 'pending') {
            return redirect()->route('requisitions.index')
                ->with('error', 'Cannot delete a requisition that has been ' . $requisition->approve_status);
        }

        // Use database transaction to ensure data consistency
        DB::beginTransaction();
        
        try {
            // Update requisition status to 'delete'
            $requisition->update([
                'status' => 'delete',
                'updated_by' => Auth::id()
            ]);

            // Update all related requisition items status to 'deleted'
            $requisition->allItems()->update([
                'status' => 'delete',
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('requisitions.index')
                ->with('success', 'Requisition deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to delete requisition. Please try again.');
        }
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
     * Get item availability.
     */
    public function getItemAvailability($itemCode, $locationCode)
    {
        $availableQuantity = $this->itemAvailabilityService->getAvailableQuantity($itemCode, $locationCode);
        $pendingQuantity = $this->itemAvailabilityService->getPendingQuantity($itemCode);

        return response()->json([
            'item_code' => $itemCode,
            'location_code' => $locationCode,
            'available_quantity' => $availableQuantity,
            'pending_quantity' => $pendingQuantity,
            'is_available' => $availableQuantity > 0
        ]);
    }

    /**
     * Get pending approval items.
     */
    public function getPendingApprovalItems()
    {
        try {
            $pendingQuantity = $this->itemAvailabilityService->getAllPendingQuantity();
            
            return response()->json($pendingQuantity);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load pending approvals'
            ], 500);
        }
    }
}