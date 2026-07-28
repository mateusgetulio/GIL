<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="gil-status-line">
		<span class="gil-status-dot<?php echo $enabled ? ' gil-status-active' : ''; ?>"></span>
		<span>
			<?php
			if ( $enabled ) {
				echo esc_html(
					sprintf(
						/* translators: %s: concurrent visitor limit. */
						_n(
							'Waiting room is active, limiting the site to %s concurrent visitor.',
							'Waiting room is active, limiting the site to %s concurrent visitors.',
							$limit,
							'gil-waiting-room'
						),
						number_format_i18n( $limit )
					)
				);
			} else {
				esc_html_e( 'Waiting room is off. Visitors are not being limited.', 'gil-waiting-room' );
			}
			?>
		</span>
		<a class="gil-preview-link" href="<?php echo esc_url( $preview_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview the waiting room', 'gil-waiting-room' ); ?></a>
	</div>

	<?php if ( ! empty( $show_saved_notice ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'gil-waiting-room' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $show_cleared_notice ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'The queue has been cleared.', 'gil-waiting-room' ); ?></p>
		</div>
	<?php endif; ?>

	<?php settings_errors( Get_In_Line_Options::OPTION_KEY ); ?>

	<h2><?php esc_html_e( 'Live status', 'gil-waiting-room' ); ?></h2>
	<?php if ( $enabled ) : ?>
		<div class="gil-stat-cards">
			<div class="gil-stat-card">
				<div class="gil-stat-card-value" id="gil-admitted-count"><?php echo esc_html( $counts['admitted'] ); ?></div>
				<div class="gil-stat-card-label"><?php esc_html_e( 'Currently admitted', 'gil-waiting-room' ); ?></div>
			</div>
			<div class="gil-stat-card">
				<div class="gil-stat-card-value<?php echo $counts['waiting'] > 0 ? ' gil-waiting-hot' : ''; ?>" id="gil-waiting-count"><?php echo esc_html( $counts['waiting'] ); ?></div>
				<div class="gil-stat-card-label"><?php esc_html_e( 'Waiting in line', 'gil-waiting-room' ); ?></div>
			</div>
		</div>
		<p class="gil-stat-meta"><?php esc_html_e( 'Auto-updates every 10 seconds.', 'gil-waiting-room' ); ?> <span id="gil-updated"></span></p>
		<p class="gil-screen-reader-only" role="status" id="gil-admin-live-status"></p>
	<?php else : ?>
		<p class="gil-status-disabled-note"><?php esc_html_e( 'Live status appears here when the waiting room is on.', 'gil-waiting-room' ); ?></p>
	<?php endif; ?>

	<form action="options.php" method="post">
		<?php
		settings_fields( 'get_in_line_group' );
		do_settings_sections( 'gil-waiting-room' );
		submit_button( esc_html__( 'Save settings', 'gil-waiting-room' ) );
		?>
	</form>

	<h2><?php esc_html_e( 'Clear the queue', 'gil-waiting-room' ); ?></h2>
	<p><?php esc_html_e( 'Clearing the queue removes every admitted session and every waiting visitor. The next requests will fill the room again from scratch.', 'gil-waiting-room' ); ?></p>
	<?php
	$get_in_line_confirm_message = sprintf(
		/* translators: 1: admitted visitor count, 2: waiting visitor count. */
		__( 'Remove %1$s admitted sessions and %2$s waiting visitors? They will rejoin from scratch.', 'gil-waiting-room' ),
		number_format_i18n( $counts['admitted'] ),
		number_format_i18n( $counts['waiting'] )
	);
	?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" onsubmit="return confirm( '<?php echo esc_js( $get_in_line_confirm_message ); ?>' );">
		<input type="hidden" name="action" value="get_in_line_clear_queue" />
		<?php wp_nonce_field( 'get_in_line_clear_queue' ); ?>
		<?php submit_button( esc_html__( 'Clear the queue', 'gil-waiting-room' ), 'delete', 'gil-clear-queue', false ); ?>
	</form>
</div>
