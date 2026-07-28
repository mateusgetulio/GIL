<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$get_in_line_is_preview = ! empty( $is_preview );
$get_in_line_mark_svg   = '<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><circle cx="4" cy="12" r="3.2" fill="var(--gil-accent)"/><circle cx="12" cy="12" r="3.2" fill="var(--gil-muted)" opacity=".55"/><circle cx="20" cy="12" r="3.2" fill="var(--gil-muted)" opacity=".3"/></svg>';
$get_in_line_favicon    = 'data:image/svg+xml,' . rawurlencode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="4" cy="12" r="3.2" fill="#38bdf8"/><circle cx="12" cy="12" r="3.2" fill="#94a3b8" opacity=".55"/><circle cx="20" cy="12" r="3.2" fill="#94a3b8" opacity=".3"/></svg>' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="robots" content="noindex" />
	<meta name="color-scheme" content="dark light" />
	<link rel="icon" href="<?php echo esc_url( $get_in_line_favicon ); ?>" />
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> &middot; <?php esc_html_e( 'Waiting room', 'gil-waiting-room' ); ?></title>
	<style>
		:root {
			--gil-bg-1: #1e293b;
			--gil-bg-2: #0f172a;
			--gil-bg-3: #020617;
			--gil-card: rgba(15, 23, 42, 0.75);
			--gil-border: rgba(148, 163, 184, 0.25);
			--gil-text: #f8fafc;
			--gil-body: #cbd5e1;
			--gil-muted: #94a3b8;
			--gil-accent: #38bdf8;
			--gil-shadow: 0 24px 60px rgba(2, 6, 23, 0.6);
		}
		@media (prefers-color-scheme: light) {
			:root {
				--gil-bg-1: #eef2f7;
				--gil-bg-2: #f8fafc;
				--gil-bg-3: #e2e8f0;
				--gil-card: rgba(255, 255, 255, 0.9);
				--gil-border: rgba(15, 23, 42, 0.08);
				--gil-text: #0f172a;
				--gil-body: #334155;
				--gil-muted: #64748b;
				--gil-accent: #0284c7;
				--gil-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
			}
		}
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		html,
		body {
			height: 100%;
		}
		body {
			display: flex;
			align-items: center;
			justify-content: center;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			background: radial-gradient(circle at 20% 20%, var(--gil-bg-1) 0%, var(--gil-bg-2) 55%, var(--gil-bg-3) 100%);
			color: var(--gil-body);
			padding: 24px;
		}
		.gil-card {
			position: relative;
			width: 100%;
			max-width: 480px;
			background: var(--gil-card);
			border: 1px solid var(--gil-border);
			border-top: 2px solid var(--gil-accent);
			border-radius: 16px;
			padding: 48px 40px;
			text-align: center;
			box-shadow: var(--gil-shadow);
		}
		.gil-card::before {
			content: "";
			position: absolute;
			inset: -40px;
			z-index: -1;
			background: radial-gradient(circle at 50% 0%, var(--gil-accent) 0%, transparent 60%);
			opacity: 0.08;
			filter: blur(24px);
		}
		.gil-mark {
			margin-bottom: 12px;
		}
		.gil-site {
			font-size: 12px;
			letter-spacing: 0.14em;
			text-transform: uppercase;
			color: var(--gil-muted);
			margin-bottom: 12px;
		}
		h1 {
			font-size: 28px;
			font-weight: 650;
			letter-spacing: -0.01em;
			margin-bottom: 16px;
			color: var(--gil-text);
		}
		.gil-lead {
			font-size: 15px;
			line-height: 1.6;
			margin-bottom: 32px;
		}
		.gil-stats {
			display: flex;
			justify-content: center;
			margin-bottom: 28px;
		}
		.gil-stat {
			flex: 1;
			max-width: 160px;
		}
		.gil-stat + .gil-stat {
			border-left: 1px solid var(--gil-border);
		}
		.gil-stat-value {
			font-size: 56px;
			font-weight: 700;
			font-variant-numeric: tabular-nums;
			color: var(--gil-text);
			line-height: 1.15;
		}
		.gil-stat-unit {
			font-size: 18px;
			font-weight: 500;
			color: var(--gil-muted);
		}
		.gil-stat-label {
			font-size: 12px;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: var(--gil-muted);
			margin-top: 6px;
		}
		.gil-progress {
			height: 6px;
			border-radius: 999px;
			background: var(--gil-border);
			overflow: hidden;
			margin-bottom: 10px;
		}
		.gil-progress-fill {
			height: 100%;
			width: 4%;
			border-radius: 999px;
			background: var(--gil-accent);
			transition: width 0.6s ease;
		}
		.gil-meta {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			font-size: 13px;
			color: var(--gil-muted);
			margin-bottom: 24px;
		}
		.gil-meta svg {
			width: 16px;
			height: 16px;
			transform: rotate(-90deg);
		}
		.gil-meta circle {
			fill: none;
			stroke-width: 2.5;
		}
		.gil-meta .gil-ring-track {
			stroke: var(--gil-border);
		}
		.gil-meta .gil-ring-progress {
			stroke: var(--gil-accent);
			stroke-linecap: round;
			stroke-dasharray: 37.7;
			animation: gil-countdown 5s linear infinite;
		}
		@keyframes gil-countdown {
			from { stroke-dashoffset: 0; }
			to { stroke-dashoffset: 37.7; }
		}
		.gil-fine {
			font-size: 13px;
			line-height: 1.6;
			color: var(--gil-muted);
		}
		.gil-visually-hidden {
			position: absolute;
			width: 1px;
			height: 1px;
			overflow: hidden;
			clip: rect(0 0 0 0);
			white-space: nowrap;
		}
		@media (prefers-reduced-motion: reduce) {
			.gil-meta .gil-ring-progress {
				animation: none;
				stroke-dashoffset: 28;
			}
			.gil-progress-fill {
				transition: none;
			}
		}
	</style>
