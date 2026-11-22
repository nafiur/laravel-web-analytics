# Laravel Web Analytics

<p align="center">
  <img src="art/banner.png" alt="Laravel Web Analytics Banner" width="100%">
</p>

<p align="center">
  <a href="https://packagist.org/packages/nafiur/laravel-web-analytics"><img src="https://img.shields.io/packagist/v/nafiur/laravel-web-analytics.svg?style=flat-square" alt="Latest Version on Packagist"></a>
  <a href="https://packagist.org/packages/nafiur/laravel-web-analytics"><img src="https://img.shields.io/packagist/dt/nafiur/laravel-web-analytics.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://github.com/nafiur/laravel-web-analytics/blob/main/LICENSE"><img src="https://img.shields.io/github/license/nafiur/laravel-web-analytics.svg?style=flat-square" alt="License"></a>
</p>

A modern, fast, and self-hosted Web Traffic & Visitor Analytics Dashboard for Laravel with real-time Server-Sent Events (SSE) live streaming and Tailwind CSS styling.

---

## ⚡ Highlights & Key Features

- 🚀 **Real-Time Live Stream (SSE)**: Live active visitor count and instant real-time traffic feed without WebSockets, Node.js, or Pusher. Each stream response is short-lived and paced by EventSource, so no PHP worker is held open while waiting for new traffic.
- 🧭 **Cloudflare-Style Navigation**: Sidebar with Overview, Requests, Visitors, Performance, Live Stream and Setting. Each section has its own Blade view, and the Settings screen is hidden from the analytics tab bar.
- 🎨 **Tailwind CSS UI with Dark Mode**: Modern, responsive dashboard powered by Tailwind CSS Play CDN (zero build step / zero npm dependencies). Includes a 1-click persistent Dark/Light mode toggle.
- 📊 **Comprehensive Metrics**: Total Requests, Unique Visitors, Bandwidth (Data Transferred in GB/MB), Avg Response Time, Error Rate.
- 🧪 **Built-In Live Traffic Simulator**: Easily generate realistic live traffic every second with `php artisan lwa:live` to demo and test live streaming.
- 🌍 **GeoIP & Location Provider**: Automatic country/city resolution with fallback support for Cloudflare headers and `ip-api`.
- 🚨 **Instant Error Alerts**: Telegram, Slack, or Discord webhook alerts on 5xx responses (deduplicated per URL/IP) and on error-rate spikes above a configurable threshold. Disabled by default.
- 📥 **Export to CSV**: Download filtered analytics logs into structured CSV format via high-performance chunked streams.
- 🧹 **Automatic Log Pruning**: Includes an Artisan command (`php artisan lwa:prune`) to maintain a clean database and prevent disk bloat.
- ⚡ **Post-Response Telemetry**: Tracking runs in Laravel's `terminate()` hook, after the HTTP response is sent. Optional GeoIP lookups and webhook alerts still occupy the worker for up to their configured timeouts (1s / 3s by default); keep alerts disabled unless needed.
- 🗄️ **Lightweight & Decoupled Storage**: Stores telemetry in the `lwa_logs` table without coupling to any specific User model, with GDPR-compliant IP anonymization options.
- 🔐 **Privacy-First Tracking**: Do Not Track support and an optional cookieless, daily-salted visitor fingerprint — no cookie banner required.
- 🎯 **UTM Campaigns**: `utm_source`, `utm_medium` and `utm_campaign` are captured automatically and shown in the Visitors tab.
- 🏢 **Multi-Tenancy Ready**: Native support for single-database multi-tenant applications (`stancl/tenancy`, `spatie/laravel-multitenancy`, or custom resolvers) with tenant-scoped dashboards and streams.

---

## 📋 Requirements

- PHP `^8.1 | ^8.2 | ^8.3 | ^8.4 | ^8.5`
- Laravel `^9.0 | ^10.0 | ^11.0 | ^12.0 | ^13.0`

---

## 📦 Installation

Install the package via Composer:

```bash
composer require nafiur/laravel-web-analytics
```

Publish configuration and (optionally) views:

```bash
php artisan vendor:publish --provider="Nafiur\WebAnalytics\WebAnalyticsServiceProvider"
```

Or publish selectively:

```bash
# Publish configuration
php artisan vendor:publish --tag=laravel-web-analytics-config

# Publish views (optional, to customize dashboard UI)
php artisan vendor:publish --tag=web-analytics-views
```

