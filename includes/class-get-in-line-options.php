<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Options {

	const OPTION_KEY = 'get_in_line_options';

	public static function defaults() {
		return array(
			'gil_enabled'    => 1,
			'gil_limit'      => 100,
			'gil_expiration' => 20,
		);
	}

	public static function all() {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( self::defaults(), $stored );
	}

	public static function enabled() {
		$options = self::all();
		return ! empty( $options['gil_enabled'] );
	}

	public static function limit() {
		$options = self::all();
		return max( 1, (int) $options['gil_limit'] );
	}

	public static function session_minutes() {
		$options = self::all();
		return max( 1, (int) $options['gil_expiration'] );
	}
}
