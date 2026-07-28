<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Rest {

	public static function register_routes() {
		register_rest_route(
			'get-in-line/v1',
			'/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'status' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'get-in-line/v1',
			'/admin/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'admin_status' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' );
	}

	public static function admin_status() {
		Get_In_Line_Queue::maintain();

		$response = new WP_REST_Response( Get_In_Line_Queue::counts() );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	public static function status() {
		Get_In_Line_Queue::maintain();

		$response_data = self::status_payload();

		$response = new WP_REST_Response( $response_data );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	private static function status_payload() {
		$visitor_id = Get_In_Line_Visitor::current_id();

		if ( null === $visitor_id ) {
			return array( 'status' => 'unknown' );
		}

		if ( Get_In_Line_Queue::is_admitted( $visitor_id ) ) {
			return array( 'status' => 'admitted' );
		}

		$position = Get_In_Line_Queue::position( $visitor_id );

		if ( $position < 1 ) {
			return array( 'status' => 'unknown' );
		}

		return array(
			'status'                 => 'waiting',
			'position'               => $position,
			'estimated_wait_minutes' => Get_In_Line_Queue::estimated_wait_minutes( $position ),
		);
	}
}
