<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequisitionApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of all requisitions.
     */
    public function index(Request $request)
    {
        $query = Requisition::with(['user', 'department', 'subDepartment', 'division', 'items']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $requisitions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.requisitions.index', compact('requisitions'));
    }

    /**
     * Display the specified requisition.
     */
    public function show(Requisition $requisition)
    {
        $requisition->load(['department', 'subDepartment', 'division', 'items', 'user', 'approvedBy']);
        
        return view('admin.requisitions.show', compact('requisition'));
    }

    /**
     * Approve the requisition.
     */
    public function approve(Request $request, Requisition $requisition)
    {
        if ($requisition->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending requisitions can be approved.');
        }

        $requisition->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.requisitions.show', $requisition->id)
            ->with('success', 'Requisition approved successfully.');
    }

    /**
     * Reject the requisition.
     */
    public function reject(Request $request, Requisition $requisition)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($requisition->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending requisitions can be rejected.');
        }

        $requisition->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.requisitions.show', $requisition->id)
            ->with('success', 'Requisition rejected.');
    }
}