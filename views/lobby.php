<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="robots" content="noindex" />
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> &middot; <?php esc_html_e( 'Waiting room', 'get-in-line' ); ?></title>
	<style>
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
			background: radial-gradient(circle at 20% 20%, #1e293b 0%, #0f172a 55%, #020617 100%);
			color: #e2e8f0;
			padding: 24px;
		}
		.gil-card {
			width: 100%;
			max-width: 480px;
			background: rgba(15, 23, 42, 0.75);
			border: 1px solid rgba(148, 163, 184, 0.25);
			border-radius: 16px;
			padding: 48px 40px;
			text-align: center;
			box-shadow: 0 24px 60px rgba(2, 6, 23, 0.6);
		}
		.gil-site {
			font-size: 14px;
			letter-spacing: 0.12em;
			text-transform: uppercase;
			color: #94a3b8;
			margin-bottom: 12px;
		}
		h1 {
			font-size: 26px;
			font-weight: 600;
			margin-bottom: 16px;
			color: #f8fafc;
		}
		.gil-lead {
			font-size: 15px;
			line-height: 1.6;
			color: #cbd5e1;
			margin-bottom: 32px;
		}
		.gil-stats {
			display: flex;
			justify-content: center;
			gap: 40px;
			margin-bottom: 32px;
		}
		.gil-stat-value {
			font-size: 40px;
			font-weight: 700;
			color: #f8fafc;
			line-height: 1.2;
		}
		.gil-stat-label {
			font-size: 12px;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: #94a3b8;
			margin-top: 6px;
		}
		.gil-ring {
			position: relative;
			width: 44px;
			height: 44px;
			margin: 0 auto 20px;
		}
		.gil-ring svg {
			width: 44px;
			height: 44px;
			transform: rotate(-90deg);
		}
		.gil-ring circle {
			fill: none;
			stroke-width: 3;
		}
		.gil-ring .gil-ring-track {
			stroke: rgba(148, 163, 184, 0.25);
		}
		.gil-ring .gil-ring-progress {
			stroke: #38bdf8;
			stroke-linecap: round;
			stroke-dasharray: 119.4;
			animation: gil-countdown 5s linear infinite;
		}
		@keyframes gil-countdown {
			from { stroke-dashoffset: 0; }
			to { stroke-dashoffset: 119.4; }
		}
		.gil-fine {
			font-size: 13px;
			line-height: 1.6;
			color: #64748b;
		}
	</style>
</head>
<body>
	<main class="gil-card">
		<div class="gil-site"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
		<h1><?php esc_html_e( 'You are in line', 'get-in-line' ); ?></h1>
		<p class="gil-lead"><?php esc_html_e( 'We are welcoming a high number of visitors right now. To keep the site fast for everyone, access is briefly limited. Your spot is saved.', 'get-in-line' ); ?></p>

		<div class="gil-stats">
			<div>
				<div class="gil-stat-value" id="gil-position"><?php echo esc_html( $position ); ?></div>
				<div class="gil-stat-label"><?php esc_html_e( 'Your place in line', 'get-in-line' ); ?></div>
			</div>
			<div>
				<div class="gil-stat-value"><span id="gil-wait"><?php echo esc_html( $estimated_wait_minutes ); ?></span></div>
				<div class="gil-stat-label"><?php esc_html_e( 'Est. wait (minutes)', 'get-in-line' ); ?></div>
			</div>
		</div>

		<div class="gil-ring">
			<svg viewBox="0 0 44 44">
				<circle class="gil-ring-track" r="19" cx="22" cy="22"></circle>
				<circle class="gil-ring-progress" r="19" cx="22" cy="22"></circle>
			</svg>
		</div>

		<p class="gil-fine"><?php esc_html_e( 'This page checks your status automatically and will let you in as soon as a spot opens. You can leave and come back without losing your place.', 'get-in-line' ); ?></p>
	</main>

	<script>
		( function () {
			var endpoint = <?php echo wp_json_encode( esc_url_raw( $status_endpoint ) ); ?>;
			var positionEl = document.getElementById( 'gil-position' );
			var waitEl = document.getElementById( 'gil-wait' );

			function poll() {
				fetch( endpoint, { credentials: 'same-origin', cache: 'no-store' } )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function ( data ) {
						if ( 'admitted' === data.status ) {
							window.location.reload();
							return;
						}
						if ( 'waiting' === data.status ) {
							positionEl.textContent = data.position;
							waitEl.textContent = data.estimated_wait_minutes;
						}
					} )
					.catch( function () {} );
			}

			setInterval( poll, 5000 );
		} )();
	</script>
</body>
</html>
