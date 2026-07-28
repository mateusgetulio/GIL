<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_get_in_line_clear_queue', array( $this, 'handle_clear_queue' ) );
	}

	public function add_menu() {
		add_menu_page(
			esc_html__( 'Get in Line', 'get-in-line' ),
			esc_html__( 'Get in Line', 'get-in-line' ),
			'manage_options',
			'get-in-line',
			array( $this, 'render_settings_page' ),
			'dashicons-groups'
		);
	}

	public function register_settings() {
		register_setting(
			'get_in_line_group',
			Get_In_Line_Options::OPTION_KEY,
			array( 'sanitize_callback' => array( $this, 'sanitize' ) )
		);

		add_settings_section(
			'get_in_line_main_section',
			esc_html__( 'Waiting room settings', 'get-in-line' ),
			null,
			'get-in-line'
		);

		add_settings_field(
			'gil_enabled',
			esc_html__( 'Enabled', 'get-in-line' ),
			array( $this, 'render_enabled_field' ),
			'get-in-line',
			'get_in_line_main_section',
			array( 'label_for' => 'gil_enabled' )
		);

		add_settings_field(
			'gil_limit',
			esc_html__( 'Concurrent visitor limit', 'get-in-line' ),
			array( $this, 'render_limit_field' ),
			'get-in-line',
			'get_in_line_main_section',
			array( 'label_for' => 'gil_limit' )
		);

		add_settings_field(
			'gil_expiration',
			esc_html__( 'Session length (minutes)', 'get-in-line' ),
			array( $this, 'render_expiration_field' ),
			'get-in-line',
			'get_in_line_main_section',
			array( 'label_for' => 'gil_expiration' )
		);
	}

	public function render_enabled_field() {
		$options = Get_In_Line_Options::all();
		?>
		<input type="checkbox" name="<?php echo esc_attr( Get_In_Line_Options::OPTION_KEY ); ?>[gil_enabled]" id="gil_enabled" value="1" <?php checked( 1, (int) $options['gil_enabled'] ); ?> />
		<label for="gil_enabled"><?php esc_html_e( 'Limit concurrent access to the site', 'get-in-line' ); ?></label>
		<?php
	}

	public function render_limit_field() {
		$options = Get_In_Line_Options::all();
		?>
		<input type="number" min="1" name="<?php echo esc_attr( Get_In_Line_Options::OPTION_KEY ); ?>[gil_limit]" id="gil_limit" value="<?php echo esc_attr( $options['gil_limit'] ); ?>">
		<p class="description"><?php esc_html_e( 'How many visitors may browse the site at the same time.', 'get-in-line' ); ?></p>
		<?php
	}

	public function render_expiration_field() {
		$options = Get_In_Line_Options::all();
		?>
		<input type="number" min="1" name="<?php echo esc_attr( Get_In_Line_Options::OPTION_KEY ); ?>[gil_expiration]" id="gil_expiration" value="<?php echo esc_attr( $options['gil_expiration'] ); ?>">
		<p class="description"><?php esc_html_e( 'How long an admitted visitor keeps their spot before it is freed for the next in line.', 'get-in-line' ); ?></p>
		<?php
	}

	public function sanitize( $input ) {
		$old = Get_In_Line_Options::all();

		if ( ! is_array( $input ) ) {
			return $old;
		}

		$clean = array(
			'gil_enabled' => empty( $input['gil_enabled'] ) ? 0 : 1,
		);

		$limit = isset( $input['gil_limit'] ) ? absint( $input['gil_limit'] ) : 0;
		if ( $limit < 1 ) {
			add_settings_error(
				Get_In_Line_Options::OPTION_KEY,
				'get_in_line_limit_invalid',
				esc_html__( 'The visitor limit must be a whole number greater than zero. The previous value was kept.', 'get-in-line' )
			);
			return $old;
		}
		$clean['gil_limit'] = $limit;

		$expiration = isset( $input['gil_expiration'] ) ? absint( $input['gil_expiration'] ) : 0;
		if ( $expiration < 1 ) {
			add_settings_error(
				Get_In_Line_Options::OPTION_KEY,
				'get_in_line_expiration_invalid',
				esc_html__( 'The session length must be a whole number of minutes greater than zero. The previous value was kept.', 'get-in-line' )
			);
			return $old;
		}
		$clean['gil_expiration'] = $expiration;

		return $clean;
	}

	public function handle_clear_queue() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to clear the queue.', 'get-in-line' ) );
		}

		check_admin_referer( 'get_in_line_clear_queue' );

		$cleared = Get_In_Line_Queue::clear();

		wp_safe_redirect(
			add_query_arg(
				array(
					'get-in-line-cleared' => $cleared ? '1' : '0',
					'_wpnonce'            => wp_create_nonce( 'get_in_line_notice' ),
				),
				admin_url( 'admin.php?page=get-in-line' )
			)
		);
		exit;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$show_cleared_notice = isset( $_GET['get-in-line-cleared'], $_GET['_wpnonce'] )
			&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'get_in_line_notice' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$show_saved_notice = isset( $_GET['settings-updated'] )
			&& empty( get_settings_errors( Get_In_Line_Options::OPTION_KEY ) );

		$counts = Get_In_Line_Queue::counts();

		include GET_IN_LINE_PATH . 'views/settings-page.php';
	}
}
