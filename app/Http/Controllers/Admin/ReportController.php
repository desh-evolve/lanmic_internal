<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionIssuedItem;
use App\Models\PurchaseOrderItem;
use App\Models\ReturnModel;
use App\Models\ReturnItem;
use App\Exports\InventoryMovementExport;
use App\Models\GrnItem;
use App\Models\ScrapItem;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RequisitionSummaryExport;
use App\Exports\ItemRequisitionExport;
use App\Exports\IssuedItemsExport;
use App\Exports\PurchaseOrderExport;
use App\Exports\ReturnsSummaryExport;
use App\Exports\GrnExport;
use App\Exports\ScrapExport;
use App\Exports\DepartmentActivityExport;
use App\Exports\UserActivityExport;
use App\Exports\MonthlySummaryExport;

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
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'requisition_summary_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new RequisitionSummaryExport($request->all()), $filename);
        }

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
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'item_requisition_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new ItemRequisitionExport($request->all()), $filename);
        }

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
            DB::raw('SUM(quantity) as total_quantity')
        )
        ->where('status', '!=', 'delete')
        ->groupBy('item_code', 'item_name')
        ->orderBy('total_quantity', 'desc')
        ->limit(10)
        ->get();

        return view('admin.reports.item-requisition', compact('items', 'itemStats'));
    }

    /**
     * Build a base issued-items query with all common filters applied.
     */
    private function buildIssuedQuery(Request $request)
    {
        $query = RequisitionIssuedItem::with([
                'requisition.user', 'requisition.department',
                'requisition.subDepartment', 'requisitionItem', 'issuedBy',
            ])
            ->where('requisition_issued_items.status', '!=', 'delete');

        if ($request->filled('date_from'))    $query->whereDate('issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))      $query->whereDate('issued_at', '<=', $request->date_to);
        if ($request->filled('item_code'))    $query->where('item_code',  'like', '%' . $request->item_code  . '%');
        if ($request->filled('item_name'))    $query->where('item_name',  'like', '%' . $request->item_name  . '%');
        if ($request->filled('department_id')) {
            $query->whereHas('requisition', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('category'))     $query->where('item_category', 'like', '%' . $request->category . '%');

        return $query;
    }

    /**
     * Build department-wise totals for issued items with the same filters.
     */
    private function buildIssuedDeptTotals(Request $request)
    {
        $query = DB::table('requisition_issued_items as rii')
            ->join('requisitions as r', 'rii.requisition_id', '=', 'r.id')
            ->join('departments as d', 'r.department_id', '=', 'd.id')
            ->where('rii.status', '!=', 'delete')
            ->select(
                'd.name as dept_name',
                DB::raw('SUM(rii.issued_quantity) as total_qty'),
                DB::raw('SUM(rii.total_price) as total_value')
            );

        if ($request->filled('date_from'))     $query->whereDate('rii.issued_at', '>=', $request->date_from);
        if ($request->filled('date_to'))       $query->whereDate('rii.issued_at', '<=', $request->date_to);
        if ($request->filled('item_code'))     $query->where('rii.item_code', 'like', '%' . $request->item_code . '%');
        if ($request->filled('item_name'))     $query->where('rii.item_name', 'like', '%' . $request->item_name . '%');
        if ($request->filled('department_id')) $query->where('r.department_id', $request->department_id);
        if ($request->filled('category'))      $query->where('rii.item_category', 'like', '%' . $request->category . '%');

        return $query->groupByRaw('d.id, d.name')->orderByRaw('d.name ASC')->get();
    }

    /**
     * Issued Items Report.
     */
    public function issuedItems(Request $request)
    {
        if ($request->has('export') && $request->export === 'excel') {
            return Excel::download(
                new IssuedItemsExport($request->all()),
                'issued_items_' . date('Y-m-d_H-i-s') . '.xlsx'
            );
        }

        $query       = $this->buildIssuedQuery($request);
        $issuedItems = (clone $query)
            ->orderByRaw('(SELECT d.name FROM departments d JOIN requisitions r ON r.department_id = d.id WHERE r.id = requisition_issued_items.requisition_id LIMIT 1) ASC')
            ->orderBy('issued_at', 'desc')
            ->paginate(50)->appends($request->query());
        $statistics  = [
            'total_issued'   => $query->count(),
            'total_quantity' => $query->sum('issued_quantity'),
            'total_value'    => $query->sum('total_price'),
        ];
        $departments = Department::active()->orderBy('name')->get();
        $deptTotals  = $this->buildIssuedDeptTotals($request);

        return view('admin.reports.issued-items', compact('issuedItems', 'statistics', 'departments', 'deptTotals'));
    }

    /**
     * Local Item Issuing Report.
     */
    public function localIssuedItems(Request $request)
    {
        if (!$request->filled('category')) $request->merge(['category' => 'LOCAL']);

        if ($request->has('export') && $request->export === 'excel') {
            return Excel::download(
                new IssuedItemsExport($request->all()),
                'local_issued_items_' . date('Y-m-d_H-i-s') . '.xlsx'
            );
        }

        $query       = $this->buildIssuedQuery($request);
        $issuedItems = (clone $query)
            ->orderByRaw('(SELECT d.name FROM departments d JOIN requisitions r ON r.department_id = d.id WHERE r.id = requisition_issued_items.requisition_id LIMIT 1) ASC')
            ->orderBy('issued_at', 'desc')
            ->paginate(50)->appends($request->query());
        $statistics  = [
            'total_issued'   => $query->count(),
            'total_quantity' => $query->sum('issued_quantity'),
            'total_value'    => $query->sum('total_price'),
        ];
        $departments = Department::active()->orderBy('name')->get();
        $deptTotals  = $this->buildIssuedDeptTotals($request);

        return view('admin.reports.local-issued-items', compact('issuedItems', 'statistics', 'departments', 'deptTotals'));
    }

    /**
     * Import Item Issuing Report.
     */
    public function importIssuedItems(Request $request)
    {
        if (!$request->filled('category')) $request->merge(['category' => 'IMPORT']);

        if ($request->has('export') && $request->export === 'excel') {
            return Excel::download(
                new IssuedItemsExport($request->all()),
                'import_issued_items_' . date('Y-m-d_H-i-s') . '.xlsx'
            );
        }

        $query       = $this->buildIssuedQuery($request);
        $issuedItems = (clone $query)
            ->orderByRaw('(SELECT d.name FROM departments d JOIN requisitions r ON r.department_id = d.id WHERE r.id = requisition_issued_items.requisition_id LIMIT 1) ASC')
            ->orderBy('issued_at', 'desc')
            ->paginate(50)->appends($request->query());
        $statistics  = [
            'total_issued'   => $query->count(),
            'total_quantity' => $query->sum('issued_quantity'),
            'total_value'    => $query->sum('total_price'),
        ];
        $departments = Department::active()->orderBy('name')->get();
        $deptTotals  = $this->buildIssuedDeptTotals($request);

        return view('admin.reports.import-issued-items', compact('issuedItems', 'statistics', 'departments', 'deptTotals'));
    }

    /**
     * Purchase Order Report.
     */
    public function purchaseOrder(Request $request)
    {
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'purchase_order_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new PurchaseOrderExport($request->all()), $filename);
        }

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
        ];

        return view('admin.reports.purchase-order', compact('poItems', 'statistics'));
    }

    /**
     * Returns Summary Report.
     */
    public function returnsSummary(Request $request)
    {
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'returns_summary_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new ReturnsSummaryExport($request->all()), $filename);
        }

        $query = ReturnItem::with([
                'return.returnedBy',
                'return.requisition.department',
                'return.requisition.subDepartment',
                'issuedItem',
                'approvedBy',
            ])
            ->where('status', 'active');

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereHas('return', fn($q) => $q->whereDate('returned_at', '>=', $request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereHas('return', fn($q) => $q->whereDate('returned_at', '<=', $request->date_to));
        }

        // Status filter (return-level)
        if ($request->filled('status')) {
            $query->whereHas('return', fn($q) => $q->where('status', $request->status));
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->whereHas('return', fn($q) => $q->where('returned_by', $request->user_id));
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->whereHas('return.requisition', fn($q) => $q->where('department_id', $request->department_id));
        }

        // Item name filter
        if ($request->filled('item_name')) {
            $query->where('item_name', 'like', '%' . $request->item_name . '%');
        }

        $returns = $query->orderByDesc(
            ReturnModel::select('returned_at')->whereColumn('returns.id', 'return_items.return_id')->limit(1)
        )->paginate(50)->appends($request->query());

        // Statistics
        $statistics = [
            'total_returns' => ReturnModel::where('status', '!=', 'delete')->count(),
            'pending'       => ReturnModel::where('status', 'pending')->count(),
            'cleared'       => ReturnModel::where('status', 'cleared')->count(),
            'total_items'   => $query->count(),
        ];

        $users       = User::all();
        $departments = Department::active()->orderBy('name')->get();

        // Dept-wise totals (using issuedItem price for value)
        $deptTotals = DB::table('return_items as ri')
            ->join('returns as ret', 'ri.return_id', '=', 'ret.id')
            ->join('requisitions as req', 'ret.requisition_id', '=', 'req.id')
            ->join('departments as d', 'req.department_id', '=', 'd.id')
            ->leftJoin('requisition_issued_items as rii', 'ri.requisition_issued_item_id', '=', 'rii.id')
            ->where('ri.status', 'active')
            ->select(
                'd.name as dept_name',
                DB::raw('SUM(ri.quantity) as total_qty'),
                DB::raw('SUM(COALESCE(rii.unit_price, 0) * ri.quantity) as total_value')
            );

        if ($request->filled('date_from'))     $deptTotals->whereDate('ret.returned_at', '>=', $request->date_from);
        if ($request->filled('date_to'))       $deptTotals->whereDate('ret.returned_at', '<=', $request->date_to);
        if ($request->filled('status'))        $deptTotals->where('ret.status', $request->status);
        if ($request->filled('user_id'))       $deptTotals->where('ret.returned_by', $request->user_id);
        if ($request->filled('department_id')) $deptTotals->where('req.department_id', $request->department_id);
        if ($request->filled('item_name'))     $deptTotals->where('ri.item_name', 'like', '%' . $request->item_name . '%');

        $deptTotals = $deptTotals->groupByRaw('d.id, d.name')->orderByRaw('d.name ASC')->get();

        return view('admin.reports.returns-summary', compact('returns', 'statistics', 'users', 'departments', 'deptTotals'));
    }

    /**
     * GRN Report.
     */
    public function grn(Request $request)
    {
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'grn_report_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new GrnExport($request->all()), $filename);
        }

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
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'scrap_report_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new ScrapExport($request->all()), $filename);
        }

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
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'department_activity_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new DepartmentActivityExport($request->all()), $filename);
        }

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
                'pending_requisitions' => Requisition::where('department_id', $department->id)->where('status', 'active')->where('approve_status', 'pending')->count(),
                'approved_requisitions' => Requisition::where('department_id', $department->id)->where('status', 'active')->where('approve_status', 'approved')->count(),
                'rejected_requisitions' => Requisition::where('department_id', $department->id)->where('status', 'active')->where('approve_status', 'rejected')->count(),
                'total_items' => RequisitionItem::whereIn('requisition_id', $requisitionIds)->sum('quantity'),
            ];
        }

        return view('admin.reports.department-activity', compact('reportData'));
    }

    /**
     * User Activity Report.
     */
    public function userActivity(Request $request)
    {
        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'user_activity_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new UserActivityExport($request->all()), $filename);
        }

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
                'pending_requisitions' => (clone $requisitions)->where('approve_status', 'pending')->count(),
                'approved_requisitions' => (clone $requisitions)->where('approve_status', 'approved')->count(),
                'total_returns' => $returns->count(),
                'pending_returns' => (clone $returns)->where('status', 'pending')->count(),
            ];
        }

        return view('admin.reports.user-activity', compact('reportData'));
    }

    /**
     * Inventory Movement Report — mirrors Sage 300 ICMVMT02.
     * Shows all Inventory Out (Issues) and Inventory In (GRN) grouped by item,
     * with document number, invoice number, department, remarks, and running totals.
     */
    public function inventoryMovement(Request $request)
    {
        $dateFrom    = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo      = $request->input('date_to',   Carbon::now()->toDateString());
        $itemCode    = $request->input('item_code');
        $deptId      = $request->input('department_id');

        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'inventory_movement_' . $dateFrom . '_to_' . $dateTo . '.xlsx';
            return Excel::download(new InventoryMovementExport($dateFrom, $dateTo, $itemCode, $deptId), $filename);
        }

        // ── Inventory OUT — Issued Items ──────────────────────────────────
        $issuesQuery = RequisitionIssuedItem::with(['requisition.department', 'requisition.subDepartment', 'issuedBy'])
            ->where('status', '!=', 'delete')
            ->whereDate('issued_at', '>=', $dateFrom)
            ->whereDate('issued_at', '<=', $dateTo);

        if ($itemCode) $issuesQuery->where('item_code', 'like', '%' . $itemCode . '%');
        if ($deptId)   $issuesQuery->whereHas('requisition', fn($q) => $q->where('department_id', $deptId));

        $issues = $issuesQuery->orderBy('issued_at')->get();

        // ── Inventory IN — GRN Items ──────────────────────────────────────
        $grnQuery = GrnItem::with(['return.requisition.department', 'processedBy'])
            ->where('status', '!=', 'delete')
            ->whereDate('processed_at', '>=', $dateFrom)
            ->whereDate('processed_at', '<=', $dateTo);

        if ($itemCode) $grnQuery->where('item_code', 'like', '%' . $itemCode . '%');

        $grns = $grnQuery->orderBy('processed_at')->get();

        // ── Merge into item-grouped movements ────────────────────────────
        $movements = collect();

        foreach ($issues as $issue) {
            $movements->push([
                'date'         => $issue->issued_at,
                'document_no'  => $issue->reference_number_1 ?? $issue->requisition?->requisition_number ?? '—',
                'invoice_no'   => $issue->reference_number_2 ?? '—',
                'vendor_no'    => '—',
                'vendor_name'  => '—',
                'type'         => 'Internal Usage',
                'unit'         => $issue->unit ?? '—',
                'department'   => $issue->requisition?->department?->name ?? '—',
                'sub_dept'     => $issue->requisition?->subDepartment?->name ?? '',
                'remarks'      => $issue->notes ?? '—',
                'qty_in'       => 0,
                'cost_in'      => 0,
                'qty_out'      => (float) $issue->issued_quantity,
                'cost_out'     => (float) $issue->total_price,
                'item_code'    => $issue->item_code,
                'item_name'    => $issue->item_name,
            ]);
        }

        foreach ($grns as $grn) {
            $movements->push([
                'date'         => $grn->processed_at,
                'document_no'  => $grn->reference_number_1 ?? '—',
                'invoice_no'   => $grn->reference_number_2 ?? '—',
                'vendor_no'    => '—',
                'vendor_name'  => '—',
                'type'         => 'GRN',
                'unit'         => $grn->unit ?? '—',
                'department'   => $grn->return?->requisition?->department?->name ?? '—',
                'sub_dept'     => '',
                'remarks'      => '—',
                'qty_in'       => (float) $grn->grn_quantity,
                'cost_in'      => (float) $grn->total_price,
                'qty_out'      => 0,
                'cost_out'     => 0,
                'item_code'    => $grn->item_code,
                'item_name'    => $grn->item_name,
            ]);
        }

        // Group by item code, sorted by date within each group
        $grouped = $movements
            ->sortBy('date')
            ->groupBy('item_code')
            ->sortKeys();

        $departments = Department::active()->get();

        return view('admin.reports.inventory-movement', compact(
            'grouped', 'dateFrom', 'dateTo', 'itemCode', 'deptId', 'departments'
        ));
    }

    /**
     * Monthly Summary Report.
     */
    public function monthlySummary(Request $request)
    {
        $year         = $request->input('year', date('Y'));
        $departmentId = $request->input('department_id');
        $subDeptId    = $request->input('sub_department_id');

        // Check if export is requested
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'monthly_summary_' . $year . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new MonthlySummaryExport($year, $departmentId, $subDeptId), $filename);
        }

        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

            $requisitions = Requisition::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'active');
            if ($departmentId) $requisitions->where('department_id', $departmentId);
            if ($subDeptId)    $requisitions->where('sub_department_id', $subDeptId);

            $reqIds = (clone $requisitions)->pluck('id');

            $returns = ReturnModel::whereBetween('returned_at', [$startDate, $endDate])
                ->where('status', '!=', 'delete');
            if ($reqIds->isNotEmpty()) {
                $returns->whereIn('requisition_id', $reqIds);
            } elseif ($departmentId || $subDeptId) {
                $returns->whereRaw('0=1'); // no requisitions match → no returns
            }

            $issuedQuery = RequisitionIssuedItem::whereBetween('issued_at', [$startDate, $endDate])
                ->where('status', '!=', 'delete');
            if ($reqIds->isNotEmpty()) {
                $issuedQuery->whereIn('requisition_id', $reqIds);
            } elseif ($departmentId || $subDeptId) {
                $issuedQuery->whereRaw('0=1');
            }

            $months[] = [
                'month'                 => $startDate->format('F'),
                'requisitions_count'    => $requisitions->count(),
                'requisitions_approved' => (clone $requisitions)->where('approve_status', 'approved')->count(),
                'returns_count'         => $returns->count(),
                'returns_cleared'       => (clone $returns)->where('status', 'cleared')->count(),
                'issued_items'          => (clone $issuedQuery)->sum('issued_quantity'),
                'issued_cost'           => (clone $issuedQuery)->sum('total_price'),
                'grn_items'             => GrnItem::whereBetween('created_at', [$startDate, $endDate])
                                            ->where('status', '!=', 'delete')->sum('grn_quantity'),
            ];
        }

        $years       = range(date('Y'), date('Y') - 5);
        $departments = Department::active()->get();

        // Sub-departments for the selected department
        $subDepartments = $departmentId
            ? \App\Models\SubDepartment::where('department_id', $departmentId)->get()
            : collect();

        return view('admin.reports.monthly-summary', compact(
            'months', 'year', 'years', 'departments', 'subDepartments',
            'departmentId', 'subDeptId'
        ));
    }
}