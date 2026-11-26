<?php

use Illuminate\Support\Facades\Route;
use Nafiur\WebAnalytics\Http\Controllers\DashboardController;
use Nafiur\WebAnalytics\Http\Controllers\EventExplorerController;
use Nafiur\WebAnalytics\Http\Controllers\ExperimentsController;
use Nafiur\WebAnalytics\Http\Controllers\FeatureFlagsController;
use Nafiur\WebAnalytics\Http\Controllers\FunnelsController;
use Nafiur\WebAnalytics\Http\Controllers\FormsController;
use Nafiur\WebAnalytics\Http\Controllers\HeatmapsController;
use Nafiur\WebAnalytics\Http\Controllers\ObservabilityController;
use Nafiur\WebAnalytics\Http\Controllers\PathsController;
use Nafiur\WebAnalytics\Http\Controllers\ReplayBundleController;
use Nafiur\WebAnalytics\Http\Controllers\ReplayController;
use Nafiur\WebAnalytics\Http\Controllers\ReplayPlayerBundleController;
use Nafiur\WebAnalytics\Http\Controllers\ReplayPlayerController;
use Nafiur\WebAnalytics\Http\Controllers\RetentionController;
use Nafiur\WebAnalytics\Http\Controllers\SdkController;
use Nafiur\WebAnalytics\Http\Controllers\TrendsController;
use Nafiur\WebAnalytics\Http\Controllers\WebAnalyticsController;
use Nafiur\WebAnalytics\Http\Controllers\WebVitalsController;

$replayGate = 'can:' . config('laravel-web-analytics.replay.gate', 'viewLwaReplay');
$observabilityGate = 'can:' . config('laravel-web-analytics.observability.gate', 'viewLwaObservability');

Route::get('/', [WebAnalyticsController::class, 'index'])->name('lwa_dashboard');
Route::get('/index', [WebAnalyticsController::class, 'index'])->name('lwa_index');
Route::get('/data', [WebAnalyticsController::class, 'data'])->name('lwa_data');
Route::get('/stream', [WebAnalyticsController::class, 'stream'])->name('lwa_stream');
Route::get('/export', [WebAnalyticsController::class, 'export'])->name('lwa_export');
Route::get('/sdk.js', [SdkController::class, '__invoke'])->name('lwa_sdk');
Route::get('/replay.js', [ReplayBundleController::class, '__invoke'])->name('lwa_replay_bundle');
Route::get('/replay-player.js', [ReplayPlayerBundleController::class, '__invoke'])
    ->middleware($replayGate)
    ->name('lwa_replay_player_bundle');
Route::get('/replay', [ReplayController::class, 'index'])
    ->middleware($replayGate)
    ->name('lwa_replay');
Route::get('/heatmaps', [HeatmapsController::class, 'index'])
    ->middleware($replayGate)
    ->name('lwa_heatmaps');
Route::get('/forms', [FormsController::class, 'index'])
    ->middleware($replayGate)
    ->name('lwa_forms');
Route::get('/replay/{replaySession}/manifest', [ReplayPlayerController::class, 'manifest'])
    ->middleware($replayGate)
    ->name('lwa_replay_manifest');
Route::get('/replay/{replaySession}/chunk/{sequence}', [ReplayPlayerController::class, 'chunk'])
    ->middleware($replayGate)
    ->name('lwa_replay_chunk');
Route::get('/replay/{replaySession}', [ReplayPlayerController::class, 'show'])
    ->middleware($replayGate)
    ->name('lwa_replay_show');
