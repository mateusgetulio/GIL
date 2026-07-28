<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Gate {

	const PREVIEW_POSITION = 47;
	const PREVIEW_WAIT     = 12;

	public static function maybe_gate() {
		if ( self::is_preview_request() ) {
			self::render_lobby( self::PREVIEW_POSITION, self::PREVIEW_WAIT, true );
		}

		if ( ! Get_In_Line_Options::enabled() ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( function_exists( 'is_favicon' ) && is_favicon() ) {
			return;
		}

		$visitor_id = Get_In_Line_Visitor::ensure_id();

		if ( Get_In_Line_Queue::STATUS_ADMITTED === Get_In_Line_Queue::request_admission( $visitor_id ) ) {
			return;
		}

		$position = Get_In_Line_Queue::position( $visitor_id );
		self::render_lobby( $position, Get_In_Line_Queue::estimated_wait_minutes( $position ), false );
	}

	public static function preview_url() {
		return add_query_arg(
			array(
				'get_in_line_preview' => '1',
				'_wpnonce'            => wp_create_nonce( 'get_in_line_preview' ),
			),
			home_url( '/' )
		);
	}

	private static function is_preview_request() {
		if ( ! isset( $_GET['get_in_line_preview'], $_GET['_wpnonce'] ) ) {
			return false;
		}

		return current_user_can( 'manage_options' )
			&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'get_in_line_preview' );
	}

	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- consumed by the included view.
	private static function render_lobby( $position, $estimated_wait_minutes, $is_preview ) {
		$status_endpoint = rest_url( 'get-in-line/v1/status' );

		status_header( 503 );
		header( 'Retry-After: 30' );
		header( 'X-Robots-Tag: noindex' );
		nocache_headers();

		include GET_IN_LINE_PATH . 'views/lobby.php';
		exit;
	}
}
