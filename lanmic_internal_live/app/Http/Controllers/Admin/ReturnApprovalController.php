<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Models\GrnItem;
use App\Models\ScrapItem;
use App\Services\Sage300Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnApprovalController extends Controller
{
    protected Sage300Service $sage300;

    public function __construct(Sage300Service $sage300)
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
        $this->sage300 = $sage300;
    }

    /**
     * Display a listing of all returns.
     */
    public function index(Request $request)
    {
        $query = ReturnModel::with(['returnedBy', 'items', 'requisition'])
            ->where('status', '!=', 'delete');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $returns = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.returns.index', compact('returns'));
    }

    /**
     * Display the specified return.
     */
    public function show(ReturnModel $return)
    {
        $return->load([
            'items',
            'returnedBy',
            'grnItems',
            'scrapItems',
            'requisition.department'
        ]);
        
        // Add location names
        foreach ($return->items as $item) {
            $location = $this->sage300->getLocation($item->location_code);
            $item->location_name = $location['Name'] ?? $item->location_code;
        }
        
        return view('admin.returns.show', compact('return'));
    }

    /**
     * Show approve items form.
     */
    public function approveItemsForm(ReturnModel $return)
    {
        if ($return->status !== 'pending') {
            return redirect()->route('admin.returns.show', $return->id)
                ->with('error', 'Only pending returns can be processed.');
        }

        $return->load(['items.issuedItem', 'returnedBy', 'requisition']);
        
        // For each item, get current location name
        foreach ($return->items as $item) {
            // Get current location details
            $currentLocation = $this->sage300->getLocation($item->location_code);
            $item->location_name = $currentLocation['Name'] ?? $item->location_code;
        }
        
        return view('admin.returns.approve-items', compact('return'));
    }

    /**
     * Approve or reject return items with quantity split.
     */
    public function approveItems(Request $request, ReturnModel $return)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.return_item_id' => 'required|exists:return_items,id',
            'items.*.return_type' => 'required|in:used,same',
            'items.*.item_code' => 'required|string',
            'items.*.location_code' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.grn_quantity' => 'required|integer|min:0',
            'items.*.scrap_quantity' => 'required|integer|min:0',
            'items.*.admin_note' => 'nullable|string',
        ]);

        if ($return->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending returns can be processed.');
        }

        DB::beginTransaction();
        try {
            $processedItems = [];
            $failedItems = [];

            foreach ($request->items as $itemData) {
                $returnItem = ReturnItem::find($itemData['return_item_id']);
                
                if (!$returnItem || $returnItem->approve_status !== 'pending') {
                    continue; // Skip already processed items
                }
                
                $grnQty = (int)($itemData['grn_quantity'] ?? 0);
                $scrapQty = (int)($itemData['scrap_quantity'] ?? 0);
                $unitPrice = (float)($itemData['unit_price'] ?? 0);
                
                // Validate total quantity
                if (($grnQty + $scrapQty) != $returnItem->quantity) {
                    throw new \Exception("Total of GRN and Scrap quantities must equal the return quantity for item: {$returnItem->item_name}");
                }

                // Validate at least one quantity is greater than 0
                if ($grnQty == 0 && $scrapQty == 0) {
                    throw new \Exception("At least one quantity (GRN or Scrap) must be greater than 0 for item: {$returnItem->item_name}");
                }

                // Determine overall status
                if ($grnQty > 0 && $scrapQty == 0) {
                    $approveStatus = 'approved';
                } elseif ($scrapQty > 0 && $grnQty == 0) {
                    $approveStatus = 'rejected';
                } else {
                    $approveStatus = 'partial'; // Partial approval
                }

                // Update return item with admin's changes
                $returnItem->update([
                    'approve_status' => $approveStatus,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'return_type' => $itemData['return_type'],
                    'location_code' => $itemData['location_code'],
                    'item_code' => $itemData['item_code'], // Allow admin to change item
                    'updated_by' => Auth::id(),
                    'admin_note' => $itemData['admin_note'] ?? null,
                ]);

                // Process GRN if quantity > 0
                if ($grnQty > 0) {
                    try {
                        // Post to SAGE300 API for GRN (BothIncrease)
                        $sage300Response = $this->sage300->postGrnAdjustment([
                            'reference' => "Return #{$return->id} - GRN",
                            'description' => "GRN for {$returnItem->item_name}",
                            'item_code' => $itemData['item_code'],
                            'location_code' => $itemData['location_code'],
                            'quantity' => $grnQty,
                            'unit_price' => $unitPrice,
                            'notes' => $itemData['admin_note'] ?? '',
                        ]);
                        
                        // Only create DB record if SAGE300 post was successful
                        if ($sage300Response['success']) {
                            $grnTotalPrice = $sage300Response['unit_price'] * $grnQty;

                            GrnItem::create([
                                'return_id' => $return->id,
                                'return_item_id' => $returnItem->id,
                                'item_code' => $itemData['item_code'],
                                'item_name' => $returnItem->item_name,
                                'item_category' => $returnItem->item_category,
                                'unit' => $returnItem->unit,
                                'location_code' => $itemData['location_code'],
                                'unit_price' => $sage300Response['unit_price'],
                                'total_price' => $grnTotalPrice,
                                'grn_quantity' => $grnQty,
                                'reference_number_1' => $sage300Response['reference_number_1'],
                                'reference_number_2' => $sage300Response['reference_number_2'],
                                'processed_by' => Auth::id(),
                                'processed_at' => now(),
                                'status' => 'active',
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                            ]);
                            
                            $processedItems[] = [
                                'type' => 'GRN',
                                'item' => $returnItem->item_name,
                                'quantity' => $grnQty,
                            ];
                        } else {
                            $failedItems[] = [
                                'type' => 'GRN',
                                'item' => $returnItem->item_name,
                                'quantity' => $grnQty,
                                'error' => $sage300Response['error'] ?? 'Unknown error',
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to process GRN for item {$returnItem->item_code}: " . $e->getMessage());
                        $failedItems[] = [
                            'type' => 'GRN',
                            'item' => $returnItem->item_name,
                            'quantity' => $grnQty,
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                // Process Scrap if quantity > 0 (only DB record, no SAGE API)
                if ($scrapQty > 0) {
                    $scrapTotalPrice = $unitPrice * $scrapQty;

                    ScrapItem::create([
                        'return_id' => $return->id,
                        'return_item_id' => $returnItem->id,
                        'item_code' => $itemData['item_code'],
                        'item_name' => $returnItem->item_name,
                        'item_category' => $returnItem->item_category,
                        'unit' => $returnItem->unit,
                        'location_code' => $itemData['location_code'],
                        'unit_price' => $unitPrice,
                        'total_price' => $scrapTotalPrice,
                        'scrap_quantity' => $scrapQty,
                        'processed_by' => Auth::id(),
                        'processed_at' => now(),
                        'status' => 'active',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                    
                    $processedItems[] = [
                        'type' => 'Scrap',
                        'item' => $returnItem->item_name,
                        'quantity' => $scrapQty,
                    ];
                }
            }

            // If all failed, rollback
            if (empty($processedItems)) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'All items failed to process. Please check logs for details.')
                    ->withInput();
            }

            // Check if all items are processed
            $allProcessed = $return->items()->where('approve_status', 'pending')->count() === 0;
            
            if ($allProcessed) {
                $return->update([
                    'status' => 'cleared',
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            // Prepare success/warning message
            $message = count($processedItems) . ' item(s) processed successfully.';
            if (!empty($failedItems)) {
                $message .= ' ' . count($failedItems) . ' item(s) failed. Please check and try again.';
            }

            return redirect()->route('admin.returns.show', $return->id)
                ->with(empty($failedItems) ? 'success' : 'warning', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process return items: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to process return items: ' . $e->getMessage())
                ->withInput();
        }
    }
}