<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');

// Cards
Route::get('/cards', [CardController::class, 'index'])->name('cards.index');

// Import
Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::post('/import', [ImportController::class, 'store'])->name('import.store');

/*
|--------------------------------------------------------------------------
| API Routes (AJAX)
|--------------------------------------------------------------------------
*/

// Stats API
Route::prefix('api')->group(function () {
    Route::get('/stats/weekly', [DashboardController::class, 'weeklyStats']);
    Route::get('/stats/monthly', [DashboardController::class, 'monthlyStats']);
    Route::get('/stats/analysts', [DashboardController::class, 'analystStats']);
    Route::get('/stats/portals', [DashboardController::class, 'portalStats']);
    Route::get('/stats/metrics', [DashboardController::class, 'metricsStats']);

    // Cards API
    Route::get('/cards/{card}', [CardController::class, 'show']);
    Route::patch('/cards/{card}', [CardController::class, 'update']);
    
    // Tags API
    Route::get('/cards/{card}/tags', [CardController::class, 'getTags']);
    Route::post('/cards/{card}/tags', [CardController::class, 'addTag']);
    Route::delete('/tags/{tag}', [CardController::class, 'deleteTag']);
});
