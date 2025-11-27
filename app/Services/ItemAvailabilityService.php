<?php

namespace App\Services;

use App\Models\RequisitionItem;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Storage;

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
     * Get pending quantity (items in pending requisitions that are not in PO).
     */
    public function getPendingQuantity($itemCode, $requisitionId = '')
    {
        // Get total quantity in pending requisition items
        $pendingRequisitionItems = RequisitionItem::whereHas('requisition', function($query) {
            $query->where('approve_status', 'pending')
                  ->where('status', 'active');
        })
        ->where('item_code', $itemCode)
        ->where('requisition_id', '!=', $requisitionId)
        ->where('status', '!=', 'delete')
        ->sum('quantity');

        // Get quantity in pending PO items (these are not available, so shouldn't be subtracted)
        $pendingPOItems = PurchaseOrderItem::whereHas('requisition', function($query) {
            $query->where('approve_status', 'pending')
                  ->where('status', 'active');
        })
        ->where('item_code', $itemCode)
        ->where('status', 'pending')
        ->sum('quantity');

        // Pending = Requisition Items - PO Items
        return $pendingRequisitionItems - $pendingPOItems;
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