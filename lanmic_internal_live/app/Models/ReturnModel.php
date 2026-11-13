<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'requisition_id',
        'returned_by',
        'returned_at',
        'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    /**
     * Get the requisition that this return belongs to
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Get the user who created the return
     */
    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Get all return items (active only)
     */
    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id')->where('status', 'active');
    }

    /**
     * Get all return items including deleted
     */
    public function allItems()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    /**
     * Get GRN items
     */
    public function grnItems()
    {
        return $this->hasMany(GrnItem::class, 'return_id')->where('status', 'active');
    }

    /**
     * Get scrap items
     */
    public function scrapItems()
    {
        return $this->hasMany(ScrapItem::class, 'return_id')->where('status', 'active');
    }

    /**
     * Check if return is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if all items are approved
     */
    public function allItemsApproved()
    {
        return $this->items()->where('approve_status', '!=', 'approved')->count() === 0;
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Get total quantity
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Get used items count
     */
    public function getUsedItemsCountAttribute()
    {
        return $this->items()->where('return_type', 'used')->count();
    }

    /**
     * Get same condition items count
     */
    public function getSameItemsCountAttribute()
    {
        return $this->items()->where('return_type', 'same')->count();
    }

    /**
     * Scope for pending returns.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for cleared returns.
     */
    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    /**
     * Scope for active returns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'delete');
    }
}