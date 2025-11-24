<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_code',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get all departments that this sub-department belongs to.
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_sub_department')
                    ->withTimestamps();
    }

    /**
     * Get all divisions for this sub-department.
     */
    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'division_sub_department')
                    ->withTimestamps();
    }

    /**
     * Scope to get only active sub-departments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}