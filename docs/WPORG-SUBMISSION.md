# WordPress.org Submission Plan

## Status of the 2023 submission

The plugin was submitted in May 2023 and pended with a list of required fixes. A corrected version was sent in August 2023, the review team replied in September 2023 with a second round of findings, and the thread was never answered, so the submission was auto-rejected after the 90-day window.

A rejection is not a ban. The path forward is a fresh submission at https://wordpress.org/plugins/developers/add/ with v2.0.0. The whole plugin is re-reviewed from scratch, so the old thread does not carry over.

## Compliance map: every 2023 finding vs v2.0.0

Round 1 (May 2023):

| Finding | v2.0.0 status |
|---|---|
| Stable tag did not match plugin version | Fixed. `readme.txt` Stable tag and the plugin header are both 2.0.0 |
| Direct file access not blocked in all files | Fixed. Every PHP file guards on `ABSPATH` (`WP_UNINSTALL_PLUGIN` for `uninstall.php`) |
| Bundled jQuery, a core library | Fixed. No jQuery anywhere; the lobby uses a few lines of vanilla JS |
| Remote CDN assets (Bootstrap, Google Fonts) | Fixed. The lobby is fully self-contained: inline CSS, system font stack, no external requests |
| Unescaped echoes in settings fields | Fixed. All output goes through `esc_html`/`esc_attr`/`esc_url`/`wp_json_encode` at echo time |
| Scripts not enqueued | Fixed for the admin side (no custom assets at all). The lobby is a standalone interstitial that exits before the theme loads, which is the standard pattern for maintenance/queue pages; its tiny script is inline by design and documented as such for the reviewer |
| Generic names, 2-3 letter prefixes (`GIL_`, `clear_queue`, `GUID`) | Fixed. Everything is prefixed `get_in_line_` / `Get_In_Line_` / `GET_IN_LINE_`: classes, functions, constants, options, hooks, cookie, transient, DB table, MySQL lock name |
| Unsafe SQL, interpolated variables | Fixed. Every query with input uses `$wpdb->prepare()` with placeholders; table names come only from `$wpdb->prefix` interpolation, the WPCS-sanctioned pattern |

Round 2 (September 2023):

| Finding | v2.0.0 status |
|---|---|
| Writing the global `rewrite_rules` option | Fixed. Removed entirely |
| Variables passed to gettext functions, concatenated translatable strings | Fixed. All i18n calls use literal strings and the `gil-waiting-room` domain |
| Text domain must match the plugin slug | `gil-waiting-room` everywhere, matching the slug that derives from the plugin name (see below) |
| Unsanitized `$_SESSION` return | Fixed. PHP sessions are gone; the cookie is sanitized, format-validated, and HMAC-verified before use |
| Interpolated value inside a prepared query | Fixed. No interpolated values remain in any query |
| Unescaped echoes in the lobby | Fixed. All lobby output is escaped |

## Pre-submission checklist

1. Confirm the WordPress.org account (the 2023 review ran under mateus.getulio@gmail.com) and update `Contributors:` in `readme.txt` to the exact .org username.
2. Verify `Tested up to:` against the current WordPress release and actually smoke-test on it (wp-env makes this a one-line version bump).
3. The public name is "GIL Waiting Room" and the slug derives from it automatically (`gil-waiting-room`), with text domain and admin slug already matching. The original "Get in Line" name collides with an abandoned 2018 booking plugin that owns `get-in-line`, so it cannot be used.
4. Prepare .org listing assets (kept out of the plugin zip): `screenshot-1.png` (lobby), `screenshot-2.png` (settings), optional banner and icon. The Playwright screenshot job already produces the raw captures.
5. Build the distribution zip from a clean export: plugin PHP, views, `readme.txt`, `LICENSE`, `uninstall.php`. Exclude development files (`node_modules`, `tests`, `docs`, CI config, `package.json`, `.wp-env.json`, `playwright*`); a `.distignore` covers this.
6. One plugin per account can be in review at a time, and the current queue takes weeks. Reply promptly to every review email; silence is what killed the 2023 submission.

## Notes for the reviewer conversation

- The lobby intentionally renders as a standalone interstitial with inline CSS/JS and exits, the same approach WordPress core uses for `wp_die()` and maintenance pages. If a reviewer asks for `wp_enqueue_*`, the answer is that the theme never loads on a queued request and enqueuing would defeat the page's purpose (minimal, dependency-free, cache-proof).
- `GET_LOCK` is used for atomicity. It is available on MySQL 5.5+ and MariaDB, requires no extra privileges, and degrades safely: if the lock cannot be acquired the visitor is queued, never over-admitted.
- The plugin stores no personal data. The cookie holds a random id and a signature, nothing identifying; worth stating proactively in the review thread.
