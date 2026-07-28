<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Visitor {

	const COOKIE = 'get_in_line_visitor';

	public static function current_id() {
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return null;
		}

		$raw   = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
		$parts = explode( '.', $raw );

		if ( 2 !== count( $parts ) ) {
			return null;
		}

		list( $id, $signature ) = $parts;

		if ( ! preg_match( '/^[a-f0-9]{32}$/', $id ) ) {
			return null;
		}

		if ( ! hash_equals( self::sign( $id ), $signature ) ) {
			return null;
		}

		return $id;
	}

	public static function ensure_id() {
		$id = self::current_id();
		if ( null !== $id ) {
			return $id;
		}

		$id    = bin2hex( random_bytes( 16 ) );
		$value = $id . '.' . self::sign( $id );

		setcookie(
			self::COOKIE,
			$value,
			array(
				'expires'  => time() + DAY_IN_SECONDS,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
		$_COOKIE[ self::COOKIE ] = $value;

		return $id;
	}

	private static function sign( $id ) {
		return hash_hmac( 'sha256', $id, wp_salt( 'auth' ) );
	}
}
