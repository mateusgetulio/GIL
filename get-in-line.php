<?php
/**
 * Plugin Name: GIL Waiting Room
 * Plugin URI: https://github.com/mateusgetulio/GIL
 * Description: A virtual waiting room for WordPress. Caps concurrent visitors at a configurable limit and places everyone else in a FIFO queue with live position updates.
 * Version: 2.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: Mateus Getulio Vieira
 * Author URI: https://mateusgetulio.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: gil-waiting-room
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GET_IN_LINE_VERSION', '2.0.0' );
define( 'GET_IN_LINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'GET_IN_LINE_URL', plugin_dir_url( __FILE__ ) );

require_once GET_IN_LINE_PATH . 'includes/class-get-in-line-options.php';
require_once GET_IN_LINE_PATH . 'includes/class-get-in-line-schema.php';
require_once GET_IN_LINE_PATH . 'includes/class-get-in-line-visitor.php';
require_once GET_IN_LINE_PATH . 'includes/class-get-in-line-queue.php';
require_once GET_IN_LINE_PATH . 'includes/class-get-in-line-gate.php';
require_once GET_IN_LINE_PATH . 'includes/class-get-in-line-rest.php';
require_once GET_IN_LINE_PATH . 'includes/class-get-in-line-settings.php';

register_activation_hook( __FILE__, array( 'Get_In_Line_Schema', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Get_In_Line_Schema', 'deactivate' ) );

add_action( 'plugins_loaded', 'get_in_line_boot' );

function get_in_line_boot() {
	add_action( 'template_redirect', array( 'Get_In_Line_Gate', 'maybe_gate' ), 1 );
	add_action( 'rest_api_init', array( 'Get_In_Line_Rest', 'register_routes' ) );
	add_action( 'get_in_line_hourly_maintenance', array( 'Get_In_Line_Queue', 'maintain' ) );

	if ( is_admin() ) {
		new Get_In_Line_Settings();
	}
}
