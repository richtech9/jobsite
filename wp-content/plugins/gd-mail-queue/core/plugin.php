<?php

if (!defined('ABSPATH')) exit;

class gdmaq_core_plugin extends d4p_plugin_core {
	public $svg_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDMwMCAzMDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM6c2VyaWY9Imh0dHA6Ly93d3cuc2VyaWYuY29tLyIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoyOyI+CiAgICA8ZyBpZD0icGF0aDQiIHRyYW5zZm9ybT0ibWF0cml4KDQuNjg3NDMsMCwwLDQuNjg3NDMsMTAsMTAuMSkiPgogICAgICAgIDxwYXRoIGQ9Ik0xLjk4NCwwQzAuODc1LDAgMCwwLjg5NiAwLDEuOTg0TDAsNTcuNDNDMCw1OC41MzkgMS4xMzEsNTkuNzEzIDIuMjQsNTkuNzEzTDU3LjM0NCw1OS43MTNDNTguNDUzLDU5LjcxMyA1OS43MTMsNTguNTE4IDU5LjcxMyw1Ny40M0w1OS43MTMsMS45ODRDNTkuNzM0LDAuODk2IDU4LjgzOCwwIDU3LjcyOSwwTDEuOTg0LDBaTTMuNzU0LDEuOTJMNTYuMjU2LDEuOTJDNTcuMzAxLDEuOTIgNTguMTMzLDIuNzUyIDU4LjEzMywzLjc5N0w1OC4xMzMsNTZMNTguMTUyLDU2QzU4LjEzNiw1Ny4wMzkgNTYuOTUzLDU4LjEzMyA1NS45MzYsNTguMTMzTDMuOTg4LDU4LjEzM0MyLjk0Myw1OC4xMzMgMS44NzcsNTcuMDI0IDEuODc3LDU2TDEuODc3LDMuNzk3QzEuODc3LDIuNzUyIDIuNzA5LDEuOTIgMy43NTQsMS45MlpNMjkuODY3LDQuODk2QzI3LjYwNiw0Ljg2MiAyNC4zNTMsNy43NCAyMi43MTMsOC45MzZDMTEuMTQ1LDE3LjMzMSA5LjEzOSwxOC44NzQgNi43MTEsMjAuNzc3QzUuNTgsMjEuNjYzIDQuOTE4LDIzLjAyMiA0LjkxOCwyNC40NTlMNC45MTgsNTAuMTE3QzQuOTE4LDUyLjcwMSA3LjAxMiw1NC43OTUgOS41OTYsNTQuNzk1TDUwLjEzOSw1NC43OTVDNTIuNzIyLDU0Ljc5NSA1NC44MTYsNTIuNzAxIDU0LjgxNiw1MC4xMTdMNTQuODE2LDI0LjQ1OUM1NC44MTYsMjMuMDIyIDU0LjE1NCwyMS42NjMgNTMuMDIzLDIwLjc3N0M1MC41OTcsMTguODc1IDQ4LjU4OCwxNy4zMyAzNy4wMjEsOC45MzZDMzUuMzgzLDcuNzQxIDMyLjEyOCw0Ljg2MiAyOS44NjcsNC44OTZaTTExLjgzLDMwLjYyOUMxMi4wMzEsMzAuNTk1IDEyLjI0NSwzMC42MzkgMTIuNDIyLDMwLjc3QzE0LjY0OSwzMi40MTQgMTcuODI5LDM0LjczOSAyMi43MTMsMzguMjgzQzI0LjM1NSwzOS40ODEgMjcuNjA1LDQyLjM1NCAyOS44NjcsNDIuMzJDMzIuMTI4LDQyLjM1NSAzNS4zODIsMzkuNDc5IDM3LjAyMSwzOC4yODNDNDEuOTA2LDM0LjczOCA0NS4wODYsMzIuNDE0IDQ3LjMxMywzMC43N0M0Ny42NjcsMzAuNTA4IDQ4LjE2NiwzMC41OTQgNDguNDE2LDMwLjk1N0w0OS4zMDEsMzIuMjQyQzQ5LjUzNywzMi41ODYgNDkuNDU3LDMzLjA2MyA0OS4xMjEsMzMuMzExQzQ2Ljg5MSwzNC45NTcgNDMuNzE4LDM3LjI3NyAzOC44NTksNDAuODAzQzM2Ljg4MSw0Mi4yNDUgMzMuMzQ5LDQ1LjQ2MyAyOS44NjcsNDUuNDM5QzI2LjM4NCw0NS40NjMgMjIuODUxLDQyLjI0MyAyMC44NzUsNDAuODAzQzE2LjAxNywzNy4yNzcgMTIuODQ0LDM0Ljk1NyAxMC42MTMsMzMuMzExQzEwLjI3NywzMy4wNjMgMTAuMTk3LDMyLjU4NiAxMC40MzQsMzIuMjQyTDExLjMxOCwzMC45NTdDMTEuNDQzLDMwLjc3NSAxMS42MjksMzAuNjYzIDExLjgzLDMwLjYyOVpNNTguMTU0LDU1Ljk3OUM1OC4xNTQsNTUuOTgzIDU4LjE1Miw1NS45ODggNTguMTUyLDU1Ljk5Mkw1OC4xNTIsNTUuOThMNTguMTU0LDU1Ljk3OVoiIHN0eWxlPSJmaWxsOnJnYigxNTgsMTYzLDE2OCk7ZmlsbC1ydWxlOm5vbnplcm87Ii8+CiAgICA8L2c+Cjwvc3ZnPgo=';

