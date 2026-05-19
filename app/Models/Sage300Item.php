<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sage300Item extends Model
{
    protected $fillable = [
        'item_code',
        'description',
        'category',
        'unit',
        'quantity_on_hand',
        'average_cost',
        'active',
    ];

    protected $casts = [
        'quantity_on_hand' => 'float',
        'average_cost'     => 'float',
        'active'           => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
