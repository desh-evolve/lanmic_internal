<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Sage300Service;
use App\Services\ItemAvailabilityService;

class RequisitionApprovalController extends Controller
{
    protected $sage300Service;
    
    public function __construct(Sage300Service $sage300Service)
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
        $this->sage300Service = $sage300Service;
    }

    /**
     * Display a listing of all requisitions.
     */
    public function index(Request $request)
    {
        $query = Requisition::with(['user', 'department', 'subDepartment', 'division', 'items'])
            ->where('status', 'active');

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

        DB::beginTransaction();
        
        try {
            $requisition->update([
                'approve_status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
                'updated_by' => Auth::id(),
            ]);

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
    public function issueItemsForm(Requisition $requisition, ItemAvailabilityService $availabilityService)
    {
        if ($requisition->approve_status !== 'approved') {
            return redirect()->route('admin.requisitions.show', $requisition->id)
                ->with('error', 'Only approved requisitions can have items issued.');
        }

        $requisition->load(['items.issuedItems', 'department', 'subDepartment', 'division', 'user']);
        
        // Add available quantities and locations for each item
        foreach ($requisition->items as $item) {
            try {
                // Get item locations from SAGE300
                $locations = $this->sage300Service->getItemLocations($item->item_code);
                $item->locations = $locations;
                $itemRequestedLocation = $this->sage300Service->getLocation($item->location_code);
                $item->location_name = $itemRequestedLocation['Name'];
                // Calculate total stock across all locations
                $totalStock = collect($locations)->sum('quantity');
                $pendingQuantity = $availabilityService->getPendingQuantity($item->item_code, $requisition->id);
                
                $item->available_quantity = max(0, $totalStock - $pendingQuantity);
                $item->stock_quantity = $totalStock;
                $item->pending_quantity = $pendingQuantity;
            } catch (\Exception $e) {
                $item->locations = [];
                $item->available_quantity = 0;
                $item->stock_quantity = 0;
                $item->pending_quantity = 0;
            }
        }
        
        return view('admin.requisitions.issue-items', compact('requisition'));
    }

    /**
     * Issue items to requisition.
     */
    public function issueItems(Request $request, Requisition $requisition)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.locations' => 'required|array|min:1',
            'items.*.locations.*.location_code' => 'required|string',
            'items.*.locations.*.issued_quantity' => 'required|integer|min:1',
            'items.*.locations.*.requisition_item_id' => 'required|exists:requisition_items,id',
            'items.*.locations.*.notes' => 'nullable|string',
        ]);

        if ($requisition->approve_status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Only approved requisitions can have items issued.');
        }

        DB::beginTransaction();
        try {
            $issueDataForSage = []; // Store data for SAGE API calls
            
            foreach ($request->items as $itemData) {
                // Group by requisition_item_id to validate per item
                $locationsByItem = collect($itemData['locations'])->groupBy('requisition_item_id');
                
                foreach ($locationsByItem as $requisitionItemId => $locations) {
                    $requisitionItem = RequisitionItem::find($requisitionItemId);
                    
                    // Calculate total quantity being issued for this item
                    $totalIssuingQuantity = $locations->sum('issued_quantity');
                    
                    // Validate that we're not issuing more than requested
                    $alreadyIssued = $requisitionItem->issuedItems()->sum('issued_quantity');
                    $remainingToIssue = $requisitionItem->quantity - $alreadyIssued;
                    
                    if ($totalIssuingQuantity > $remainingToIssue) {
                        throw new \Exception("Cannot issue more than remaining quantity for item: {$requisitionItem->item_name}. Remaining: {$remainingToIssue}, Attempting: {$totalIssuingQuantity}");
                    }

                    // Validate against location stock
                    foreach ($locations as $locationData) {
                        $locationStock = $this->sage300Service->getLocationQuantity(
                            $requisitionItem->item_code, 
                            $locationData['location_code']
                        );
                        
                        if ($locationData['issued_quantity'] > $locationStock) {
                            throw new \Exception("Insufficient stock at location {$locationData['location_code']} for item: {$requisitionItem->item_name}. Available: {$locationStock}, Requested: {$locationData['issued_quantity']}");
                        }
                    }

                    // Prepare data for SAGE API calls (don't create DB records yet)
                    foreach ($locations as $locationData) {
                        $issueDataForSage[] = [
                            'requisition_item' => $requisitionItem,
                            'location_data' => $locationData,
                            'requisition_number' => $requisition->requisition_number,
                        ];
                    }
                }
            }

            // All validations passed, now post to SAGE300 and create DB records
            $successfulIssues = [];
            $failedIssues = [];
            
            foreach ($issueDataForSage as $issueData) {
                $requisitionItem = $issueData['requisition_item'];
                $locationData = $issueData['location_data'];
                
                try {
                    // Post to SAGE300 API first
                    $sage300Response = $this->sage300Service->postAdjustment([
                        'requisition_number' => $issueData['requisition_number'],
                        'description' => "Issue for {$requisitionItem->item_name}",
                        'item_code' => $requisitionItem->item_code,
                        'item_name' => $requisitionItem->item_name,
                        'location_code' => $locationData['location_code'],
                        'quantity' => $locationData['issued_quantity'],
                        'unit' => $requisitionItem->unit,
                        'notes' => $locationData['notes'] ?? null,
                    ]);
                    
                    // Only create DB record if SAGE300 post was successful
                    if ($sage300Response['success']) {
                        $issuedItem = RequisitionIssuedItem::create([
                            'requisition_id' => $requisition->id,
                            'requisition_item_id' => $requisitionItem->id,
                            'item_code' => $requisitionItem->item_code,
                            'item_name' => $requisitionItem->item_name,
                            'item_category' => $requisitionItem->item_category,
                            'unit' => $requisitionItem->unit,
                            'unit_price' => $sage300Response['unit_price'],
                            'total_price' => $sage300Response['unit_price'] * $locationData['issued_quantity'],
                            'issued_quantity' => $locationData['issued_quantity'],
                            'location_code' => $locationData['location_code'],
                            'reference_number_1' => $sage300Response['reference_number_1'],
                            'reference_number_2' => $sage300Response['reference_number_2'],
                            'notes' => $locationData['notes'] ?? null,
                            'issued_by' => Auth::id(),
                            'issued_at' => now(),
                            'status' => 'active',
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);
                        
                        $successfulIssues[] = [
                            'item' => $requisitionItem->item_name,
                            'location' => $locationData['location_code'],
                            'quantity' => $locationData['issued_quantity'],
                        ];
                    } else {
                        $failedIssues[] = [
                            'item' => $requisitionItem->item_name,
                            'location' => $locationData['location_code'],
                            'quantity' => $locationData['issued_quantity'],
                            'error' => $sage300Response['error'] ?? 'Unknown error',
                        ];
                    }
                    
                } catch (\Exception $e) {
                    \Log::error("Failed to process issue for item {$requisitionItem->item_code}: " . $e->getMessage());
                    $failedIssues[] = [
                        'item' => $requisitionItem->item_name,
                        'location' => $locationData['location_code'],
                        'quantity' => $locationData['issued_quantity'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // If all failed, rollback
            if (empty($successfulIssues)) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'All items failed to issue. Please check logs for details.')
                    ->withInput();
            }

            // Check if all items are fully issued
            $requisition->refresh();
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

            // Prepare success/warning message
            $message = count($successfulIssues) . ' item(s) issued successfully.';
            if (!empty($failedIssues)) {
                $message .= ' ' . count($failedIssues) . ' item(s) failed. Please check and try again.';
            }

            return redirect()->route('admin.requisitions.show', $requisition->id)
                ->with(empty($failedIssues) ? 'success' : 'warning', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to issue items: ' . $e->getMessage())
                ->withInput();
        }
    }
}