</head>
<body>
	<main class="gil-card">
		<div class="gil-mark"><?php echo $get_in_line_mark_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<div class="gil-site"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
		<h1 id="gil-heading"><?php echo 1 === (int) $position ? esc_html__( 'You’re next', 'gil-waiting-room' ) : esc_html__( 'You are in line', 'gil-waiting-room' ); ?></h1>
		<p class="gil-lead" id="gil-lead"><?php echo 1 === (int) $position ? esc_html__( 'A spot is opening up for you. Hang tight, this page will refresh on its own.', 'gil-waiting-room' ) : esc_html__( 'We are welcoming a high number of visitors right now. To keep the site fast for everyone, access is briefly limited. Your spot is saved.', 'gil-waiting-room' ); ?></p>

		<div class="gil-stats">
			<div class="gil-stat">
				<div class="gil-stat-value" id="gil-position"><?php echo esc_html( $position ); ?></div>
				<div class="gil-stat-label"><?php esc_html_e( 'Place in line', 'gil-waiting-room' ); ?></div>
			</div>
			<div class="gil-stat">
				<div class="gil-stat-value"><span id="gil-wait"><?php echo esc_html( $estimated_wait_minutes ); ?></span><span class="gil-stat-unit"> <?php esc_html_e( 'min', 'gil-waiting-room' ); ?></span></div>
				<div class="gil-stat-label"><?php esc_html_e( 'Estimated wait', 'gil-waiting-room' ); ?></div>
			</div>
		</div>

		<div class="gil-progress" aria-hidden="true">
			<div class="gil-progress-fill" id="gil-progress-fill"></div>
		</div>
		<div class="gil-meta">
			<svg viewBox="0 0 16 16" aria-hidden="true">
				<circle class="gil-ring-track" r="6" cx="8" cy="8"></circle>
				<circle class="gil-ring-progress" r="6" cx="8" cy="8"></circle>
			</svg>
			<span id="gil-meta-label"><?php esc_html_e( 'Checking automatically, no need to refresh', 'gil-waiting-room' ); ?></span>
		</div>

		<p class="gil-fine"><?php esc_html_e( 'You can leave and come back without losing your place.', 'gil-waiting-room' ); ?></p>
		<p class="gil-visually-hidden" role="status" id="gil-live-status"></p>
	</main>

	<?php if ( ! $get_in_line_is_preview ) : ?>
	<script>
		( function () {
			var config = 
			<?php
			echo wp_json_encode(
				array(
					'endpoint' => esc_url_raw( $status_endpoint ),
					'siteName' => get_bloginfo( 'name' ),
					'strings'  => array(
						'inLine'       => __( 'You are in line', 'gil-waiting-room' ),
						'next'         => __( 'You’re next', 'gil-waiting-room' ),
						'nextLead'     => __( 'A spot is opening up for you. Hang tight, this page will refresh on its own.', 'gil-waiting-room' ),
						'admitted'     => __( 'You’re in', 'gil-waiting-room' ),
						'admittedLead' => __( 'Taking you to the site now.', 'gil-waiting-room' ),
						'checking'     => __( 'Checking automatically, no need to refresh', 'gil-waiting-room' ),
						'offline'      => __( 'Connection lost, retrying automatically', 'gil-waiting-room' ),
						/* translators: 1: place in line, 2: estimated wait in minutes. */
						'status'       => __( 'You are number %1$s in line. Estimated wait %2$s minutes.', 'gil-waiting-room' ),
						/* translators: %1$s: place in line, shown in the browser tab title. */
						'titleWaiting' => __( '#%1$s in line', 'gil-waiting-room' ),
					),
				)
			);
			?>
			;

			var headingEl = document.getElementById( 'gil-heading' );
			var leadEl = document.getElementById( 'gil-lead' );
			var positionEl = document.getElementById( 'gil-position' );
			var waitEl = document.getElementById( 'gil-wait' );
			var fillEl = document.getElementById( 'gil-progress-fill' );
			var metaEl = document.getElementById( 'gil-meta-label' );
			var liveEl = document.getElementById( 'gil-live-status' );

			var initialPosition = parseInt( positionEl.textContent, 10 ) || 1;
			var lastAnnounced = '';
			var failures = 0;
			var admittedHandled = false;

			function render( position, wait ) {
				positionEl.textContent = position;
				waitEl.textContent = wait;
				var progress = Math.max( 4, Math.round( ( 1 - position / Math.max( initialPosition, position ) ) * 100 ) );
				fillEl.style.width = progress + '%';
				document.title = config.strings.titleWaiting.replace( '%1$s', position ) + ' · ' + config.siteName;
				if ( 1 === position ) {
					headingEl.textContent = config.strings.next;
					leadEl.textContent = config.strings.nextLead;
				} else {
					headingEl.textContent = config.strings.inLine;
				}
				var announcement = config.strings.status.replace( '%1$s', position ).replace( '%2$s', wait );
				if ( announcement !== lastAnnounced ) {
					liveEl.textContent = announcement;
					lastAnnounced = announcement;
				}
			}

			function admit() {
				if ( admittedHandled ) {
					return;
				}
				admittedHandled = true;
				headingEl.textContent = config.strings.admitted;
				leadEl.textContent = config.strings.admittedLead;
				liveEl.textContent = config.strings.admittedLead;
				fillEl.style.width = '100%';
				window.setTimeout( function () {
					window.location.reload();
				}, 900 );
			}

			function poll() {
				if ( 'hidden' === document.visibilityState || admittedHandled ) {
					return;
				}
				fetch( config.endpoint, { credentials: 'same-origin', cache: 'no-store' } )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function ( data ) {
						failures = 0;
						metaEl.textContent = config.strings.checking;
						if ( 'admitted' === data.status ) {
							admit();
							return;
						}
						if ( 'waiting' === data.status ) {
							render( data.position, data.estimated_wait_minutes );
						}
					} )
					.catch( function () {
						failures++;
						if ( failures >= 3 ) {
							metaEl.textContent = config.strings.offline;
						}
					} );
			}

			document.addEventListener( 'visibilitychange', function () {
				if ( 'visible' === document.visibilityState ) {
					poll();
				}
			} );

			window.setInterval( poll, 5000 );
			poll();
		} )();
	</script>
	<?php endif; ?>
</body>
</html>
