# Upgrade guide — v2 request logs → advanced analytics schema

**Target:** v3.1.0 (reporting additions; `v3.0.0` remains the stable tag) · **Source:** any supported v2 release on PHP 8.2+ / Laravel 11+

The advanced analytics **collector and API are opt-in**. The v3 migrations are additive: they create their tables and add nullable `host` / `cache_status` columns to `lwa_logs` (backfilling `host` from `full_url`). They never rewrite or delete existing `lwa_logs` rows, and existing v2 dashboards keep working with no configuration changes.

## What's new in v3.1.0 (reporting additions)

- Technology page with a device-types-over-time chart and icon-decorated OS/browser/user-agent breakdowns.
- Breakdown detail modal (grid icon) with search, sorting, row limits and per-row actions, plus reusable card menus (Proportion/Trend, per-card export, pin-to-dashboard).
- Chart **Group by** across 11 dimensions, including new **Host** and **Cache status** columns populated by the middleware.
- Row-level "View in Observability" deep links into the Real-time feed.

No config keys were removed. After upgrading, run `php artisan migrate` (additive), then `php artisan optimize:clear` and re-publish views with `--force` if you had published them before.

---

## Before you start

0. **Run the preflight check:**

```bash
php artisan lwa:doctor          # human-readable table
php artisan lwa:doctor --json   # machine-readable report
```

If it reports missing tables (this happened on some installs where an older, guarded migration was recorded as "run" without creating anything), repair idempotently:

```bash
php artisan lwa:doctor --repair
php artisan lwa:doctor           # re-check until no FAIL remains
```

1. **Back up the database** — especially `lwa_logs` — and `.env`.
2. Note your `WEB_ANALYTICS_TABLE_NAME` (default `lwa_logs`).
3. If you plan to use `queue` collector mode, make sure a queue worker can reach the same DB/cache.
4. Run the whole flow on staging with a production copy first.

---

## 1. Update the package

```bash
composer require nafiur/laravel-web-analytics:^3.0 -W
# development branch alternative:
# composer require nafiur/laravel-web-analytics:dev-dev -W

php artisan optimize:clear
```

If you published views earlier, refresh them (otherwise you will keep seeing the old UI):

```bash
php artisan vendor:publish --tag=web-analytics-views --force
php artisan view:clear
```

---

## 2. Enable the collector and migrate

```env
WEB_ANALYTICS_SCHEMA_ENABLED=true
WEB_ANALYTICS_COLLECTOR_MODE=sync     # use 'queue' only with a running worker
WEB_ANALYTICS_WRITE_LEGACY=true       # keep v2 logging during transition
# WEB_ANALYTICS_WEBSITE_ID=1          # optional; upgrade can create one from --website-domain
```

```bash
php artisan optimize:clear
php artisan migrate
```

This creates, additively: `lwa_websites`, `lwa_visitors`, `lwa_sessions`, `lwa_pageviews`, `lwa_events`, `lwa_hourly_stats`, `lwa_daily_stats`, `lwa_upgrade_checkpoints`, `lwa_api_tokens`, plus `request_uuid` idempotency columns.

> `optimize:clear` is mandatory after any `.env`/config change. Cached config is the most common reason for "Nothing to migrate" and missing tables.

---

## 3. Dry run

```bash
php artisan lwa:upgrade --dry-run --website-domain=example.com
```

Understand the report:

| Column | Meaning |
|---|---|
| `read` | **Total** legacy rows scanned — not the number pending |
| `pending_since_id` | Rows the next real run will import |
| `checkpoint_last_id` | Current resumable position |
| `pageviews` | Always `0` in dry-run — nothing is written |

---

## 4. Backfill

```bash
php artisan lwa:upgrade --website-domain=example.com --chunk=2000
```

- Resumable via `lwa_upgrade_checkpoints`; re-run to import rows logged since the last run.
- Safe to interrupt — just re-run (it resumes from `last_id`).
- For very large tables, run it in the background so an SSH disconnect cannot stop it:

```bash
nohup php artisan lwa:upgrade --website-domain=example.com --chunk=2000 \
  > storage/logs/lwa-upgrade.log 2>&1 &
tail -f storage/logs/lwa-upgrade.log
```

- Visitors are reconstructed from `session_id` (or daily salted IP/UA hash).
- Sessions use the configured 30-minute inactivity timeout.
- Imported pageviews get a stable `request_uuid` (`legacy_<id>`), so re-runs cannot duplicate them.

Check progress any time:

```bash
php artisan lwa:doctor
# OK Upgrade checkpoint  last_id=..., N legacy row(s) pending
```

---

## 5. Build rollups

The upgrade command automatically aggregates the **last 2 days hourly** and **last 90 days daily**. For older history, run explicitly:

```bash
php artisan lwa:aggregate --granularity=daily --from=2025-01-01 --to=2026-11-23
php artisan lwa:aggregate --granularity=hourly --from=2026-11-20 --to=2026-11-23
```

