<?php

namespace Nafiur\WebAnalytics;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish Configuration
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Nafiur\WebAnalytics\Commands\PruneAnalyticsCommand::class,
                \Nafiur\WebAnalytics\Commands\SimulateLiveTrafficCommand::class,
            ]);

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
}
