<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Gate {

	public static function maybe_gate() {
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

		self::render_lobby( $visitor_id );
	}

	private static function render_lobby( $visitor_id ) {
		$position               = Get_In_Line_Queue::position( $visitor_id );
		$estimated_wait_minutes = Get_In_Line_Queue::estimated_wait_minutes( $position );
		$status_endpoint        = rest_url( 'get-in-line/v1/status' );

		status_header( 503 );
		header( 'Retry-After: 30' );
		header( 'X-Robots-Tag: noindex' );
		nocache_headers();

		include GET_IN_LINE_PATH . 'views/lobby.php';
		exit;
	}
}
