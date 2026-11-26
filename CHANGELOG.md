# Changelog

All notable changes to this project are documented here. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [3.4.0] - 2026-11-26

This release consolidates the previously planned v3.4 replay/heatmaps, v3.5 flags/experiments and v3.6 observability increments, plus the first PostHog-inspired UI parity items. Everything remains MIT, self-hosted, zero-build and free of paid tiers or activation servers.

### Added

- **Feature flags foundation (v3.5, opt-in)** — new `/web-analytics/flags` CRUD page and `GET /web-analytics/api/v1/flags/evaluate` endpoint backed by website/tenant-scoped `lwa_feature_flags` / `lwa_flag_overrides` tables. Boolean and multivariate flags support deterministic `sha256(key:subject) % 100` rollout buckets, per-subject overrides and AND-combined targeting rules. `@feature('key') ... @endfeature` and `@featureVariant('key')` gate host markup; each distinct key+subject evaluation is logged to `lwa_events` as `feature_flag_evaluated` with a hashed subject for honest exposure analysis.
- **A/B experiments (v3.5)** — new `/web-analytics/experiments` CRUD page links a flag to a recorded goal event. `ExperimentService` reads exposures from `feature_flag_evaluated` and conversions from the goal event's variant property in `lwa_events`, reporting per-variant counts, rates, lift versus control, a two-proportion z-test p-value and significance only with at least 30 exposures and 5 conversions in both groups. Every read is bounded to 20,000 events and all definitions ship with the page.
- **Error tracking foundation (v3.6, opt-in)** — `POST /web-analytics/api/v1/observability/errors` accepts project-key-authenticated error reports with bounded, sanitised stack traces and context. `ErrorTrackingService` fingerprints and groups exceptions into website/tenant-scoped `lwa_error_groups` / `lwa_error_events`, de-duplicates by event id, reopens resolved groups on recurrence and mirrors a safe summary into `lwa_events` as `error`. Gated `/web-analytics/observability` list/detail pages allow open/resolved/ignored status changes; performance spans and tracing remain open.
- **Web Vitals report (PostHog-inspired P1)** — new opt-in `/web-analytics/web-vitals` page computes p50/p75/max and good/needs-work/poor buckets for LCP, CLS and INP from bounded `web_vital` events in `lwa_events`, with path filtering and published thresholds. The browser SDK now reports INP alongside LCP/CLS and attaches the path to each web vital event; nothing is inferred from server timings.
- **Installation Health UI (PostHog-inspired P1)** — opt-in `/web-analytics/health` page renders the same isolated checks as `lwa:doctor` with pass/warn/fail summary counts and the recommended next step. It is read-only and never mutates configuration or data.
- **Compare to previous period (PostHog-inspired P1)** — a `Compare prior` control adds KPI deltas against the immediately preceding window of equal length. Count metrics use relative percent change, rate metrics use percentage points, and new/zero-base cases are labelled instead of dividing by zero. The same deltas are available from `/web-analytics/data` for live refreshes.
- **Page reports (PostHog-inspired P1)** — new opt-in `/web-analytics/page-reports` page aggregates bounded `lwa_logs` rows into per-path visitors, views, bounce, entry/exit rates and top next/previous paths. Selecting a path shows explicit definitions, a 20,000-row cap note and optional previous-period deltas.
- **Per-path recording links (PostHog-inspired P2)** — top-path breakdown rows and Page Reports now link to `/web-analytics/replay?path=...`. The replay index filters sessions by that recorded path and shows a clearable filter chip.
- **Richer replay timeline (PostHog-inspired P2)** — the recorder now emits console-error, failed-request (status ≥ 400 with query stripped), rage-click (3 clicks on one target within 1 second) and CLS web-vital markers, with sensitive `key=value` pairs redacted. The player adds 0.5x/1x/2x speed control and an Inspect DOM panel showing the current sanitized snapshot source.
- **Filter test accounts (PostHog-inspired P2)** — opt-in `Filter test accounts` toggle excludes configured internal user keys, IPs and session ids from request-log dashboards and every `EventStore`-backed report. User keys match raw, `u_<id>` and sha256 visitor hashes; identifiers come only from configuration, never request input.
- **Integrated error card (PostHog-inspired P3)** — the Overview page now shows the top five open exception groups with occurrence counts and an Observability link when error tracking is enabled and its gate allows it. Stack traces never leave the gated Observability pages.
- **Vendored browser libraries** — prebuilt `rrweb` 2.2.2 (MIT) and `web-vitals` 6.2.3 (Apache-2.0) assets ship with the package, with their license files, and are served from `/web-analytics/vendor/*`. Host apps still need no npm/build step. The SDK now uses the vendored `web-vitals` library for LCP/CLS/INP/FCP/TTFB when enabled, with a PerformanceObserver fallback.