> **After upgrading the package:** published views override the package's own views. If you have published them and want the new UI changes, re-publish with `--force` and clear the compiled views:
>
> ```bash
> php artisan vendor:publish --tag=web-analytics-views --force
> php artisan view:clear
> ```
>
> If you do not customize the UI, simply delete `resources/views/vendor/web-analytics/` to always use the package views.

Migrations are auto-loaded by the service provider, so do **not** publish them — publishing would register the same migrations from two paths. Run database migrations to create the `lwa_logs` table:

```bash
php artisan migrate
```

---

## 🚀 Setup & Middleware Registration

To start capturing web traffic, register the `TrackWebTraffic` middleware.

### For Laravel 11, 12, and 13 (`bootstrap/app.php`):

```php
use Nafiur\WebAnalytics\Http\Middleware\TrackWebTraffic;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            TrackWebTraffic::class,
        ]);
    })
    ->create();
```

### For Laravel 9 and Laravel 10 (`app/Http/Kernel.php`):

Add the middleware to the `$middlewareGroups['web']` array:

```php
protected $middlewareGroups = [
    'web' => [
        // ... other middlewares
        \Nafiur\WebAnalytics\Http\Middleware\TrackWebTraffic::class,
    ],
];
```

---

## 🖥️ Accessing the Dashboard

Navigate to:

```
https://your-domain.com/web-analytics
```

By default, the dashboard is protected by the `['web', 'auth']` middleware stack. You can adjust the route prefix and authorization rules in `config/laravel-web-analytics.php`.

### Route Names

The package defines routes with the `lwa_` prefix to avoid conflicts with your application routes:

| Route | Name | Description |
|-------|------|-------------|
| `GET /web-analytics` | `lwa_dashboard` | Main analytics dashboard (alias: `lwa_index`) |
| `GET /web-analytics/data` | `lwa_data` | AJAX endpoint for real-time data |
| `GET /web-analytics/stream` | `lwa_stream` | SSE stream for live traffic |
| `GET /web-analytics/export` | `lwa_export` | Export analytics to CSV |
| `POST /web-analytics/settings/prune` | `lwa_settings_prune` | Prune rows older than N days |
| `POST /web-analytics/settings/empty` | `lwa_settings_empty` | Empty the telemetry table (requires `confirm=DELETE`) |
| `POST /web-analytics/settings/test-alert` | `lwa_settings_test_alert` | Send a test alert through the configured channel |

Generate URLs in your Blade templates or code:

```php
route('lwa_dashboard', ['range' => '24h']);
route('lwa_data', ['range' => '24h']);
route('lwa_stream');
route('lwa_export', ['range' => '24h']);
```

### Settings page

The dashboard's **Setting** tab provides an adapted settings screen:

- **General** — **12 languages** (English, বাংলা, हिन्दी, Español, Français, Deutsch, Português, العربية, 中文, 日本語, اردو, Indonesian — Arabic/Urdu switch the layout to RTL), **22 timezones**, and appearance (System/Light/Dark). These are browser preferences stored in `localStorage`; no database changes are needed. The timezone is applied to live-feed timestamps and chart labels via `Intl.DateTimeFormat`.
- **Dashboard** — default time range, data source, compact numbers and keyboard shortcuts.
- **Collection** — read-only view of capture/GeoIP config.
- **Alerts** — read-only channel/threshold info and a **Send test alert** action.
- **Export** — default CSV range and whether to include the tenant scope in export links.
- **Data maintenance** — row counts and oldest/newest timestamps, plus **Prune** and **Empty table** actions. Both are POST routes behind the dashboard's middleware with CSRF protection; emptying requires typing `DELETE`, and in multi-tenant mode only the current tenant's rows are removed.
- **About** — package/runtime information.

Set `WEB_ANALYTICS_DESTRUCTIVE_ACTIONS=false` to disable the prune/empty actions entirely.

### Data source & client IP

By default the dashboard reads the `lwa_logs` database table. The **Data source** selector can switch to `?source=file`, which parses a web-server access log from `WEB_ANALYTICS_SERVER_LOG_PATH` (or a detected default path). Configure `WEB_ANALYTICS_MAX_LINES` to bound how much of the file is read.

