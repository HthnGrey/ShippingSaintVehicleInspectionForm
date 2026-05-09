<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleInspectionController;
use App\Http\Controllers\VehicleController;


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/vehicles', [VehicleController::class, 'index'])
    ->name('vehicles.index');

Route::post('/vehicles', [VehicleController::class, 'store'])
    ->name('vehicles.store');

Route::get('/inspections/pre-trip', [VehicleInspectionController::class, 'createPreTrip'])
    ->name('inspections.pre');

Route::post('/inspections/pre-trip', [VehicleInspectionController::class, 'storePreTrip'])
    ->name('inspections.pre.store');

Route::get('/inspections/post-trip', [VehicleInspectionController::class, 'createPostTrip'])
    ->name('inspections.post');

Route::post('/inspections/post-trip', [VehicleInspectionController::class, 'storePostTrip'])
    ->name('inspections.post.store');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
