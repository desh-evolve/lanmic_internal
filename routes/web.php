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

    // Profile routes
    //Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    //Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Requisition routes (for all authenticated users)
    Route::resource('requisitions', RequisitionController::class);
    Route::get('api/items/{itemCode}/availability', [RequisitionController::class, 'getItemAvailability']);

    Route::get('api/departments/{department}/sub-departments', [RequisitionController::class, 'getSubDepartments']);
    Route::get('api/sub-departments/{subDepartment}/divisions', [RequisitionController::class, 'getDivisions']);
    Route::get('api/requisitions/pending-items', [RequisitionController::class, 'getItemAvailability']);

    Route::get('api/requisitions/pending-items', [RequisitionController::class, 'getPendingApprovalItems']);

    // Return routes (for all authenticated users)
    Route::resource('returns', ReturnController::class);
    Route::get('api/returns/items-by-type/{type}', [ReturnController::class, 'getItemsByType']);
    
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Custom route for user permission management (MUST BE BEFORE resource route)
        Route::get('users/{user}/permissions', [UserController::class, 'userPermission'])->name('users.user_permission');
        Route::post('users/{user}/permissions', [UserController::class, 'updateUserPermission'])->name('users.update_permission');

        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('sub-departments', SubDepartmentController::class);
        Route::resource('divisions', DivisionController::class);

        // Requisition approval routes
        Route::get('requisitions', [RequisitionApprovalController::class, 'index'])->name('admin.requisitions.index');
        Route::get('requisitions/{requisition}', [RequisitionApprovalController::class, 'show'])->name('admin.requisitions.show');
        Route::post('requisitions/{requisition}/approve', [RequisitionApprovalController::class, 'approve'])->name('admin.requisitions.approve');
        Route::post('requisitions/{requisition}/reject', [RequisitionApprovalController::class, 'reject'])->name('admin.requisitions.reject');
        Route::get('requisitions/{requisition}/issue-items', [RequisitionApprovalController::class, 'issueItemsForm'])->name('admin.requisitions.issue-items');
        Route::post('requisitions/{requisition}/issue-items', [RequisitionApprovalController::class, 'issueItems'])->name('admin.requisitions.issue-items.store');
    
        // Return approval routes
        Route::get('returns', [ReturnApprovalController::class, 'index'])->name('admin.returns.index');
        Route::get('returns/{return}', [ReturnApprovalController::class, 'show'])->name('admin.returns.show');
        Route::get('returns/{return}/approve-items', [ReturnApprovalController::class, 'approveItemsForm'])->name('admin.returns.approve-items');
        Route::post('returns/{return}/approve-items', [ReturnApprovalController::class, 'approveItems'])->name('admin.returns.approve-items.store');
    
        // Purchase Order routes
        Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('admin.purchase-orders.index');
        Route::get('purchase-orders/clear-form', [PurchaseOrderController::class, 'clearForm'])->name('admin.purchase-orders.clear-form');
        Route::post('purchase-orders/clear', [PurchaseOrderController::class, 'clear'])->name('admin.purchase-orders.clear');
        Route::post('purchase-orders/bulk-clear', [PurchaseOrderController::class, 'bulkClear'])->name('admin.purchase-orders.bulk-clear');
        Route::get('purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('admin.purchase-orders.show');

        // Reports Routes
        Route::prefix('reports')->name('reports.')->group(function () {
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
            Route::get('/api/locations', [Sage300Controller::class, 'getLocations'])->name('api.locations');
        });
    });
});