Schedule ongoing aggregation in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('lwa:aggregate --granularity=hourly')->hourly();
Schedule::command('lwa:aggregate --granularity=daily')->daily();
Schedule::command('lwa:prune')->daily();
Schedule::command('lwa:upgrade --website-domain=example.com')->hourly(); // import late-arriving legacy rows
Schedule::command('lwa:geoip --auto')->weekly();                          // if using MaxMind
```

Dashboard KPIs then come from rollup tables, not raw scans.

---

## 6. Verify

```bash
php artisan lwa:doctor
php artisan lwa:benchmark --range=24h
```

Check the Overview, Visitors, Geography and Real-time pages. With MaxMind configured, country/city markers are precise; without it, country-centroid fallback still plots approximate locations.

---

## 7. Optional — retire legacy data

Only after verification:

```bash
php artisan lwa:upgrade --prune-legacy --website-domain=example.com
```

Deletes only `lwa_logs` rows already imported (tracked by the checkpoint). Keep `WEB_ANALYTICS_WRITE_LEGACY=true` until comfortable, then set it `false`.

---

## Configuration reference

| Setting | Default | Purpose |
|---|---|---|
| `WEB_ANALYTICS_SCHEMA_ENABLED` | `false` | Enables collector + API (tables always exist) |
| `WEB_ANALYTICS_COLLECTOR_MODE` | `sync` | `sync` or `queue` |
| `WEB_ANALYTICS_WRITE_LEGACY` | `true` | Keep writing `lwa_logs` |
| `WEB_ANALYTICS_QUEUE` | — | Optional named queue |
| `WEB_ANALYTICS_WEBSITE_ID` | — | Default website scope |
| `WEB_ANALYTICS_SESSION_TIMEOUT` | `30` | Session inactivity minutes |
| `WEB_ANALYTICS_BOUNCE_MAX_SECONDS` | `0` | Bounce duration threshold |
| `WEB_ANALYTICS_API_RATE_LIMIT` | `60` | API requests/minute |
| `WEB_ANALYTICS_GEOIP_PROVIDER` | — | `''`, `ip-api`, `maxmind` |
| `WEB_ANALYTICS_MAXMIND_DB` | — | Local `.mmdb` path |
| `MAXMIND_LICENSE_KEY` / `MAXMIND_ACCOUNT_ID` | — | Secrets — keep in `.env` only |
| `WEB_ANALYTICS_GEOIP_AUTO_DOWNLOAD` | `false` | Scheduled DB refresh |
| `WEB_ANALYTICS_GEOIP_UPDATE_DAYS` | `7` | Refresh interval |

Non-secret values can also be set in published `config/laravel-web-analytics.php`; keep secrets in `.env` and run `optimize:clear` after any change.

---

## Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `Nothing to migrate`, advanced tables missing | Older guarded migration was recorded as run → `php artisan lwa:doctor --repair` |
| Dashboard shows setup page / API 503 / SSE `error` event | Required tables missing → doctor + `--repair`, then `optimize:clear` |
| `lwa:upgrade --dry-run` shows huge `read` but tiny `pending` | Normal — `read` is the total row count; only `pending_since_id` will be imported |
| Upgrade interrupted (`^C`) | Nothing lost — re-run; checkpoint resumes from `last_id` |
| Pending rows never reach zero | Tracking is still active in v2 → normal; schedule `lwa:upgrade` hourly or accept a small tail |
| `Reconnecting…` flashes on Real-time | Fixed in v3 — a single normal SSE EOF no longer flips the badge |
| Map shows country codes as `BD BD` | Old published views — re-publish with `--force` and `view:clear` |
| City list empty | City data requires GeoIP; configure MaxMind or Cloudflare headers |
| "Host"/"Cache status" group-by is empty | Run `php artisan migrate` — the additive migration adds the columns. Cache status only appears on requests logged after the upgrade (from `cf-cache-status` / `x-cache` headers) |
| New card menu/modal not visible | Old published views are still in use → `php artisan vendor:publish --tag=web-analytics-views --force` and `php artisan view:clear` |
| Exposed MaxMind license key | Revoke it in the MaxMind portal immediately, then use `.env` only |

---

## Rollback

1. Set `WEB_ANALYTICS_SCHEMA_ENABLED=false` and `WEB_ANALYTICS_WRITE_LEGACY=true`.
2. `php artisan optimize:clear`.
3. Re-publish/restore v2 views if you customized them.
4. Remove advanced tables: `php artisan migrate:rollback --step=3` (three v3 migrations: host/cache-status columns, idempotency keys, table creation). Verify with `php artisan lwa:doctor` afterwards.
5. Restore the DB backup if you ran `--prune-legacy`.

No v2 config keys were removed, so a config rollback is not strictly required.

## Known limitations

- Events/goals/conversions report only events actually recorded; no historical events are inferred from `lwa_logs`.
- Bounce and engagement for imported sessions are approximations from legacy `session_id` and timestamps.
- Multi-website isolation is session/config based; API tokens are bound to one website at issue time.
- `queue` collector mode requires a worker; without one use `sync`.
