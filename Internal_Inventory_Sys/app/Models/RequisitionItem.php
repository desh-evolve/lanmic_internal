<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'item_code',
        'item_name',
        'item_category',
        'unit',
        'quantity',
        'location_code',
        'specifications',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the requisition that owns the item.
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Get issued items for this requisition item.
     */
    public function issuedItems()
    {
        return $this->hasMany(RequisitionIssuedItem::class)->where('status', '!=', 'delete');
    }

    /**
     * Get the remaining quantity to be issued.
     */
    public function getRemainingQuantityAttribute()
    {
        $issuedQuantity = $this->issuedItems()->sum('issued_quantity');
        return $this->quantity - $issuedQuantity;
    }

    /**
     * Check if item is fully issued.
     */
    public function isFullyIssued()
    {
        return $this->remaining_quantity <= 0;
    }
}