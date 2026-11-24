# Changelog

All notable changes to this project are documented here. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- **Technology report** — Devices & Browsers page now shows a stacked "Device types over time" chart, Top operating systems, Top browsers and Top user agents, plus a card menu with CSV/JSON export. Device categories are normalised through `DeviceCategory` so both database and file-log sources produce identical series.
- **Technology icons** — OS/browser/device breakdowns render inline SVG marks (no external requests) through `TechnologyIcon`; misleading country flags no longer appear on non-location breakdowns.
- **Card menus** — reusable `card-menu` partial with expand, **View as** (Proportion/Trend), per-card CSV/JSON export, "View in Observability" and pin-to-dashboard. Trend series are exposed as `analytics.breakdown_trends` for both database and file-log sources and drawn as inline sparklines. View/expand/pin preferences persist in localStorage; overview renders pinned report slots.
- **Breakdown detail modal** — the grid icon opens a full-table detail view with search, Proportion/Value/Name sorting, 10/25/50 row limits, select-all checkboxes and per-row actions. Works on every breakdown list, not just Technology cards.
- **Chart Group by** — time-series charts can be broken down by country, path, status code, browser, OS, device type, method, referrer, data center (city), host or cache status. `breakdown_trends` now covers all of these dimensions in both data sources. ApexCharts initialisation is guarded so a blocked CDN cannot stop the rest of the dashboard JS.
- **Host & cache status** — additive migration adds `host` and `cache_status` to `lwa_logs` (hosts backfilled from `full_url`). The middleware records the request host and normalises `cf-cache-status` / `x-cache` headers. Row-level "View in Observability" links carry a `?filter=` deep link into the Real-time feed, which pre-fills the quick filter.

## [3.0.0] - 2026-11-23

### Added

- **Advanced analytics schema (opt-in)** — `lwa_websites`, `lwa_visitors`, `lwa_sessions`, `lwa_pageviews`, `lwa_events`, hourly/daily rollups, upgrade checkpoints and API tokens.
- **Visitor/session engine** — `SessionResolver` with configurable inactivity timeout, bounce/engagement definitions and unit tests.
- **Collector** — `AnalyticsCollector` with `sync` and `queue` modes, `RecordPageview` / `RecordEvent` jobs, `StorageDriver` and `Collector` contracts, DTOs and event-property validation.
- **Aggregation** — `AggregationService`, `AggregatePeriod` job, `lwa:aggregate` command and aggregate-only `MetricService`.
- **Upgrade path** — resumable `lwa:upgrade` (`analytics:upgrade`) with `--dry-run`, `--chunk`, `--since` and `--prune-legacy`.
- **Developer API** — `Analytics` facade (`pageview`, `event`, `track`, `goal`, `summary`, `timeseries`) and token-authenticated REST API with abilities, expiry, rate limiting and URL masking.
- **Multi-website** — website registry, session switcher, header selector and Settings section.
- **UI/system** — 13 route-based Blade pages with a shared layout, design tokens, Lucide icons, skeletons, explicit empty/error/loading states, 12 languages, full IANA timezone list, offline MaxMind visitors map, SSE connection states and polling fallback.
- **Security/privacy** — DNT, cookieless fingerprint option, IP anonymization, bot filtering, sensitive query masking, CSV-injection protection, tenant/website scoping and authorized SSE.
- **Benchmarking** — read-only `lwa:benchmark` command comparing aggregate vs raw scan timings.
- **Preflight** — `lwa:doctor` (`analytics:doctor`, `--json`, `--repair`) prints an installer-style readiness report and can create missing advanced tables; guards across UI/API/commands handle unmigrated installs.
- **GeoIP setup** — `lwa:geoip` (`analytics:geoip`) reports/checks offline MaxMind readiness and can install the optional reader or download GeoLite2-City.mmdb with a license key.
- **Keyless map fallback** — country code/name resolves to an approximate country centroid, so visitors can be plotted without a MaxMind key, database or external API.
- **Auto GeoIP updates** — `lwa:geoip --auto` downloads only when the database is missing/stale, driven by `.env` credentials and a configurable interval; safe to schedule weekly.
- **Queue idempotency** — `request_uuid` on pageviews/events prevents duplicate rows when jobs are retried.
- **Token management** — `lwa:token` (`analytics:token`) lists, issues, rotates and revokes API tokens.
- **Event reports** — Events & Goals / Conversions pages list actually recorded events instead of placeholder numbers.
- **Documentation** — `ARCHITECTURE.md`, `UPGRADE.md` and this changelog.
- **Testing** — PHPUnit + Orchestra Testbench; CI matrix across PHP 8.2–8.5 with SQLite, MySQL 8 and PostgreSQL 16, plus PHP 8.1 syntax checks. Full suite verified locally on all three databases (SQLite, MySQL 8, PostgreSQL 14+).

### Changed

- Migrations are auto-loaded (not published) to avoid duplicate registration.
- Dashboard sections are separate routes/pages instead of hidden tabs.
- Live stream responses are short-lived with `retry:`/`Last-Event-ID`, avoiding long-held PHP workers.
- Settings actions (prune/empty/test-alert/website CRUD) are POST/DELETE with CSRF and confirmations.

### Fixed

- Numeric-column comparisons that could fail on PostgreSQL.
- Carbon sign handling in engagement calculations.
- Duplicate `class` attribute on the CSV export link.
- Command registration outside `runningInConsole()` so `Artisan::call()` works from HTTP contexts.
- Fake trend percentages and fabricated live-visitor counts removed.
- Schema migration now honours either `.env` or cached config, with debug output when tables are missing.
- Unmigrated installs no longer crash: dashboard shows a setup page, API returns `503`, SSE emits an `error` event, commands exit with guidance.

### Deprecated

- Direct dashboard reads from raw `lwa_logs` when the advanced schema is enabled — use rollups via `MetricService`.

### Upgrade

See [UPGRADE.md](UPGRADE.md). No breaking changes by default; the advanced schema is opt-in.
