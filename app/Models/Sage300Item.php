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
        'average_cost',
        'active',
    ];

    protected $casts = [
        'average_cost' => 'float',
        'active'       => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
