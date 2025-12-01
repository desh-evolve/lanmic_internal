<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Models\GrnItem;
use App\Models\ScrapItem;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of all returns.
     */
    public function index(Request $request)
    {
        $query = ReturnModel::with(['returnedBy', 'items'])
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
        $return->load(['items', 'returnedBy', 'grnItems', 'scrapItems']);
        $locations = Location::all();
        
        return view('admin.returns.show', compact('return', 'locations'));
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

        $return->load('items', 'returnedBy');
        $locations = Location::all();
        
        return view('admin.returns.approve-items', compact('return', 'locations'));
    }

    /**
     * Approve or reject return items with quantity split.
     */
    public function approveItems(Request $request, ReturnModel $return)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.return_item_id' => 'required|exists:return_items,id',
            'items.*.grn_quantity' => 'nullable|integer|min:0',
            'items.*.scrap_quantity' => 'nullable|integer|min:0',
            'items.*.return_type' => 'required|in:used,same',
            'items.*.return_location_id' => 'required|integer',
        ]);

        if ($return->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending returns can be processed.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                $returnItem = ReturnItem::find($itemData['return_item_id']);
                
                $grnQty = (int)($itemData['grn_quantity'] ?? 0);
                $scrapQty = (int)($itemData['scrap_quantity'] ?? 0);
                
                // Validate total quantity
                if (($grnQty + $scrapQty) != $returnItem->return_quantity) {
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
                    $approveStatus = 'approved'; // Partial approval
                }

                // Update return item
                $returnItem->update([
                    'approve_status' => $approveStatus,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'return_type' => $itemData['return_type'],
                    'return_location_id' => $itemData['return_location_id'],
                    'updated_by' => Auth::id(),
                ]);

                // Create GRN item if quantity > 0
                if ($grnQty > 0) {
                    $grnUnitPrice = $returnItem->unit_price;
                    $grnTotalPrice = $grnUnitPrice * $grnQty;

                    GrnItem::create([
                        'return_id' => $return->id,
                        'return_item_id' => $returnItem->id,
                        'item_code' => $returnItem->item_code,
                        'item_name' => $returnItem->item_name,
                        'item_category' => $returnItem->item_category,
                        'unit' => $returnItem->unit,
                        'unit_price' => $grnUnitPrice,
                        'total_price' => $grnTotalPrice,
                        'grn_quantity' => $grnQty,
                        'status' => 'active',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }

                // Create Scrap item if quantity > 0
                if ($scrapQty > 0) {
                    $scrapUnitPrice = $returnItem->unit_price;
                    $scrapTotalPrice = $scrapUnitPrice * $scrapQty;

                    ScrapItem::create([
                        'return_id' => $return->id,
                        'return_item_id' => $returnItem->id,
                        'item_code' => $returnItem->item_code,
                        'item_name' => $returnItem->item_name,
                        'item_category' => $returnItem->item_category,
                        'unit' => $returnItem->unit,
                        'unit_price' => $scrapUnitPrice,
                        'total_price' => $scrapTotalPrice,
                        'scrap_quantity' => $scrapQty,
                        'status' => 'active',
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
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

            return redirect()->route('admin.returns.show', $return->id)
                ->with('success', 'Return items processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to process return items: ' . $e->getMessage())
                ->withInput();
        }
    }
}