- **Session replay foundation (v3.4, opt-in)** — new `@lwaReplay` Blade directive, build-free `/web-analytics/replay.js` recorder and `lwa:replay:prune` retention command. Recorder consent is separate from analytics consent, guests are not recorded by default, every input value and `[data-lwa-mask]` node is removed before upload, framework state attributes are stripped, and path selectors can exclude sensitive pages. Uploads use an expiring HMAC-signed config token, are stored verbatim on a Laravel disk, and are indexed in website/tenant-scoped `lwa_replay_sessions` / `chunks` / `markers` / `assets` tables. The `/web-analytics/replay` list is gated by a configurable `viewLwaReplay` gate.
- **Minimal replay playback (v3.4)** — `/web-analytics/replay/{session}` plus gated manifest and chunk routes stream stored chunks on demand. The deferred, build-free player renders sanitized snapshots in a script-disabled sandbox, steps through chunks with a timeline and lists bounded markers. Every playback and asset request re-checks the configured gate and the session's website/tenant scope; no stored DOM is parsed or enriched on the server.
- **Heatmaps & interaction signals (v3.4)** — new gated `/web-analytics/heatmaps` page aggregates click and scroll markers from `lwa_replay_markers` with path and time-window filters. The recorder now captures viewport dimensions with each marker so clicks can be bucketed to a 2.5% grid and scrolls to 10% depth bands. Top targets, rage clicks (3 clicks on one target within 1 second) and dead clicks (no different marker within 2 seconds) are computed in PHP from at most 20,000 most recent scoped markers, with definitions and cap notes shipped on the page.
- **Form analytics (v3.4)** — masked field focus/change, form submit and validation-error markers are recorded without any field values or validation messages. A gated `/web-analytics/forms` page aggregates distinct sessions per form into starts, submits, errors, invalid submits and conversion/error rates, plus per-field focus/change counts and change rates. Bounded to 20,000 most recent scoped markers with published definitions. Full-fidelity DOM patching remains open.

## [3.3.0] - 2026-11-25

### Added

- **Trends / Insights builder (v3.3)** — new `/web-analytics/trends` page and `GET /web-analytics/api/v1/insights/trend` endpoint backed by one documented `InsightQuery` → `InsightService` → `EventStore` path. Metrics: event count, distinct users (anonymous ids folded into profiles) and summed event value, with hourly/daily/weekly/monthly buckets, property filters and up-to-six-series breakdowns. Queries are bounded at 20,000 events / 1,000 buckets; long hourly windows auto-coarsen, and every response ships its metric definitions, source and sample-size notes.
- **Saved insight reports (v3.3)** — DB-backed `lwa_insights` table (website/tenant scoped) with a Save/Delete UI on the Trends page and read/admin REST CRUD at `/web-analytics/api/v1/insights`. Reports are shared through the database, never stored in localStorage.
- **Funnel analysis (v3.3)** — new `/web-analytics/funnels` page and `GET /web-analytics/api/v1/insights/funnel` endpoint for ordered 2–10 step funnels with 1h/24h/7d/30d/no-limit conversion windows, property filters and aliased user identity. Each user is counted at most once per step (furthest progress), one completed sequence is credited, and the response documents rates, drop-off, median completion time, source and sample cap. Saved funnels reuse the DB-backed `lwa_insights` table and are separated from saved trends by `type`.
- **Retention, cohorts & lifecycle (v3.3)** — new `/web-analytics/retention` page and `GET /web-analytics/api/v1/insights/retention` endpoint for daily/weekly cohorts with 2–12 periods. A cohort is the bucket of a user's first matching event; period N is the Nth full bucket after it, with each user counted once per period. Unelapsed periods are `null` (shown as —), never zero, and period-1 retention is weighted by cohort size. Lifecycle buckets classify users as New / Returning / Resurrecting / Dormant from the same bounded `EventStore` read, excluding the in-progress bucket. Aliased anonymous ids share a profile, the 20,000-event cap is disclosed, and saved reports use `lwa_insights` with `type: retention`.
- **User paths (v3.3)** — new `/web-analytics/paths` page and `GET /web-analytics/api/v1/insights/paths` endpoint for the most common per-user event sequences (2–8 steps, top 5–50 paths). Each user contributes one first qualifying path, consecutive repeats collapse into one step, and optional start/end events and property filters bound the sequence. The same bounded read powers top paths with shares and per-position event distributions; single-step and start-excluded users are accounted for separately. Aliased identities are canonical, the 20,000-event cap is disclosed, and saved reports use `lwa_insights` with `type: paths`.
- **Insight widgets on custom dashboards (v3.3)** — saved `lwa_insights` reports can now be added from the dashboard editor alongside the existing KPI/chart/breakdown catalog. Results are rendered server-side by the same Trends/Funnel/Retention/Paths engines, scoped to the dashboard's website and tenant, and always use the dashboard's selected time window. At most 12 insight widgets are computed per page; each shows its headline, top rows, definition, source (`lwa_events`) and sample size. Legacy layouts get their widget type/key/suffix resolved from the catalog, deleted or foreign reports degrade to explicit messages, and all layout writes stay in the database.

