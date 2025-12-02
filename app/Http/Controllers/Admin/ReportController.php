<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use App\Models\PurchaseOrderItem;
use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Models\GrnItem;
use App\Models\ScrapItem;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display reports dashboard.
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Requisition Summary Report.
     */
    public function requisitionSummary(Request $request)
    {
        $query = Requisition::with(['user', 'department', 'items'])
            ->where('status', 'active');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('approve_status')) {
            $query->where('approve_status', $request->approve_status);
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $requisitions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Calculate statistics
        $statistics = [
            'total_requisitions' => $query->count(),
            'total_items' => RequisitionItem::whereIn('requisition_id', $query->pluck('id'))->sum('quantity'),
            'total_value' => RequisitionItem::whereIn('requisition_id', $query->pluck('id'))->sum('total_price'),
            'pending' => Requisition::where('approve_status', 'pending')->where('status', 'active')->count(),
            'approved' => Requisition::where('approve_status', 'approved')->where('status', 'active')->count(),
            'rejected' => Requisition::where('approve_status', 'rejected')->where('status', 'active')->count(),
        ];

        $departments = Department::active()->get();
        $users = User::all();

        return view('admin.reports.requisition-summary', compact('requisitions', 'statistics', 'departments', 'users'));
    }

    /**
     * Item Requisition Report.
     */
    public function itemRequisition(Request $request)
    {
        $query = RequisitionItem::with(['requisition.user', 'requisition.department'])
            ->where('status', '!=', 'delete');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereHas('requisition', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            });
        }
        if ($request->filled('date_to')) {
            $query->whereHas('requisition', function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            });
        }

        // Item code filter
        if ($request->filled('item_code')) {
            $query->where('item_code', 'like', '%' . $request->item_code . '%');
        }

        // Item name filter
        if ($request->filled('item_name')) {
            $query->where('item_name', 'like', '%' . $request->item_name . '%');
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(50);

        // Group by item statistics
        $itemStats = RequisitionItem::select(
            'item_code',
            'item_name',
            DB::raw('COUNT(*) as request_count'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total_price) as total_value')
        )
        ->where('status', '!=', 'delete')
        ->groupBy('item_code', 'item_name')
        ->orderBy('total_quantity', 'desc')
        ->limit(10)
        ->get();

        return view('admin.reports.item-requisition', compact('items', 'itemStats'));
    }

    /**
     * Issued Items Report.
     */
    public function issuedItems(Request $request)
    {
        $query = RequisitionIssuedItem::with(['requisition.user', 'requisitionItem'])
            ->where('status', '!=', 'delete');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('issued_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issued_at', '<=', $request->date_to);
        }

        // Item filter
        if ($request->filled('item_code')) {
            $query->where('item_code', 'like', '%' . $request->item_code . '%');
        }

        $issuedItems = $query->orderBy('issued_at', 'desc')->paginate(50);

        // Statistics
        $statistics = [
            'total_issued' => $query->count(),
            'total_quantity' => $query->sum('issued_quantity'),
            'total_value' => $query->sum('total_price'),
        ];

        return view('admin.reports.issued-items', compact('issuedItems', 'statistics'));
    }

    /**
     * Purchase Order Report.
     */
    public function purchaseOrder(Request $request)
    {
        $query = PurchaseOrderItem::with(['requisition.user', 'requisition.department'])
            ->whereHas('requisition', function($q) {
                $q->where('status', 'active');
            });

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $poItems = $query->orderBy('created_at', 'desc')->paginate(50);

        // Statistics
        $statistics = [
            'total_po_items' => $query->count(),
            'pending' => PurchaseOrderItem::where('status', 'pending')->count(),
            'cleared' => PurchaseOrderItem::where('status', 'cleared')->count(),
            'total_value' => $query->sum('total_price'),
        ];

        return view('admin.reports.purchase-order', compact('poItems', 'statistics'));
    }

    /**
     * Returns Summary Report.
     */
    public function returnsSummary(Request $request)
    {
        $query = ReturnModel::with(['returnedBy', 'items'])
            ->where('status', '!=', 'delete');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('returned_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('returned_at', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('returned_by', $request->user_id);
        }

        $returns = $query->orderBy('returned_at', 'desc')->paginate(50);

        // Statistics
        $statistics = [
            'total_returns' => $query->count(),
            'pending' => ReturnModel::where('status', 'pending')->count(),
            'cleared' => ReturnModel::where('status', 'cleared')->count(),
            'total_items' => ReturnItem::whereIn('return_id', $query->pluck('id'))->sum('return_quantity'),
        ];

        $users = User::all();

        return view('admin.reports.returns-summary', compact('returns', 'statistics', 'users'));
    }

    /**
     * GRN Report.
     */
    public function grn(Request $request)
    {
        $query = GrnItem::with(['return.returnedBy', 'returnItem'])
            ->where('status', '!=', 'delete');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Item filter
        if ($request->filled('item_code')) {
            $query->where('item_code', 'like', '%' . $request->item_code . '%');
        }

        $grnItems = $query->orderBy('created_at', 'desc')->paginate(50);

        // Statistics
        $statistics = [
            'total_grn_items' => $query->count(),
            'total_quantity' => $query->sum('grn_quantity'),
            'total_value' => $query->sum('total_price'),
        ];

        // Group by item
        $itemStats = GrnItem::select(
            'item_code',
            'item_name',
            DB::raw('COUNT(*) as grn_count'),
            DB::raw('SUM(grn_quantity) as total_quantity'),
            DB::raw('SUM(total_price) as total_value')
        )
        ->where('status', '!=', 'delete')
        ->groupBy('item_code', 'item_name')
        ->orderBy('total_quantity', 'desc')
        ->limit(10)
        ->get();

        return view('admin.reports.grn', compact('grnItems', 'statistics', 'itemStats'));
    }

    /**
     * Scrap Report.
     */
    public function scrap(Request $request)
    {
        $query = ScrapItem::with(['return.returnedBy', 'returnItem'])
            ->where('status', '!=', 'delete');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Item filter
        if ($request->filled('item_code')) {
            $query->where('item_code', 'like', '%' . $request->item_code . '%');
        }

        $scrapItems = $query->orderBy('created_at', 'desc')->paginate(50);

        // Statistics
        $statistics = [
            'total_scrap_items' => $query->count(),
            'total_quantity' => $query->sum('scrap_quantity'),
            'total_value' => $query->sum('total_price'),
        ];

        // Group by item
        $itemStats = ScrapItem::select(
            'item_code',
            'item_name',
            DB::raw('COUNT(*) as scrap_count'),
            DB::raw('SUM(scrap_quantity) as total_quantity'),
            DB::raw('SUM(total_price) as total_value')
        )
        ->where('status', '!=', 'delete')
        ->groupBy('item_code', 'item_name')
        ->orderBy('total_quantity', 'desc')
        ->limit(10)
        ->get();

        return view('admin.reports.scrap', compact('scrapItems', 'statistics', 'itemStats'));
    }

    /**
     * Department Activity Report.
     */
    public function departmentActivity(Request $request)
    {
        $departments = Department::active()->get();

        $reportData = [];

        foreach ($departments as $department) {
            $requisitions = Requisition::where('department_id', $department->id)
                ->where('status', 'active');

            // Date filter
            if ($request->filled('date_from')) {
                $requisitions->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $requisitions->whereDate('created_at', '<=', $request->date_to);
            }

            $requisitionIds = $requisitions->pluck('id');

            $reportData[] = [
                'department' => $department,
                'total_requisitions' => $requisitions->count(),
                'pending_requisitions' => $requisitions->where('approve_status', 'pending')->count(),
                'approved_requisitions' => $requisitions->where('approve_status', 'approved')->count(),
                'rejected_requisitions' => $requisitions->where('approve_status', 'rejected')->count(),
                'total_items' => RequisitionItem::whereIn('requisition_id', $requisitionIds)->sum('quantity'),
                'total_value' => RequisitionItem::whereIn('requisition_id', $requisitionIds)->sum('total_price'),
            ];
        }

        return view('admin.reports.department-activity', compact('reportData'));
    }

    /**
     * User Activity Report.
     */
    public function userActivity(Request $request)
    {
        $users = User::with('roles')->get();

        $reportData = [];

        foreach ($users as $user) {
            $requisitions = Requisition::where('user_id', $user->id)
                ->where('status', 'active');

            $returns = ReturnModel::where('returned_by', $user->id)
                ->where('status', '!=', 'delete');

            // Date filter
            if ($request->filled('date_from')) {
                $requisitions->whereDate('created_at', '>=', $request->date_from);
                $returns->whereDate('returned_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $requisitions->whereDate('created_at', '<=', $request->date_to);
                $returns->whereDate('returned_at', '<=', $request->date_to);
            }

            $reportData[] = [
                'user' => $user,
                'total_requisitions' => $requisitions->count(),
                'pending_requisitions' => $requisitions->where('approve_status', 'pending')->count(),
                'approved_requisitions' => $requisitions->where('approve_status', 'approved')->count(),
                'total_returns' => $returns->count(),
                'pending_returns' => $returns->where('status', 'pending')->count(),
            ];
        }

        return view('admin.reports.user-activity', compact('reportData'));
    }

    /**
     * Monthly Summary Report.
     */
    public function monthlySummary(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $requisitions = Requisition::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'active');

            $returns = ReturnModel::whereBetween('returned_at', [$startDate, $endDate])
                ->where('status', '!=', 'delete');

            $months[] = [
                'month' => $startDate->format('F'),
                'requisitions_count' => $requisitions->count(),
                'requisitions_approved' => $requisitions->where('approve_status', 'approved')->count(),
                'returns_count' => $returns->count(),
                'returns_cleared' => $returns->where('status', 'cleared')->count(),
                'issued_items' => RequisitionIssuedItem::whereBetween('issued_at', [$startDate, $endDate])
                    ->where('status', '!=', 'delete')->sum('issued_quantity'),
                'grn_items' => GrnItem::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', '!=', 'delete')->sum('grn_quantity'),
            ];
        }

        $years = range(date('Y'), date('Y') - 5);

        return view('admin.reports.monthly-summary', compact('months', 'year', 'years'));
    }
}