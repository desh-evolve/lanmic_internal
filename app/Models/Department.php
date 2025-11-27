<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
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
     * Get all sub-departments for this department.
     */
    public function subDepartments()
    {
        return $this->belongsToMany(SubDepartment::class, 'department_sub_department')
                    ->wherePivot('status', 'active')  // respect pivot status
                    ->where('sub_departments.status', 'active')
                    ->select('sub_departments.*'); // avoids ambiguous id error
    }

    /**
     * Get all divisions through sub-departments.
     */
    public function divisions()
    {
        return $this->hasManyThrough(
            Division::class,
            SubDepartment::class,
            'department_id', // Foreign key on department_sub_department table
            'sub_department_id', // Foreign key on division_sub_department table
            'id', // Local key on departments table
            'id' // Local key on sub_departments table
        );
    }

    /**
     * Scope to get only active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}