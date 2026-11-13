<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'return_item_id',
        'item_code',
        'item_name',
        'item_category',
        'unit',
        'location_code',
        'unit_price',
        'total_price',
        'grn_quantity',
        'reference_number_1',
        'reference_number_2',
        'processed_by',
        'processed_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Get the return that this GRN item belongs to
     */
    public function return()
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    /**
     * Get the return item
     */
    public function returnItem()
    {
        return $this->belongsTo(ReturnItem::class, 'return_item_id');
    }

    /**
     * Get the user who processed this GRN
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}