<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverMileageController;
use App\Http\Controllers\DriverHistoryController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleInspectionController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkOrderController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:view-dashboard')
        ->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('can:manage-users')
        ->name('users.index');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('can:manage-users')
        ->name('users.store');

    Route::patch('/users/{user}', [UserController::class, 'update'])
        ->middleware('can:manage-users')
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:manage-users')
        ->name('users.destroy');

    Route::get('/vehicles', [VehicleController::class, 'index'])
        ->middleware('can:view-vehicles')
        ->name('vehicles.index');

    Route::post('/vehicles', [VehicleController::class, 'store'])
        ->middleware('can:create-vehicles')
        ->name('vehicles.store');

    Route::patch('/vehicles/{vehicle}', [VehicleController::class, 'update'])
        ->middleware('can:update-vehicles')
        ->name('vehicles.update');

    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])
        ->middleware('can:delete-vehicles')
        ->name('vehicles.destroy');

    Route::get('/work-orders', [WorkOrderController::class, 'index'])
        ->middleware('can:manage-work-orders')
        ->name('work-orders.index');

    Route::post('/work-orders/{vehicle}/complete', [WorkOrderController::class, 'complete'])
        ->middleware('can:manage-work-orders')
        ->name('work-orders.complete');

    Route::get('/driver-mileage', [DriverMileageController::class, 'index'])
        ->middleware('can:view-driver-mileage')
        ->name('driver-mileage.index');

    Route::get('/driver-history', [DriverHistoryController::class, 'index'])
        ->middleware('can:view-driver-history')
        ->name('driver-history.index');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('can:view-audit-logs')
        ->name('audit-logs.index');

    Route::get('/exports/reports', [ExportController::class, 'reports'])
        ->middleware('can:view-reports')
        ->name('exports.reports');

    Route::get('/exports/inspections', [ExportController::class, 'inspections'])
        ->middleware('can:view-inspections')
        ->name('exports.inspections');

    Route::get('/exports/driver-mileage', [ExportController::class, 'driverMileage'])
        ->middleware('can:view-driver-mileage')
        ->name('exports.driver-mileage');

    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('can:view-reports')
        ->name('reports.index');

    Route::patch('/reports/{maintenanceReport}', [ReportController::class, 'update'])
        ->middleware('can:manage-reports')
        ->name('reports.update');

    Route::delete('/reports/{maintenanceReport}', [ReportController::class, 'destroy'])
        ->middleware('can:manage-reports')
        ->name('reports.destroy');

    Route::get('/inspections', [VehicleInspectionController::class, 'index'])
        ->middleware('can:view-inspections')
        ->name('inspections.index');

    Route::get('/inspections/pre-trip', [VehicleInspectionController::class, 'createPreTrip'])
        ->middleware('can:create-inspections')
        ->name('inspections.pre');

    Route::post('/inspections/pre-trip', [VehicleInspectionController::class, 'storePreTrip'])
        ->middleware('can:create-inspections')
        ->name('inspections.pre.store');

    Route::get('/inspections/post-trip', [VehicleInspectionController::class, 'createPostTrip'])
        ->middleware('can:create-inspections')
        ->name('inspections.post');

    Route::post('/inspections/post-trip', [VehicleInspectionController::class, 'storePostTrip'])
        ->middleware('can:create-inspections')
        ->name('inspections.post.store');

    Route::get('/inspections/{inspection}/edit', [VehicleInspectionController::class, 'edit'])
        ->middleware('can:manage-inspections')
        ->name('inspections.edit');

    Route::patch('/inspections/{inspection}', [VehicleInspectionController::class, 'update'])
        ->middleware('can:manage-inspections')
        ->name('inspections.update');

    Route::delete('/inspections/{inspection}', [VehicleInspectionController::class, 'destroy'])
        ->middleware('can:manage-inspections')
        ->name('inspections.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
