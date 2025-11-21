<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all sub-departments that this division belongs to.
     */
    public function subDepartments()
    {
        return $this->belongsToMany(SubDepartment::class, 'division_sub_department')
                    ->withTimestamps();
    }

    /**
     * Get all departments through sub-departments.
     */
    public function departments()
    {
        return $this->hasManyThrough(
            Department::class,
            SubDepartment::class,
            'division_id', // Foreign key on division_sub_department table
            'department_id', // Foreign key on department_sub_department table
            'id', // Local key on divisions table
            'id' // Local key on sub_departments table
        );
    }

    /**
     * Scope to get only active divisions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}