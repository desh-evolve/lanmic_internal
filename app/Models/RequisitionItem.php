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
        'unit_price',
        'total_price',
        'specifications',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Get the requisition that owns the item.
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Scope to get only active req items.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}