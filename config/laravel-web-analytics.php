<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Web Analytics Master Switch
    |--------------------------------------------------------------------------
    |
    | Enable or disable traffic tracking globally. Set to false in local
    | testing environments if you do not want to record requests.
    |
    */
    'enabled' => env('WEB_ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | The database table name where all analytics telemetry and logs are stored.
    | Default: lwa_logs
    |
    */
    'table_name' => env('WEB_ANALYTICS_TABLE_NAME', 'lwa_logs'),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route & Middleware
    |--------------------------------------------------------------------------
    |
    | Specify the URI prefix and middleware stack protecting the analytics dashboard.
    | Defaults to 'web-analytics' with 'web' and 'auth' middlewares.
    |
    */
    'route' => [
        'prefix'     => env('WEB_ANALYTICS_ROUTE_PREFIX', 'web-analytics'),
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Real-Time Stream (SSE) Polling Interval
    |--------------------------------------------------------------------------
    |
    | The interval in seconds at which the dashboard pushes live active visitors
    | and new traffic logs via Server-Sent Events.
    |
    */
    'stream_interval' => env('WEB_ANALYTICS_STREAM_INTERVAL', 2),

    /*
    |--------------------------------------------------------------------------
    | Active Visitor Timeout (Minutes)
    |--------------------------------------------------------------------------
    |
    | Window in minutes to consider a visitor active in real-time calculations.
    | Default: 5 minutes.
    |
    */
    'active_visitor_window' => env('WEB_ANALYTICS_ACTIVE_WINDOW', 5),

    /*
    |--------------------------------------------------------------------------
    | Capture Settings (Web & API)
    |--------------------------------------------------------------------------
    |
    | Choose which types of incoming requests to capture.
    |
    */
    'capture' => [
        'web'       => true,
        'api'       => env('WEB_ANALYTICS_CAPTURE_API', true),
        'ajax'      => true,
        'bots'      => env('WEB_ANALYTICS_CAPTURE_BOTS', false), // Set to false to avoid crawler noise
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Anonymization (GDPR Compliance)
    |--------------------------------------------------------------------------
    |
    | When enabled, IPv4 addresses mask the last octet (e.g. 192.168.1.0)
    | and IPv6 addresses mask the interface IDs.
    |
    */
    'anonymize_ip' => env('WEB_ANALYTICS_ANONYMIZE_IP', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored URL Patterns & File Extensions
    |--------------------------------------------------------------------------
    |
    | Requests matching these paths or patterns will be completely skipped.
    | Wildcards (*) are supported.
    |
    */
    'ignored_paths' => [
        'livewire/*',
        'broadcasting/auth',
        'web-analytics*',
        'admin/web-analytics*',
        'admin/server-analytics*',
        'horizon/*',
        'telescope/*',
        'pulse/*',
        'log-viewer*',
        '*.css',
        '*.js',
        '*.map',
        '*.png',
        '*.jpg',
        '*.jpeg',
        '*.gif',
        '*.svg',
        '*.webp',
        '*.woff*',
        '*.ttf',
        '*.eot',
        '*.ico',
        '*.pdf',
        '*.zip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Server Log Fallback (Optional)
    |--------------------------------------------------------------------------
    |
    | Custom path to server access log file if you wish to inspect server files.
    |
    */
    'server_log_path' => env('WEB_ANALYTICS_SERVER_LOG_PATH', null),
    'max_lines'       => env('WEB_ANALYTICS_MAX_LINES', 50000),

    /*
    |--------------------------------------------------------------------------
    | Automatic Log Pruning / Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to retain logs. Run `php artisan lwa:prune` in your scheduler.
    |
    */
    'retention_days' => env('WEB_ANALYTICS_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | GeoIP / City Lookup Provider
    |--------------------------------------------------------------------------
    |
    | Supported: 'cloudflare', 'ip-api', 'maxmind'
    |
    */
    'geoip' => [
        'enabled'         => env('WEB_ANALYTICS_GEOIP_ENABLED', true),
        'provider'        => env('WEB_ANALYTICS_GEOIP_PROVIDER', 'cloudflare'), // 'cloudflare', 'ip-api'
        'maxmind_db_path' => env('WEB_ANALYTICS_MAXMIND_DB', function_exists('storage_path') ? storage_path('app/GeoLite2-City.mmdb') : null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Notifications (Spikes & 5xx Server Errors)
    |--------------------------------------------------------------------------
    |
    | Trigger instant alerts to Slack, Telegram, or Discord webhook when 
    | an error spike or traffic threshold is reached.
    |
    */
    'alerts' => [
        'enabled'                         => env('WEB_ANALYTICS_ALERTS_ENABLED', false),
        'webhook_url'                     => env('WEB_ANALYTICS_ALERT_WEBHOOK', null),
        'channel'                         => env('WEB_ANALYTICS_ALERT_CHANNEL', 'telegram'), // 'telegram', 'slack', 'discord'
        'telegram_bot_token'              => env('WEB_ANALYTICS_TELEGRAM_BOT_TOKEN', null),
        'telegram_chat_id'                => env('WEB_ANALYTICS_TELEGRAM_CHAT_ID', null),
        'error_rate_threshold_percentage' => env('WEB_ANALYTICS_ERROR_THRESHOLD', 10), // e.g. 10% errors
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Support
    |--------------------------------------------------------------------------
    |
    | Enable multi-tenant tracking and dashboard data isolation.
    | Compatible with stancl/tenancy, spatie/laravel-multitenancy, or custom tenancy setups.
    |
    | 'enabled': Set to true to store and scope telemetry by tenant.
    | 'tenant_resolver': A custom closure or class to retrieve the current tenant ID.
    |                    When null, it automatically detects:
    |                    - stancl/tenancy: tenant('id')
    |                    - spatie/laravel-multitenancy: Tenant::current()?->id
    |                    - auth user: auth()->user()?->tenant_id
    |                    - request header: request()->header('X-Tenant-ID')
    |
    */
    'multi_tenancy' => [
        'enabled'         => env('WEB_ANALYTICS_MULTI_TENANCY', false),
        'tenant_column'   => 'tenant_id',
        'tenant_resolver' => null,
    ],
];
