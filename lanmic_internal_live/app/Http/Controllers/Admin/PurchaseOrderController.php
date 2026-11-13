<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrderItem;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Sage300Service;

class PurchaseOrderController extends Controller
{
    protected Sage300Service $sage300;

    public function __construct(Sage300Service $sage300)
    {
        $this->middleware('auth');
        $this->middleware('role:admin');

        $this->sage300 = $sage300;
    }

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $groupBy = $request->input('group_by', 'item'); // requisition or item

        $query = PurchaseOrderItem::with(['requisition.user', 'requisition.department'])
            ->where('status', $status);

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Item filter
        if ($request->filled('item_code')) {
            $query->where('item_code', 'like', '%' . $request->item_code . '%');
        }

        if ($groupBy === 'requisition') {
            // Group by requisition
            $poItems = $query->orderBy('requisition_id', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('requisition_id');
        } else {
            // Group by item
            $poItems = $query->orderBy('item_code')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('item_code');
        }
        
        // Loop through each group and then each item within the group
        foreach ($poItems as $groupKey => $items) {
            foreach ($items as $item) {
                $location = $this->sage300->getLocation($item['location_code']);
                $item['location_name'] = $location['Name'] ?? '-';
            }
        }

        // Statistics
        $statistics = [
            'pending_count' => PurchaseOrderItem::where('status', 'pending')->count(),
            'pending_quantity' => PurchaseOrderItem::where('status', 'pending')->sum('quantity'),
            'cleared_count' => PurchaseOrderItem::where('status', 'cleared')->count(),
            'cleared_quantity' => PurchaseOrderItem::where('status', 'cleared')->sum('quantity'),
        ];

        return view('admin.purchase-orders.index', compact('poItems', 'statistics', 'status', 'groupBy'));
    }

    /**
     * Show clear purchase order form.
     */
    public function clearForm(Request $request)
    {
        $type = $request->input('type', 'requisition');
        $id = $request->input('id');

        if ($type === 'requisition') {
            $requisition = Requisition::findOrFail($id);
            $poItems = PurchaseOrderItem::where('requisition_id', $id)
                ->where('status', 'pending')
                ->get();
            
            return view('admin.purchase-orders.clear-requisition', compact('requisition', 'poItems'));
        } else {
            $itemCode = $id;
            $poItems = PurchaseOrderItem::where('item_code', $itemCode)
                ->where('status', 'pending')
                ->with(['requisition.user', 'requisition.department'])
                ->get();
            
            return view('admin.purchase-orders.clear-item', compact('itemCode', 'poItems'));
        }
    }

    /**
     * Clear purchase orders.
     */
    public function clear(Request $request)
    {
        $request->validate([
            'po_item_ids' => 'required|array|min:1',
            'po_item_ids.*' => 'exists:purchase_order_items,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->po_item_ids as $poItemId) {
                $poItem = PurchaseOrderItem::find($poItemId);
                
                if ($poItem && $poItem->status === 'pending') {
                    $poItem->update([
                        'status' => 'cleared',
                        'cleared_by' => Auth::id(),
                        'cleared_at' => now(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.purchase-orders.index', ['status' => 'cleared'])
                ->with('success', 'Purchase orders cleared successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to clear purchase orders: ' . $e->getMessage());
        }
    }

    /**
     * Bulk clear purchase orders.
     */
    public function bulkClear(Request $request)
    {
        $request->validate([
            'action' => 'required|in:clear_selected,clear_requisition,clear_item',
            'po_item_ids' => 'required_if:action,clear_selected|array',
            'po_item_ids.*' => 'exists:purchase_order_items,id',
            'requisition_id' => 'required_if:action,clear_requisition',
            'item_code' => 'required_if:action,clear_item',
        ]);

        DB::beginTransaction();
        try {
            $poItems = collect();

            if ($request->action === 'clear_selected') {
                $poItems = PurchaseOrderItem::whereIn('id', $request->po_item_ids)
                    ->where('status', 'pending')
                    ->get();
            } elseif ($request->action === 'clear_requisition') {
                $poItems = PurchaseOrderItem::where('requisition_id', $request->requisition_id)
                    ->where('status', 'pending')
                    ->get();
            } elseif ($request->action === 'clear_item') {
                $poItems = PurchaseOrderItem::where('item_code', $request->item_code)
                    ->where('status', 'pending')
                    ->get();
            }

            foreach ($poItems as $poItem) {
                $poItem->update([
                    'status' => 'cleared',
                    'cleared_by' => Auth::id(),
                    'cleared_at' => now(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.purchase-orders.index', ['status' => 'cleared'])
                ->with('success', count($poItems) . ' purchase order(s) cleared successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to clear purchase orders: ' . $e->getMessage());
        }
    }

    /**
     * View purchase order details.
     */
    public function show($id)
    {
        $poItem = PurchaseOrderItem::with(['requisition.user', 'requisition.department', 'clearedBy'])
            ->findOrFail($id);
        
        return view('admin.purchase-orders.show', compact('poItem'));
    }
}