### Changed

- `EventQuery` now applies `from` and `to` independently; open-ended windows such as "last 7 days" are actually time-bounded, and multi-name `names` queries are supported for funnel steps. The Event Explorer computes KPIs from the full matching set (capped at 20,000 scanned events) while rendering the 200 most recent rows, and shares one `AnalyticsWindow` resolver with Trends, Funnels, Retention and User Paths.

## [3.2.0] - 2026-11-25

### Added

- **Unified event schema (v3.2 foundation)** — new `UnifiedEvent` value object and `UnifiedEventMapper` translate tracker events (PHP facade, JS SDK, REST, importer) onto a single envelope (`uuid`, `name`, `timestamp`, `distinct_id`, `anonymous_id`, `session_id`, `website_id`, `properties`, `context`, `value`, `created_at`). The collector now flows events through the mapper before persisting; property sanitisation and the 100-char event-name cap moved into the mapper. Tracker context (path, referrer, user agent, UTM) is carried on `EventData`. No breaking changes — legacy storage payloads keep working as the compatibility layer.
- **EventStore abstraction (v3.2)** — `EventStore` contract plus a relational `EloquentEventStore` implementation (writes via the existing collector storage driver, queries/aggregates/prunes through the `lwa_events` model). Binding registered in the service provider; container now resolves `Nafiur\WebAnalytics\Contracts\EventStore`.
- **Batch ingestion (v3.2)** — `POST /web-analytics/api/v1/events/batch` accepts up to 500 events, validates every event before writing, sanitises identity/context fields and deduplicates on `uuid` so retries never create duplicate rows. The endpoint accepts either a write-capable API token or a public project key (the website's `tracking_id`) via `X-LWA-Project-Key`, and stays behind the existing rate limiter. A queued `ProcessEvent` job is available for queue-backed installs, with three backoff attempts and a `lwa_dead_letters` table that preserves exhausted batches instead of dropping them.
- **JavaScript SDK (v3.2)** — dependency-free `sdk.js` served from `/web-analytics/sdk.js` (opt-in via `laravel-web-analytics.sdk.enabled`, with config defaults injected and `init()` overrides supported). Exposes `init`, `track`, `identify`, `group`, `pageview`, `flush`, `optOut` and `optIn`, with consent-aware defaults, banned-key/property sanitisation, byte-capped batching, `sendBeacon` + `fetch` delivery, cookieless anonymous/session ids, click/submit autocapture via `data-lwa-track` and opt-in Core Web Vitals (`lcp`, `cls`). Public key support means no secret token ever ships to the browser.
- **Event Explorer (v3.2)** — bookmarkable `/web-analytics/explorer` page with filters for event name, property key/value, user/visitor (including aliased anonymous ids) and time window. Results are read from the EventStore with visitor identity and value totals, and the stylesheet now includes token-driven form controls (`lwa-input`, `lwa-select`, `lwa-field-label`).
- **User identity & profiles (v3.2)** — `identify` events alias the current anonymous id to a distinct id, merge profile traits (new values win) and expose a combined pageview/event timeline that includes activity recorded before sign-in. New `lwa_profiles` / `lwa_identity_aliases` tables, `IdentityService`, and read-only `GET /users` + `GET /users/{distinctId}` API endpoints. The browser SDK now sends the anonymous id on `identify` so history can be merged.
- **Custom dashboards** — DB-backed `lwa_dashboards` table (website/tenant scoped) plus a Dashboards page with a create modal (blank or preset templates) and an editor that picks widgets from the existing catalog (KPIs, charts, breakdowns, visitors map, recent logs). Widgets can be added, removed, reordered and resized. Full CRUD is also exposed through the token-authenticated REST API with `read` / `admin` abilities.

### Changed

- Logs page "Group by" now opens with **None** (a single total-requests line). Select **Cache status (all)** to see all delivery outcomes together.
- Time-series charts use smooth curves, hover markers and a shared card-style tooltip showing the bucket label, series colour/name and formatted value.
- Precision-focused UI redesign inspired by developer tools: the layout stylesheet was rebuilt as a small design system with warm-neutral light / deep-neutral dark tokens, flat 1px-border cards with a single soft elevation, grouped sidebar navigation, a segmented subheader, compact KPI tiles with icon chips, solid accent buttons, progress-bar breakdown rows, a blur-backed sticky shell, token-driven chart palettes/grids, and a utility bridge that keeps older view markup on the same palette. No routes, metrics or features changed.
- Chart and table polish: dense time buckets now thin their axis labels to evenly spaced ticks, the visitors map highlights countries with a soft accent tint, and status-code badges share one success / redirect / warning / error palette across Logs, Real-time and Error Monitoring. Heading typography was normalised to the design system (`lwa-card-title`, `lwa-kpi-value`) so cards, modals and settings sections no longer mix raw scale utilities.

## [3.1.0] - 2026-11-24

### Added

- **Technology report** — Devices & Browsers page now shows a stacked "Device types over time" chart, Top operating systems, Top browsers and Top user agents, plus a card menu with CSV/JSON export. Device categories are normalised through `DeviceCategory` so both database and file-log sources produce identical series.
- **Technology icons** — OS/browser/device breakdowns render inline SVG marks (no external requests) through `TechnologyIcon`; misleading country flags no longer appear on non-location breakdowns.
- **Card menus** — reusable `card-menu` partial with expand, **View as** (Proportion/Trend), per-card CSV/JSON export, "View in Observability" and pin-to-dashboard. Trend series are exposed as `analytics.breakdown_trends` for both database and file-log sources and drawn as inline sparklines. View/expand/pin preferences persist in localStorage; overview renders pinned report slots.
- **Breakdown detail modal** — the grid icon opens a full-table detail view with search, Proportion/Value/Name sorting, 10/25/50 row limits, select-all checkboxes and per-row actions. Works on every breakdown list, not just Technology cards.
- **Chart Group by** — time-series charts can be broken down by country, path, status code, browser, OS, device type, method, referrer, data center (city), host or cache status. `breakdown_trends` now covers all of these dimensions in both data sources. ApexCharts initialisation is guarded so a blocked CDN cannot stop the rest of the dashboard JS.
- **Host & cache status** — additive migration adds `host` and `cache_status` to `lwa_logs` (hosts backfilled from `full_url`). The middleware records the request host and normalises `cf-cache-status` / `x-cache` headers. Row-level "View in Observability" links carry a `?filter=` deep link into the Real-time feed, which pre-fills the quick filter.
- **Live report updates** — subheader auto-refresh (Off/5s/10s/30s, persisted) polls a compact `?light=1` data endpoint, and the SSE stream pushes an `analytics` event on new traffic. Breakdowns (including pinned reports), sparklines, device legend, KPIs and chart series update in place; the manual Refresh button uses the same path.
- **Logs page** — new `lwa_logs` route/sidebar item with an outcomes-over-time chart, filters (method, status class/code, cache status, host, path, IP, free-text), paginated table, expandable rows with full request detail and filtered CSV export. Edge status, security decisions and bot scores are intentionally out of scope. The chart defaults to "Cache status (all)"; "None" now consistently shows a single total-requests series.
- **Log field sidebar** — collapsible field groups (Request / Client / Response / Other fields) beside the log table; clicking a field adds a removable filter chip. Added server-side support for country, city, user agent, referrer, full URL, UTM, session/user and tenant filters.
- **Documentation** — README, UPGRADE and ARCHITECTURE updated for the reporting additions, host/cache-status migration and observability deep links. README badges now use Markdown image syntax (with an absolute banner URL) so they render on both GitHub and Packagist.

### Fixed

- Logs page with the File data source no longer throws `Collection::hasPages()`; an empty paginator and an explanatory notice are rendered instead.

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
