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
     * Display returns created by the current user or by a colleague who shares
     * a department with them (directly, or via the linked requisition's creator).
     */
    public function index()
    {
        $colleagueIds = Auth::user()->departmentColleagueIds();

        $returns = ReturnModel::where(function ($q) use ($colleagueIds) {
                $q->whereIn('returned_by', $colleagueIds)
                  ->orWhereHas('requisition', fn($rq) => $rq->whereIn('user_id', $colleagueIds));
            })
            ->where('status', '!=', 'delete')
            ->with(['requisition.user', 'returnedBy', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('returns.index', compact('returns'));
    }

    /**
     * Determine whether the current user may access a requisition for return creation.
     * Access is granted when the requisition was created by the user themselves
     * or by a colleague who shares a department with them.
     */
    private function canAccessRequisition(Requisition $requisition): bool
    {
        return Auth::user()->departmentColleagueIds()->contains($requisition->user_id);
    }

    /**
     * Show the form for creating a new return.
     */
    public function create()
    {
        $colleagueIds = Auth::user()->departmentColleagueIds();

        // Requisitions created by the user or a same-department colleague that are
        // approved, active and have issued items available to return.
        $requisitions = Requisition::whereIn('user_id', $colleagueIds)
            ->where('approve_status', 'approved')
            ->where('status', 'active')
            ->whereHas('issuedItems')
            ->with(['department', 'subDepartment', 'division', 'issuedItems', 'user'])
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

        if (! $this->canAccessRequisition($requisition)) {
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
                    'issued_at' => $item->issued_at?->format('Y-m-d'),
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
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify the current user is allowed to create a return for this requisition
        $requisition = Requisition::findOrFail($request->requisition_id);
        if (! $this->canAccessRequisition($requisition)) {
            return redirect()->back()
                ->with('error', 'Unauthorized action.')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Validate quantities and ownership
            foreach ($request->items as $itemData) {
                $issuedItem = RequisitionIssuedItem::find($itemData['requisition_issued_item_id']);

                if (!$issuedItem) {
                    throw new \Exception("Issued item not found");
                }

                // Ensure the issued item belongs to the submitted requisition
                if ((int)$issuedItem->requisition_id !== (int)$request->requisition_id) {
                    throw new \Exception("Issued item does not belong to the selected requisition.");
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
        $user = Auth::user();

        if (!$user->hasPermission('full-access')) {
            $colleagueIds   = $user->departmentColleagueIds();
            $returnByColleague = $colleagueIds->contains($return->returned_by);
            $reqByColleague    = $colleagueIds->contains(optional($return->requisition)->user_id);

            if (!$returnByColleague && !$reqByColleague) {
                abort(403, 'Unauthorized action.');
            }
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
     * Show the form for editing a pending return.
     * Returns are immutable once submitted — editing is not supported.
     */
    public function edit(ReturnModel $return)
    {
        if ((int)$return->returned_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return redirect()->route('returns.show', $return->id)
            ->with('error', 'Returns cannot be edited after submission. Delete and re-create if needed.');
    }

    /**
     * Update a return — not supported; returns are immutable after submission.
     */
    public function update(Request $request, ReturnModel $return)
    {
        return redirect()->route('returns.show', $return->id)
            ->with('error', 'Returns cannot be edited after submission.');
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