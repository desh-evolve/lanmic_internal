<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        // "Admin" dashboard widgets are shown to full-access holders (permission, not role name).
        $isAdmin = $user->hasPermission('full-access');

        // Get date ranges
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Basic Statistics
        $stats = [
            'total_requisitions' => Requisition::count(),
            'pending_requisitions' => Requisition::where('approve_status', 'pending')->count(),
            'approved_requisitions' => Requisition::where('approve_status', 'approved')->count(),
            'rejected_requisitions' => Requisition::where('approve_status', 'rejected')->count(),
            'total_returns' => ReturnModel::count(),
            'pending_returns' => ReturnModel::where('status', 'pending')->count(),
            'cleared_returns' => ReturnModel::where('status', 'cleared')->count(),
            'total_users' => User::count(),
            'total_departments' => Department::count(),
        ];

        // This month stats
        $thisMonthStats = [
            'requisitions' => Requisition::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count(),
            'approved' => Requisition::where('approve_status', 'approved')->whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count(),
            'returns' => ReturnModel::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count(),
            'issued_items' => RequisitionIssuedItem::whereMonth('issued_at', $today->month)->whereYear('issued_at', $today->year)->sum('issued_quantity'),
        ];

        // Last month stats for comparison
        $lastMonthStats = [
            'requisitions' => Requisition::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count(),
            'approved' => Requisition::where('approve_status', 'approved')->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count(),
            'returns' => ReturnModel::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count(),
        ];

        // Calculate percentage changes
        $changes = [
            'requisitions' => $this->calculateChange($thisMonthStats['requisitions'], $lastMonthStats['requisitions']),
            'approved' => $this->calculateChange($thisMonthStats['approved'], $lastMonthStats['approved']),
            'returns' => $this->calculateChange($thisMonthStats['returns'], $lastMonthStats['returns']),
        ];

        // Monthly trend data (last 6 months)
        $monthlyTrends = $this->getMonthlyTrends(6);

        // Department-wise requisitions (top 5)
        $departmentStats = Requisition::select('department_id', DB::raw('count(*) as total'))
            ->with('department')
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Requisition status distribution
        $statusDistribution = [
            'pending' => $stats['pending_requisitions'],
            'approved' => $stats['approved_requisitions'],
            'rejected' => $stats['rejected_requisitions'],
        ];

        // Recent pending requisitions (for admin)
        $pendingRequisitions = Requisition::where('approve_status', 'pending')
            ->with(['user', 'department', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent pending returns (for admin)
        $pendingReturns = ReturnModel::where('status', 'pending')
            ->with(['returnedBy', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top requested items (this month)
        $topItems = RequisitionItem::select('item_code', 'item_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('COUNT(*) as request_count'))
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->where('status', '!=', 'delete')
            ->groupBy('item_code', 'item_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Recent activity log
        $recentActivity = $this->getRecentActivity(10);

        // User's own stats (for non-admin or additional info)
        $userStats = [
            'my_requisitions' => $user->requisitions()->count(),
            'my_pending' => $user->requisitions()->where('approve_status', 'pending')->count(),
            'my_approved' => $user->requisitions()->where('approve_status', 'approved')->count(),
            'my_rejected' => $user->requisitions()->where('approve_status', 'rejected')->count(),
        ];

        return view('home', compact(
            'stats',
            'thisMonthStats',
            'changes',
            'monthlyTrends',
            'departmentStats',
            'statusDistribution',
            'pendingRequisitions',
            'pendingReturns',
            'topItems',
            'recentActivity',
            'userStats',
            'isAdmin'
        ));
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getMonthlyTrends($months)
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $trends[] = [
                'month' => $date->format('M'),
                'requisitions' => Requisition::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'approved' => Requisition::where('approve_status', 'approved')->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'returns' => ReturnModel::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            ];
        }
        return $trends;
    }

    private function getRecentActivity($limit)
    {
        $activities = collect();

        // Recent requisitions
        $recentRequisitions = Requisition::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($req) {
                return [
                    'type' => 'requisition',
                    'icon' => 'fa-file-alt',
                    'color' => 'primary',
                    'title' => 'New Requisition',
                    'description' => $req->requisition_number . ' by ' . ($req->user->name ?? 'Unknown'),
                    'status' => $req->approve_status,
                    'time' => $req->created_at,
                ];
            });

        // Recent returns
        $recentReturns = ReturnModel::with('returnedBy')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($ret) {
                return [
                    'type' => 'return',
                    'icon' => 'fa-undo-alt',
                    'color' => 'warning',
                    'title' => 'Item Return',
                    'description' => ($ret->return_no ?? 'RET-' . str_pad($ret->id, 6, '0', STR_PAD_LEFT)) . ' by ' . ($ret->returnedBy->name ?? 'Unknown'),
                    'status' => $ret->status,
                    'time' => $ret->created_at,
                ];
            });

        // Recent issued items
        $recentIssued = RequisitionIssuedItem::with(['requisition.user'])
            ->orderBy('issued_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'issued',
                    'icon' => 'fa-hand-holding',
                    'color' => 'success',
                    'title' => 'Item Issued',
                    'description' => $item->item_name . ' (' . $item->issued_quantity . ' qty)',
                    'status' => 'issued',
                    'time' => $item->issued_at,
                ];
            });

        return $activities
            ->merge($recentRequisitions)
            ->merge($recentReturns)
            ->merge($recentIssued)
            ->sortByDesc('time')
            ->take($limit)
            ->values();
    }
}