Client IP resolution uses Laravel's trusted-proxy configuration. Only enable `WEB_ANALYTICS_TRUST_FORWARDED_HEADERS=true` if the immediate peer is a trusted reverse proxy; otherwise `CF-Connecting-IP` / `X-Forwarded-For` can be spoofed by clients.

---

## 🧭 Dashboard Views & Cloudflare-Style Navigation

The dashboard features a clean, tabbed Cloudflare-style layout with segmented controls:

| Tab | Key Insights & Visualizations |
|-----|-------------------------------|
| **📈 Requests** | Total requests volume over time, bandwidth data transfer (GB/MB), status codes (2xx, 3xx, 4xx, 5xx), HTTP methods (GET, POST, PUT, DELETE), top paths, and top IP addresses. |
| **👥 Visitors** | Unique visitors, active live visitors (5m window), geographic breakdowns (top countries & cities), devices, operating systems, browsers, referrers, and **UTM source / medium / campaign** breakdowns. |
| **⚡ Performance** | Latency time series, average response times (ms), bandwidth consumption by path, and slowest application endpoints. |
| **🛡️ Security** | 4xx & 5xx error spike rates, status code distribution, top error paths, and recent error requests telemetry. |
| **🟢 Live Stream** | Instant Server-Sent Events (SSE) feed updating live requests second-by-second with real-time row animations without refreshing the page. |

### Time Range Filtering
Select from predefined or custom time ranges via the segmented dropdown:
- **`1h`**: Last hour with hourly breakdown.
- **`6h`** / **`12h`**: Last 6 or 12 hours.
- **`24h`** (Default): Last 24 hours with clean hourly breakdown.
- **`today`**: Today from midnight to current time.
- **`7d`**: Last 7 days with daily trends.
- **`30d`**: Last 30 days historical telemetry.
- **`all`**: Complete all-time records.
- **`custom`**: Choose custom `start_date` and `end_date` from the dashboard's range menu.

Empty periods are zero-filled in charts so time gaps stay visible.

---

## 🧪 Live Traffic Simulation & Demo Testing

Laravel Web Analytics includes a built-in Artisan simulator to generate realistic live web traffic every second. This allows you to demo and test the real-time SSE stream, live counters, and dashboard charts immediately:

```bash
# Start simulating live traffic (2 requests/sec, runs continuously)
php artisan lwa:live

# Custom requests per second (e.g. 5 req/sec)
php artisan lwa:live --rps=5

# Custom delay interval between batches (e.g. every 2 seconds)
php artisan lwa:live --interval=2

# Run for a specific number of seconds then exit (e.g. 60 seconds)
php artisan lwa:live --count=60

# Tag traffic with a specific tenant ID for multi-tenant testing
php artisan lwa:live --tenant=company-xyz
```

> **Tip:** Open the dashboard in your browser (`/web-analytics`), switch to the **Live Stream** tab, and run `php artisan lwa:live` in your terminal to watch requests flow in live second by second!

---

## ⚙️ Configuration (`config/laravel-web-analytics.php`)

