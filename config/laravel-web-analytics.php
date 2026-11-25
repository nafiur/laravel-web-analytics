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
    'enabled'               => env('WEB_ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | The database table name where all analytics telemetry and logs are stored.
    | Default: lwa_logs
    |
    */
    'table_name'            => env('WEB_ANALYTICS_TABLE_NAME', 'lwa_logs'),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route & Middleware
    |--------------------------------------------------------------------------
    |
    | Specify the URI prefix and middleware stack protecting the analytics dashboard.
    | Defaults to 'web-analytics' with 'web' and 'auth' middlewares.
    |
    */
    'route'                 => [
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
    'stream_interval'       => env('WEB_ANALYTICS_STREAM_INTERVAL', 2),

    /*
    |--------------------------------------------------------------------------
    | Live Stream Mode & Limits
    |--------------------------------------------------------------------------
    |
    | mode: 'sse' (EventSource) or 'polling' (plain fetch, safer on shared
    | hosting with very few PHP workers).
    | max_execution_time: 0 leaves the PHP setting untouched; a positive value
    | caps a single stream response with set_time_limit().
    |
    */
    'stream' => [
        'mode'               => env('WEB_ANALYTICS_STREAM_MODE', 'sse'),
        'polling_interval'   => env('WEB_ANALYTICS_POLLING_INTERVAL', 5),
        'max_execution_time' => env('WEB_ANALYTICS_STREAM_MAX_EXECUTION_TIME', 0),
    ],

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
    'capture'               => [
        'web'                     => env('WEB_ANALYTICS_CAPTURE_WEB', true),
        'api'                     => env('WEB_ANALYTICS_CAPTURE_API', true),
        'ajax'                    => env('WEB_ANALYTICS_CAPTURE_AJAX', true),
        /*
         | false  -> skip all detected bots/crawlers (recommended)
         | true   -> capture everything, including scrapers and CLI tooling
         | 'known'-> capture only recognised search-engine/social crawlers
         */
        'bots'                    => env('WEB_ANALYTICS_CAPTURE_BOTS', false),

        /*
         | Only enable when you are certain the immediate peer is a trusted
         | reverse proxy (e.g. ). Otherwise CF-Connecting-IP and
         | X-Forwarded-For can be spoofed by any client to poison analytics.
         | Prefer configuring Laravel's trusted proxies instead.
         */
        'trust_forwarded_headers' => env('WEB_ANALYTICS_TRUST_FORWARDED_HEADERS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Privacy-First Tracking
    |--------------------------------------------------------------------------
    |
    | respect_dnt: skip tracking when the browser sends DNT: 1.
    | visitor_id_strategy:
    |   'session'     -> use Laravel session id (existing behaviour)
    |   'fingerprint' -> cookieless daily hash of IP + UA + date + salt
    |
    */
    'api' => [
        'rate_limit_per_minute' => env('WEB_ANALYTICS_API_RATE_LIMIT', 60),
    ],

    'privacy'               => [
        'respect_dnt'            => env('WEB_ANALYTICS_RESPECT_DNT', true),
        'visitor_id_strategy'    => env('WEB_ANALYTICS_VISITOR_ID_STRATEGY', 'session'),
        'fingerprint_salt'       => env('WEB_ANALYTICS_FINGERPRINT_SALT', ''),
        'masked_query_parameters' => [
            'token', 'access_token', 'refresh_token', 'api_key', 'apikey', 'key',
            'password', 'secret', 'authorization', 'code', 'session', 'sid',
            'email', 'phone', 'card', 'card_number', 'cvv', 'ssn',
        ],
        'mask_referrer'          => env('WEB_ANALYTICS_MASK_REFERRER', true),
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
    'anonymize_ip'          => env('WEB_ANALYTICS_ANONYMIZE_IP', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored URL Patterns & File Extensions
    |--------------------------------------------------------------------------
    |
    | Requests matching these paths or patterns will be completely skipped.
    | Wildcards (*) are supported.
    |
    */
    'ignored_paths'         => [
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
    'server_log_path'       => env('WEB_ANALYTICS_SERVER_LOG_PATH', null),
    'max_lines'             => env('WEB_ANALYTICS_MAX_LINES', 50000),

    /*
    |--------------------------------------------------------------------------
    | Automatic Log Pruning / Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to retain logs. Run `php artisan lwa:prune` in your scheduler.
    |
    */
    'retention_days'        => env('WEB_ANALYTICS_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Analytics Schema (opt-in during migration)
    |--------------------------------------------------------------------------
    |
    | When false (default), the analytics tables are not created and the
    | v2 request-log pipeline remains the only active store. Enable it only
    | after reviewing the architecture blueprint and before running the upgrade command.
    |
    */
    'analytics' => [
        'enabled'                  => env('WEB_ANALYTICS_SCHEMA_ENABLED', false),
        'mode'                     => env('WEB_ANALYTICS_COLLECTOR_MODE', 'sync'), // 'queue' | 'sync'
        'write_legacy'             => env('WEB_ANALYTICS_WRITE_LEGACY', true),
        'queue'                    => env('WEB_ANALYTICS_QUEUE', null),
        'website_id'               => env('WEB_ANALYTICS_WEBSITE_ID', null),
        'session_timeout_minutes'  => env('WEB_ANALYTICS_SESSION_TIMEOUT', 30),
        'bounce_max_duration_seconds' => env('WEB_ANALYTICS_BOUNCE_MAX_SECONDS', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | JavaScript SDK (v3.2)
    |--------------------------------------------------------------------------
    |
    | Served bundle + browser defaults. The SDK itself is consent aware and
    | privacy first: no data leaves the browser without an explicit track /
    | identify / pageview call or an enabled consent state.
    |
    */
    'sdk'                   => [
        'enabled'         => env('WEB_ANALYTICS_SDK_ENABLED', false),
        'endpoint'        => env('WEB_ANALYTICS_SDK_ENDPOINT', '/web-analytics/api/v1/events/batch'),
        'autocapture'     => env('WEB_ANALYTICS_SDK_AUTOCAPTURE', false),
        'web_vitals'      => env('WEB_ANALYTICS_SDK_WEB_VITALS', false),
        'consent'         => [
            'analytics' => env('WEB_ANALYTICS_SDK_CONSENT_ANALYTICS', true),
        ],
        'max_properties'  => 20,
        'max_batch_bytes' => 65536,
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Page
    |--------------------------------------------------------------------------
    |
    | The dashboard settings page can prune or empty telemetry tables. Disable
    | these destructive actions entirely in environments where they are not
    | wanted (the buttons are still visible but return 403).
    |
    */
    'settings'              => [
        'destructive_actions_enabled' => env('WEB_ANALYTICS_DESTRUCTIVE_ACTIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | GeoIP / City Lookup Provider
    |--------------------------------------------------------------------------
    |
    | Supported: '' (header-based, no HTTP call), 'ip-api', 'maxmind'
    |
    | MaxMind runs fully offline using a local GeoLite2-City.mmdb file and the
    | optional composer package maxmind-db/reader.
    |
    */
    'geoip'                 => [
        'enabled'         => env('WEB_ANALYTICS_GEOIP_ENABLED', true),
        'provider'        => env('WEB_ANALYTICS_GEOIP_PROVIDER', ''), // '', 'ip-api', 'maxmind'
        'timeout'         => env('WEB_ANALYTICS_GEOIP_TIMEOUT', 1),
        'maxmind_db_path' => env('WEB_ANALYTICS_MAXMIND_DB', function_exists('storage_path') ? storage_path('app/GeoLite2-City.mmdb') : null),
        'license_key'     => env('MAXMIND_LICENSE_KEY', null),
        'account_id'      => env('MAXMIND_ACCOUNT_ID', null),
        'auto_download'   => env('WEB_ANALYTICS_GEOIP_AUTO_DOWNLOAD', false),
        'update_days'     => env('WEB_ANALYTICS_GEOIP_UPDATE_DAYS', 7),
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
    'alerts'                => [
        'enabled'                         => env('WEB_ANALYTICS_ALERTS_ENABLED', false),
        'webhook_url'                     => env('WEB_ANALYTICS_ALERT_WEBHOOK', null),
        'channel'                         => env('WEB_ANALYTICS_ALERT_CHANNEL', 'telegram'), // 'telegram', 'slack', 'discord'
        'telegram_bot_token'              => env('WEB_ANALYTICS_TELEGRAM_BOT_TOKEN', null),
        'telegram_chat_id'                => env('WEB_ANALYTICS_TELEGRAM_CHAT_ID', null),
        'email'                           => env('WEB_ANALYTICS_ALERT_EMAIL', null), // weekly summary recipient
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
    'multi_tenancy'         => [
        'enabled'                    => env('WEB_ANALYTICS_MULTI_TENANCY', false),
        'tenant_resolver'            => null,

        /*
         | Optional opt-in authorizer for cross-tenant dashboard views.
         |
         | By default the dashboard ignores ?tenant_id= and X-Tenant-ID and only
         | uses the tenant resolved from trusted server-side context. Set this to
         | a callable (or a class exposing authorize()/__invoke()) that returns
         | true when the current user may view the requested tenant, e.g.:
         |
         |   'tenant_override_authorizer' => fn ($requested, $resolved, $request) =>
         |       auth()->user()?->is_admin === true,
         */
        'tenant_override_authorizer' => null,
    ],
];
