<?php

namespace App\Services;

use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use Illuminate\Support\Facades\DB;

class ItemAvailabilityService
{
    protected Sage300Service $sage300;

    public function __construct(Sage300Service $sage300)
    {
        $this->sage300 = $sage300;
    }

    /**
     * Get items with availability status from Sage300.
     */
    public function getItemsWithAvailability()
    {
        $items = $this->sage300->getItems();
        
        foreach ($items as &$item) {
            $itemCode = $item['UnformattedItemNumber'];
            $item['available_quantity'] = $this->getAvailableQuantity($itemCode);
            $item['pending_quantity'] = $this->getPendingQuantity($itemCode);
            $item['is_available'] = $item['available_quantity'] > 0;
        }
        
        return $items;
    }

    /**
     * Calculate available quantity for an item.
     * Available = Sage300 Stock - (Pending Requisition Items - Issued Items)
     */
    public function getAvailableQuantity($itemCode, $requisitionId = null)
    {
        // Get stock quantity from Sage300
        $stockQuantity = $this->sage300->getItemQuantity($itemCode);
       
        // Get pending quantity from local database
        $pendingQuantity = $this->getPendingQuantity($itemCode, $requisitionId);
        
        // Available = Stock - Pending
        return max(0, $stockQuantity - $pendingQuantity);
    }

    /**
     * Get pending quantity from local database
     */
    public function getPendingQuantity($itemCode, $requisitionId = null)
    {
        // Get total quantity in pending requisition items
        $pendingRequisitionItems = RequisitionItem::whereHas('requisition', function($query) {
            $query->where('clear_status', 'pending')
                ->where('status', 'active');
        })
        ->where('item_code', $itemCode)
        ->where('status', 'active');
        
        // Exclude current requisition if provided
        if ($requisitionId) {
            $pendingRequisitionItems->where('requisition_id', '!=', $requisitionId);
        }
        
        $pendingRequisitionItems = $pendingRequisitionItems->sum('quantity');

        // Get total issued quantity for this item
        $issuedQuantity = RequisitionIssuedItem::whereHas('requisition', function($query) {
            $query->where('clear_status', 'pending')
                ->where('status', 'active');
        })
        ->where('item_code', $itemCode)
        ->where('status', 'active');
        
        // Exclude current requisition if provided
        if ($requisitionId) {
            $issuedQuantity->where('requisition_id', '!=', $requisitionId);
        }
        
        $issuedQuantity = $issuedQuantity->sum('issued_quantity');

        // Pending = Requisition Items - Issued Items
        return max(0, $pendingRequisitionItems - $issuedQuantity);
    }

    /**
     * Get pending quantity of all items
     */
    public function getAllPendingQuantity($requisitionId = null)
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
            $issued = $issuedItems->get($item->item_code);
            $issuedQty = $issued ? $issued->issued_quantity : 0;
            return [
                'item_code' => $item->item_code,
                'quantity' => max(0, $item->quantity - $issuedQty)
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
     * Uses Sage300 stock quantity.
     */
    public function splitAvailableAndPO($itemCode, $locationCode, $requestedQuantity, $requisitionId = null)
    {
        $availableQuantity = $this->getAvailableQuantity($itemCode, $requisitionId);
        
        if ($availableQuantity >= $requestedQuantity) {
            return [
                'available' => $requestedQuantity,
                'needs_po' => 0
            ];
        } else {
            return [
                'available' => max(0, $availableQuantity),
                'needs_po' => max(0, $requestedQuantity - $availableQuantity)
            ];
        }
    }
}