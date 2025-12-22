<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'requisition_issued_item_id',
        'return_type',
        'location_code',
        'item_code',
        'item_name',
        'item_category',
        'unit',
        'quantity',
        'approve_status',
        'approved_by',
        'approved_at',
        'notes',
        'admin_note',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the return that this item belongs to
     */
    public function return()
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    /**
     * Get the original issued item
     */
    public function issuedItem()
    {
        return $this->belongsTo(RequisitionIssuedItem::class, 'requisition_issued_item_id');
    }

    /**
     * Get the user who approved this item
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get GRN item if exists
     */
    public function grnItem()
    {
        return $this->hasOne(GrnItem::class, 'return_item_id')->where('status', 'active');
    }

    /**
     * Get scrap item if exists
     */
    public function scrapItem()
    {
        return $this->hasOne(ScrapItem::class, 'return_item_id')->where('status', 'active');
    }

    /**
     * Check if item is pending approval
     */
    public function isPending()
    {
        return $this->approve_status === 'pending';
    }

    /**
     * Check if item is approved
     */
    public function isApproved()
    {
        return $this->approve_status === 'approved';
    }

    /**
     * Check if item is rejected
     */
    public function isRejected()
    {
        return $this->approve_status === 'rejected';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->approve_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get type badge color
     */
    public function getTypeBadgeAttribute()
    {
        return $this->return_type === 'used' ? 'warning' : 'success';
    }

    /**
     * Get type label
     */
    public function getTypeLabel()
    {
        return $this->return_type === 'used' ? 'Used' : 'Same Condition';
    }
}