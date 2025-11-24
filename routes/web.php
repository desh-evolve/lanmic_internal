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
    Route::get('api/departments/{department}/sub-departments', [RequisitionController::class, 'getSubDepartments']);
    Route::get('api/sub-departments/{subDepartment}/divisions', [RequisitionController::class, 'getDivisions']);
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
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
    });
});