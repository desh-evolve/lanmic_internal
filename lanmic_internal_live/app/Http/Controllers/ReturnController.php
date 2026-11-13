<?php

namespace App\Http\Controllers;

use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Models\Requisition;
use App\Models\RequisitionIssuedItem;
use App\Services\Sage300Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReturnController extends Controller
{
    protected Sage300Service $sage300;

    public function __construct(Sage300Service $sage300)
    {
        $this->middleware('auth');
        $this->sage300 = $sage300;
    }

    /**
     * Display a listing of returns.
     */
    public function index()
    {
        $returns = ReturnModel::where('returned_by', Auth::id())
            ->where('status', '!=', 'delete')
            ->with(['requisition', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new return.
     */
    public function create()
    {
        // Get user's cleared requisitions with issued items
        $requisitions = Requisition::where('user_id', Auth::id())
            ->where('approve_status', 'approved')
            ->where('status', 'active')
            ->whereHas('issuedItems')
            ->with(['department', 'subDepartment', 'division', 'issuedItems'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('returns.create', compact('requisitions'));
    }

    /**
     * Get issued items for a requisition
     */
    public function getIssuedItems($requisitionId)
    {
        $requisition = Requisition::findOrFail($requisitionId);
        
        // Check if user owns this requisition
        if ((int)$requisition->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $issuedItems = RequisitionIssuedItem::where('requisition_id', $requisitionId)
            ->where('status', 'active')
            ->get()
            ->map(function($item) {
                // Get location details
                $location = $this->sage300->getLocation($item->location_code);
                
                return [
                    'id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'item_category' => $item->item_category,
                    'unit' => $item->unit,
                    'issued_quantity' => $item->issued_quantity,
                    'location_code' => $item->location_code,
                    'location_name' => $location['Name'] ?? $item->location_code,
                    'issued_at' => $item->issued_at->format('Y-m-d'),
                ];
            });

        return response()->json($issuedItems);
    }

    /**
     * Store a newly created return.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requisition_id' => 'required|exists:requisitions,id',
            'items' => 'required|array|min:1',
            'items.*.requisition_issued_item_id' => 'required|exists:requisition_issued_items,id',
            'items.*.return_type' => 'required|in:used,same',
            'items.*.item_code' => 'required|string',
            'items.*.item_name' => 'required|string',
            'items.*.location_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify requisition belongs to user
        $requisition = Requisition::findOrFail($request->requisition_id);
        if ((int)$requisition->user_id !== Auth::id()) {
            return redirect()->back()
                ->with('error', 'Unauthorized action.')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Validate quantities
            foreach ($request->items as $itemData) {
                $issuedItem = RequisitionIssuedItem::find($itemData['requisition_issued_item_id']);
                
                if (!$issuedItem) {
                    throw new \Exception("Issued item not found");
                }

                // Get already returned quantity for this issued item
                $alreadyReturned = ReturnItem::where('requisition_issued_item_id', $issuedItem->id)
                    ->where('status', 'active')
                    ->sum('quantity');

                $availableToReturn = $issuedItem->issued_quantity - $alreadyReturned;

                if ($itemData['quantity'] > $availableToReturn) {
                    throw new \Exception("Cannot return more than issued quantity for item: {$issuedItem->item_name}. Available to return: {$availableToReturn}");
                }
            }

            // Create return
            $return = ReturnModel::create([
                'requisition_id' => $request->requisition_id,
                'returned_by' => Auth::id(),
                'returned_at' => now(),
                'status' => 'pending',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create return items
            foreach ($request->items as $itemData) {
                ReturnItem::create([
                    'return_id' => $return->id,
                    'requisition_issued_item_id' => $itemData['requisition_issued_item_id'],
                    'return_type' => $itemData['return_type'],
                    'location_code' => $itemData['location_code'],
                    'item_code' => $itemData['item_code'],
                    'item_name' => $itemData['item_name'],
                    'item_category' => $itemData['item_category'] ?? null,
                    'unit' => $itemData['unit'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'approve_status' => 'pending',
                    'notes' => $itemData['notes'] ?? null,
                    'status' => 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('returns.show', $return->id)
                ->with('success', 'Return created successfully and sent for approval.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create return: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified return.
     */
    public function show(ReturnModel $return)
    {
        // Check if user owns this return or is admin
        if ((int)$return->returned_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $return->load([
            'requisition.department',
            'items.issuedItem',
            'returnedBy',
            'grnItems',
            'scrapItems'
        ]);

        // Add location names
        foreach ($return->items as $item) {
            $location = $this->sage300->getLocation($item->location_code);
            $item->location_name = $location['Name'] ?? $item->location_code;
        }
        
        return view('returns.show', compact('return'));
    }

    /**
     * Remove the specified return.
     */
    public function destroy(ReturnModel $return)
    {
        // Only allow deletion if return is pending and user owns it
        if ((int)$return->returned_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($return->status !== 'pending') {
            return redirect()->route('returns.index')
                ->with('error', 'Cannot delete a return that has been processed.');
        }

        DB::beginTransaction();
        try {
            $return->update([
                'status' => 'delete',
                'updated_by' => Auth::id()
            ]);

            $return->allItems()->update([
                'status' => 'delete',
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('returns.index')
                ->with('success', 'Return deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete return.');
        }
    }
}