<?php

namespace App\Http\Controllers;

use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of returns.
     */
    public function index()
    {
        $returns = ReturnModel::where('returned_by', Auth::id())
            ->where('status', '!=', 'delete')
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new return.
     */
    public function create()
    {
        $items = $this->getItemsFromJson();
        $locations = Location::all();
        
        return view('returns.create', compact('items', 'locations'));
    }

    /**
     * Store a newly created return.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.return_type' => 'required|in:used,same',
            'items.*.return_location_id' => 'required|integer',
            'items.*.item_code' => 'required|string',
            'items.*.item_name' => 'required|string',
            'items.*.return_quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create return
            $return = ReturnModel::create([
                'returned_by' => Auth::id(),
                'returned_at' => now(),
                'status' => 'pending',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create return items
            foreach ($request->items as $item) {
                $unitPrice = $item['unit_price'] ?? 0;
                $quantity = $item['return_quantity'];
                $totalPrice = $unitPrice * $quantity;

                ReturnItem::create([
                    'return_id' => $return->id,
                    'return_type' => $item['return_type'],
                    'return_location_id' => $item['return_location_id'],
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'item_category' => $item['item_category'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'return_quantity' => $quantity,
                    'approve_status' => 'pending',
                    'notes' => $item['notes'] ?? null,
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

        $return->load('items', 'returnedBy', 'grnItems', 'scrapItems');
        
        return view('returns.show', compact('return'));
    }

    /**
     * Show the form for editing the specified return.
     */
    public function edit(ReturnModel $return)
    {
        // Only allow editing if return is pending and user owns it
        if ((int)$return->returned_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($return->status !== 'pending') {
            return redirect()->route('returns.show', $return->id)
                ->with('error', 'Cannot edit a return that has been processed.');
        }

        $items = $this->getItemsFromJson();
        $locations = Location::all();
        
        return view('returns.edit', compact('return', 'items', 'locations'));
    }

    /**
     * Update the specified return.
     */
    public function update(Request $request, ReturnModel $return)
    {
        // Only allow editing if return is pending and user owns it
        if ((int)$return->returned_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($return->status !== 'pending') {
            return redirect()->route('returns.show', $return->id)
                ->with('error', 'Cannot edit a return that has been processed.');
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.return_type' => 'required|in:used,same',
            'items.*.return_location_id' => 'required|integer',
            'items.*.item_code' => 'required|string',
            'items.*.item_name' => 'required|string',
            'items.*.return_quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update return
            $return->update([
                'updated_by' => Auth::id(),
            ]);

            // Delete old items
            $return->allItems()->update(['status' => 'delete', 'updated_by' => Auth::id()]);

            // Create new items
            foreach ($request->items as $item) {
                $unitPrice = $item['unit_price'] ?? 0;
                $quantity = $item['return_quantity'];
                $totalPrice = $unitPrice * $quantity;

                ReturnItem::create([
                    'return_id' => $return->id,
                    'return_type' => $item['return_type'],
                    'return_location_id' => $item['return_location_id'],
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'item_category' => $item['item_category'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'return_quantity' => $quantity,
                    'approve_status' => 'pending',
                    'notes' => $item['notes'] ?? null,
                    'status' => 'active',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('returns.show', $return->id)
                ->with('success', 'Return updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update return: ' . $e->getMessage())
                ->withInput();
        }
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

        $return->update(['status' => 'delete', 'updated_by' => Auth::id()]);

        return redirect()->route('returns.index')
            ->with('success', 'Return deleted successfully.');
    }

    /**
     * Get items from JSON based on return type.
     */
    public function getItemsByType($returnType)
    {
        $items = $this->getItemsFromJson();
        $locations = Location::all();

        // For 'used' type, only show items from used locations
        // For 'same' type, show items from all locations
        if ($returnType === 'used') {
            $usedLocationIds = Location::used()->pluck('id')->toArray();
            // Filter items that belong to used locations
            // Since items don't have location in JSON, we'll return all items
            // In real scenario, items would have location_id
        }

        return response()->json([
            'items' => $items,
            'locations' => $returnType === 'used' 
                ? Location::used()->values() 
                : $locations->values()
        ]);
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