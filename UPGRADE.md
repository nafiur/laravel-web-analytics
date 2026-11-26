# Upgrade guide — v2 / v3 → v3.3.0 (plus the v3.4 preview)

**Released target:** `v3.3.0` (tag `v3.3.0`, 2026-11-25). **Preview target:** `dev` for the unreleased v3.4 replay work. **Supported:** PHP `^8.2 || ^8.3 || ^8.4 || ^8.5`, Laravel `^11 || ^12 || ^13`.

All v3 migrations are additive: they create their own tables and add nullable `host` / `cache_status` columns to `lwa_logs` (backfilling `host` from `full_url`). No legacy `lwa_logs` row is rewritten or deleted. Existing v2 dashboards keep working without configuration changes.

> **Important:** Packagist treats stable versions as immutable. Never delete or re-point a released tag. If a release needs a fix, publish the next version (`v3.3.1`, `v3.3.0.1`, …) instead.

---

## Release summary

### v3.2.0 (released)

Unified event schema, `EventStore` abstraction, browser SDK, identity resolution/profiles, Event Explorer and batch ingestion with retry/dead-letter support.

### v3.3.0 (released)

- Trends / Insights builder with event, user and value metrics, breakdowns and documented definitions.
- DB-backed saved reports in `lwa_insights` (no localStorage); REST CRUD with `read` / `admin` abilities.
- Ordered funnels with conversion windows, drop-off and median completion time.
- Cohort retention, lifecycle stages and per-user event paths.
- Insight widgets on custom dashboards, scoped to website/tenant and rendered by the same bounded engines.
- Sidebar has **20** links by default (Funnels, Retention, User Paths, Trends and Event Explorer were added).

### v3.4 (unreleased on `dev`)

- Opt-in, consent-gated session replay with masked recording, signed uploads, verbatim chunk storage and a minimal gated player.
- Gated click/scroll heatmaps with bounded aggregation, top targets and rage/dead-click signals.
- Gated form analytics with masked field focus/change, submit and validation-error markers; no field values are recorded.
- Optional Session Replay and Heatmaps sidebar links (22 links when enabled and the gate allows).

v3.4 is preview quality: full-fidelity DOM patching is not implemented.

### v3.5 (unreleased on `dev`)

- Opt-in feature flags at `/web-analytics/flags` with boolean/multivariate variants, deterministic rollout, per-subject overrides and targeting rules.
- `@feature('key') ... @endfeature`, `@featureVariant('key')` and `GET /api/v1/flags/evaluate` (read token).
- Evaluations are logged to `lwa_events` as `feature_flag_evaluated` with a hashed subject.
- A/B experiments link a flag to a recorded goal event and report per-variant rates, lift, p-values and significance gates from `lwa_events` only.

---

## 1. Update the package

```bash
# Released v3.3
composer require nafiur/laravel-web-analytics:^3.3 -W

# Preview the unreleased v3.4 work instead
# composer require nafiur/laravel-web-analytics:dev-dev -W

php artisan optimize:clear
```

If you published views earlier, refresh them — otherwise you will keep seeing the old UI:

```bash
php artisan vendor:publish --tag=web-analytics-views --force
php artisan view:clear
```

Config may also be refreshed selectively:

```bash
php artisan vendor:publish --tag=laravel-web-analytics-config
```

---

## 2. Migrate

```bash
php artisan migrate
php artisan lwa:doctor
php artisan lwa:doctor --json   # CI-friendly
```

If `lwa:doctor` reports missing tables because an older guarded migration was recorded as run without creating them:

```bash
php artisan lwa:doctor --repair
php artisan lwa:doctor
```

Additive tables now include:

- Core reporting: `lwa_websites`, `lwa_visitors`, `lwa_sessions`, `lwa_pageviews`, `lwa_events`, `lwa_hourly_stats`, `lwa_daily_stats`, `lwa_upgrade_checkpoints`, `lwa_api_tokens`.
- Identity / ingestion: `lwa_profiles`, `lwa_identity_aliases`, `lwa_dead_letters`.
- Product analytics: `lwa_insights`, `lwa_dashboards`.
- Replay (v3.4 preview): `lwa_replay_sessions`, `lwa_replay_chunks`, `lwa_replay_markers`, `lwa_replay_assets`.

`request_uuid` idempotency columns are added to `lwa_pageviews` and `lwa_events`.

---

## 3. Enable the collector (optional)

The collector and API remain opt-in:

```env
WEB_ANALYTICS_SCHEMA_ENABLED=true
WEB_ANALYTICS_COLLECTOR_MODE=sync     # use 'queue' only with a running worker
WEB_ANALYTICS_WRITE_LEGACY=true       # keep writing lwa_logs during transition
# WEB_ANALYTICS_WEBSITE_ID=1          # optional; upgrade can create one from --website-domain
```

