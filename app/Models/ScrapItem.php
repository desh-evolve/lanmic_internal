<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'return_item_id',
        'item_code',
        'item_name',
        'item_category',
        'unit',
        'unit_price',
        'total_price',
        'scrap_quantity',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'scrap_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the return.
     */
    public function return()
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    /**
     * Get the return item.
     */
    public function returnItem()
    {
        return $this->belongsTo(ReturnItem::class, 'return_item_id');
    }
}