```php
return [
    /*
     * Enable or disable traffic tracking globally
     */
    'enabled' => env('WEB_ANALYTICS_ENABLED', true),

    /*
     * Database table name for storing web analytics logs
     */
    'table_name' => env('WEB_ANALYTICS_TABLE_NAME', 'lwa_logs'),

    /*
     * Optional server access-log fallback (used by ?source=file)
     */
    'server_log_path' => env('WEB_ANALYTICS_SERVER_LOG_PATH', null),
    'max_lines'       => env('WEB_ANALYTICS_MAX_LINES', 50000),

    /*
     * Route configurations for the Analytics Dashboard
     */
    'route' => [
        'prefix'     => env('WEB_ANALYTICS_ROUTE_PREFIX', 'web-analytics'),
        'middleware' => ['web', 'auth'],
    ],

    /*
     * Real-time SSE polling interval in seconds
     */
    'stream_interval' => env('WEB_ANALYTICS_STREAM_INTERVAL', 2),

    /*
     * Active visitor calculation window in minutes
     */
    'active_visitor_window' => env('WEB_ANALYTICS_ACTIVE_WINDOW', 5),

    /*
     * Capture Settings (Web & API)
     */
    'capture' => [
        'web'                     => env('WEB_ANALYTICS_CAPTURE_WEB', true),
        'api'                     => env('WEB_ANALYTICS_CAPTURE_API', true),
        'ajax'                    => env('WEB_ANALYTICS_CAPTURE_AJAX', true),
        'bots'                    => env('WEB_ANALYTICS_CAPTURE_BOTS', false), // false | true | 'known'
        'trust_forwarded_headers' => env('WEB_ANALYTICS_TRUST_FORWARDED_HEADERS', false),
    ],

    'privacy' => [
        'respect_dnt'         => env('WEB_ANALYTICS_RESPECT_DNT', true),
        'visitor_id_strategy' => env('WEB_ANALYTICS_VISITOR_ID_STRATEGY', 'session'), // 'session' | 'fingerprint'
        'fingerprint_salt'    => env('WEB_ANALYTICS_FINGERPRINT_SALT', ''),
    ],

    /*
     * GeoIP & Location Provider
     */
    'geoip' => [
        'enabled'  => env('WEB_ANALYTICS_GEOIP_ENABLED', true),
        'provider' => env('WEB_ANALYTICS_GEOIP_PROVIDER', 'cloudflare'), // 'cloudflare', 'ip-api'
        'timeout'  => env('WEB_ANALYTICS_GEOIP_TIMEOUT', 1),
    ],

    /*
     * Alert Notifications (Spikes & 5xx Server Errors)
     */
    'alerts' => [
        'enabled'                         => env('WEB_ANALYTICS_ALERTS_ENABLED', false),
        'webhook_url'                     => env('WEB_ANALYTICS_ALERT_WEBHOOK', null),
        'channel'                         => env('WEB_ANALYTICS_ALERT_CHANNEL', 'telegram'), // 'telegram', 'slack', 'discord'
        'telegram_bot_token'              => env('WEB_ANALYTICS_TELEGRAM_BOT_TOKEN', null),
        'telegram_chat_id'                => env('WEB_ANALYTICS_TELEGRAM_CHAT_ID', null),
        'error_rate_threshold_percentage' => env('WEB_ANALYTICS_ERROR_THRESHOLD', 10),
    ],

    /*
     * Exclude specific URL paths and file extensions from being tracked
     */
    'ignored_paths' => [
        'livewire/*',
        'broadcasting/auth',
        'admin/web-analytics*',
        '*.css',
        '*.js',
        '*.png',
        '*.jpg',
        '*.jpeg',
        '*.gif',
        '*.svg',
        '*.webp',
        '*.woff*',
        '*.ico',
    ],

    /*
     * IP Anonymization for privacy compliance (GDPR)
     */
    'anonymize_ip' => env('WEB_ANALYTICS_ANONYMIZE_IP', false),

    /*
     * Number of days to retain logs
     */
    'retention_days' => env('WEB_ANALYTICS_RETENTION_DAYS', 30),

    'settings' => [
        'destructive_actions_enabled' => env('WEB_ANALYTICS_DESTRUCTIVE_ACTIONS', true),
    ],

    /*
     * Multi-Tenancy Support
     */
    'multi_tenancy' => [
        'enabled'                    => env('WEB_ANALYTICS_MULTI_TENANCY', false),
        'tenant_resolver'            => null, // Auto-detects stancl, spatie, or auth user
        'tenant_override_authorizer' => null, // Opt-in cross-tenant dashboard access
    ],
];
```

---

## 🔐 Privacy & bot filtering

- **Do Not Track:** with `WEB_ANALYTICS_RESPECT_DNT=true` (default), requests carrying `DNT: 1` are never logged.
- **Cookieless visitors:** set `WEB_ANALYTICS_VISITOR_ID_STRATEGY=fingerprint` to identify guests with `sha256(IP|User-Agent|date|salt)` instead of the Laravel session id. Always set `WEB_ANALYTICS_FINGERPRINT_SALT` to a stable random value; otherwise the app key is used as a fallback.
- **Bot filtering:** `WEB_ANALYTICS_CAPTURE_BOTS` accepts `false` (skip all bots — default), `true` (capture everything), or `known` (capture recognised search-engine/social crawlers but skip scrapers and CLI tooling).

## 🧹 Database Maintenance (Pruning)

> Upgrading from an earlier release: `user_id` is now a nullable string column (up to 64 chars) so UUID / string user keys are supported. Existing installs must alter the column manually if they store non-integer identifiers. A migration also adds `utm_source`, `utm_medium` and `utm_campaign`.

