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
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('laravel-web-analytics.route.middleware', ['web']);
        $app['config']->set('laravel-web-analytics.geoip.enabled', false);
        $app['config']->set('laravel-web-analytics.alerts.enabled', false);
    }
}