    public $enqueue = true;
    public $cap = 'gd-mail-queue-standard';
    public $plugin = 'gd-mail-queue';

    private $engines = array();
    private $templates = array();

    /** @var d4p_datetime_core */
    public $datetime;

    public function __construct() {
        parent::__construct();

        if (!defined('GDMAQ_HTACCESS_FILE_NAME')) {
            define('GDMAQ_HTACCESS_FILE_NAME', '.htaccess');
        }

        $this->url = GDMAQ_URL;
        $this->datetime = new d4p_datetime_core();
    }

    public function plugins_loaded() {
        parent::plugins_loaded();

        define('GDMAQ_WPV', intval($this->wp_version));
        define('GDMAQ_WPV_MAJOR', substr($this->wp_version, 0, 3));
        define('GDMAQ_WP_VERSION', $this->wp_version_real);

        add_action('gdmaq_run_maintenance', array($this, 'maintenance'));

        do_action('gdmaq_load_settings');

        add_action('gdmaq_load_engine_phpmailer', array($this, 'engine_load_phpmailer'));
        $this->register_engine('phpmailer', 'PHPMailer');

        do_action('gdmaq_register_engines');

        do_action('gdmaq_register_templates');

        gdmaq_external();
        gdmaq_mailer();
        gdmaq_queue();
        gdmaq_htmlfy();
        gdmaq_logger();

        if (gdmaq_settings()->get('queue_pause', 'core')) {
            add_filter('gdmaq_queue_paused', '__return_true');
        }

        if (gdmaq_settings()->get('email_pause', 'core')) {
            add_filter('gdmaq_email_paused', '__return_true');
        }

        do_action('gdmaq_plugin_init');

        if (!wp_next_scheduled('gdmaq_run_maintenance')) {
            $cron_hour = apply_filters('gdmaq_cron_daily_maintenance_job_hour', 4);
            $cron_time = mktime($cron_hour, 0, 0, date('m'), date('d') + 1, date('Y'));

            wp_schedule_event($cron_time, 'daily', 'gdmaq_run_maintenance');
        }
    }

    public function maintenance() {
        if (gdmaq_settings()->get('queue_active', 'cleanup')) {
            $scope = gdmaq_settings()->get('queue_scope', 'cleanup') == 'sent' ? array('sent') : array('sent', 'failed');
            $days = absint(gdmaq_settings()->get('queue_days', 'cleanup'));
            $days = $days < 1 ? 30 : $days;

            gdmaq_db()->queue_cleanup(get_current_blog_id(), $scope, $days);
        }

        if (gdmaq_settings()->get('log_active', 'cleanup')) {
            $days = absint(gdmaq_settings()->get('log_days', 'cleanup'));
            $days = $days < 1 ? 365 : $days;

            gdmaq_db()->email_log_cleanup(get_current_blog_id(), $days);
        }
    }

    public function has_additional_templates() {
        return !empty($this->templates);
    }

    public function get_additional_templates_list() {
        return wp_list_pluck($this->templates, 'label');
    }

    public function get_additional_template_path($name) {
        return isset($this->templates[$name]) ? $this->templates[$name]['path'] : false;
    }

    public function register_engine($name, $label) {
        $this->engines[$name] = $label;
    }

    public function register_template($name, $label, $path) {
        if (file_exists($path)) {
            $this->templates[$name] = array(
                'name' => $name,
                'label' => $label,
                'path' => $path
            );
        }
    }

    public function engine_load_phpmailer() {
        if (!function_exists('gdmaq_engine_sender')) {
            require_once(GDMAQ_PATH.'core/mail/engine.phpmailer.php');
        }
    }

    public function get_list_of_engines() {
        return $this->engines;
    }

    public function get_engine_label($engine) {
        return isset($this->engines[$engine]) ? $this->engines[$engine] : __("Unknown", "gd-mail-queue").' ('.$engine.')';
    }
}
