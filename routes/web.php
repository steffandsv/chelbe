<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GodModController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- */

use App\Http\Controllers\AnalyticsController;

// Dashboard
Route::get('/', [DashboardController::class , 'index'])->name('dashboard');

// GOD-MOD - Documentation
Route::get('/god-mod', [GodModController::class , 'index'])->name('god-mod');

// Risk Analytics
Route::get('/dashboard/analise-derrotas', [AnalyticsController::class , 'index'])->name('analytics.index');
Route::post('/dashboard/analise-derrotas', [AnalyticsController::class , 'analyze'])->name('analytics.analyze');
// Analytics - com nome 'analytics' para compatibilidade com a sidebar
Route::get('/analytics', [AnalyticsController::class , 'index'])->name('analytics');

// Cards - Full CRUD
Route::get('/cards', [CardController::class , 'index'])->name('cards.index');
Route::get('/cards/create', [CardController::class , 'create'])->name('cards.create');
Route::post('/cards', [CardController::class , 'store'])->name('cards.store');
Route::get('/cards/{card}/edit', [CardController::class , 'edit'])->name('cards.edit');
Route::put('/cards/{card}', [CardController::class , 'updateFull'])->name('cards.update');
Route::delete('/cards/{card}', [CardController::class , 'destroy'])->name('cards.destroy');

// Import
Route::get('/import', [ImportController::class , 'index'])->name('import.index');
Route::post('/import', [ImportController::class , 'store'])->name('import.store');
Route::post('/import/preview', [ImportController::class , 'preview'])->name('import.preview');

/* |-------------------------------------------------------------------------- | API Routes (AJAX) |-------------------------------------------------------------------------- */

// Stats API
Route::prefix('api')->group(function () {
    Route::get('/stats/weekly', [DashboardController::class , 'weeklyStats']);
    Route::get('/stats/monthly', [DashboardController::class , 'monthlyStats']);
    Route::get('/stats/analysts', [DashboardController::class , 'analystStats']);
    Route::get('/stats/portals', [DashboardController::class , 'portalStats']);
    Route::get('/stats/metrics', [DashboardController::class , 'metricsStats']);

    // Cards API
    Route::get('/cards/{card}', [CardController::class , 'show']);
    Route::patch('/cards/{card}', [CardController::class , 'update']);

    // Tags API
    Route::get('/cards/{card}/tags', [CardController::class , 'getTags']);
    Route::post('/cards/{card}/tags', [CardController::class , 'addTag']);
    Route::delete('/tags/{tag}', [CardController::class , 'deleteTag']);
});
