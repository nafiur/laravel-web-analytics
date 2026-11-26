<?php

use Illuminate\Support\Facades\Route;
use Nafiur\WebAnalytics\Http\Controllers\Api\AnalyticsApiController;
use Nafiur\WebAnalytics\Http\Controllers\Api\FlagEvaluateController;
use Nafiur\WebAnalytics\Http\Controllers\Api\ReplayIngestController;

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
    ->middleware('lwa.ingest')
    ->name('lwa_api_events_batch');

Route::post('/replay/chunks', [ReplayIngestController::class, 'store'])
    ->middleware('lwa.replay.ingest')
    ->name('lwa_api_replay_chunks');

Route::get('/flags/evaluate', [FlagEvaluateController::class, 'evaluate'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_flags_evaluate');

Route::get('/websites', [AnalyticsApiController::class, 'websites'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_websites');

Route::get('/users', [AnalyticsApiController::class, 'users'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_users');
Route::get('/users/{distinctId}', [AnalyticsApiController::class, 'userShow'])
    ->where('distinctId', '.*')
    ->middleware('lwa.api:read')
    ->name('lwa_api_user');

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

Route::get('/insights/trend', [AnalyticsApiController::class, 'trend'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_insights_trend');

Route::get('/insights/funnel', [AnalyticsApiController::class, 'funnel'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_insights_funnel');

Route::get('/insights/retention', [AnalyticsApiController::class, 'retention'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_insights_retention');

Route::get('/insights/paths', [AnalyticsApiController::class, 'paths'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_insights_paths');

Route::get('/insights', [AnalyticsApiController::class, 'insights'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_insights');
Route::post('/insights', [AnalyticsApiController::class, 'insightStore'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_insights_store');
Route::get('/insights/{insight}', [AnalyticsApiController::class, 'insightShow'])
    ->middleware('lwa.api:read')
    ->name('lwa_api_insights_show');
Route::put('/insights/{insight}', [AnalyticsApiController::class, 'insightUpdate'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_insights_update');
Route::delete('/insights/{insight}', [AnalyticsApiController::class, 'insightDestroy'])
    ->middleware('lwa.api:admin')
    ->name('lwa_api_insights_destroy');
