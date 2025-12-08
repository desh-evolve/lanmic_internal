<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequisitionApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of all requisitions.
     */
    public function index(Request $request)
    {
        $query = Requisition::with(['user', 'department', 'subDepartment', 'division', 'items'])
            ->where('status', 'active');

        // Filter by approve_status
        if ($request->has('approve_status') && $request->approve_status != '') {
            $query->where('approve_status', $request->approve_status);
        }
        if ($request->has('clear_status') && $request->clear_status != '') {
            $query->where('clear_status', $request->clear_status);
        }

        $requisitions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.requisitions.index', compact('requisitions'));
    }

    /**
     * Display the specified requisition.
     */
    public function show(Requisition $requisition)
    {
        $requisition->load([
            'department', 
            'subDepartment', 
            'division', 
            'items.issuedItems', 
            'purchaseOrderItems',
            'user', 
            'approvedBy'
        ]);
        
        return view('admin.requisitions.show', compact('requisition'));
    }

    /**
     * Approve the requisition.
     */
    public function approve(Request $request, Requisition $requisition)
    {
        if ($requisition->approve_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending requisitions can be approved.');
        }

        $requisition->update([
            'approve_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.requisitions.show', $requisition->id)
            ->with('success', 'Requisition approved successfully. You can now issue items.');
    }

    /**
     * Reject the requisition.
     */
    public function reject(Request $request, Requisition $requisition)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($requisition->approve_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending requisitions can be rejected.');
        }

        // Use database transaction to ensure data consistency
        DB::beginTransaction();
        
        try {
            // Update requisition status
            $requisition->update([
                'approve_status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
                'updated_by' => Auth::id(),
            ]);

            // Update all related requisition items status to 'rejected'
            $requisition->allItems()->update([
                'status' => 'rejected',
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('admin.requisitions.show', $requisition->id)
                ->with('success', 'Requisition rejected.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to reject requisition. Please try again.');
        }
    }

    /**
     * Show issue items page.
     */
    public function issueItemsForm(Requisition $requisition)
    {
        if ($requisition->approve_status !== 'approved') {
            return redirect()->route('admin.requisitions.show', $requisition->id)
                ->with('error', 'Only approved requisitions can have items issued.');
        }

        $requisition->load(['items.issuedItems', 'department', 'subDepartment', 'division', 'user']);
        
        return view('admin.requisitions.issue-items', compact('requisition'));
    }

    /**
     * Issue items to requisition.
     */
    public function issueItems(Request $request, Requisition $requisition)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.requisition_item_id' => 'required|exists:requisition_items,id',
            'items.*.issued_quantity' => 'required|integer|min:1',
        ]);

        if ($requisition->approve_status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Only approved requisitions can have items issued.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $requisitionItem = RequisitionItem::find($item['requisition_item_id']);
                
                // Validate that we're not issuing more than requested
                $alreadyIssued = $requisitionItem->issuedItems()->sum('issued_quantity');
                $remainingToIssue = $requisitionItem->quantity - $alreadyIssued;
                
                if ($item['issued_quantity'] > $remainingToIssue) {
                    throw new \Exception("Cannot issue more than remaining quantity for item: {$requisitionItem->item_name}");
                }

                $unitPrice = $requisitionItem->unit_price;
                $totalPrice = $unitPrice * $item['issued_quantity'];

                RequisitionIssuedItem::create([
                    'requisition_id' => $requisition->id,
                    'requisition_item_id' => $requisitionItem->id,
                    'item_code' => $requisitionItem->item_code,
                    'item_name' => $requisitionItem->item_name,
                    'item_category' => $requisitionItem->item_category,
                    'unit' => $requisitionItem->unit,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'issued_quantity' => $item['issued_quantity'],
                    'issued_by' => Auth::id(),
                    'issued_at' => now(),
                    'status' => 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            // Check if all items are fully issued
            $allItemsIssued = true;
            foreach ($requisition->items as $item) {
                if (!$item->isFullyIssued()) {
                    $allItemsIssued = false;
                    break;
                }
            }

            // If all items issued, update clear_status
            if ($allItemsIssued) {
                $requisition->update([
                    'clear_status' => 'cleared',
                    'cleared_by' => Auth::id(),
                    'cleared_at' => now(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.requisitions.show', $requisition->id)
                ->with('success', 'Items issued successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to issue items: ' . $e->getMessage())
                ->withInput();
        }
    }
}