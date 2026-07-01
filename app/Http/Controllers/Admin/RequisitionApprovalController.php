<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use App\Models\User;
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
        // Access is enforced per-route via permission:* middleware (see routes/web.php).
        $this->sage300Service = $sage300Service;
    }

    /**
     * Display a listing of all requisitions.
     */
    public function index(Request $request)
    {
        $query = Requisition::with(['user', 'department', 'subDepartment', 'division', 'items'])
            ->where('status', 'active');

        if ($request->filled('approve_status')) {
            $query->where('approve_status', $request->approve_status);
        }
        if ($request->filled('clear_status')) {
            $query->where('clear_status', $request->clear_status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $requisitions = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::orderBy('name')->get();

        return view('admin.requisitions.index', compact('requisitions', 'users'));
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
                $item->location_name = $itemRequestedLocation['Name'] ?? $item->location_code;
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
            'items.*.locations.*.issued_quantity' => 'required|numeric|min:0.0001',
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

    /**
     * Manually force-clear a requisition regardless of issued qty.
     */
    public function forceClear(Request $request, Requisition $requisition)
    {
        if ($requisition->approve_status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Only approved requisitions can be cleared.');
        }

        if ($requisition->clear_status === 'cleared') {
            return redirect()->back()
                ->with('error', 'This requisition is already cleared.');
        }

        $request->validate([
            'clear_reason' => 'required|string|max:500',
        ]);

        $requisition->update([
            'clear_status' => 'cleared',
            'cleared_by'   => Auth::id(),
            'cleared_at'   => now(),
            'notes'        => ($requisition->notes ? $requisition->notes . "\n" : '')
                              . '[Force-cleared by ' . Auth::user()->name . ': ' . $request->clear_reason . ']',
            'updated_by'   => Auth::id(),
        ]);

        return redirect()->route('admin.requisitions.show', $requisition->id)
            ->with('success', 'Requisition has been force-cleared successfully.');
    }

    /**
     * Update requisition items (approver can edit quantities, add/remove items while pending).
     */
    public function updateItems(Request $request, Requisition $requisition)
    {
        if ($requisition->approve_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Items can only be edited while the requisition is pending.');
        }

        $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.item_code'         => 'required|string',
            'items.*.item_name'         => 'required|string',
            'items.*.quantity'          => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit'              => 'nullable|string',
            'items.*.item_category'     => 'nullable|string',
            'items.*.location_code'     => 'required|string',
            'items.*.specifications'    => 'nullable|string',
            'items.*.id'                => 'nullable|exists:requisition_items,id',
        ]);

        DB::beginTransaction();
        try {
            $submittedIds = collect($request->items)
                ->filter(fn($i) => !empty($i['id']))
                ->pluck('id')
                ->map('intval');

            // Soft-delete items that were removed
            $requisition->allItems()
                ->whereNotIn('id', $submittedIds->toArray())
                ->update(['status' => 'delete', 'updated_by' => Auth::id()]);

            // Update existing / create new items
            foreach ($request->items as $itemData) {
                if (!empty($itemData['id'])) {
                    // Update existing item
                    RequisitionItem::where('id', $itemData['id'])
                        ->where('requisition_id', $requisition->id)
                        ->update([
                            'quantity'       => $itemData['quantity'],
                            'specifications' => $itemData['specifications'] ?? null,
                            'status'         => 'active',
                            'updated_by'     => Auth::id(),
                        ]);
                } else {
                    // New item added by approver
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
            }

            DB::commit();
            return redirect()->route('admin.requisitions.show', $requisition->id)
                ->with('success', 'Requisition items updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update items: ' . $e->getMessage())
                ->withInput();
        }
    }
}