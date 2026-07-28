<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}get_in_line_queue" );

delete_option( 'get_in_line_options' );
delete_option( 'get_in_line_db_version' );
