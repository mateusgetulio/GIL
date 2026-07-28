<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_In_Line_Schema {

	const DB_VERSION_KEY = 'get_in_line_db_version';

	public static function activate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$wpdb->prefix}get_in_line_queue (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				visitor_id char(32) NOT NULL,
				status varchar(10) NOT NULL DEFAULT 'waiting',
				queued_at datetime NOT NULL,
				admitted_at datetime NULL,
				expires_at datetime NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY visitor (visitor_id),
				KEY status_expires (status,expires_at),
				KEY status_queued (status,queued_at)
			) {$charset_collate};"
		);

		add_option( Get_In_Line_Options::OPTION_KEY, Get_In_Line_Options::defaults() );
		update_option( self::DB_VERSION_KEY, GET_IN_LINE_VERSION );

		if ( ! wp_next_scheduled( 'get_in_line_hourly_maintenance' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'get_in_line_hourly_maintenance' );
		}
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'get_in_line_hourly_maintenance' );
	}
}
