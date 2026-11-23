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

Route::get('/websites', [AnalyticsApiController::class, 'websites'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_websites');
