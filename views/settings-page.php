<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php if ( ! empty( $show_saved_notice ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'get-in-line' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $show_cleared_notice ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'The queue has been cleared.', 'get-in-line' ); ?></p>
		</div>
	<?php endif; ?>

	<?php settings_errors( Get_In_Line_Options::OPTION_KEY ); ?>

	<h2><?php esc_html_e( 'Live status', 'get-in-line' ); ?></h2>
	<table class="widefat striped" style="max-width: 420px;">
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Visitors currently admitted', 'get-in-line' ); ?></td>
				<td><strong id="gil-admitted-count"><?php echo esc_html( $counts['admitted'] ); ?></strong></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Visitors waiting in line', 'get-in-line' ); ?></td>
				<td><strong id="gil-waiting-count"><?php echo esc_html( $counts['waiting'] ); ?></strong></td>
			</tr>
		</tbody>
	</table>

	<form action="options.php" method="post">
		<?php
		settings_fields( 'get_in_line_group' );
		do_settings_sections( 'get-in-line' );
		submit_button( esc_html__( 'Save settings', 'get-in-line' ) );
		?>
	</form>

	<h2><?php esc_html_e( 'Reset', 'get-in-line' ); ?></h2>
	<p><?php esc_html_e( 'Clearing the queue removes every admitted session and every waiting visitor. The next requests will fill the room again from scratch.', 'get-in-line' ); ?></p>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" onsubmit="return confirm( '<?php echo esc_js( __( 'Clear all admitted sessions and the whole waiting line?', 'get-in-line' ) ); ?>' );">
		<input type="hidden" name="action" value="get_in_line_clear_queue" />
		<?php wp_nonce_field( 'get_in_line_clear_queue' ); ?>
		<?php submit_button( esc_html__( 'Clear the queue', 'get-in-line' ), 'delete', 'gil-clear-queue', false ); ?>
	</form>
</div>
