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

    // Requisition routes (for authenticated users with permissions)
    Route::middleware(['permission:view-requisitions,create-requisitions'])->group(function () {
        Route::resource('requisitions', RequisitionController::class);
        Route::get('api/items/{itemCode}/availability', [RequisitionController::class, 'getItemAvailability']);
        Route::get('api/departments/{department}/sub-departments', [RequisitionController::class, 'getSubDepartments']);
        Route::get('api/sub-departments/{subDepartment}/divisions', [RequisitionController::class, 'getDivisions']);
        Route::get('api/requisitions/pending-items', [RequisitionController::class, 'getPendingApprovalItems']);
    });

    // Return routes (for authenticated users with permissions)
    Route::middleware(['permission:view-returns,create-returns'])->group(function () {
        Route::resource('returns', ReturnController::class);
        Route::get('api/returns/items-by-type/{type}', [ReturnController::class, 'getItemsByType']);
        Route::get('api/requisitions/{requisition}/issued-items', [ReturnController::class, 'getIssuedItems']);
    });

    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // User Management
        Route::middleware(['permission:view-users,create-users,edit-users,delete-users'])->group(function () {
            Route::resource('users', UserController::class);
        });
        Route::middleware(['permission:assign-user-permissions'])->group(function () {
            Route::get('users/{user}/permissions', [UserController::class, 'permissions'])->name('users.permissions');
            Route::post('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
        });

        // Role Management
        Route::middleware(['permission:view-roles,create-roles,edit-roles,delete-roles'])->group(function () {
            Route::resource('roles', RoleController::class);
        });

        // Permission Management
        Route::middleware(['permission:view-permissions,create-permissions,edit-permissions,delete-permissions'])->group(function () {
            Route::resource('permissions', PermissionController::class);
        });

        // Department Management
        Route::middleware(['permission:view-departments,create-departments,edit-departments,delete-departments'])->group(function () {
            Route::resource('departments', DepartmentController::class);
        });

        // Sub-Department Management
        Route::middleware(['permission:view-sub-departments,create-sub-departments,edit-sub-departments,delete-sub-departments'])->group(function () {
            Route::resource('sub-departments', SubDepartmentController::class);
        });

        // Division Management
        Route::middleware(['permission:view-divisions,create-divisions,edit-divisions,delete-divisions'])->group(function () {
            Route::resource('divisions', DivisionController::class);
        });

        // Requisition approval routes
        Route::middleware(['permission:view-requisitions,approve-requisitions,issue-requisitions'])->group(function () {
            Route::get('requisitions', [RequisitionApprovalController::class, 'index'])->name('admin.requisitions.index');
            Route::get('requisitions/{requisition}', [RequisitionApprovalController::class, 'show'])->name('admin.requisitions.show');
        });
        Route::middleware(['permission:approve-requisitions'])->group(function () {
            Route::post('requisitions/{requisition}/approve', [RequisitionApprovalController::class, 'approve'])->name('admin.requisitions.approve');
            Route::post('requisitions/{requisition}/reject', [RequisitionApprovalController::class, 'reject'])->name('admin.requisitions.reject');
        });
        Route::middleware(['permission:issue-requisitions'])->group(function () {
            Route::get('requisitions/{requisition}/issue-items', [RequisitionApprovalController::class, 'issueItemsForm'])->name('admin.requisitions.issue-items');
            Route::post('requisitions/{requisition}/issue-items', [RequisitionApprovalController::class, 'issueItems'])->name('admin.requisitions.issue-items.store');
        });

        // Return approval routes
        Route::middleware(['permission:view-returns,approve-returns'])->group(function () {
            Route::get('returns', [ReturnApprovalController::class, 'index'])->name('admin.returns.index');
            Route::get('returns/{return}', [ReturnApprovalController::class, 'show'])->name('admin.returns.show');
        });
        Route::middleware(['permission:approve-returns'])->group(function () {
            Route::get('returns/{return}/approve-items', [ReturnApprovalController::class, 'approveItemsForm'])->name('admin.returns.approve-items');
            Route::post('returns/{return}/approve-items', [ReturnApprovalController::class, 'approveItems'])->name('admin.returns.approve-items.store');
        });

        // Purchase Order routes
        Route::middleware(['permission:view-purchase-orders,clear-purchase-orders'])->group(function () {
            Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('admin.purchase-orders.index');
            Route::get('purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('admin.purchase-orders.show');
        });
        Route::middleware(['permission:clear-purchase-orders'])->group(function () {
            Route::get('purchase-orders/clear-form', [PurchaseOrderController::class, 'clearForm'])->name('admin.purchase-orders.clear-form');
            Route::post('purchase-orders/clear', [PurchaseOrderController::class, 'clear'])->name('admin.purchase-orders.clear');
            Route::post('purchase-orders/bulk-clear', [PurchaseOrderController::class, 'bulkClear'])->name('admin.purchase-orders.bulk-clear');
        });

        // Reports Routes
        Route::middleware(['permission:view-reports'])->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/requisition-summary', [ReportController::class, 'requisitionSummary'])->name('requisition-summary');
            Route::get('/item-requisition', [ReportController::class, 'itemRequisition'])->name('item-requisition');
            Route::get('/issued-items', [ReportController::class, 'issuedItems'])->name('issued-items');
            Route::get('/purchase-order', [ReportController::class, 'purchaseOrder'])->name('purchase-order');
            Route::get('/returns-summary', [ReportController::class, 'returnsSummary'])->name('returns-summary');
            Route::get('/grn', [ReportController::class, 'grn'])->name('grn');
            Route::get('/scrap', [ReportController::class, 'scrap'])->name('scrap');
            Route::get('/department-activity', [ReportController::class, 'departmentActivity'])->name('department-activity');
            Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');
            Route::get('/monthly-summary', [ReportController::class, 'monthlySummary'])->name('monthly-summary');
        });

        // Sage 300 Routes
        Route::prefix('sage300')->name('sage300.')->group(function () {
            Route::get('/', [Sage300Controller::class, 'index'])->name('index');

            // API Routes
            Route::get('/api/get', [Sage300Controller::class, 'getData'])->name('api.get');
            Route::post('/api/post', [Sage300Controller::class, 'postData'])->name('api.post');

            // Item Routes
            Route::get('/api/items', [Sage300Controller::class, 'getItems'])->name('api.items');
            Route::get('/api/items/{code}', [Sage300Controller::class, 'getItemDetails'])->name('api.item.details');
            Route::get('/api/items/{itemCode}/locations', [Sage300Controller::class, 'getItemLocations'])->name('api.item.locations');

            // Location Routes
            Route::get('/api/locations', [Sage300Controller::class, 'getLocations'])->name('api.locations');
        });
    });
});
