<?php

use Illuminate\Support\Facades\Route;
use Nafiur\WebAnalytics\Http\Controllers\WebAnalyticsController;

Route::get('/', [WebAnalyticsController::class, 'index'])->name('lwa_dashboard');
Route::get('/index', [WebAnalyticsController::class, 'index'])->name('lwa_index');
Route::get('/data', [WebAnalyticsController::class, 'data'])->name('lwa_data');
Route::get('/stream', [WebAnalyticsController::class, 'stream'])->name('lwa_stream');
Route::get('/export', [WebAnalyticsController::class, 'export'])->name('lwa_export');
Route::post('/settings/prune', [WebAnalyticsController::class, 'prune'])->name('lwa_settings_prune');
Route::post('/settings/empty', [WebAnalyticsController::class, 'emptyTable'])->name('lwa_settings_empty');
Route::post('/settings/test-alert', [WebAnalyticsController::class, 'testAlert'])->name('lwa_settings_test_alert');