Route::get('/explorer', [EventExplorerController::class, 'index'])->name('lwa_explorer');
Route::get('/trends', [TrendsController::class, 'index'])->name('lwa_trends');
Route::post('/trends/save', [TrendsController::class, 'save'])->name('lwa_trends_save');
Route::post('/trends/{insight}/delete', [TrendsController::class, 'destroy'])->name('lwa_trends_destroy');
Route::get('/funnels', [FunnelsController::class, 'index'])->name('lwa_funnels');
Route::post('/funnels/save', [FunnelsController::class, 'save'])->name('lwa_funnels_save');
Route::post('/funnels/{insight}/delete', [FunnelsController::class, 'destroy'])->name('lwa_funnels_destroy');
Route::get('/retention', [RetentionController::class, 'index'])->name('lwa_retention');
Route::post('/retention/save', [RetentionController::class, 'save'])->name('lwa_retention_save');
Route::post('/retention/{insight}/delete', [RetentionController::class, 'destroy'])->name('lwa_retention_destroy');
Route::get('/paths', [PathsController::class, 'index'])->name('lwa_paths');
Route::post('/paths/save', [PathsController::class, 'save'])->name('lwa_paths_save');
Route::post('/paths/{insight}/delete', [PathsController::class, 'destroy'])->name('lwa_paths_destroy');
Route::get('/flags', [FeatureFlagsController::class, 'index'])->name('lwa_flags');
Route::get('/flags/{featureFlag}/edit', [FeatureFlagsController::class, 'index'])->name('lwa_flags_edit');
Route::post('/flags', [FeatureFlagsController::class, 'store'])->name('lwa_flags_store');
Route::put('/flags/{featureFlag}', [FeatureFlagsController::class, 'update'])->name('lwa_flags_update');
Route::delete('/flags/{featureFlag}', [FeatureFlagsController::class, 'destroy'])->name('lwa_flags_destroy');
Route::get('/experiments', [ExperimentsController::class, 'index'])->name('lwa_experiments');
Route::get('/experiments/{experiment}/edit', [ExperimentsController::class, 'index'])->name('lwa_experiments_edit');
Route::post('/experiments', [ExperimentsController::class, 'store'])->name('lwa_experiments_store');
Route::put('/experiments/{experiment}', [ExperimentsController::class, 'update'])->name('lwa_experiments_update');
Route::delete('/experiments/{experiment}', [ExperimentsController::class, 'destroy'])->name('lwa_experiments_destroy');
Route::get('/observability', [ObservabilityController::class, 'index'])
    ->middleware($observabilityGate)
    ->name('lwa_observability');
Route::get('/observability/{errorGroup}', [ObservabilityController::class, 'show'])
    ->middleware($observabilityGate)
    ->name('lwa_observability_show');
Route::post('/observability/{errorGroup}/status', [ObservabilityController::class, 'status'])
    ->middleware($observabilityGate)
    ->name('lwa_observability_status');
Route::get('/web-vitals', [WebVitalsController::class, 'index'])->name('lwa_web_vitals');
Route::post('/settings/prune', [WebAnalyticsController::class, 'prune'])->name('lwa_settings_prune');
Route::post('/settings/empty', [WebAnalyticsController::class, 'emptyTable'])->name('lwa_settings_empty');
Route::post('/settings/test-alert', [WebAnalyticsController::class, 'testAlert'])->name('lwa_settings_test_alert');

Route::get('/dashboards', [DashboardController::class, 'index'])->name('lwa_dashboards');
Route::post('/dashboards', [DashboardController::class, 'store'])->name('lwa_dashboards_store');
Route::get('/dashboards/{dashboard}', [DashboardController::class, 'show'])->name('lwa_dashboard_view');
Route::get('/dashboards/{dashboard}/edit', [DashboardController::class, 'edit'])->name('lwa_dashboard_edit');
Route::put('/dashboards/{dashboard}', [DashboardController::class, 'update'])->name('lwa_dashboard_update');
Route::delete('/dashboards/{dashboard}', [DashboardController::class, 'destroy'])->name('lwa_dashboard_destroy');
Route::get('/websites', [WebAnalyticsController::class, 'websitesIndex'])->name('lwa_websites_index');
Route::post('/websites', [WebAnalyticsController::class, 'storeWebsite'])->name('lwa_websites_store');
Route::post('/websites/switch', [WebAnalyticsController::class, 'switchWebsite'])->name('lwa_websites_switch');
Route::delete('/websites/{website}', [WebAnalyticsController::class, 'destroyWebsite'])->name('lwa_websites_destroy');

/*
 * One bookmarkable URL per sidebar section. All of them render the same
 * dashboard shell with the matching report pane active.
 */
$sections = [
    'overview'    => 'lwa_overview',
    'realtime'    => 'lwa_realtime',
    'logs'        => 'lwa_logs',
    'visitors'    => 'lwa_visitors',
    'sources'     => 'lwa_sources',
    'pages'       => 'lwa_pages',
    'geography'   => 'lwa_geography',
    'devices'     => 'lwa_devices',
    'campaigns'   => 'lwa_campaigns',
    'events'      => 'lwa_events',
    'conversions' => 'lwa_conversions',
    'performance' => 'lwa_performance',
    'errors'      => 'lwa_errors',
    'settings'    => 'lwa_settings',
];

foreach ($sections as $slug => $name) {
    Route::get('/' . $slug, [WebAnalyticsController::class, 'section'])
        ->defaults('section', $slug)
        ->name($name);
}
