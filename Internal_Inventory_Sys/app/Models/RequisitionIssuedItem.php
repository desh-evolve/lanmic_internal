<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionIssuedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'requisition_item_id',
        'item_code',
        'item_name',
        'item_category',
        'unit',
        'unit_price',
        'total_price',
        'issued_quantity',
        'issued_by',
        'issued_at',
        'status',
        'created_by',
        'updated_by',
        'location_code',
        'reference_number_1',
        'reference_number_2',
        'notes'
    ];

    protected $casts = [
        'issued_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    /**
     * Get the requisition.
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Get the requisition item.
     */
    public function requisitionItem()
    {
        return $this->belongsTo(RequisitionItem::class);
    }

    /**
     * Get the user who issued the item.
     */
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get return items for this issued item
     */
    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class, 'requisition_issued_item_id');
    }
}