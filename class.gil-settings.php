<?php

if (!defined("ABSPATH")) {
    exit(); // Exit if accessed directly
}

if (!class_exists("GET_IN_LINE_Settings")) {
    class GET_IN_LINE_Settings
    {
        public static $options;

        public function __construct()
        {
            self::$options = get_option("get_in_line_options");
            add_action("admin_init", [$this, "admin_init"]);
        }

        public function     admin_init()
        {
            register_setting("gil_group", "get_in_line_options", [
                $this,
                "gil_validate",
            ]);


            add_settings_section(
                "gil_main_section",
                esc_html__("Main settings", "gil"),
                null,
                "gil_page1"
            );

            add_settings_field(
                "gil_enabled",
                esc_html__("Enabled", "gil"),
                [$this, "gil_enabled_callback"],
                "gil_page1",
                "gil_main_section",
                [
                    "label_for" => "gil_enabled",
                ]
            );

            add_settings_field(
                "gil_limit",
                esc_html__("Simultaneous access limit", "gil"),
                [$this, "gil_limit_callback"],
                "gil_page1",
                "gil_main_section",
                [
                    "label_for" => "gil_limit",
                ]
            );

            add_settings_field(
                "gil_expiration",
                esc_html__("Session expiration(minutes)", "gil"),
                [$this, "gil_expiration_callback"],
                "gil_page1",
                "gil_main_section",
                [
                    "label_for" => "gil_expiration",
                ]
            );
        }

        public function gil_enabled_callback($args)
        {
?>
            <input type="checkbox" name="get_in_line_options[gil_enabled]" id="gil_enabled" value="1" <?php
                                                                                                        if (isset(self::$options['gil_enabled'])) {
                                                                                                            checked("1", self::$options['gil_enabled'], true);
                                                                                                        }
                                                                                                        ?> />
            <label for="gil_enabled"><?php esc_html_e('Limit the access to the site?', 'gil'); ?></label>
        <?php
        }

        public function gil_limit_callback($args)
        {
        ?>
            <input type="number" name="get_in_line_options[gil_limit]" id="gil_limit" value="<?php echo esc_attr(isset(self::$options["gil_limit"]))
                                                                                                    ? esc_attr(self::$options["gil_limit"])
                                                                                                    : 500; ?>">
        <?php
        }

        public function gil_expiration_callback($args)
        {
        ?>
            <input type="number" name="get_in_line_options[gil_expiration]" id="gil_expiration" value="<?php echo esc_attr(isset(self::$options["gil_expiration"]))
                                                                                                            ? esc_attr(self::$options["gil_expiration"])
                                                                                                            : 20; ?>">
<?php
        }

        public function gil_validate($input)
        {
            $new_input = [];
            $old_option = get_option('get_in_line_options');
            foreach ($input as $key => $value) {
                switch ($key) {
                    case "gil_limit":
                        if (empty($value)) {
                            add_settings_error(
                                "get_in_line_options",
                                "gil_message",
                                esc_html__(
                                    "The limit field can not be left empty",
                                    "gil"
                                ),
                                "error"
                            );
                            $value = esc_html__(
                                "Please, type some text",
                                "gil"
                            );
                            return $old_option;
                        }
                        if (intval($value) < 1) {
                            add_settings_error(
                                "get_in_line_options",
                                "gil_message",
                                esc_html__(
                                    "The limit field must be greater than 0",
                                    "gil"
                                ),
                                "error"
                            );
                            $value = esc_html__(
                                "Please fix it",
                                "gil"
                            );
                            return $old_option;
                        }
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                    case "gil_expiration":
                        if (empty($value)) {
                            add_settings_error(
                                "get_in_line_options",
                                "gil_message",
                                esc_html__(
                                    "The expiration field can not be left empty",
                                    "gil"
                                ),
                                "error"
                            );
                            $value = esc_html__(
                                "Please, type some text",
                                "gil"
                            );
                            return $old_option;
                        }
                        if (intval($value) < 1) {
                            add_settings_error(
                                "get_in_line_options",
                                "gil_message",
                                esc_html__(
                                    "The expiration field must be greater than 0",
                                    "gil"
                                ),
                                "error"
                            );
                            $value = esc_html__(
                                "Please fix it",
                                "gil"
                            );
                            return $old_option;
                        }
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                    default:
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                }
            }
            return $new_input;
        }
    }
}
