<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\PurchaseOrderItem;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\Division;
use App\Services\ItemAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequisitionController extends Controller
{
    protected $itemAvailabilityService;

    public function __construct(ItemAvailabilityService $itemAvailabilityService)
    {
        $this->middleware('auth');
        $this->itemAvailabilityService = $itemAvailabilityService;
    }

    /**
     * Display a listing of the user's requisitions.
     */
    public function index()
    {
        $requisitions = Requisition::where('user_id', Auth::id())
            ->where('status', 'active')
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
        $items = $this->itemAvailabilityService->getItemsWithAvailability();
        
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
            'requisition_items.*.quantity' => 'required|integer|min:1',
            'requisition_items.*.unit_price' => 'nullable|numeric|min:0',
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
                $unitPrice = $item['unit_price'] ?? 0;
                $quantity = $item['quantity'];
                $totalPrice = $unitPrice * $quantity;

                // Insert into requisition_items (all items)
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
                    'status' => 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                // Check availability and insert into purchase_order_items if needed
                $split = $this->itemAvailabilityService->splitAvailableAndPO($item['item_code'], $quantity, $requisition->id);
                
                if ($split['needs_po'] > 0) {
                    $poTotalPrice = $unitPrice * $split['needs_po'];
                    
                    PurchaseOrderItem::create([
                        'requisition_id' => $requisition->id,
                        'item_code' => $item['item_code'],
                        'item_name' => $item['item_name'],
                        'item_category' => $item['item_category'] ?? null,
                        'unit' => $item['unit'] ?? null,
                        'unit_price' => $unitPrice,
                        'total_price' => $poTotalPrice,
                        'quantity' => $split['needs_po'],
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
     * Display the specified requisition.
     */
    public function show(Requisition $requisition)
    {
        // Check if user owns this requisition or is admin
        if ((int)$requisition->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $requisition->load(['department', 'subDepartment', 'division', 'items', 'purchaseOrderItems', 'user', 'approvedBy']);
        
        return view('requisitions.show', compact('requisition'));
    }

    /**
     * Show the form for editing the specified requisition.
     */
    public function edit(Requisition $requisition)
    {
        // Only allow editing if requisition is pending and user owns it
        if ((int)$requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->approve_status !== 'pending') {
            return redirect()->route('requisitions.show', $requisition->id)
                ->with('error', 'Cannot edit a requisition that has been ' . $requisition->approve_status);
        }

        $departments = Department::active()->get();
        $subDepartments = SubDepartment::active()->get();
        $divisions = Division::active()->get();
        $items = $this->itemAvailabilityService->getItemsWithAvailability();
        
        return view('requisitions.edit', compact('requisition', 'departments', 'subDepartments', 'divisions', 'items'));
        }

    /**
     * Update the specified requisition in storage.
     */
    public function update(Request $request, Requisition $requisition)
    {
        // Only allow editing if requisition is pending and user owns it
        if ((int)$requisition->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($requisition->approve_status !== 'pending') {
            return redirect()->route('requisitions.show', $requisition->id)
                ->with('error', 'Cannot edit a requisition that has been ' . $requisition->approve_status);
        }

        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'nullable|exists:sub_departments,id',
            'division_id' => 'nullable|exists:divisions,id',
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
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Delete old items and PO items
            $requisition->allItems()->update(['status' => 'delete', 'updated_by' => Auth::id()]);
            $requisition->purchaseOrderItems()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $unitPrice = $item['unit_price'] ?? 0;
                $quantity = $item['quantity'];
                $totalPrice = $unitPrice * $quantity;

                // Insert into requisition_items
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
                    'status' => 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                // Check availability and insert into purchase_order_items if needed
                $split = $this->itemAvailabilityService->splitAvailableAndPO($item['item_code'], $quantity);
                
                if ($split['needs_po'] > 0) {
                    $poTotalPrice = $unitPrice * $split['needs_po'];
                    
                    PurchaseOrderItem::create([
                        'requisition_id' => $requisition->id,
                        'item_code' => $item['item_code'],
                        'item_name' => $item['item_name'],
                        'item_category' => $item['item_category'] ?? null,
                        'unit' => $item['unit'] ?? null,
                        'unit_price' => $unitPrice,
                        'total_price' => $poTotalPrice,
                        'quantity' => $split['needs_po'],
                        'status' => 'pending',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
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
    public function getItemAvailability($itemCode)
    {
        $availableQuantity = $this->itemAvailabilityService->getAvailableQuantity($itemCode);
        $pendingQuantity = $this->itemAvailabilityService->getPendingQuantity($itemCode);

        return response()->json([
            'item_code' => $itemCode,
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