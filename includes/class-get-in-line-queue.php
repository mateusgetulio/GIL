<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Queue {

	const STATUS_ADMITTED = 'admitted';
	const STATUS_WAITING  = 'waiting';

	public static function request_admission( $visitor_id ) {
		if ( self::is_admitted( $visitor_id ) ) {
			return self::STATUS_ADMITTED;
		}

		if ( ! self::acquire_lock( 2 ) ) {
			return self::STATUS_WAITING;
		}

		try {
			self::reap_and_promote();

			if ( self::is_admitted( $visitor_id ) ) {
				return self::STATUS_ADMITTED;
			}

			if ( self::admitted_count() < Get_In_Line_Options::limit() ) {
				self::admit( $visitor_id );
				return self::STATUS_ADMITTED;
			}

			self::enqueue( $visitor_id );
			return self::STATUS_WAITING;
		} finally {
			self::release_lock();
		}
	}

	public static function maintain() {
		if ( get_transient( 'get_in_line_maintenance_ran' ) ) {
			return;
		}
		set_transient( 'get_in_line_maintenance_ran', 1, 5 );

		if ( ! self::acquire_lock( 1 ) ) {
			return;
		}

		try {
			self::reap_and_promote();
		} finally {
			self::release_lock();
		}
	}

	public static function is_admitted( $visitor_id ) {
		global $wpdb;

		$status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT status FROM {$wpdb->prefix}get_in_line_queue WHERE visitor_id = %s AND status = %s AND expires_at > %s",
				$visitor_id,
				self::STATUS_ADMITTED,
				self::now()
			)
		);

		return null !== $status;
	}

	public static function position( $visitor_id ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, queued_at FROM {$wpdb->prefix}get_in_line_queue WHERE visitor_id = %s AND status = %s",
				$visitor_id,
				self::STATUS_WAITING
			)
		);

		if ( null === $row ) {
			return 0;
		}

		$ahead = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}get_in_line_queue
				WHERE status = %s AND ( queued_at < %s OR ( queued_at = %s AND id < %d ) )",
				self::STATUS_WAITING,
				$row->queued_at,
				$row->queued_at,
				$row->id
			)
		);

		return $ahead + 1;
	}

	public static function estimated_wait_minutes( $position ) {
		if ( $position < 1 ) {
			return 0;
		}
		return (int) ceil( $position / Get_In_Line_Options::limit() ) * Get_In_Line_Options::session_minutes();
	}

	public static function counts() {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT status, COUNT(*) AS total FROM {$wpdb->prefix}get_in_line_queue GROUP BY status",
			OBJECT_K
		);

		return array(
			'admitted' => isset( $rows[ self::STATUS_ADMITTED ] ) ? (int) $rows[ self::STATUS_ADMITTED ]->total : 0,
			'waiting'  => isset( $rows[ self::STATUS_WAITING ] ) ? (int) $rows[ self::STATUS_WAITING ]->total : 0,
		);
	}

	public static function clear() {
		global $wpdb;

		return false !== $wpdb->query( "DELETE FROM {$wpdb->prefix}get_in_line_queue" );
	}

	private static function reap_and_promote() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}get_in_line_queue WHERE status = %s AND expires_at <= %s",
				self::STATUS_ADMITTED,
				self::now()
			)
		);

		$free_slots = Get_In_Line_Options::limit() - self::admitted_count();
		if ( $free_slots < 1 ) {
			return;
		}

		$promotable = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT visitor_id FROM {$wpdb->prefix}get_in_line_queue WHERE status = %s ORDER BY queued_at ASC, id ASC LIMIT %d",
				self::STATUS_WAITING,
				$free_slots
			)
		);

		foreach ( $promotable as $visitor_id ) {
			self::admit( $visitor_id );
		}
	}

	private static function admitted_count() {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}get_in_line_queue WHERE status = %s",
				self::STATUS_ADMITTED
			)
		);
	}

	private static function admit( $visitor_id ) {
		global $wpdb;

		$now        = self::now();
		$expires_at = gmdate( 'Y-m-d H:i:s', time() + Get_In_Line_Options::session_minutes() * MINUTE_IN_SECONDS );

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}get_in_line_queue (visitor_id, status, queued_at, admitted_at, expires_at)
				VALUES (%s, %s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE status = VALUES(status), admitted_at = VALUES(admitted_at), expires_at = VALUES(expires_at)",
				$visitor_id,
				self::STATUS_ADMITTED,
				$now,
				$now,
				$expires_at
			)
		);
	}

	private static function enqueue( $visitor_id ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$wpdb->prefix}get_in_line_queue (visitor_id, status, queued_at)
				VALUES (%s, %s, %s)",
				$visitor_id,
				self::STATUS_WAITING,
				self::now()
			)
		);
	}

	private static function acquire_lock( $timeout ) {
		global $wpdb;

		return '1' === $wpdb->get_var(
			$wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', self::lock_name(), $timeout )
		);
	}

	private static function release_lock() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', self::lock_name() )
		);
	}

	private static function lock_name() {
		global $wpdb;

		return 'get_in_line_admission_' . $wpdb->prefix;
	}

	private static function now() {
		return gmdate( 'Y-m-d H:i:s' );
	}
}
