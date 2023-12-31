<?php

if (!defined("ABSPATH")) {
    exit(); // Exit if accessed directly
}

/**
 * Clear state of the queue for those with active sessions, don't touch devices in the line 
 */
if (!function_exists("get_in_line_clear_queue")) {
    function get_in_line_clear_queue()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "get_in_line";

        return $wpdb->query(
            $wpdb->prepare("DELETE FROM %1s
            WHERE position = 0", array($table_name))
        );
    }
}

/**
 * Check on session if the get_in_line_computer_id is stored 
 */
if (!function_exists("get_in_line_computer_id")) {
    function get_in_line_computer_id()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if ($_SESSION["get_in_line_computer_id"] === null) {
            $_SESSION["get_in_line_computer_id"] = md5(GET_IN_LINE_GUID());
        }
        return 'test12';
        return $_SESSION["get_in_line_computer_id"];
    }
}

/**
 * Unique ID per device and browser
 */
function GET_IN_LINE_GUID()
{
    if (function_exists("com_create_guid") === true) {
        return trim(com_create_guid(), "{}");
    }

    return sprintf(
        "%04X%04X-%04X-%04X-%04X-%04X%04X%04X",
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(16384, 20479),
        mt_rand(32768, 49151),
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(0, 65535)
    );
}

/**
 * Retrieve the current's device position on the queue
 */
if (!function_exists("get_in_line_position_in_queue")) {
    function get_in_line_position_in_queue()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "get_in_line";

        $get_in_line_computer_id = get_in_line_computer_id();
        $computer_data = $wpdb->get_row(
            $wpdb->prepare("SELECT position FROM %1s WHERE computer_id = %s", array($table_name, $get_in_line_computer_id))
        );
        $position =
            $computer_data === null ? 0 : intval($computer_data->position);
        return $position;
    }
}

/**
 * Retrieve the current waiting time estimation
 */
if (!function_exists("get_in_line_waiting_time")) {
    function get_in_line_waiting_time()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "get_in_line";

        $get_in_line_computer_id = get_in_line_computer_id();
        $computer_data = $wpdb->get_row(
            $wpdb->prepare("SELECT position FROM %1s WHERE computer_id = %s", array($table_name, $get_in_line_computer_id))
        );
        $position =
            $computer_data === null ? 0 : intval($computer_data->position);
        $position = 10;
        $time_left =
            intval($position / GET_IN_LINE_MAX_CONNECTIONS) * GET_IN_LINE_EXPIRATION_TIME_RAW;

        if ($time_left > 0) {
            return $time_left;
        } else {
            return GET_IN_LINE_EXPIRATION_TIME_RAW;
        }
    }
}
