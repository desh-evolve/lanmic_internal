<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SubDepartmentController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\RequisitionApprovalController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\Admin\ReturnApprovalController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\PurchaseOrderController;

use App\Http\Controllers\Sage300Controller;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Sage 300 item API routes — accessible to all authenticated users (read-only reference data).
    // Kept under /admin/sage300/api to match sage300.js without requiring a browser cache bust.
    Route::prefix('admin/sage300/api')->group(function () {
        Route::get('/items', [Sage300Controller::class, 'getItems']);
        Route::get('/items/{code}', [Sage300Controller::class, 'getItemDetails']);
        Route::get('/items/{itemCode}/locations', [Sage300Controller::class, 'getItemLocations']);
        Route::get('/locations', [Sage300Controller::class, 'getLocations']);
    });

    // Requisition routes
    Route::get('requisitions', [RequisitionController::class, 'index'])->middleware('permission:view-requisitions')->name('requisitions.index');
    Route::get('requisitions/create', [RequisitionController::class, 'create'])->middleware('permission:create-requisitions')->name('requisitions.create');
    Route::post('requisitions', [RequisitionController::class, 'store'])->middleware('permission:create-requisitions')->name('requisitions.store');
    Route::get('requisitions/{requisition}', [RequisitionController::class, 'show'])->middleware('permission:view-requisitions')->name('requisitions.show');
    Route::get('requisitions/{requisition}/edit', [RequisitionController::class, 'edit'])->middleware('permission:edit-requisitions')->name('requisitions.edit');
    Route::put('requisitions/{requisition}', [RequisitionController::class, 'update'])->middleware('permission:edit-requisitions')->name('requisitions.update');
    Route::patch('requisitions/{requisition}', [RequisitionController::class, 'update']);
    Route::delete('requisitions/{requisition}', [RequisitionController::class, 'destroy'])->middleware('permission:delete-requisitions')->name('requisitions.destroy');

    // Requisition supporting API endpoints
    Route::middleware(['permission:view-requisitions,create-requisitions'])->group(function () {
        Route::get('api/items/{itemCode}/availability', [RequisitionController::class, 'getItemAvailability']);
        Route::get('api/departments/{department}/sub-departments', [RequisitionController::class, 'getSubDepartments']);
        Route::get('api/sub-departments/{subDepartment}/divisions', [RequisitionController::class, 'getDivisions']);
        Route::get('api/requisitions/pending-items', [RequisitionController::class, 'getPendingApprovalItems']);
    });

    // Return routes
    Route::get('returns', [ReturnController::class, 'index'])->middleware('permission:view-returns')->name('returns.index');
    Route::get('returns/create', [ReturnController::class, 'create'])->middleware('permission:create-returns')->name('returns.create');
    Route::post('returns', [ReturnController::class, 'store'])->middleware('permission:create-returns')->name('returns.store');
    Route::get('returns/{return}', [ReturnController::class, 'show'])->middleware('permission:view-returns')->name('returns.show');
    Route::get('returns/{return}/edit', [ReturnController::class, 'edit'])->middleware('permission:edit-returns')->name('returns.edit');
    Route::put('returns/{return}', [ReturnController::class, 'update'])->middleware('permission:edit-returns')->name('returns.update');
    Route::patch('returns/{return}', [ReturnController::class, 'update']);
    Route::delete('returns/{return}', [ReturnController::class, 'destroy'])->middleware('permission:delete-returns')->name('returns.destroy');

    // Returns supporting API endpoints
    Route::middleware(['permission:view-returns,create-returns'])->group(function () {
        Route::get('api/returns/items-by-type/{type}', [ReturnController::class, 'getItemsByType']);
        Route::get('api/requisitions/{requisition}/issued-items', [ReturnController::class, 'getIssuedItems']);
    });

    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // User Management
        Route::get('users', [UserController::class, 'index'])->middleware('permission:view-users')->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->middleware('permission:create-users')->name('users.create');
        Route::post('users', [UserController::class, 'store'])->middleware('permission:create-users')->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:view-users')->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:edit-users')->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:edit-users')->name('users.update');
        Route::patch('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-users')->name('users.destroy');
        Route::get('users/{user}/permissions', [UserController::class, 'permissions'])->middleware('permission:assign-user-permissions')->name('users.permissions');
        Route::post('users/{user}/permissions', [UserController::class, 'updatePermissions'])->middleware('permission:assign-user-permissions')->name('users.permissions.update');

        // Role Management
        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:view-roles')->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->middleware('permission:create-roles')->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:create-roles')->name('roles.store');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:view-roles')->name('roles.show');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:edit-roles')->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:edit-roles')->name('roles.update');
        Route::patch('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:delete-roles')->name('roles.destroy');

        // Permission Management
        Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:view-permissions')->name('permissions.index');
        Route::get('permissions/create', [PermissionController::class, 'create'])->middleware('permission:create-permissions')->name('permissions.create');
        Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:create-permissions')->name('permissions.store');
        Route::get('permissions/{permission}', [PermissionController::class, 'show'])->middleware('permission:view-permissions')->name('permissions.show');
        Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->middleware('permission:edit-permissions')->name('permissions.edit');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:edit-permissions')->name('permissions.update');
        Route::patch('permissions/{permission}', [PermissionController::class, 'update']);
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:delete-permissions')->name('permissions.destroy');

        // Department Management
        Route::get('departments', [DepartmentController::class, 'index'])->middleware('permission:view-departments')->name('departments.index');
        Route::get('departments/create', [DepartmentController::class, 'create'])->middleware('permission:create-departments')->name('departments.create');
        Route::post('departments', [DepartmentController::class, 'store'])->middleware('permission:create-departments')->name('departments.store');
        Route::get('departments/{department}', [DepartmentController::class, 'show'])->middleware('permission:view-departments')->name('departments.show');
        Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])->middleware('permission:edit-departments')->name('departments.edit');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:edit-departments')->name('departments.update');
        Route::patch('departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:delete-departments')->name('departments.destroy');

        // Sub-Department Management
        Route::get('sub-departments', [SubDepartmentController::class, 'index'])->middleware('permission:view-sub-departments')->name('sub-departments.index');
        Route::get('sub-departments/create', [SubDepartmentController::class, 'create'])->middleware('permission:create-sub-departments')->name('sub-departments.create');
        Route::post('sub-departments', [SubDepartmentController::class, 'store'])->middleware('permission:create-sub-departments')->name('sub-departments.store');
        Route::get('sub-departments/{sub_department}', [SubDepartmentController::class, 'show'])->middleware('permission:view-sub-departments')->name('sub-departments.show');
        Route::get('sub-departments/{sub_department}/edit', [SubDepartmentController::class, 'edit'])->middleware('permission:edit-sub-departments')->name('sub-departments.edit');
        Route::put('sub-departments/{sub_department}', [SubDepartmentController::class, 'update'])->middleware('permission:edit-sub-departments')->name('sub-departments.update');
        Route::patch('sub-departments/{sub_department}', [SubDepartmentController::class, 'update']);
        Route::delete('sub-departments/{sub_department}', [SubDepartmentController::class, 'destroy'])->middleware('permission:delete-sub-departments')->name('sub-departments.destroy');

        // Division Management
        Route::get('divisions', [DivisionController::class, 'index'])->middleware('permission:view-divisions')->name('divisions.index');
        Route::get('divisions/create', [DivisionController::class, 'create'])->middleware('permission:create-divisions')->name('divisions.create');
        Route::post('divisions', [DivisionController::class, 'store'])->middleware('permission:create-divisions')->name('divisions.store');
        Route::get('divisions/{division}', [DivisionController::class, 'show'])->middleware('permission:view-divisions')->name('divisions.show');
        Route::get('divisions/{division}/edit', [DivisionController::class, 'edit'])->middleware('permission:edit-divisions')->name('divisions.edit');
        Route::put('divisions/{division}', [DivisionController::class, 'update'])->middleware('permission:edit-divisions')->name('divisions.update');
        Route::patch('divisions/{division}', [DivisionController::class, 'update']);
        Route::delete('divisions/{division}', [DivisionController::class, 'destroy'])->middleware('permission:delete-divisions')->name('divisions.destroy');

        // Sage 300 admin explorer routes (raw API access, admin only)
        Route::prefix('sage300')->name('sage300.')->group(function () {
            Route::get('/', [Sage300Controller::class, 'index'])->name('index');
            Route::get('/items', [Sage300Controller::class, 'itemsList'])->name('items');
            Route::get('/api/get', [Sage300Controller::class, 'getData'])->name('api.get');
            Route::post('/api/post', [Sage300Controller::class, 'postData'])->name('api.post');
            Route::post('/api/items/refresh-cache', [Sage300Controller::class, 'refreshItemsCache'])->name('api.items.refresh-cache');
        });
    });

    // Requisition approval routes — permission-gated, no role:admin required
    Route::prefix('admin')->group(function () {
        Route::get('requisitions', [RequisitionApprovalController::class, 'index'])->middleware('permission:view-requisitions')->name('admin.requisitions.index');
        Route::get('requisitions/{requisition}', [RequisitionApprovalController::class, 'show'])->middleware('permission:view-requisitions')->name('admin.requisitions.show');
        Route::post('requisitions/{requisition}/approve', [RequisitionApprovalController::class, 'approve'])->middleware('permission:approve-requisitions')->name('admin.requisitions.approve');
        Route::post('requisitions/{requisition}/reject', [RequisitionApprovalController::class, 'reject'])->middleware('permission:approve-requisitions')->name('admin.requisitions.reject');
        Route::get('requisitions/{requisition}/issue-items', [RequisitionApprovalController::class, 'issueItemsForm'])->middleware('permission:issue-requisitions')->name('admin.requisitions.issue-items');
        Route::post('requisitions/{requisition}/issue-items', [RequisitionApprovalController::class, 'issueItems'])->middleware('permission:issue-requisitions')->name('admin.requisitions.issue-items.store');
        Route::post('requisitions/{requisition}/update-items', [RequisitionApprovalController::class, 'updateItems'])->middleware('permission:approve-requisitions')->name('admin.requisitions.update-items');

        // Return approval routes — permission-gated, no role:admin required
        Route::get('returns', [ReturnApprovalController::class, 'index'])->middleware('permission:view-returns')->name('admin.returns.index');
        Route::get('returns/{return}', [ReturnApprovalController::class, 'show'])->middleware('permission:view-returns')->name('admin.returns.show');
        Route::get('returns/{return}/approve-items', [ReturnApprovalController::class, 'approveItemsForm'])->middleware('permission:approve-returns')->name('admin.returns.approve-items');
        Route::post('returns/{return}/approve-items', [ReturnApprovalController::class, 'approveItems'])->middleware('permission:approve-returns')->name('admin.returns.approve-items.store');

        // Purchase Order routes — permission-gated, no role:admin required
        Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('permission:view-purchase-orders')->name('admin.purchase-orders.index');
        Route::get('purchase-orders/clear-form', [PurchaseOrderController::class, 'clearForm'])->middleware('permission:clear-purchase-orders')->name('admin.purchase-orders.clear-form');
        Route::post('purchase-orders/clear', [PurchaseOrderController::class, 'clear'])->middleware('permission:clear-purchase-orders')->name('admin.purchase-orders.clear');
        Route::post('purchase-orders/bulk-clear', [PurchaseOrderController::class, 'bulkClear'])->middleware('permission:clear-purchase-orders')->name('admin.purchase-orders.bulk-clear');
        Route::get('purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->middleware('permission:view-purchase-orders')->name('admin.purchase-orders.show');

        // Reports routes — permission-gated, no role:admin required
        Route::middleware(['permission:view-reports'])->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/requisition-summary', [ReportController::class, 'requisitionSummary'])->name('requisition-summary');
            Route::get('/item-requisition', [ReportController::class, 'itemRequisition'])->name('item-requisition');
            Route::get('/issued-items', [ReportController::class, 'issuedItems'])->name('issued-items');
            Route::get('/local-issued-items', [ReportController::class, 'localIssuedItems'])->name('local-issued-items');
            Route::get('/import-issued-items', [ReportController::class, 'importIssuedItems'])->name('import-issued-items');
            Route::get('/purchase-order', [ReportController::class, 'purchaseOrder'])->name('purchase-order');
            Route::get('/returns-summary', [ReportController::class, 'returnsSummary'])->name('returns-summary');
            Route::get('/grn', [ReportController::class, 'grn'])->name('grn');
            Route::get('/scrap', [ReportController::class, 'scrap'])->name('scrap');
            Route::get('/department-activity', [ReportController::class, 'departmentActivity'])->name('department-activity');
            Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');
            Route::get('/monthly-summary', [ReportController::class, 'monthlySummary'])->name('monthly-summary');
            Route::get('/inventory-movement', [ReportController::class, 'inventoryMovement'])->name('inventory-movement');
        });
    });
});
