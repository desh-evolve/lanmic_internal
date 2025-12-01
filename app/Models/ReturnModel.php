<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'returned_by',
        'returned_at',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    /**
     * Get the user who returned.
     */
    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Get the user who created.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get return items.
     */
    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id')->where('status', '!=', 'delete');
    }

    /**
     * Get all items including deleted.
     */
    public function allItems()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    /**
     * Get GRN items.
     */
    public function grnItems()
    {
        return $this->hasMany(GrnItem::class, 'return_id')->where('status', '!=', 'delete');
    }

    /**
     * Get scrap items.
     */
    public function scrapItems()
    {
        return $this->hasMany(ScrapItem::class, 'return_id')->where('status', '!=', 'delete');
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