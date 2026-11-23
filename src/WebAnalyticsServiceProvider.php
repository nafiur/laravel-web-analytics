<?php

namespace Nafiur\WebAnalytics;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Nafiur\WebAnalytics\Contracts\Collector;
use Nafiur\WebAnalytics\Contracts\StorageDriver;
use Nafiur\WebAnalytics\Services\AnalyticsCollector;
use Nafiur\WebAnalytics\Services\EloquentStorageDriver;

class WebAnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-web-analytics.php', 'laravel-web-analytics'
        );

        $this->app->bind(StorageDriver::class, EloquentStorageDriver::class);
        $this->app->bind(Collector::class, AnalyticsCollector::class);
        $this->app->singleton('lwa.analytics', \Nafiur\WebAnalytics\Services\AnalyticsManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Commands must be registered outside the console guard so they are
        // also available to Artisan::call() from HTTP routes and queue jobs.
        $this->commands([
            \Nafiur\WebAnalytics\Commands\PruneAnalyticsCommand::class,
            \Nafiur\WebAnalytics\Commands\SimulateLiveTrafficCommand::class,
            \Nafiur\WebAnalytics\Commands\ExportAnalyticsCommand::class,
            \Nafiur\WebAnalytics\Commands\WeeklySummaryCommand::class,
            \Nafiur\WebAnalytics\Commands\AggregateAnalyticsCommand::class,
            \Nafiur\WebAnalytics\Commands\UpgradeAnalyticsCommand::class,
            \Nafiur\WebAnalytics\Commands\BenchmarkAnalyticsCommand::class,
            \Nafiur\WebAnalytics\Commands\DoctorCommand::class,
        ]);

        // Publish Configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-web-analytics.php' => config_path('laravel-web-analytics.php'),
            ], 'laravel-web-analytics-config');

            // Publish Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/web-analytics'),
            ], 'web-analytics-views');
        }

        // Migrations are auto-loaded on install. Do not also publish them,
        // otherwise the same migration can be registered from two locations.
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load Views (support both 'web-analytics' and 'laravel-web-analytics' namespaces)
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'web-analytics');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-web-analytics');

        // Register Routes
        $this->registerRoutes();
        $this->registerApiRoutes();
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('laravel-web-analytics.route.prefix', 'web-analytics'),
            'middleware' => config('laravel-web-analytics.route.middleware', ['web', 'auth']),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Register token-authenticated reporting API routes.
     */
    protected function registerApiRoutes(): void
    {
        $this->app['router']->aliasMiddleware('lwa.api', \Nafiur\WebAnalytics\Http\Middleware\AuthenticateApiToken::class);
        $this->app['router']->aliasMiddleware('lwa.schema', \Nafiur\WebAnalytics\Http\Middleware\EnsureAnalyticsSchema::class);

        RateLimiter::for('lwa.api', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier()
                ?: ($request->header('Authorization') ?: $request->ip());

            return Limit::perMinute((int) config('laravel-web-analytics.api.rate_limit_per_minute', 60))
                ->by('lwa_api_' . sha1((string) $key));
        });

        Route::group([
            'prefix'    => config('laravel-web-analytics.route.prefix', 'web-analytics') . '/api/v1',
            'middleware' => ['api', 'lwa.schema', 'throttle:lwa.api'],
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }
}