```bash
php artisan optimize:clear
```

---

## 4. Backfill legacy `lwa_logs` rows (v2 upgrades)

```bash
php artisan lwa:upgrade --dry-run --website-domain=example.com
php artisan lwa:upgrade --website-domain=example.com --chunk=2000
```

| Column / behaviour | Meaning |
|---|---|
| `read` | Total legacy rows scanned, not the pending count |
| `pending_since_id` | Rows the next run will import |
| `checkpoint_last_id` | Current resumable position |
| `pageviews` | Always `0` in dry-run |

The command is resumable via `lwa_upgrade_checkpoints`; re-run it to import rows logged since the last run. Imported rows use `legacy_<id>` request UUIDs so retries cannot duplicate them.

For large tables, run it detached:

```bash
nohup php artisan lwa:upgrade --website-domain=example.com --chunk=2000 \
  > storage/logs/lwa-upgrade.log 2>&1 &
tail -f storage/logs/lwa-upgrade.log
```

---

## 5. Build rollups and schedule maintenance

The upgrade command aggregates the last 2 days hourly and 90 days daily. For older history:

```bash
php artisan lwa:aggregate --granularity=daily --from=2025-01-01 --to=2026-11-25
php artisan lwa:aggregate --granularity=hourly --from=2026-11-23 --to=2026-11-25
```

Suggested `routes/console.php` schedule:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('lwa:aggregate --granularity=hourly')->hourly();
Schedule::command('lwa:aggregate --granularity=daily')->daily();
Schedule::command('lwa:prune')->daily();
Schedule::command('lwa:replay:prune')->daily(); // v3.4 preview, replay retention
Schedule::command('lwa:upgrade --website-domain=example.com')->hourly();
Schedule::command('lwa:geoip --auto')->weekly();
```

Dashboard KPIs then come from rollup tables rather than raw scans.

---

## 6. Notes for v3.3 users

- Saved reports are database-backed. Anything previously pinned with the card menu stays browser-local and is separate from Trends/Funnel/Retention/Paths reports and dashboard insight widgets.
- Insight widgets are stored in `lwa_dashboards`; the dashboard time window overrides the window saved with a report.
- Funnel, retention and path metrics read only from `lwa_events` through `EventStore`, with published definitions and a 20,000-event cap.
- Published views must be refreshed after upgrading to see the new pages and sidebar links.

---

## 7. Enable v3.4 session replay (preview)

Replay is disabled by default and must be consented to. Add to `.env`:

```env
WEB_ANALYTICS_REPLAY_ENABLED=true
WEB_ANALYTICS_REPLAY_CONSENT=false        # set true only after user consent
WEB_ANALYTICS_REPLAY_SAMPLE_RATE=1         # 0–1 fraction of sessions
WEB_ANALYTICS_REPLAY_RECORD_GUESTS=false
WEB_ANALYTICS_REPLAY_RETENTION_DAYS=30
WEB_ANALYTICS_REPLAY_DISK=                 # blank = default filesystem disk
```

Define the viewing gate in your application (the package intentionally does not define it for you):

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewLwaReplay', fn ($user = null) => $user !== null);
```

Without that gate, replay and heatmap routes fail closed with `403`.

Opt into recording in a layout:

```blade
@if (config('laravel-web-analytics.replay.enabled'))
    @lwaReplay
@endif
```

Set consent at runtime only after your consent UI grants it:

```php
config()->set('laravel-web-analytics.replay.consent.replay', true);
```

The recorder removes every input value, empties `[data-lwa-mask]` nodes, strips framework state attributes such as `wire:snapshot`, and uploads masked structural markers with an expiring HMAC-signed token. Chunks are stored verbatim; the server never parses replay payloads.

Run `php artisan lwa:replay:prune` daily to enforce retention.

---

## 8. Verify

```bash
php artisan lwa:doctor
php artisan lwa:benchmark --range=24h
```

Check Overview, Visitors, Geography, Real-time, Trends, Funnels, Retention, Paths and Dashboards. With replay enabled and the gate defined, also check `/web-analytics/replay`, `/web-analytics/heatmaps` and `/web-analytics/forms`.

---

## 9. Optional — retire legacy data

Only after verification:

```bash
php artisan lwa:upgrade --prune-legacy --website-domain=example.com
```

This deletes only `lwa_logs` rows already imported (tracked by the checkpoint). Keep `WEB_ANALYTICS_WRITE_LEGACY=true` until comfortable, then set it to `false`.

---

## Configuration reference

