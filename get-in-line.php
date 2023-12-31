<?php

/**
 * Plugin Name: Get in Line - GET_IN_LINE
 * Plugin URI: https://www.wordpress.org/plugins/gil
 * Description: Use this plugin if you want to limit the access to the site to a specific number. It creates a virtual queue where access is permited as soon as there are new spots available.
 * Version: 1.0.0
 * Requires at least: 5.6
 * Requires PHP: 7.0
 * Author: Mateus Getulio Vieira
 * Author URI: https://mateusgetulio.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: get-in-line
 * Domain Path: /languages
 */
/*
Get in Line is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Get in Line is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Get in Line. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if (!defined("ABSPATH")) {
    exit(); // Exit if accessed directly
}


if (!class_exists("GET_IN_LINE")) {
    class GET_IN_LINE
    {
        protected $computerId = "";
        public static $options;

        /** –– **\
         * Constructor of the class.
         * @since 1.0.0
         */
        public function __construct()
        {
            self::$options = get_option("get_in_line_options");
            $this->define_constants();
            require_once GET_IN_LINE_PATH . "functions/functions.php";
            require_once GET_IN_LINE_PATH . "class.gil-settings.php";
            $get_in_line_settings = new GET_IN_LINE_Settings();

            $enabled = false;
            if (isset(self::$options["gil_enabled"])) {
                $enabled = self::$options["gil_enabled"];
            }

            // Don't limit the access if it is to the back end or if the plugin is in disabled mode
            if (is_admin()) {
                add_action("admin_menu", [$this, "add_menu"]);
                return true;
            } elseif (($GLOBALS["pagenow"] === "wp-login.php") || ($enabled === false)) {
                return true;
            }

            $computerId = get_in_line_computer_id();

            $this->get_in_line_flush_connections();


            // If access is not allowed, send to the waiting lobby
            if ($this->access_permitted($computerId)) {
                $this->register_access($computerId, true);
            } else {
                $this->register_access($computerId, false);
                require_once GET_IN_LINE_PATH . "views/lobby.php";
            }

            $this->load_textdomain();
        }

        /** –– **\
         * Meant to check which sessions are already expired and remove them from the queue.
         * @since 1.0.0
         */
        public function get_in_line_flush_connections()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . "get_in_line";
            $current_time = date("Y-m-d H:i:s");

            // Clear expired sessions
            $wpdb->query(
                $wpdb->prepare("DELETE FROM %1s WHERE %s > expiration and position = 0", array($table_name, $current_time))
            );

            // Get number os active sessions and calculate the remaining spots to the site
            $new_spots = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) as qt FROM %1s WHERE position = 0 ", array($table_name))
            );


            $new_spots = $new_spots === null ? 0 : intval($new_spots);
            $new_spots = GET_IN_LINE_MAX_CONNECTIONS - $new_spots;

            if ($new_spots > 0) {
                $wpdb->query(

                    $wpdb->prepare("UPDATE %1s
                    SET position = (position - %d)
                    WHERE expiration = %s
                    and position <> 0
                    ", array(
                        $table_name, $new_spots,
                        GET_IN_LINE_VOID_EXPIRATION
                    ))
                );

                // Set expiration time and update it on the DB
                $expiration = new DateTime($current_time);
                $expiration->modify(GET_IN_LINE_EXPIRATION_TIME);
                $expiration = $expiration->format("Y-m-d H:i:s");

                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE %1s
                    SET expiration = %s,
                    position = 0
                    WHERE expiration = %s
                    and position <= 0
                    ",
                        array($table_name, $expiration, GET_IN_LINE_VOID_EXPIRATION)
                    )
                );
            }
        }

        /** –– **\
         * Register the access to the site in the database, both permitted and not permitted types of access.
         * @since 1.0.0
         */
        public function register_access($computerId, $is_allowed = false)
        {
            $session_date = date("Y-m-d H:i:s");
            $position = 0;

            global $wpdb;
            $table_name = $wpdb->prefix . "get_in_line";
            $computer_data = $wpdb->get_row(
                $wpdb->prepare("SELECT position, computer_id FROM %1s WHERE computer_id = %s", array($table_name, $computerId))
            );

            $computer_registered = $computer_data !== null;

            // Check if computer already registered a session or attempt before, if it does and it is not active yet,
            // it needs to add the device to the allowed list
            if ($is_allowed && $computer_registered) {
                $computer_data = get_object_vars($computer_data);

                // If device has an active session, no need to update the record on DB
                if ($computer_data["position"] == 0) {
                    return true;
                }

                $expiration = new DateTime($session_date);
                $expiration->modify(GET_IN_LINE_EXPIRATION_TIME);
                $expiration = $expiration->format("Y-m-d H:i:s");

                $wpdb->update(
                    $table_name,
                    [
                        "position" => $position,
                        "expiration" => $expiration,
                    ],
                    [
                        "computer_id" => $computerId,
                    ],
                    ["%d", "%s "],
                    ["%s"]
                );
            } elseif ($is_allowed && !$computer_registered) {

                // Save the access for the first time
                $expiration = new DateTime($session_date);
                $expiration->modify(GET_IN_LINE_EXPIRATION_TIME);
                $expiration = $expiration->format("Y-m-d H:i:s");

                $wpdb->insert($table_name, [
                    "computer_id" => $computerId,
                    "time" => $session_date,
                    "expiration" => $expiration,
                    "position" => $position,
                ]);
            }

            // Update the device's position in the queue for not allowed access
            if (!$is_allowed && $computer_registered) {
                $db_result = $wpdb->get_row(
                    $wpdb->prepare("SELECT time FROM %1s WHERE computer_id = %s", array($table_name, $computerId))
                );
                $db_result = get_object_vars($db_result);

                $connections = $wpdb->get_var(
                    $wpdb->prepare("SELECT COUNT(*) FROM %1s WHERE position > 0 AND time < '{$db_result["time"]}'", array($table_name))
                );
                $connections = $connections === null ? 0 : intval($connections);
                $position = $connections + 1;

                $wpdb->update(
                    $table_name,
                    [
                        "position" => $position,
                    ],
                    [
                        "computer_id" => $computerId,
                    ],
                    ["%d"],
                    ["%s"]
                );
            } elseif (!$is_allowed && !$computer_registered) {
                $connections = $wpdb->get_var(
                    $wpdb->prepare("SELECT COUNT(*) FROM %1s WHERE position > 0", array($table_name))
                );
                $connections = $connections === null ? 0 : intval($connections);
                $position = $connections + 1;

                $wpdb->insert($table_name, [
                    "computer_id" => $computerId,
                    "time" => $session_date,
                    "expiration" => "null",
                    "position" => $position,
                ]);
            }
        }


        /** –– **\
         * Check if the current device can access the site or not, based on the availability of the plugin.
         * @since 1.0.0
         */
        public function access_permitted($computerId)
        {
            global $wpdb;

            $table_name = $wpdb->prefix . "get_in_line";

            $db_result = $wpdb->get_row(
                $wpdb->prepare("SELECT expiration, position FROM %1s WHERE computer_id = %s", array($table_name, $computerId))
            );
            $connections = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM %1s WHERE position = 0", array($table_name))
            );

            $connections = $connections === null ? 0 : intval($connections);

            // If there's no spot left and the computer isn't registered the access is denied
            if ($db_result === null && $connections > GET_IN_LINE_MAX_CONNECTIONS) {
                return false;
            }

            // If the computer isn't registered and there are spots left access is permitted
            if ($db_result === null && $connections < GET_IN_LINE_MAX_CONNECTIONS) {
                return true;
            }

            // Continue if computer is already registered
            $db_result = get_object_vars($db_result);

            $date_now = new DateTime("now");

            $expiration = new DateTime($db_result["expiration"]);

            $spots_left = GET_IN_LINE_MAX_CONNECTIONS - $connections;

            return $expiration > $date_now ||
                $spots_left >= intval($db_result["position"]);
        }


        /** –– **\
         * Set the constants.
         * @since 1.0.0
         */
        public function define_constants()
        {
            // Path/URL to root of this plugin, with trailing slash.
            define("GET_IN_LINE_PATH", plugin_dir_path(__FILE__));
            define("GET_IN_LINE_URL", plugin_dir_url(__FILE__));
            define("GET_IN_LINE_VERSION", "1.0.0");

            // For comparison purposes only
            define("GET_IN_LINE_VOID_EXPIRATION", "0000-00-00 00:00:00");

            // Max connections, it comes from the options table, default is 100
            if (isset(self::$options["gil_limit"])) {
                define(
                    "GET_IN_LINE_MAX_CONNECTIONS",
                    intval(self::$options["gil_limit"])
                );
            } else {
                define("GET_IN_LINE_MAX_CONNECTIONS", 100);
            }

            // Expiration time in minutes, it comes from the options table, default is 20
            if (isset(self::$options["gil_expiration"])) {
                define(
                    "GET_IN_LINE_EXPIRATION_TIME_RAW",
                    intval(self::$options["gil_expiration"])
                );
                define(
                    "GET_IN_LINE_EXPIRATION_TIME",
                    "+" . self::$options["gil_expiration"] . " minutes"
                );
            } else {
                define("GET_IN_LINE_EXPIRATION_TIME_RAW", 20);
                define("GET_IN_LINE_EXPIRATION_TIME", "+20 minutes");
            }
        }


        /** –– **\
         * Load the language of the texts.
         * @since 1.0.0
         */
        public function load_textdomain()
        {
            load_plugin_textdomain(
                "get-in-line",
                false,
                dirname(plugin_basename(__FILE__)) . "/languages/"
            );
        }

        /** –– **\
         * Add configuration menu to wp-admin.
         * @since 1.0.0
         */
        public function add_menu()
        {
            add_menu_page(
                esc_html__("Get in Line", "gil"),
                "GIL",
                "manage_options",
                "gil_admin",
                [$this, "gil_settings_page"],
                "dashicons-universal-access"
            );
        }


        /** –– **\
         * Create settings page.
         * @since 1.0.0
         */
        public function gil_settings_page()
        {
            if (!current_user_can("manage_options")) {
                return;
            }
            $settings_errors = get_settings_errors();
            if (isset($_GET["settings-updated"]) && ($settings_errors[0]['type']) == 'success') {

                add_settings_error(
                    "get_in_line_options",
                    "gil_message",
                    esc_html__("Settings Saved", "gil"),
                    "success"
                );
            } elseif (isset($_GET["clear-queue"])) {
                if (get_in_line_clear_queue() == true) {
                    add_settings_error(
                        "get_in_line_options",
                        "gil_message",
                        esc_html__("The queue has been cleared!", "gil"),
                        "success"
                    );
                } else {
                    add_settings_error(
                        "get_in_line_options",
                        "gil_message",
                        esc_html__("Queue was already empty!", "gil"),
                        "warning"
                    );
                }
            }

            settings_errors("get_in_line_options");

            require GET_IN_LINE_PATH . "views/settings-page.php";
        }

        /** –– **\
         * Activate the plugin.
         * @since 1.0.0
         */
        public static function activate()
        {
            update_option("rewrite_rules", "");

            global $wpdb;

            $table_name = $wpdb->prefix . "get_in_line";

            $gil_db_version = get_option("get_in_line_db_version");

            if (empty($gil_db_version)) {
                $query = $wpdb->prepare("
                    CREATE TABLE %s (
                        session_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        computer_id varchar(255) NOT NULL DEFAULT '0',                        
                        time timestamp,
                        expiration timestamp,
                        position int,
                        PRIMARY KEY  (session_id))
                        ENGINE=InnoDB DEFAULT CHARSET=utf8;", array($table_name));

                require_once ABSPATH . "wp-admin/includes/upgrade.php";
                dbDelta($query);

                $gil_db_version = "1.0";
                add_option("get_in_line_db_version", $gil_db_version);

                $default_options = array(
                    "gil_enabled" => true,
                    "gil_limit" => 100,
                    "gil_expiration" => 25

                );

                if (get_option('get_in_line_options') == null) {
                    add_option("get_in_line_options", $gil_db_version);
                }

                update_option('get_in_line_options', $default_options);
            }
        }

        /** –– **\
         * Deactivation method.
         * @since 1.0.0
         */
        public static function deactivate()
        {
            flush_rewrite_rules();
        }

        /** –– **\
         * Uninstall method.
         * @since 1.0.0
         */
        public static function uninstall()
        {
            delete_option("get_in_line_db_version");
            delete_option("get_in_line_options");

            global $wpdb;
            $table_name = $wpdb->prefix . "get_in_line";
            $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %1s", array($table_name)));
        }
    }
}

// Plugin Instantiation
if (class_exists("GET_IN_LINE")) {
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, ["GET_IN_LINE", "activate"]);
    register_deactivation_hook(__FILE__, ["GET_IN_LINE", "deactivate"]);
    register_uninstall_hook(__FILE__, ["GET_IN_LINE", "uninstall"]);

    // Instantiate the plugin class
    $gil = new GET_IN_LINE();
}
