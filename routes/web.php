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
