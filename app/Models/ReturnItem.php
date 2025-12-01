<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'return_type',
        'return_location_id',
        'item_code',
        'item_name',
        'item_category',
        'unit',
        'unit_price',
        'total_price',
        'return_quantity',
        'approve_status',
        'approved_by',
        'approved_at',
        'notes',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'return_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the return.
     */
    public function return()
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    /**
     * Get the location from JSON.
     */
    public function getLocationAttribute()
    {
        return Location::find($this->return_location_id);
    }

    /**
     * Get the user who approved.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get GRN item if approved.
     */
    public function grnItem()
    {
        return $this->hasOne(GrnItem::class, 'return_item_id')->where('status', '!=', 'delete');
    }

    /**
     * Get scrap item if rejected.
     */
    public function scrapItem()
    {
        return $this->hasOne(ScrapItem::class, 'return_item_id')->where('status', '!=', 'delete');
    }

    /**
     * Scope for pending items.
     */
    public function scopePending($query)
    {
        return $query->where('approve_status', 'pending')->where('status', 'active');
    }

    /**
     * Scope for approved items.
     */
    public function scopeApproved($query)
    {
        return $query->where('approve_status', 'approved')->where('status', 'active');
    }

    /**
     * Scope for rejected items.
     */
    public function scopeRejected($query)
    {
        return $query->where('approve_status', 'rejected')->where('status', 'active');
    }
}