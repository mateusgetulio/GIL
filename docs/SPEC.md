# Get in Line v2.0 Specification

## Summary

Get in Line (GIL) is a WordPress plugin that implements a virtual waiting room. When the number of concurrent visitors exceeds a configurable limit, new visitors are placed in a FIFO queue and shown a lobby page with their live position and an estimated wait. As admitted sessions expire, waiting visitors are promoted automatically and their browser enters the site without any action on their part.

v2.0 is a ground-up rework of the 2023 v1.0 release. The product idea is unchanged; the admission model, visitor identity, request lifecycle, and tooling are new.

## Goals

1. The concurrency cap must hold under concurrent traffic. Admission is atomic; parallel requests cannot overshoot the limit.
2. Visitors keep their place in line across page reloads and short absences, without PHP sessions.
3. Waiting visitors see live progress (position, estimated wait) and are admitted automatically when a slot frees, via lightweight polling rather than full page reloads.
4. The site owner can never lock themselves out.
5. The plugin is verifiable: a Dockerized environment plus an end-to-end Playwright suite exercise the real admission flow in a real WordPress.

## Non-goals

- DDoS protection. A PHP-level queue cannot defend the TCP/HTTP layer and v2 removes that claim from all copy. GIL is capacity management, not attack mitigation.
- Compatibility with full-page cache layers in front of PHP (Varnish, CDN page cache). If PHP never runs, no PHP queue can gate the request. Documented as a known limitation.
- Multi-server queue coordination beyond what a shared MySQL provides. The design is correct on any topology where all app servers share one database, which covers standard WordPress hosting.

## What v1 got wrong (drives the v2 design)

| v1 defect | v2 answer |
|---|---|
| Debug `return 'test12'` shipped, giving every visitor the same identity | Identity is a random 128-bit id in an HMAC-signed cookie, covered by e2e tests that run two isolated browser contexts |
| `CREATE TABLE` passed through `$wpdb->prepare()`, quoting the table name and breaking activation | Schema managed by `dbDelta()` with an unprepared identifier and `$wpdb->get_charset_collate()` |
| Check-then-insert admission with no locking, so bursts overshoot the cap | Admission runs inside a MySQL named lock (`GET_LOCK`), making count-and-admit atomic across PHP workers |
| Fatal `get_object_vars(null)` when the site is exactly at capacity | Explicit states, no null-branch arithmetic; covered by an at-capacity e2e test |
| `session_start()` on every request; clearing cookies skipped the queue | Cookie is signed; a forged or cleared cookie simply re-enters the back of the line. No PHP sessions |
| All logic in the class constructor at plugin load | Bootstrap on `plugins_loaded`, gate on `template_redirect`, admin wiring on `admin_menu`/`admin_init` |
| Every anonymous request performed multiple writes | Steady-state admitted request performs one indexed read; cleanup and promotion are throttled and lock-guarded |
| Lobby reloaded the whole page every 15 s | Lobby polls a REST status endpoint every 5 s and reloads once, when admitted |
| Queue clearing was a plain GET link with no nonce | `admin-post.php` action with nonce and capability check |
| Mixed text domains, string `"null"` timestamps, stray rewrite-rule writes | Single `get-in-line` domain, nullable DATETIME columns in UTC, no unrelated option writes |

## Architecture

### Data model

Table `{$wpdb->prefix}gil_queue`:

| column | type | notes |
|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT PK | |
| `visitor_id` | CHAR(32) NOT NULL, UNIQUE KEY | hex random id from the cookie |
| `status` | VARCHAR(10) NOT NULL | `admitted` or `waiting` |
| `queued_at` | DATETIME NOT NULL | UTC, FIFO ordering key |
| `admitted_at` | DATETIME NULL | UTC |
| `expires_at` | DATETIME NULL | UTC, only for admitted rows |

Indexes: unique on `visitor_id`, composite on `(status, expires_at)`, composite on `(status, queued_at)`.

### Visitor identity

