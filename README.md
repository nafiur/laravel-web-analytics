# Laravel Web Analytics

![Laravel Web Analytics Banner](https://raw.githubusercontent.com/nafiur/laravel-web-analytics/main/art/banner.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nafiur/laravel-web-analytics.svg?style=flat-square)](https://packagist.org/packages/nafiur/laravel-web-analytics)
[![Total Downloads](https://img.shields.io/packagist/dt/nafiur/laravel-web-analytics.svg?style=flat-square)](https://packagist.org/packages/nafiur/laravel-web-analytics)
[![License](https://img.shields.io/github/license/nafiur/laravel-web-analytics.svg?style=flat-square)](https://github.com/nafiur/laravel-web-analytics/blob/main/LICENSE)

A modern, fast, and self-hosted Web Traffic & Visitor Analytics Dashboard for Laravel with real-time Server-Sent Events (SSE) live streaming and Tailwind CSS styling.

**Current release:** `v3.3.0` (Trends, saved reports, Funnels, Retention, Paths and insight widgets). **Preview on `dev`:** opt-in session replay, minimal playback and heatmaps.

---

## ⚡ Highlights & Key Features

- 🚀 **Real-Time Live Stream (SSE)**: Live active visitor count and instant real-time traffic feed without WebSockets, Node.js, or Pusher. Each stream response is short-lived and paced by EventSource, so no PHP worker is held open while waiting for new traffic.
- 🧭 **Sidebar Navigation**: 20 sidebar links — Overview, Real-time, Logs, Dashboards, Visitors, Traffic Sources, Pages & Screens, Geography, Devices & Browsers, Campaigns, Funnels, Retention, User Paths, Trends, Event Explorer, Events & Goals, Conversions, Performance, Error Monitoring and Settings. Three optional replay links — Session Replay, Heatmaps and Forms — appear only when replay is enabled and the configured gate allows them. A separate optional Feature Flags link appears when flags are enabled. Each has its own bookmarkable route and Blade page; the Settings screen has no analytics tab bar.
- 🎨 **Tailwind CSS UI with Dark Mode**: Modern, responsive dashboard powered by Tailwind CSS Play CDN (zero build step / zero npm dependencies). Uses CSS design tokens (`--lwa-*`, `[data-theme="dark"]`), Lucide outline icons, visible focus states, skeleton loaders, explicit empty/error states, and a 1-click persistent Dark/Light/System preference.
- 📊 **Comprehensive Metrics**: Total Requests, Unique Visitors, Bandwidth (Data Transferred in GB/MB), Avg Response Time, Error Rate.
- 🧪 **Built-In Live Traffic Simulator**: Easily generate realistic live traffic every second with `php artisan lwa:live` to demo and test live streaming.
- 🌍 **GeoIP & Location Provider**: Cloudflare headers, `ip-api`, or fully offline MaxMind GeoLite2 — country/city and coordinates power flag icons and the visitors map.
- 🚨 **Instant Error Alerts**: Telegram, Slack, or Discord webhook alerts on 5xx responses (deduplicated per URL/IP) and on error-rate spikes above a configurable threshold. Disabled by default.
- 📥 **Export to CSV**: Download filtered analytics logs into structured CSV format via high-performance chunked streams.
- 🧹 **Automatic Log Pruning**: Includes an Artisan command (`php artisan lwa:prune`) to maintain a clean database and prevent disk bloat.
- ⚡ **Post-Response Telemetry**: Tracking runs in Laravel's `terminate()` hook, after the HTTP response is sent. Optional GeoIP lookups and webhook alerts still occupy the worker for up to their configured timeouts (1s / 3s by default); keep alerts disabled unless needed.
- 🗄️ **Lightweight & Decoupled Storage**: Stores telemetry in the `lwa_logs` table without coupling to any specific User model, with GDPR-compliant IP anonymization options.
- 🔐 **Privacy-First Tracking**: Do Not Track support and an optional cookieless, daily-salted visitor fingerprint — no cookie banner required.
- 🎯 **UTM Campaigns**: `utm_source`, `utm_medium` and `utm_campaign` are captured automatically and shown on the dedicated Campaigns page.
- 🏳️ **Country Flags**: IP breakdowns and the live traffic feed show emoji flags resolved from the recorded country; the Settings language picker shows the active language flag.
- 🏢 **Multi-Tenancy Ready**: Native support for single-database multi-tenant applications (`stancl/tenancy`, `spatie/laravel-multitenancy`, or custom resolvers) with tenant-scoped dashboards and streams.
- 🖥️ **Technology report**: A dedicated Devices & Browsers page with a "Device types over time" stacked chart, Top operating systems, Top browsers, Top user agents and inline SVG icons for each technology (no extra network requests).
- 🔍 **Breakdown explorer**: Every breakdown has a grid icon that opens a full-table detail modal with search, sorting, row limits, checkboxes and per-row actions. Card menus also offer Proportion/Trend views, per-card CSV/JSON export, expand and pin-to-dashboard.
- 📈 **Chart Group by**: Traffic charts can be grouped by country, path, status code, browser, OS, device type, HTTP method, referrer, data center (city), host or cache status — trend series are computed for both database and file-log sources.
- 🔗 **Observability deep links**: Row-level "View in Observability" opens the Real-time feed pre-filtered by that breakdown value.
- 📜 **Raw request logs**: Dedicated `/web-analytics/logs` page with an outcomes-over-time chart, a collapsible field sidebar (Request / Client / Response / Other fields — click a field to add a filter chip), paginated table, expandable rows with full request details and filtered CSV export. Edge/security fields (bot score, edge status) are intentionally not collected.
- 🧩 **Custom dashboards**: `/web-analytics/dashboards` with a create modal (blank or preset templates) and a DB-backed editor. Users pick widgets from a catalog of existing KPIs, charts, breakdowns, visitors map and recent logs, or place saved Trends/Funnels/Retention/Paths reports as insight widgets; add/remove/reorder/resize them and save. Layouts are scoped by website and tenant, and also manageable through the token-authenticated REST API (`read` for listing, `admin` for writes).
- 📊 **Product analytics (v3.3)**: Trends, DB-backed saved reports, ordered funnels, cohort retention, lifecycle stages and per-user event paths — all computed from bounded `lwa_events` reads with published definitions, never fabricated numbers.
- 🎬 **Session replay (v3.4, opt-in)**: Consent-gated, masked browser recorder with signed uploads, verbatim chunk storage, a gated minimal player and `lwa:replay:prune` retention. Guests are excluded by default and no input values are recorded.
- 🔥 **Heatmaps (v3.4)**: Gated click/scroll heatmaps with path and time filters, bounded aggregation, top targets and explicit rage/dead-click signals derived from recorded replay markers.
- 📝 **Form analytics (v3.4)**: Masked field focus/change, submit and validation-error markers become per-form and per-field funnel counts — without recording a single field value.
- 🚩 **Feature flags (v3.5, opt-in)**: Website/tenant-scoped boolean and multivariate flags with deterministic rollout, overrides and targeting; evaluations are logged to `lwa_events` with a hashed subject.

---

## 🚧 Advanced analytics schema (opt-in)

The advanced analytics data model is additive and disabled by default. Enable it only after reading the architecture blueprint:

```env
WEB_ANALYTICS_SCHEMA_ENABLED=true
WEB_ANALYTICS_WEBSITE_ID=1
WEB_ANALYTICS_COLLECTOR_MODE=sync   # or 'queue' once a worker is available
WEB_ANALYTICS_WRITE_LEGACY=true     # keep writing lwa_logs during migration
WEB_ANALYTICS_QUEUE=                # optional named queue
```

This creates separate `lwa_websites`, `lwa_visitors`, `lwa_sessions`, `lwa_pageviews`, `lwa_events`, hourly/daily rollup, upgrade-checkpoint, API-token, identity, dead-letter, saved-insight and custom-dashboard tables. Session replay adds opt-in `lwa_replay_sessions`, `lwa_replay_chunks`, `lwa_replay_markers` and `lwa_replay_assets` index tables. Existing `lwa_logs` rows are never rewritten or deleted by these migrations; a later additive migration only adds nullable `host` and `cache_status` columns (with `host` backfilled from `full_url`). `WEB_ANALYTICS_WRITE_LEGACY=true` keeps new requests in `lwa_logs` as well. When enabled, the middleware also feeds the collector, which resolves visitors/sessions with a 30-minute inactivity timeout and either writes inline (`sync`) or dispatches `RecordPageview` / `RecordEvent` jobs (`queue`). Custom event properties are validated and stripped of sensitive keys. Run `php artisan lwa:aggregate` (or schedule it hourly/daily) to build rollups; `MetricService` then answers KPI/chart queries from those rollups instead of scanning raw events.

If tables are missing, the dashboard shows a setup page with the `php artisan migrate` instruction, the API returns `503`, the SSE stream emits an `error` event, and commands exit with guidance — no raw stack traces.

Run `php artisan lwa:doctor` before upgrading or enabling the schema: it prints an installer-style table covering PHP/Laravel versions, extensions, DB connection, table readiness, config cache, queue/cache drivers, storage permissions and pending upgrade rows. Use `--json` for CI. If advanced tables are missing because an earlier `migrate` recorded the migration while a config guard skipped it, run `php artisan lwa:doctor --repair` — it executes the additive migration directly and re-checks.

### Production upgrade quickstart

```bash
composer update nafiur/laravel-web-analytics -W
php artisan optimize:clear

# create/repair the advanced tables and verify
php artisan lwa:doctor --repair
php artisan lwa:doctor

# backfill legacy telemetry
php artisan lwa:upgrade --dry-run --website-domain=example.com
php artisan lwa:upgrade --website-domain=example.com

# build rollup tables used by the dashboard
php artisan lwa:aggregate --granularity=hourly
php artisan lwa:aggregate --granularity=daily
```

If views were published earlier, refresh them too:

```bash
php artisan vendor:publish --tag=web-analytics-views --force
php artisan view:clear
```

**Upgrade path:** `lwa:upgrade` is resumable via an `lwa_upgrade_checkpoints` row and never deletes legacy data unless `--prune-legacy` is passed. Re-run it to import rows logged since the previous run. See [UPGRADE.md](UPGRADE.md) for the full guide and rollback steps.

Full design/reference: [ARCHITECTURE.md](ARCHITECTURE.md) — current-state audit, ER diagram, metric definitions, migration plan, security review and milestones. Upgrade steps: [UPGRADE.md](UPGRADE.md).

## 🧩 Developer API (advanced analytics enabled)

```php
use Nafiur\WebAnalytics\Facades\Analytics;

Analytics::pageview('/products', ['title' => 'Products']);
Analytics::event('purchase', ['plan' => 'pro'], 1500.0);
Analytics::track('button_clicked', ['button' => 'buy']);
Analytics::goal('registration');

$summary = Analytics::summary('30d');
$series = Analytics::timeseries('7d');
```

Issue an API token for the REST endpoints:

```php
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;

[$token, $plainToken] = ApiToken::issue($websiteId, ' dashboard', ['read', 'write']);
// store $plainToken securely — only its hash is kept
```

Send it as `Authorization: Bearer <token>`. Abilities: `read`, `write`, `admin`, or `*`. Tokens support expiry, rotation and are tracked with `last_used_at`. Dashboard layouts are available at `GET/POST /dashboards`, `GET/PUT/DELETE /dashboards/{id}` (`read` for reads, `admin` for writes):

```bash
php artisan lwa:token                       # list tokens
php artisan lwa:token --create --website=1 --abilities=read,write
php artisan lwa:token --rotate=1            # prints the new token once
php artisan lwa:token --revoke=1
```

Collector jobs are idempotent: each pageview/event carries a `request_uuid`, so a retried queue job cannot create duplicate rows.

### Product analytics foundation (v3.2)

The package now ships a dependency-free browser tracker and a unified event pipeline:

```blade
{{-- in your public layout; the project key is your website's tracking_id --}}
@if (config('laravel-web-analytics.sdk.enabled'))
    <script src="{{ route('lwa_sdk') }}"></script>
    <script>
        LwaWebAnalytics.init({ key: '{{ $website->tracking_id }}', web_vitals: true });
        LwaWebAnalytics.track('purchase', { plan: 'pro' }, 1500);
        LwaWebAnalytics.identify(42, { plan: 'pro' });
        LwaWebAnalytics.pageview('/pricing');
    </script>
@endif
```

Enable bundle serving with `WEB_ANALYTICS_SDK_ENABLED=true`. Config defaults (`sdk.autocapture`, `sdk.web_vitals`, `sdk.consent`, `sdk.endpoint`, `sdk.max_*`) are injected into the served bundle; values passed to `LwaWebAnalytics.init({...})` override them per page. The SDK batches events into `POST /web-analytics/api/v1/events/batch` using `sendBeacon` (with `fetch` fallback) and authenticates with the public project key — no secret token is shipped to the browser. Server-to-server callers can still use a `write` bearer token on the same endpoint. Payloads are validated, sanitised and deduplicated on `uuid`, so retries are safe.

`identify` aliases the current anonymous id to a known `u_<id>` profile, merges traits, and keeps pre-sign-in events in the same timeline. Browse the result at `/web-analytics/explorer`, or through the read-scoped REST endpoints `GET /users` and `GET /users/{distinctId}`.

### Trends & saved reports (v3.3)

Open `/web-analytics/trends` to chart **events**, **unique users** or summed **value** over hourly/daily/weekly/monthly buckets. Filter by event name, property key/value and time window; add a breakdown property to split the chart into up to six series. Every chart response includes its definitions, the number of events scanned and a note when the 20,000-event sample cap is reached. Anonymous visitor ids aliased by `identify` count as one profile in the users metric.

The same engine is available to scripts via `GET /web-analytics/api/v1/insights/trend` (read token). Save a query from the page or manage reports through `/web-analytics/api/v1/insights` (read/admin tokens); reports live in the DB-backed `lwa_insights` table and are scoped to the active website.

### Funnels (v3.3)

Open `/web-analytics/funnels` to measure ordered, per-user conversion paths. Enter 2–10 event names, choose a conversion window (1 hour to no limit), and optionally filter by an event property. The page reports users at each step, step/overall conversion, drop-off and median completion time; every number is computed from `lwa_events`, counts each user once per step, and ships with its definition. Anonymous ids aliased by `identify` are folded into the same profile.

The same engine is exposed at `GET /web-analytics/api/v1/insights/funnel` (read token). Saved funnels use the same `lwa_insights` table as saved trends, tagged with `type: funnel` so each page only shows its own reports.

### Retention, cohorts & lifecycle (v3.3)

Open `/web-analytics/retention` for daily or weekly cohorts over 2–12 periods. A cohort is the bucket of a user's first matching event; period N is the Nth full bucket after it, and a user is counted once per period. Only fully elapsed periods have a rate — unelapsed buckets render as `—` rather than a fabricated 0%, and period-1 retention is weighted by cohort size. The lifecycle table classifies each fully elapsed bucket as New, Returning, Resurrecting or Dormant.

Both views come from one bounded `EventStore` read (20,000 events, 12 most recent cohorts), fold aliased anonymous ids into one profile, and ship their definitions with the response. Use `GET /web-analytics/api/v1/insights/retention` (read token) for scripts; saved reports live in `lwa_insights` with `type: retention`.

### User paths (v3.3)

Open `/web-analytics/paths` to see the most common ordered event sequences. Each user contributes one path — their first qualifying sequence — with consecutive repeats collapsed into one step. Choose a start/end event, depth (2–8) and number of paths (5–50); the page reports users and share per path plus the top events at each position. Users with a single step or who never reach the start event are counted separately, so denominators stay explicit.

Everything is computed from the same bounded `EventStore` read (20,000 events) with canonical identities and documented definitions. Scripts can use `GET /web-analytics/api/v1/insights/paths` (read token); saved reports live in `lwa_insights` with `type: paths`.

### Insight widgets on custom dashboards (v3.3)

Open a dashboard in edit mode to see a **Saved insights** section next to the standard widget catalog. It lists website/tenant-scoped reports from `lwa_insights`; adding one creates a DB-backed widget that is rendered server-side by the same Trends, Funnel, Retention or Paths engine used on the full report page. Insight widgets follow the dashboard's selected time window (including custom dates), show a headline plus the top rows, and disclose the definition, `lwa_events` source and number of events scanned. At most 12 insight widgets are computed per page, and deleted or foreign reports render an explicit unavailable message instead of fabricated numbers.

This is separate from the **Add to dashboard** card-menu action, which pins browser-local report shortcuts into Overview via `localStorage`. Only reports placed through the dashboard editor are saved in `lwa_dashboards` and shared across browsers.

### Session replay foundation (v3.4, opt-in)

Session replay is disabled by default and must be explicitly consented to. Enable the recorder with `WEB_ANALYTICS_REPLAY_ENABLED=true` and define the `viewLwaReplay` gate in your app — replay pages fail closed when it is missing:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewLwaReplay', fn ($user = null) => $user !== null);
```

Then place the directive in the layouts where recording is allowed:

```blade
@if (config('laravel-web-analytics.replay.enabled'))
    @lwaReplay
@endif
```

The directive emits a signed, expiring config object plus a deferred, build-free recorder bundle served from `/web-analytics/replay.js`. The recorder starts only when replay consent is granted (`WEB_ANALYTICS_REPLAY_CONSENT=true`, or set `laravel-web-analytics.replay.consent.replay` at runtime after your consent UI), the visitor is eligible (guests are excluded unless `WEB_ANALYTICS_REPLAY_RECORD_GUESTS=true`), and the page path is not excluded. Before upload, the recorder removes every input value, empties `[data-lwa-mask]` nodes, strips framework state attributes such as `wire:snapshot`, and records only structural target metadata — never input text.

Chunks are uploaded to `POST /web-analytics/api/v1/replay/chunks` with the signed token; no session cookie, CSRF token or secret bearer token is required. The server stores chunks verbatim on the configured Laravel disk and indexes them in `lwa_replay_sessions`, `lwa_replay_chunks`, `lwa_replay_markers` and `lwa_replay_assets`. Website and tenant scope come from the signed token, never client input. Run `php artisan lwa:replay:prune` daily to enforce the 30-day default retention.

`/web-analytics/replay` lists indexed sessions behind the configurable gate. **Play** opens `/web-analytics/replay/{session}`, whose deferred player fetches a gated manifest and chunk routes, decompresses chunks in the browser and renders sanitized snapshots in a script-disabled sandbox with a timeline and marker list. Every playback and asset request re-checks the gate and the session's website/tenant scope. This is minimal playback, not full-fidelity DOM patching.

#### How replay works

1. **Recorder** — `@lwaReplay` emits a small signed config object and a deferred, build-free `replay.js`. One recording is kept per browser session key, with masked DOM snapshots plus structural click/scroll/error/web-vital markers. No rrweb or npm build step is involved.
2. **Signed token** — the server signs website, tenant, user, session key, path and sampling settings with `APP_KEY` (signed, not encrypted, and expiring). The recorder sends this token back with every upload, so ingest needs no session, cookie, CSRF token or secret bearer token.
3. **Ingest** — every few seconds the recorder uploads a gzipped, size-bounded chunk with a compact marker index to `POST /web-analytics/api/v1/replay/chunks`. Uploads are idempotent per session key + sequence; the server stores the file and updates the index without parsing the replay events.
4. **Storage** — chunks go to `lwa-replay/sessions/{key}/{seq}.json.gz` on any Laravel disk. `lwa_replay_sessions`, `lwa_replay_chunks` and `lwa_replay_markers` hold the searchable index; `lwa_replay_assets` is reserved for content-hashed stylesheets.
5. **Viewer** — `/web-analytics/replay` lists recordings; `/web-analytics/replay/{id}` loads a manifest, chunks and markers through routes that re-check the configured gate and the recording's website/tenant scope on **every** request.

### Heatmaps & interaction signals (v3.4)

`/web-analytics/heatmaps` is gated by the same `viewLwaReplay` gate and aggregates `lwa_replay_markers` into click and scroll views. Filter by recorded path and a 7/30/90-day or all-time window. The recorder captures viewport dimensions with each marker, so clicks are bucketed to a 2.5% grid and scroll depth to 10% bands. A bounded PHP aggregation (20,000 most recent scoped markers) also reports top targets, rage clicks (3 clicks on one target within 1 second) and dead clicks (no different marker within 2 seconds), with every definition and sample-cap note shown on the page. No input text is used — only masked, structural target metadata.

### Form analytics (v3.4)

`/web-analytics/forms` uses the same gate, path filter and time windows. The recorder emits only structural markers: first focus and first change per field, form submit with `checkValidity()` result, and validation errors with boolean validity flags. Field values and validation messages are never captured. The service aggregates distinct sessions per form into starts, submits, errors, invalid submits and conversion/error rates, plus per-field focus/change counts and change rates. Conversion is `submits / starts` and stays `0%` when no start was recorded — no submission is inferred from navigation.

### Feature flags (v3.5, opt-in)

Enable flags with `WEB_ANALYTICS_FLAGS_ENABLED=true` and manage boolean or multivariate flags at `/web-analytics/flags`. Flags are scoped to the active website and trusted tenant. Evaluation order is: per-subject override (`off` disables), then all targeting rules (AND), then `enabled` and a deterministic `sha256(key:subject) % 100` rollout bucket. Multivariate variants use the same bucket with declared weights.

Gate host markup with `@feature('key') ... @endfeature` or print the assigned variant with `@featureVariant('key')`. Scripts can call `GET /web-analytics/api/v1/flags/evaluate?key=...&subject=...` with a `read` token. Each distinct flag key + subject evaluation is logged to `lwa_events` as `feature_flag_evaluated` with a hashed subject (never a raw user id), so exposure is measurable with the same Trends engine. A/B result statistics and flag-driven experiments are not part of this foundation.

## 🎨 Design System

| Topic | Choice |
|-------|--------|
| UI Framework | Tailwind CSS (Play CDN — no build step) |
| Backend | Laravel Blade |
| Charts | ApexCharts |
| Icons | Lucide |
| Theme | Dark + Light (+ System), persisted in `localStorage` |
| Layout | Responsive (desktop / tablet / mobile) |
| Sidebar | Collapsible on desktop, off-canvas on mobile |
| Typography | Inter (with JetBrains Mono for numbers/codes) |
| Style | Modern restrained SaaS |

Theme colors are exposed as CSS custom properties (`--lwa-*`) with `[data-theme="dark"]`, so components do not duplicate color variants. The desktop sidebar collapse state is remembered across visits.

---

## 📋 Requirements

- PHP `^8.2 | ^8.3 | ^8.4 | ^8.5`
- Laravel `^11.0 | ^12.0 | ^13.0`

> v2 applications on older PHP/Laravel versions should stay on the 2.x line and follow [UPGRADE.md](UPGRADE.md) when moving to v3.

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
| `POST /web-analytics/websites` | `lwa_websites_store` | Register a website |
| `POST /web-analytics/websites/switch` | `lwa_websites_switch` | Switch the active website (session) |
| `DELETE /web-analytics/websites/{website}` | `lwa_websites_destroy` | Remove a website (requires `confirm=DELETE`) |
| `POST /web-analytics/settings/test-alert` | `lwa_settings_test_alert` | Send a test alert through the configured channel |
| `GET /web-analytics/dashboards` | `lwa_dashboards` | Custom dashboard list and create modal |
| `GET /web-analytics/dashboards/{id}` | `lwa_dashboard_view` | Read-only custom dashboard |
| `GET /web-analytics/dashboards/{id}/edit` | `lwa_dashboard_edit` | Custom dashboard editor |
| `GET /web-analytics/sdk.js` | `lwa_sdk` | Browser tracker bundle (when enabled) |
| `GET /web-analytics/replay.js` | `lwa_replay_bundle` | Replay recorder bundle (when enabled) |
| `GET /web-analytics/replay` | `lwa_replay` | Gated replay session index (foundation) |
| `GET /web-analytics/replay/{id}` | `lwa_replay_show` | Gated minimal replay player |
| `GET /web-analytics/replay/{id}/manifest` | `lwa_replay_manifest` | Gated chunk/marker manifest |
| `GET /web-analytics/replay/{id}/chunk/{sequence}` | `lwa_replay_chunk` | Gated verbatim chunk download |
| `GET /web-analytics/replay-player.js` | `lwa_replay_player_bundle` | Deferred player bundle (gated) |
| `GET /web-analytics/heatmaps` | `lwa_heatmaps` | Gated click/scroll heatmaps and interaction signals |
| `GET /web-analytics/forms` | `lwa_forms` | Gated form funnel and field engagement report |
| `GET /web-analytics/flags` | `lwa_flags` | Feature flag list and editor |
| `GET /web-analytics/flags/{id}/edit` | `lwa_flags_edit` | Edit an existing flag |
| `GET /web-analytics/api/v1/flags/evaluate` | `lwa_api_flags_evaluate` | Deterministic flag evaluation (bearer token, `read`) |
| `GET /web-analytics/api/v1/summary` | `lwa_api_summary` | JSON summary (bearer token, `read`) |
| `GET /web-analytics/api/v1/timeseries` | `lwa_api_timeseries` | JSON time series (bearer token, `read`) |
| `POST /web-analytics/api/v1/events` | `lwa_api_event` | Track a custom event (bearer token, `write`) |
| `POST /web-analytics/api/v1/events/batch` | `lwa_api_events_batch` | Batch ingest (project key or bearer token, `write`) |
| `POST /web-analytics/api/v1/replay/chunks` | `lwa_api_replay_chunks` | Replay chunk ingest (HMAC-signed config token) |
| `GET /web-analytics/api/v1/users` | `lwa_api_users` | List user profiles (bearer token, `read`) |
| `GET /web-analytics/api/v1/users/{distinctId}` | `lwa_api_user` | Profile, aliases and timeline (bearer token, `read`) |
| `GET /web-analytics/api/v1/insights/trend` | `lwa_api_insights_trend` | Trends query with documented definitions (bearer token, `read`) |
| `GET /web-analytics/api/v1/insights/funnel` | `lwa_api_insights_funnel` | Ordered per-user funnel with documented rates (bearer token, `read`) |
| `GET /web-analytics/api/v1/insights/retention` | `lwa_api_insights_retention` | Cohort retention and lifecycle with documented rates (bearer token, `read`) |
| `GET /web-analytics/api/v1/insights/paths` | `lwa_api_insights_paths` | Per-user event paths and step distribution (bearer token, `read`) |
| `GET /web-analytics/api/v1/insights` | `lwa_api_insights` | List saved reports (bearer token, `read`) |
| `POST /web-analytics/api/v1/insights` | `lwa_api_insights_store` | Save a report (bearer token, `admin`) |
| `GET /web-analytics/api/v1/insights/{insight}` | `lwa_api_insights_show` | Show a report (bearer token, `read`) |
| `PUT /web-analytics/api/v1/insights/{insight}` | `lwa_api_insights_update` | Update a report (bearer token, `admin`) |
| `DELETE /web-analytics/api/v1/insights/{insight}` | `lwa_api_insights_destroy` | Delete a report (bearer token, `admin`) |
| `GET /web-analytics/api/v1/websites` | `lwa_api_websites` | List websites (bearer token, `admin`) |

Every sidebar section also has its own bookmarkable URL:

| URL | Name | URL | Name |
|-----|------|-----|------|
| `/web-analytics/overview` | `lwa_overview` | `/web-analytics/campaigns` | `lwa_campaigns` |
| `/web-analytics/realtime` | `lwa_realtime` | `/web-analytics/events` | `lwa_events` |
| `/web-analytics/visitors` | `lwa_visitors` | `/web-analytics/conversions` | `lwa_conversions` |
| `/web-analytics/sources` | `lwa_sources` | `/web-analytics/performance` | `lwa_performance` |
| `/web-analytics/pages` | `lwa_pages` | `/web-analytics/errors` | `lwa_errors` |
| `/web-analytics/geography` | `lwa_geography` | `/web-analytics/settings` | `lwa_settings` |
| `/web-analytics/devices` | `lwa_devices` | `/web-analytics/explorer` | `lwa_explorer` |
| `/web-analytics/trends` | `lwa_trends` | `/web-analytics/dashboards` | `lwa_dashboards` |
| `/web-analytics/funnels` | `lwa_funnels` | `/web-analytics/logs` | `lwa_logs` |
| `/web-analytics/retention` | `lwa_retention` | | |
| `/web-analytics/paths` | `lwa_paths` | | |
| `/web-analytics/replay` | `lwa_replay` | `/web-analytics/heatmaps` | `lwa_heatmaps` |
| `/web-analytics/forms` | `lwa_forms` | `/web-analytics/flags` | `lwa_flags` |

The legacy `/web-analytics` (`lwa_dashboard`) URL still works and shows the Overview pane. The Events and Conversions URLs render an explicit “not available yet” pane rather than fabricated numbers.

Generate URLs in your Blade templates or code:

```php
route('lwa_dashboard', ['range' => '24h']);
route('lwa_data', ['range' => '24h']);
route('lwa_stream');
route('lwa_export', ['range' => '24h']);
```

### Settings page

The dashboard's **Setting** tab provides an adapted settings screen:

- **General** — **12 languages** (English, বাংলা, हिन्दी, Español, Français, Deutsch, Português, العربية, 中文, 日本語, اردو, Indonesian — Arabic/Urdu switch the layout to RTL), the **complete IANA timezone database** (all PHP-supported zones), and appearance (System/Light/Dark). These are browser preferences stored in `localStorage`; no database changes are needed. The timezone is applied to live-feed timestamps and chart labels via `Intl.DateTimeFormat`.
- **Dashboard** — default time range, data source, compact numbers and keyboard shortcuts.
- **Collection** — read-only view of capture/GeoIP config.
- **Alerts** — read-only channel/threshold info and a **Send test alert** action.
- **Export** — default CSV range and whether to include the tenant scope in export links.
- **Data maintenance** — row counts and oldest/newest timestamps, plus **Prune** and **Empty table** actions. Both are POST routes behind the dashboard's middleware with CSRF protection; emptying requires typing `DELETE`, and in multi-tenant mode only the current tenant's rows are removed.
- **Websites** — register properties, switch the active website (session-based), and remove websites. A header selector is available when more than one website is registered; all queries are scoped to the active website.
- **About** — package/runtime information.

Set `WEB_ANALYTICS_DESTRUCTIVE_ACTIONS=false` to disable the prune/empty actions entirely.

### Data source & client IP

By default the dashboard reads the `lwa_logs` database table. The **Data source** selector can switch to `?source=file`, which parses a web-server access log from `WEB_ANALYTICS_SERVER_LOG_PATH` (or a detected default path). Configure `WEB_ANALYTICS_MAX_LINES` to bound how much of the file is read.

The header's refresh button reloads the current report through `lwa_data` and shows a retry toast on failure. The Live Stream tab displays its SSE connection state (connected / reconnecting / disconnected) with a manual retry action.

Client IP resolution uses Laravel's trusted-proxy configuration. Only enable `WEB_ANALYTICS_TRUST_FORWARDED_HEADERS=true` if the immediate peer is a trusted reverse proxy; otherwise `CF-Connecting-IP` / `X-Forwarded-For` can be spoofed by clients.

---

## 🧩 View structure

Each sidebar route renders its own Blade page; no section is a hidden tab inside another page:

```text
resources/views/
├── layouts/app.blade.php          # shared shell (head, header, sidebar, subheader, scripts)
├── pages/                         # one Blade file per route
│   ├── overview.blade.php         ├── sources.blade.php      ├── devices.blade.php
│   ├── realtime.blade.php         ├── pages.blade.php        ├── campaigns.blade.php
│   ├── visitors.blade.php         ├── geography.blade.php    ├── performance.blade.php
│   ├── logs.blade.php             ├── explorer.blade.php     ├── trends.blade.php
│   ├── funnels.blade.php          ├── retention.blade.php    ├── paths.blade.php
│   ├── events.blade.php           ├── conversions.blade.php  ├── errors.blade.php
│   ├── dashboards.blade.php       ├── dashboard-view.blade.php├── replay.blade.php
│   ├── replay-show.blade.php      ├── heatmaps.blade.php
│   ├── forms.blade.php            ├── flags.blade.php
│   └── settings.blade.php
├── sections/                      # content-only partials included by the pages
└── partials/                      # header, sidebar, subheader, scripts, breakdown-list,
                                   # breakdown-card, breakdown-trend-list, card-menu,
                                   # breakdown-detail-modal, group-by-select
```

The legacy `dashboard.blade.php` simply extends the shared layout and renders the Overview section.

## 🧭 Dashboard Navigation

The sidebar exposes 20 sections by default, plus three optional replay sections when replay is enabled and the gate allows them, and an optional Feature Flags section when flags are enabled. Each is rendered by its own route and Blade page:

| Menu item | Shows |
|-----------|-------|
| **Overview** | KPI cards, traffic chart, top pages, sources, countries, devices |
| **Real-time** | Live SSE traffic feed and active visitors |
| **Logs** | Raw request log explorer — outcomes chart, filters, expandable rows and CSV export |
| **Dashboards** | Create and edit custom dashboards from existing widgets/templates |
| **Visitors** | Unique visitors, IPs, geography, devices, browsers |
| **Traffic Sources** | Referrers / UTM sources (scrolls to Top referrers) |
| **Pages & Screens** | Top paths (scrolls to Top paths) |
| **Geography** | Top countries (scrolls to Countries) |
| **Devices & Browsers** | Device / OS / browser breakdowns |
| **Campaigns** | UTM source / medium / campaign breakdowns |
| **Funnels** | Ordered per-user conversion funnels with drop-off and median completion time |
| **Retention** | Daily/weekly cohorts, period retention and lifecycle stages |
| **User Paths** | Most common ordered event sequences with shares and step distribution |
| **Trends** | Event/user/value trends with breakdowns and saved reports |
| **Event Explorer** | Raw event browser with name, property, user and time filters |
| **Session Replay** *(optional)* | Gated recording index and minimal player with timeline and markers |
| **Heatmaps** *(optional)* | Gated click/scroll concentration, top targets and rage/dead-click signals |
| **Forms** *(optional)* | Gated per-form starts, submits, errors and per-field engagement, without field values |
| **Feature Flags** *(optional)* | Boolean/multivariate flags with deterministic rollout, overrides and targeting |
| **Events & Goals** | Recent custom events captured through the facade/API (name, properties, value) |
| **Conversions** | Recorded goal/purchase events — arrange them into a documented, ordered funnel; no inferred conversions |
| **Performance** | Latency, response times, bandwidth by path |
| **Error Monitoring** | 4xx/5xx errors, slow/error paths |
| **Settings** | Preferences, maintenance, alerts, about |

> **No fabricated analytics:** no placeholder numbers are shown anywhere. Event pages show only events that were actually recorded. All derived metrics use explicit definitions:
>
> - **Total Visitors** — distinct IP addresses in the range (privacy-preserving device proxy)
> - **Unique Visitors** — distinct `session_id` (IP fallback)
> - **Pageviews** — GET requests to non-API paths
> - **Sessions** — distinct visitor session identifiers
> - **Bounce Rate** — sessions with exactly one recorded request
> - **Avg Engagement Time** — average time between first and last request in a session |

## 🔍 Technology & breakdown reports

The **Devices & Browsers** page (`/web-analytics/devices`) shows a stacked "Device types over time" chart plus Top operating systems, browsers and user agents. Each card has an expand icon and a "..." menu:

| Option | Behaviour |
|---|---|
| **View as → Proportion** | Horizontal bars with percentages (default) |
| **View as → Trend** | Inline SVG sparkline per item using `analytics.breakdown_trends` |
| **Export → CSV / JSON** | Downloads that card's own breakdown data (CSV cells are hardened against formula injection) |
| **View in Observability** | Opens the Real-time feed; row-level links carry `?filter=<name>` and pre-fill the quick filter |
| **Add to dashboard** | Pins the card into a "Pinned reports" section on Overview (stored in `localStorage`) |
| **Expand (grid icon)** | Opens a full-table detail modal with search, Proportion/Value/Name sorting, 10/25/50 items, select-all checkboxes and per-row actions |

The grid icon is available on **every** breakdown list, not only on the Technology page. View mode, pin and expand preferences are browser-local — no server-side user state and no cookies.

### Live updates

Reports can refresh without a page reload:

- **Auto refresh** — the subheader `Auto: Off / 5s / 10s / 30s` selector polls the compact `/web-analytics/data?light=1` endpoint (the choice is stored in `localStorage`). It updates KPI values, breakdown lists (including pinned reports), trend sparklines, device legend totals and chart series in place.
- **SSE push** — when new traffic arrives, the existing stream emits an `analytics` event carrying the same compact payload, so charts and breakdowns update immediately. The manual **Refresh** button uses the same in-place apply path.
- The light payload omits recent/error log rows to keep polling cheap; no extra packets are sent when there is no new traffic beyond the periodic poll.

### Chart Group by

Time-series charts include a **Group by** select. Available dimensions depend on recorded data:

`Country · Path · Origin status code · Browser · Operating system · Device type · HTTP protocol · Referrer · Data center (city) · Host · Cache status (all)`

**None** always means a single "total requests" line — no grouping. The Logs page opens with **None**; select **Cache status (all)** to see all delivery outcomes (HIT / MISS / BYPASS / Dynamic) together.

- **Host** is recorded from the request host; older rows are backfilled from `full_url` by an additive migration.
- **Cache status** is normalised from `cf-cache-status` / `x-cache` headers (`HIT`, `MISS`, `BYPASS`, `EXPIRED`, …). Rows logged before upgrading have no cache status, so they are excluded from that breakdown.
- **Data center** uses the recorded city/country — full IATA codes are not inferred.

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

    'stream' => [
        'mode'               => env('WEB_ANALYTICS_STREAM_MODE', 'sse'), // 'sse' | 'polling'
        'polling_interval'   => env('WEB_ANALYTICS_POLLING_INTERVAL', 5),
        'max_execution_time' => env('WEB_ANALYTICS_STREAM_MAX_EXECUTION_TIME', 0),
    ],

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

    'api' => [
        'rate_limit_per_minute' => env('WEB_ANALYTICS_API_RATE_LIMIT', 60),
    ],

    'privacy' => [
        'respect_dnt'            => env('WEB_ANALYTICS_RESPECT_DNT', true),
        'visitor_id_strategy'    => env('WEB_ANALYTICS_VISITOR_ID_STRATEGY', 'session'), // 'session' | 'fingerprint'
        'fingerprint_salt'       => env('WEB_ANALYTICS_FINGERPRINT_SALT', ''),
        'masked_query_parameters'=> ['token', 'password', 'secret', 'api_key', 'email', 'card', 'cvv', /* ... */],
        'mask_referrer'          => env('WEB_ANALYTICS_MASK_REFERRER', true),
    ],

    /*
     * GeoIP & Location Provider
     */
    'geoip' => [
        'enabled'  => env('WEB_ANALYTICS_GEOIP_ENABLED', true),
        'provider'        => env('WEB_ANALYTICS_GEOIP_PROVIDER', ''), // '', 'ip-api', 'maxmind'
        'timeout'         => env('WEB_ANALYTICS_GEOIP_TIMEOUT', 1),
        'maxmind_db_path' => env('WEB_ANALYTICS_MAXMIND_DB', storage_path('app/GeoLite2-City.mmdb')),
        'license_key'     => env('MAXMIND_LICENSE_KEY', null),
        'account_id'      => env('MAXMIND_ACCOUNT_ID', null),
        'auto_download'   => env('WEB_ANALYTICS_GEOIP_AUTO_DOWNLOAD', false),
        'update_days'     => env('WEB_ANALYTICS_GEOIP_UPDATE_DAYS', 7),
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
        'email'                           => env('WEB_ANALYTICS_ALERT_EMAIL', null), // weekly summary recipient
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

    /*
     * Advanced analytics schema (opt-in)
     */
    'analytics' => [
        'enabled'                     => env('WEB_ANALYTICS_SCHEMA_ENABLED', false),
        'mode'                        => env('WEB_ANALYTICS_COLLECTOR_MODE', 'sync'), // 'sync' | 'queue'
        'write_legacy'                => env('WEB_ANALYTICS_WRITE_LEGACY', true),
        'queue'                       => env('WEB_ANALYTICS_QUEUE', null),
        'website_id'                  => env('WEB_ANALYTICS_WEBSITE_ID', null),
        'session_timeout_minutes'     => env('WEB_ANALYTICS_SESSION_TIMEOUT', 30),
        'bounce_max_duration_seconds' => env('WEB_ANALYTICS_BOUNCE_MAX_SECONDS', 0),
    ],

    /*
     * Browser SDK (served from /web-analytics/sdk.js when enabled).
     * These are injected as defaults into the bundle; init(options) wins.
     */
    'sdk' => [
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
     * Session replay (v3.4 foundation). Opt-in, masked, consent-gated.
     */
    'replay' => [
        'enabled'           => env('WEB_ANALYTICS_REPLAY_ENABLED', false),
        'gate'              => env('WEB_ANALYTICS_REPLAY_GATE', 'viewLwaReplay'),
        'sample_rate'       => (float) env('WEB_ANALYTICS_REPLAY_SAMPLE_RATE', 0),
        'record_guests'     => env('WEB_ANALYTICS_REPLAY_RECORD_GUESTS', false),
        'consent'           => ['replay' => env('WEB_ANALYTICS_REPLAY_CONSENT', false)],
        'mask_all_inputs'   => env('WEB_ANALYTICS_REPLAY_MASK_INPUTS', true),
        'masked_selectors'  => ['[data-lwa-mask]', 'input[type="password"]', 'input[type="email"]'],
        'excluded_paths'    => ['web-analytics/*', 'admin/*', 'livewire/*', 'broadcasting/auth'],
        'retention_days'    => (int) env('WEB_ANALYTICS_REPLAY_RETENTION_DAYS', 30),
        'max_chunk_bytes'   => (int) env('WEB_ANALYTICS_REPLAY_MAX_CHUNK_BYTES', 1000000),
        'flush_interval_ms' => (int) env('WEB_ANALYTICS_REPLAY_FLUSH_MS', 5000),
        'signature_ttl_minutes' => (int) env('WEB_ANALYTICS_REPLAY_SIGNATURE_TTL', 15),
        'disk'              => env('WEB_ANALYTICS_REPLAY_DISK'),
        'endpoint'          => env('WEB_ANALYTICS_REPLAY_ENDPOINT', '/web-analytics/api/v1/replay/chunks'),
    ],

    /*
     * Feature flags (v3.5 foundation). Opt-in, deterministic, logged.
     */
    'flags' => [
        'enabled'  => env('WEB_ANALYTICS_FLAGS_ENABLED', false),
        'endpoint' => env('WEB_ANALYTICS_FLAGS_ENDPOINT', '/web-analytics/api/v1/flags/evaluate'),
    ],

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
- **URL masking:** configured sensitive query parameters (tokens, passwords, emails, card data…) are replaced with `[REDACTED]` in stored URLs and referrers; UTM parameters are preserved.
- **API protection:** bearer tokens with abilities plus per-token rate limiting (`WEB_ANALYTICS_API_RATE_LIMIT`, default 60/min).

## 🗺️ Offline GeoIP & live map

The Overview and Real-time pages include a vector world map. Offline MaxMind support is **opt-in** — it is deliberately not installed automatically during `composer require` (not every site needs it, and GeoLite2 requires a MaxMind license key). Use the setup command instead:

```bash
php artisan lwa:geoip                 # status report
php artisan lwa:geoip --install       # installs maxmind-db/reader
php artisan lwa:geoip --download --license-key=YOUR_KEY
# if your account requires an account ID:
php artisan lwa:geoip --download --account-id=YOUR_ACCOUNT_ID
# scheduled/auto mode (uses .env credentials, downloads only when missing or stale):
php artisan lwa:geoip --auto
# alias: php artisan analytics:geoip
```

Never pass a real license key inline on a shared shell or commit it — use `MAXMIND_LICENSE_KEY` (and optionally `MAXMIND_ACCOUNT_ID`) in `.env`. If a key is ever exposed, revoke it in the MaxMind portal immediately.

Non-secret values (`analytics.enabled`, `geoip.provider`, `geoip.maxmind_db_path`, `geoip.auto_download`) can also be set by editing the published `config/laravel-web-analytics.php`. Keep the license key/account ID in `.env` and reference them with `env()` in config. After any config change, run `php artisan optimize:clear` (or `config:clear`) — otherwise the cached config keeps the old values.

```env
WEB_ANALYTICS_GEOIP_PROVIDER=maxmind
WEB_ANALYTICS_MAXMIND_DB=/absolute/path/to/GeoLite2-City.mmdb
MAXMIND_LICENSE_KEY=your-key         # only needed for --download
MAXMIND_ACCOUNT_ID=your-id           # only if your account requires it
WEB_ANALYTICS_GEOIP_AUTO_DOWNLOAD=true
WEB_ANALYTICS_GEOIP_UPDATE_DAYS=7
```

### Automatic updates

Set `WEB_ANALYTICS_GEOIP_AUTO_DOWNLOAD=true` and schedule the auto mode in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('lwa:geoip --auto')->weekly();
```

It downloads only when the database file is missing or older than `WEB_ANALYTICS_GEOIP_UPDATE_DAYS` (default 7). With no license key configured, it logs a warning and exits successfully, so the scheduler stays green. When auto-download is disabled, `--auto` is a no-op.

### Full setup sequence

```bash
composer update nafiur/laravel-web-analytics -W
php artisan optimize:clear

# put MAXMIND_LICENSE_KEY (and optionally MAXMIND_ACCOUNT_ID) in .env first
php artisan lwa:geoip --download
php artisan lwa:geoip            # must report Database file OK
php artisan view:clear
```

GeoLite2 is refreshed periodically by MaxMind — re-run `php artisan lwa:geoip --download` occasionally to update the local database.

### Download troubleshooting

If the download fails, check outbound HTTPS to MaxMind without exposing the key in command history:

```bash
read -rs MM_KEY
curl -sS -o /tmp/lwa.tar.gz -w 'HTTP %{http_code}\n' \
  "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=${MM_KEY}&suffix=tar.gz"
file /tmp/lwa.tar.gz     # must say "gzip compressed data"
```

An `HTTP 200` with a gzip file means the key is fine; the package command will then succeed. `401/403` means the key (or account ID) is invalid/revoked. If you cannot download at all, the keyless country-centroid fallback still plots approximate visitor locations from Cloudflare country headers.

`--download` fetches `GeoLite2-City.mmdb` from MaxMind once at setup time using the license key; all lookups afterwards are **local**, with no external API call. Country, city and coordinates are cached per IP and plotted on the map.

**No license key?** The map still works with a keyless fallback: when a country code/name is known (Cloudflare `CF_IPCOUNTRY` header or stored country), `CountryCoordinates` resolves an approximate **country centroid** so visitors appear on the map without any API call or database download. It is country-level only (never city-level) and is labelled as approximate in the UI. With the MaxMind reader/database installed, real coordinates take precedence automatically.

The map library ships its own world shape data, so no map tile API is called either.

## 📡 SSE limits & polling fallback

Each stream response is short-lived and paced by EventSource `retry` plus `Last-Event-ID`. For shared hosting with very few PHP workers:

```env
WEB_ANALYTICS_STREAM_MODE=polling
WEB_ANALYTICS_POLLING_INTERVAL=5
WEB_ANALYTICS_STREAM_MAX_EXECUTION_TIME=15
```

`polling` uses ordinary `fetch()` calls instead of a persistent connection; `max_execution_time` caps a single stream response with `set_time_limit()` (0 leaves PHP's setting untouched). The connection badge reports Connected / Reconnecting / Disconnected / Polling and offers a manual retry.

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
php artisan lwa:export --format=csv --range=7d --output=storage/app/analytics.csv
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
| `lwa:aggregate` | — | Build hourly/daily rollups from raw events (`--website`, `--from`, `--to`, `--granularity`) |
| `lwa:upgrade` | `analytics:upgrade` | Backfill legacy `lwa_logs` into the advanced schema (`--dry-run`, `--chunk`, `--since`, `--prune-legacy`) |
| `lwa:doctor` | `analytics:doctor` | Installer-style preflight report (`--json` for CI, `--repair` to create missing advanced tables) |
| `lwa:geoip` | `analytics:geoip` | Check/install offline MaxMind GeoIP support (`--install`, `--download`, `--license-key`) |
| `lwa:live` | — | Generate simulated live traffic for demos (`--rps`, `--interval`, `--count`, `--tenant`) |

---

## 🧪 Testing

```bash
composer install
vendor/bin/phpunit
```

The suite uses Orchestra Testbench. Locally it defaults to an in-memory SQLite database (`pdo_sqlite` required). CI runs the same suite against **SQLite, MySQL 8 and PostgreSQL 16** through service containers; set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` to run it against another database locally. The reporting queries are also verified against MySQL's strict `ONLY_FULL_GROUP_BY` mode.

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/nafiur/laravel-web-analytics/issues).

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.