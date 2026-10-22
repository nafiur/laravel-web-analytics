<?php

use Illuminate\Support\Facades\Route;
use Nafiur\WebAnalytics\Http\Controllers\WebAnalyticsController;

Route::get('/', [WebAnalyticsController::class, 'index'])->name('lwa_dashboard');
Route::get('/index', [WebAnalyticsController::class, 'index'])->name('lwa_index');
Route::get('/data', [WebAnalyticsController::class, 'data'])->name('lwa_data');
Route::get('/stream', [WebAnalyticsController::class, 'stream'])->name('lwa_stream');
Route::get('/export', [WebAnalyticsController::class, 'export'])->name('lwa_export');
