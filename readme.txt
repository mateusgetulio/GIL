=== GIL Waiting Room ===
Contributors: mateusgetulio
Tags: waiting room, queue, traffic, capacity, high traffic
Requires at least: 5.9
Tested up to: 7.0
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A virtual waiting room for WordPress. Caps concurrent visitors at a limit you choose and queues everyone else with live position updates.

== Description ==

What happens when a sale, a campaign, or a viral post sends more visitors than your server can comfortably handle?

GIL Waiting Room creates a virtual waiting room in front of your site. You choose how many visitors may browse at the same time; everyone above that limit is placed in a fair, first-come-first-served queue and sees a clean lobby page with their live position and an estimated wait. As soon as a spot opens, the next visitor in line is admitted automatically, no refresh or action needed.

= How it works =

* Each visitor gets a signed, HttpOnly cookie so their place in line survives reloads and short absences.
* Admission is atomic at the database level, so the limit holds even under bursts of concurrent traffic.
* Admitted visitors keep their spot for a session length you configure; when it expires, the spot goes to the next person in line.
* The lobby page polls a lightweight status endpoint every few seconds, shows live progress, and lets the visitor in the moment they are admitted.
* The lobby adapts to the visitor's light or dark system theme, respects reduced-motion preferences, and announces progress to screen readers.
* Preview the waiting room from the settings page before enabling it, and watch live admitted/waiting counts during a spike.
* The lobby is served with HTTP 503 and a Retry-After header, so search engines understand the site is temporarily busy and do not index the lobby.
* Administrators are never gated, so you cannot lock yourself out.

= What this plugin is not =

GIL Waiting Room manages capacity, it is not DDoS protection. Requests still reach PHP; the plugin decides who may browse. If you are under attack, you need protection at the network or CDN layer.

If a full-page cache or CDN serves your pages without hitting PHP, those cached hits cannot be queued. The waiting room works for requests that reach WordPress.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/`, or install it through the WordPress plugins screen.
2. Activate the plugin.
3. Open the "GIL Waiting Room" menu in your admin sidebar.
4. Set the concurrent visitor limit and session length, then enable the waiting room.

== Frequently Asked Questions ==

= Does the waiting room apply to wp-admin or the login page? =

No. Only the front end is gated. Administrators are also exempt on the front end.

= Can visitors skip the line by clearing cookies? =

Clearing cookies discards the visitor's identity, so they re-enter at the back of the line. The cookie is signed, so it cannot be forged to gain admission.

= How is the estimated wait calculated? =

It is a simple upper-bound estimate: your position divided by the visitor limit, times the configured session length.

= Where is the queue stored, and can I reset it? =

In a dedicated database table. The settings page shows live admitted and waiting counts and has a button to clear the queue.

= Does the plugin collect any personal data or contact external servers? =

No. The plugin makes no external requests of any kind, includes no analytics, and collects no personal data. The visitor cookie contains only a random identifier and a signature, nothing identifying, and the queue table stores that identifier with timestamps. Uninstalling removes the table and all plugin options.

= Does it work with page caching? =

Only requests that reach PHP can be queued. If a full-page cache or CDN answers a request by itself, the waiting room never sees it.

== Screenshots ==

1. The waiting room lobby with live position and estimated wait.
2. The settings page with live queue status.

== Changelog ==

= 2.0.0 =
* Complete rework of the admission engine: atomic admission under a database lock, FIFO promotion, and a fatal-error fix at exact capacity.
* Visitor identity moved from PHP sessions to a signed, HttpOnly cookie.
* New lobby page: self-contained, served with HTTP 503 and Retry-After, polls a REST status endpoint and admits automatically.
* Administrators are never gated.
* Queue clearing is now nonce-protected; settings are validated server-side.
* All external assets removed; everything ships with the plugin.
* Redesigned lobby (light/dark themes, progress bar, reduced-motion and screen-reader support) and settings page (live-updating counts, status line, lobby preview).

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.0.0 =
Complete rework. The queue table is rebuilt on activation; settings are preserved.
