<?php

namespace App\Services;

use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ItemAvailabilityService
{
    /**
     * Get items with availability status from JSON.
     */
    public function getItemsWithAvailability()
    {
        $items = $this->getItemsFromJson();
        
        foreach ($items as &$item) {
            $item['available_quantity'] = $this->getAvailableQuantity($item['code']);
            $item['pending_quantity'] = $this->getPendingQuantity($item['code']);
            $item['is_available'] = $item['available_quantity'] > 0;
        }
        
        return $items;
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

    /**
     * Calculate available quantity for an item.
     * Available = Stock - (Pending Requisition Items - Pending PO Items)
     */
    public function getAvailableQuantity($itemCode, $requisitionId = '')
    {
        // Get the item's stock from JSON
        $items = $this->getItemsFromJson();
        $item = collect($items)->firstWhere('code', $itemCode);
        $stockQuantity = $item['available_qty'] ?? 0;
       
        // Get pending quantity
        $pendingQuantity = $this->getPendingQuantity($itemCode, $requisitionId);
        
        // Available = Stock - Pending
        return max(0, $stockQuantity - $pendingQuantity);
    }

    /**
     * Get pending quantity
     */
    public function getPendingQuantity($itemCode, $requisitionId = '')
    {
        // Get total quantity in pending requisition items
        $pendingRequisitionItems = RequisitionItem::whereHas('requisition', function($query) {
            $query->where('clear_status', 'pending')
                ->where('status', 'active');
        })
        ->where('item_code', $itemCode)
        ->where('requisition_id', '!=', $requisitionId)
        ->where('status', 'active')
        ->sum('quantity');

        // Get total issued quantity for this item
        $issuedQuantity = RequisitionIssuedItem::whereHas('requisition', function($query) {
            $query->where('clear_status', 'pending')
                ->where('status', 'active');
        })
        ->where('item_code', $itemCode)
        ->where('requisition_id', '!=', $requisitionId)
        ->where('status', 'active')
        ->sum('issued_quantity');

        // Pending = Requisition Items - Issued Items
        return max(0, $pendingRequisitionItems - $issuedQuantity); // Ensure non-negative
    }

    /**
     * Get pending quantity of all items
     */
    public function getAllPendingQuantity($requisitionId = '')
    {
        // Get all items with pending quantities grouped by item_code
        $pendingItemsQuery = RequisitionItem::whereHas('requisition', function($query) {
            $query->where('clear_status', 'pending')
                ->where('status', 'active');
        })
        ->where('status', 'active');
        
        // Only exclude requisitionId if it's provided
        if ($requisitionId) {
            $pendingItemsQuery->where('requisition_id', '!=', $requisitionId);
        }
        
        $pendingItems = $pendingItemsQuery
            ->select('item_code', DB::raw('SUM(quantity) as quantity'))
            ->groupBy('item_code')
            ->get();

        // Get issued quantities grouped by item_code
        $issuedItemsQuery = RequisitionIssuedItem::whereHas('requisition', function($query) {
            $query->where('clear_status', 'pending')
                ->where('status', 'active');
        })
        ->where('status', 'active');
        
        // Only exclude requisitionId if it's provided
        if ($requisitionId) {
            $issuedItemsQuery->where('requisition_id', '!=', $requisitionId);
        }
        
        $issuedItems = $issuedItemsQuery
            ->select('item_code', DB::raw('SUM(issued_quantity) as issued_quantity'))
            ->groupBy('item_code')
            ->get()
            ->keyBy('item_code');

        // Subtract issued quantities from pending quantities
        $result = $pendingItems->map(function($item) use ($issuedItems) {
            $issuedQty = $issuedItems->get($item->item_code)->issued_quantity ?? 0;
            return [
                'item_code' => $item->item_code,
                'quantity' => max(0, $item->quantity - $issuedQty) // Ensure non-negative
            ];
        });

        return $result;
    }

    /**
     * Check if item quantity is available.
     */
    public function isQuantityAvailable($itemCode, $requestedQuantity)
    {
        $availableQuantity = $this->getAvailableQuantity($itemCode);
        return $availableQuantity >= $requestedQuantity;
    }

    /**
     * Determine how much of the requested quantity is available and how much needs PO.
     */
    public function splitAvailableAndPO($itemCode, $requestedQuantity, $requisitionId = '')
    {
        $availableQuantity = $this->getAvailableQuantity($itemCode, $requisitionId);
        
        if ($availableQuantity >= $requestedQuantity) {
            return [
                'available' => $requestedQuantity,
                'needs_po' => 0
            ];
        } else {
            return [
                'available' => $availableQuantity,
                'needs_po' => $requestedQuantity - $availableQuantity
            ];
        }
    }
}