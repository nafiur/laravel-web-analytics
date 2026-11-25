<?php

use Illuminate\Support\Facades\Route;
use Nafiur\WebAnalytics\Http\Controllers\Api\AnalyticsApiController;

Route::get('/summary', [AnalyticsApiController::class, 'summary'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_summary');

Route::get('/timeseries', [AnalyticsApiController::class, 'timeseries'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_timeseries');

Route::post('/events', [AnalyticsApiController::class, 'event'])
    ->middleware('lwa.api:write')
    ->name('lwa_api_event');

Route::post('/events/batch', [AnalyticsApiController::class, 'storeBatch'])
    ->middleware('lwa.api:write')
    ->name('lwa_api_events_batch');

Route::get('/websites', [AnalyticsApiController::class, 'websites'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_websites');

Route::get('/dashboards', [AnalyticsApiController::class, 'dashboards'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_dashboards');
Route::post('/dashboards', [AnalyticsApiController::class, 'dashboardStore'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_dashboards_store');
Route::get('/dashboards/{dashboard}', [AnalyticsApiController::class, 'dashboardShow'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_dashboards_show');
Route::put('/dashboards/{dashboard}', [AnalyticsApiController::class, 'dashboardUpdate'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_dashboards_update');
Route::delete('/dashboards/{dashboard}', [AnalyticsApiController::class, 'dashboardDestroy'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_dashboards_destroy');
