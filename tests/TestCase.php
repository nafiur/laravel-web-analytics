<?php

namespace Nafiur\WebAnalytics\Tests;

use Nafiur\WebAnalytics\WebAnalyticsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [WebAnalyticsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Defaults to in-memory SQLite for local runs. CI overrides these
        // through DB_* environment variables to exercise MySQL/PostgreSQL too.
        $connection = env('DB_CONNECTION', 'sqlite');
        $app['config']->set('database.default', $connection);

        if ($connection === 'sqlite') {
            $app['config']->set('database.connections.sqlite', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
        } else {
            $app['config']->set('database.connections.' . $connection, [
                'driver'    => $connection,
                'host'      => env('DB_HOST', '127.0.0.1'),
                'port'      => env('DB_PORT', $connection === 'pgsql' ? '5432' : '3306'),
                'database'  => env('DB_DATABASE', 'lwa_test'),
                'username'  => env('DB_USERNAME', $connection === 'pgsql' ? 'postgres' : 'root'),
                'password'  => env('DB_PASSWORD', ''),
                'charset'   => $connection === 'pgsql' ? 'utf8' : 'utf8mb4',
                'prefix'    => '',
                'strict'    => false,
            ]);
        }

        $app['config']->set('laravel-web-analytics.analytics.enabled',
            filter_var(env('WEB_ANALYTICS_SCHEMA_ENABLED', false), FILTER_VALIDATE_BOOLEAN));

        $app['config']->set('laravel-web-analytics.route.middleware', ['web']);
        $app['config']->set('laravel-web-analytics.geoip.enabled', false);
        $app['config']->set('laravel-web-analytics.alerts.enabled', false);
    }
}
