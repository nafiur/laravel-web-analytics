# Upgrade guide — v2 request logs → advanced analytics schema

The advanced analytics schema is **opt-in**. Existing v2 installations keep working unchanged unless you set `WEB_ANALYTICS_SCHEMA_ENABLED=true`.

## Before you start

0. **Run the preflight check** — it prints an installer-style report and tells you exactly what is missing:

```bash
php artisan lwa:doctor          # human-readable table
php artisan lwa:doctor --json   # machine-readable report
```

Checks include PHP/Laravel versions, extensions, database connection, legacy and advanced tables, config cache, schema flag, queue/cache drivers, storage permissions, published views and pending upgrade rows. Fix every `FAIL` before continuing.

1. **Back up the database** (especially `lwa_logs`) and `.env`.
2. Note your current `WEB_ANALYTICS_TABLE_NAME` (default `lwa_logs`).
3. Make sure the queue worker (if you plan to use `queue` mode) can reach the same database/cache.
4. Run the upgrade on staging first with a copy of production data.

## 1. Update the package

```bash
composer require nafiur/laravel-web-analytics:dev-dev -W   # or your tagged target version
php artisan view:clear
```

If you published views, refresh them:

```bash
php artisan vendor:publish --tag=web-analytics-views --force
```

## 2. Enable and migrate the new schema

```env
WEB_ANALYTICS_SCHEMA_ENABLED=true
WEB_ANALYTICS_WEBSITE_ID=1          # optional; the upgrade command can create one
WEB_ANALYTICS_COLLECTOR_MODE=sync   # use 'queue' only when a worker is available
WEB_ANALYTICS_WRITE_LEGACY=true     # keep v2 logging during the transition
```

```bash
php artisan optimize:clear
php artisan migrate
```

This creates (additively, never touching `lwa_logs`):

`lwa_websites`, `lwa_visitors`, `lwa_sessions`, `lwa_pageviews`, `lwa_events`,
`lwa_hourly_stats`, `lwa_daily_stats`, `lwa_upgrade_checkpoints`, `lwa_api_tokens`.

## 3. Dry run

```bash
php artisan lwa:upgrade --dry-run --website-domain=example.com
```

Review the reported row count. Nothing is written in dry-run mode.

## 4. Backfill

```bash
php artisan lwa:upgrade --website-domain=example.com --chunk=1000
```

- Idempotent and resumable via `lwa_upgrade_checkpoints`.
- Re-run to import rows logged since the previous run.
- Visitors are reconstructed from `session_id` (or a daily salted IP/UA hash).
- Sessions use the configured 30-minute inactivity timeout.

## 5. Build rollups

```bash
php artisan lwa:aggregate --granularity=hourly
php artisan lwa:aggregate --granularity=daily
```

Schedule both in `routes/console.php`:

```php
Schedule::command('lwa:aggregate --granularity=hourly')->hourly();
Schedule::command('lwa:aggregate --granularity=daily')->daily();
Schedule::command('lwa:prune')->daily();
```

Dashboard KPIs then come from rollup tables, not raw scans.

## 6. Verify

```bash
php artisan lwa:benchmark --range=24h
```

Compare aggregate vs raw scan timings, and confirm reported request counts look right on the Overview page.

## 7. Optional — retire legacy data

Only after verification:

```bash
php artisan lwa:upgrade --prune-legacy --website-domain=example.com
```

This deletes only `lwa_logs` rows that have already been imported (tracked by the checkpoint). Leave `WEB_ANALYTICS_WRITE_LEGACY=true` until you are fully comfortable, then set it to `false`.

## Configuration changes

| New setting | Default | Purpose |
|---|---|---|
| `WEB_ANALYTICS_SCHEMA_ENABLED` | `false` | Master switch for advanced tables/collector |
| `WEB_ANALYTICS_COLLECTOR_MODE` | `sync` | `sync` or `queue` |
| `WEB_ANALYTICS_WRITE_LEGACY` | `true` | Keep writing `lwa_logs` |
| `WEB_ANALYTICS_QUEUE` | — | Optional named queue |
| `WEB_ANALYTICS_WEBSITE_ID` | — | Default website scope |
| `WEB_ANALYTICS_SESSION_TIMEOUT` | `30` | Session inactivity minutes |
| `WEB_ANALYTICS_BOUNCE_MAX_SECONDS` | `0` | Bounce duration threshold |
| `WEB_ANALYTICS_API_RATE_LIMIT` | `60` | API requests/minute |
| `WEB_ANALYTICS_MASK_REFERRER` | `true` | Mask sensitive referrer params |

## Rollback

1. Set `WEB_ANALYTICS_SCHEMA_ENABLED=false` and `WEB_ANALYTICS_WRITE_LEGACY=true`.
2. `php artisan optimize:clear`.
3. If you published views, re-publish the v2 views or restore them from backup.
4. To remove advanced tables: `php artisan migrate:rollback --step=1` (or drop them manually after reviewing the migration).
5. Restore the database backup if you ran `--prune-legacy`.

No v2 configuration keys were removed, so a config rollback is not strictly required.

## Known limitations

- Events/goals/conversions only work with the collector enabled and events actually recorded; no historical events are inferred from `lwa_logs`.
- Bounce and engagement for imported sessions are approximations based on the legacy `session_id` and timestamps.
- Multi-website isolation is session/config based; API tokens are bound to a single website at issue time.
