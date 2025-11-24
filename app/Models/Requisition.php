<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_number',
        'requested_by',
        'department_id',
        'sub_department_id',
        'division_id',
        'approve_status',
        'approved_by',
        'approved_at',
        'clear_status',
        'cleared_by',
        'cleared_at',
        'rejection_reason',
        'notes',
        'status'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'cleared_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the user who created the requisition.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the sub-department.
     */
    public function subDepartment()
    {
        return $this->belongsTo(SubDepartment::class);
    }

    /**
     * Get the division.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the user who approved the requisition.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who cleared the requisition.
     */
    public function clearedBy()
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    /**
     * Get the items for the requisition.
     */
    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    /**
     * Generate a unique requisition number.
     */
    public static function generateRequisitionNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'REQ-' . $year . $month;
        
        $lastRequisition = self::where('requisition_number', 'LIKE', $prefix . '%')
            ->orderBy('requisition_number', 'desc')
            ->first();
        
        if ($lastRequisition) {
            $lastNumber = (int) substr($lastRequisition->requisition_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . '-' . $newNumber;
    }

    /**
     * Scope for pending requisitions.
     */
    public function scopePending($query)
    {
        return $query->where('approve_status', 'pending');
    }

    /**
     * Scope for approved requisitions.
     */
    public function scopeApproved($query)
    {
        return $query->where('approve_status', 'approved');
    }

    /**
     * Scope for rejected requisitions.
     */
    public function scopeRejected($query)
    {
        return $query->where('approve_status', 'rejected');
    }

    
    /**
     * Scope for pending clearance requisitions.
     */
    public function scopePendingClearance($query)
    {
        return $query->where('clear_status', 'pending');
    }

    /**
     * Scope for cleared requisitions.
     */
    public function scopeCleared($query)
    {
        return $query->where('clear_status', 'cleared');
    }

    /**
     * Scope to get only active requisitions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}