| Setting | Default | Purpose |
|---|---|---|
| `WEB_ANALYTICS_SCHEMA_ENABLED` | `false` | Enables collector + API |
| `WEB_ANALYTICS_COLLECTOR_MODE` | `sync` | `sync` or `queue` |
| `WEB_ANALYTICS_WRITE_LEGACY` | `true` | Keep writing `lwa_logs` |
| `WEB_ANALYTICS_WEBSITE_ID` | — | Default website scope |
| `WEB_ANALYTICS_SESSION_TIMEOUT` | `30` | Session inactivity minutes |
| `WEB_ANALYTICS_API_RATE_LIMIT` | `60` | API requests/minute |
| `WEB_ANALYTICS_GEOIP_PROVIDER` | — | `''`, `ip-api`, `maxmind` |
| `WEB_ANALYTICS_REPLAY_ENABLED` | `false` | Enable replay routes and recorder bundle |
| `WEB_ANALYTICS_REPLAY_GATE` | `viewLwaReplay` | Gate required for replay/heatmap pages |
| `WEB_ANALYTICS_REPLAY_CONSENT` | `false` | Replay consent default |
| `WEB_ANALYTICS_REPLAY_SAMPLE_RATE` | `0` | Fraction of sessions recorded |
| `WEB_ANALYTICS_REPLAY_RECORD_GUESTS` | `false` | Allow guest recording |
| `WEB_ANALYTICS_REPLAY_RETENTION_DAYS` | `30` | Replay prune window |
| `WEB_ANALYTICS_REPLAY_DISK` | — | Laravel disk for replay chunks |
| `WEB_ANALYTICS_FLAGS_ENABLED` | `false` | Enable feature flag UI and evaluation endpoint |
| `WEB_ANALYTICS_FLAGS_ENDPOINT` | `/web-analytics/api/v1/flags/evaluate` | Evaluation route prefix |

Keep secrets in `.env` and run `php artisan optimize:clear` after any config change.

---

## Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `Nothing to migrate`, advanced tables missing | Older guarded migration recorded as run → `php artisan lwa:doctor --repair` |
| Dashboard setup page / API 503 / SSE `error` | Required tables missing → doctor + `--repair`, then `optimize:clear` |
| New pages or sidebar links not visible | Published views are stale → `vendor:publish --tag=web-analytics-views --force` and `view:clear` |
| Trends/Funnels/Retention/Paths empty | Advanced schema disabled or no matching `lwa_events` |
| `403` on `/web-analytics/replay` or `/web-analytics/heatmaps` | Define the gate named by `WEB_ANALYTICS_REPLAY_GATE`; replay fails closed |
| `/web-analytics/replay.js` returns 404 | `WEB_ANALYTICS_REPLAY_ENABLED=false` |
| Replay upload returns 401 | Signed config token missing/expired; reload the host page to get a fresh one |
| Heatmaps empty | No recorded click/scroll markers yet, or path/window filter too narrow |
| Forms report empty | No focus/change/submit markers yet; ensure replay consent was granted on a page containing a form |
| `/web-analytics/flags` returns 404 | `WEB_ANALYTICS_FLAGS_ENABLED=false` |
| Flag evaluates as `missing` | Key not found in the active website/tenant scope, or flags are disabled |
| Flag evaluation returns `rollout` | Deterministic bucket is outside the configured rollout percentage |
| Packagist “Upstream re-tag blocked” | A released tag was moved; restore the original reference or publish a new version |

---

## Rollback

1. Set `WEB_ANALYTICS_SCHEMA_ENABLED=false` and `WEB_ANALYTICS_WRITE_LEGACY=true`.
2. Disable replay with `WEB_ANALYTICS_REPLAY_ENABLED=false`.
3. `php artisan optimize:clear`.
4. Re-publish/restore older views if you customized them.
5. Remove replay tables only (v3.4 preview): `php artisan migrate:rollback --step=1`.
6. Remove all advanced tables: roll back the remaining v3 migrations. Verify with `php artisan lwa:doctor`.
7. Restore the DB backup if you ran `--prune-legacy`.

No v2 config keys were removed, so a config rollback is not strictly required.

---

## Known limitations

- Events/goals/conversions report only events actually recorded; no historical events are inferred from `lwa_logs`.
- Bounce and engagement for imported sessions are approximations from legacy `session_id` and timestamps.
- Multi-website isolation is session/config based; API tokens are bound to one website at issue time.
- `queue` collector mode requires a worker; without one use `sync`.
- v3.4 replay is minimal: sanitized snapshots, bounded markers, dashboard-style heatmaps and form funnel counts, not full-fidelity DOM patching. Only structural form metadata is recorded — never field values.
- v3.5 experiments require the goal event to carry the configured variant property; no conversion is inferred from navigation, and significance is only reported with sufficient samples.