To keep your database lean, run the included prune command:

```bash
# Prune logs older than configured retention days (default 30)
php artisan lwa:prune

# Or specify custom days
php artisan lwa:prune --days=14
```

Schedule it in your `routes/console.php` (Laravel 11+) or `app/Console/Kernel.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('lwa:prune')->daily();
Schedule::command('lwa:summary')->weekly(); // sends a digest if alerts are enabled
```

Every command also has an `analytics:` alias (`analytics:prune`, `analytics:export`, `analytics:summary`).

### CLI export

```bash
php artisan lwa:export --range=7d --output=storage/app/analytics.csv
php artisan analytics:export --range=all --tenant=company-xyz
```

---

## 📥 Exporting Reports

Click the **Export to CSV** button on the dashboard or directly access:

```
GET /web-analytics/export?range=24h
```

Supported ranges: `1h`, `6h`, `12h`, `24h`, `today`, `7d`, `30d`, `all`, `custom` (with `start_date` / `end_date`).

---

## 🛡️ Security & Authorization

To restrict dashboard access to specific user roles or permissions, update `config/laravel-web-analytics.php`:

```php
'route' => [
    'prefix'     => 'web-analytics',
    'middleware' => ['web', 'auth', 'can:view-analytics'],
],
```

---

## 🏢 Multi-Tenancy Support

Laravel Web Analytics supports multi-tenant applications (both Single-Database with `tenant_id` and Multi-Database setups).

### 1. Enable in `.env` or `config/laravel-web-analytics.php`

```env
WEB_ANALYTICS_MULTI_TENANCY=true
```

### 2. Auto-Detection Out of the Box
When enabled, the package automatically detects active tenants from:
- **[stancl/tenancy](https://tenancyforlaravel.com/)**: `tenant('id')`
- **[spatie/laravel-multitenancy](https://spatie.be/docs/laravel-multitenancy)**: `Tenant::current()?->id`
- **Authenticated User**: `auth()->user()?->tenant_id` or `auth()->user()?->tenant?->id`
- **Session / Request**: session keys or `X-Tenant-ID` header (telemetry tagging only — see below)

### 3. Dashboard Tenant Scoping (Security)

Dashboard routes (`lwa_dashboard`, `lwa_data`, `lwa_stream`, `lwa_export` and the `lwa_settings_*` actions) **ignore** `?tenant_id=` and `X-Tenant-ID` by default. They only use tenant IDs resolved from trusted server-side context, so a user cannot view another tenant's analytics by editing the URL.

For admin dashboards that legitimately need cross-tenant views, opt in with an authorizer callback that returns `true` only when the current user is allowed:

```php
'multi_tenancy' => [
    'enabled'                    => true,
    'tenant_resolver'            => null,
    'tenant_override_authorizer' => fn ($requested, $resolved, $request) =>
        $request->user()?->is_admin === true,
],
```

The request value is accepted only when the callback returns exactly `true`; errors fail closed.

### 4. Custom Tenant Resolver
You can define a custom resolver callback in `config/laravel-web-analytics.php`:

```php
'multi_tenancy' => [
    'enabled'         => env('WEB_ANALYTICS_MULTI_TENANCY', false),
    'tenant_resolver' => function ($request) {
        return session('current_tenant_uuid');
    },
],
```

### 5. Tenant-Scoped Pruning
Prune logs for a specific tenant:

```bash
php artisan lwa:prune --days=30 --tenant=tenant-uuid
```

---

## 🛠 Artisan Commands

| Command | Alias | Description |
|---------|-------|-------------|
| `lwa:prune` | `analytics:prune` | Delete logs older than the retention window (`--days`, `--tenant`) |
| `lwa:export` | `analytics:export` | Export CSV (`--range`, `--tenant`, `--output`) |
| `lwa:summary` | `analytics:summary` | Print the periodic digest and send it if alerts are enabled |
| `lwa:live` | — | Generate simulated live traffic for demos (`--rps`, `--interval`, `--count`, `--tenant`) |

---

## 🧪 Testing

```bash
composer install
vendor/bin/phpunit
```

The test suite uses Orchestra Testbench with an in-memory SQLite database, so the `pdo_sqlite` PHP extension is required.

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/nafiur/laravel-web-analytics/issues).

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.