<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'item_code',
        'item_name',
        'item_category',
        'unit',
        'unit_price',
        'total_price',
        'quantity',
        'status',
        'cleared_by',
        'cleared_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'cleared_at' => 'datetime',
    ];

    /**
     * Get the requisition that owns the item.
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Get the user who cleared the item.
     */
    public function clearedBy()
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    /**
     * Scope for pending items.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for cleared items.
     */
    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }
}