Cookie `gil_visitor` holds `id.signature` where `id` is 16 random bytes hex-encoded and `signature = HMAC-SHA256(id, wp_salt('auth'))`. Invalid or missing signatures are treated as a brand-new visitor. The cookie is `HttpOnly`, `SameSite=Lax`, `Secure` when the site is HTTPS, and lives for 24 hours.

### Admission algorithm

On `template_redirect`, for front-end main requests only, when the plugin is enabled and the user cannot `manage_options`:

```
resolve_or_create visitor cookie
row = lookup by visitor_id
if row is admitted and not expired: allow (single read, fast path)
else:
    acquire MySQL named lock 'gil_admission' (2 s timeout)
        delete admitted rows where expires_at <= now
        promote waiting rows FIFO into freed slots
        if this visitor now admitted: allow
        else if admitted_count < limit: admit this visitor, allow
        else: upsert this visitor as waiting
    release lock
    if waiting: send 503 + Retry-After + noindex, render lobby, exit
```

If the lock cannot be acquired within the timeout the request is treated as waiting rather than admitted, favoring cap correctness over admission speed.

Promotion also runs (same lock, throttled by a 5 s transient) from the REST status endpoint, so an idle site with polling lobbies still promotes on time, and hourly from WP-Cron as a safety net to purge stale rows on sites with no waiting traffic.

### Status endpoint

`GET /wp-json/get-in-line/v1/status` (public, no auth, excluded from gating because the gate only runs on `template_redirect`):

```json
{ "status": "waiting", "position": 3, "estimated_wait_minutes": 8 }
```

`estimated_wait_minutes = ceil(position / limit) * session_minutes`, a deliberately simple upper-bound estimate.

The endpoint never creates queue rows; unknown visitors get `{"status": "unknown"}` and only the gated page view enqueues.

### Lobby page

Self-contained view (no theme dependency, no external assets), served with HTTP 503, `Retry-After`, and `X-Robots-Tag: noindex`. Shows position and estimated wait, polls the status endpoint every 5 s, animates a progress ring between polls, and reloads exactly once when the endpoint reports `admitted`.

### Settings

Options key `get_in_line_options` via the Settings API: `gil_enabled` (checkbox), `gil_limit` (int, min 1), `gil_expiration` (minutes, int, min 1). Sanitization uses `absint` with explicit error messages that keep the previous value. The settings page also shows live counts of admitted and waiting visitors and a nonce-protected Clear queue action (`admin-post.php`) that empties the table.

### Lifecycle

- Activation: `dbDelta` schema creation, default options, schedule cron.
- Deactivation: unschedule cron. Data is kept.
- Uninstall: `uninstall.php` drops the table and deletes options.

## Testing strategy

Environment: `@wordpress/env` (Docker) mapping the repo as the plugin, dev on :8888, isolated test instance on :8889.

End-to-end (Playwright, `@wordpress/e2e-test-utils-playwright`), each test resetting options and truncating the queue via WP-CLI:

1. Settings: defaults present after activation; saving new values persists; invalid values are rejected and keep the old value.
2. Under the limit a visitor browses normally and no lobby markup appears.
3. Over the limit (limit 1, visitor A admitted) visitor B in an isolated context gets the lobby, HTTP 503, position 1; visitor C gets position 2.
4. B keeps identity across reload: still position 1, not pushed back.
5. Promotion: A's session expires (or the queue is cleared), B's lobby auto-reloads within the polling window and B is admitted, C moves to position 1.
6. Admin bypass: a logged-in administrator is never gated even at capacity.
7. Clear queue from the settings page empties both admitted and waiting sets.
8. Cap holds under concurrency: 20 parallel fresh-context requests at limit 5 result in exactly 5 admitted rows (asserted via WP-CLI query).

Static analysis: PHPCS with the WordPress ruleset in CI, alongside the e2e job.

## Release

Version 2.0.0. v1.0 remains in git history intentionally; the README documents the rework as a case study.
