<?php


class FreelinguistCronBase {
    const OPTION_NAME = 'defined-in-child';
    const ACTION_NAME = 'defined-in-child';
    const ACTION_GROUP_NAME = 'defined-in-child';
    const STOP_ACTION_NAME = 'defined-in-child';
    const B_DEBUG = false;
    const B_START_IMMEDIATE_ACTION = true;
    const WORD_FOR_PAGE = 'page';

    const MAX_LOG_SIZE = 200;//how many entries before trimming to this number


    protected static function get_loop_value() {
        $option = get_option(static::OPTION_NAME, []);
        if (empty($option)) {return false;}
        if (!is_array($option)) {return false;}
        if (!isset($option['loop_number'])) {return false;}
        $loop_number = (int)$option['loop_number'];
        return $loop_number;
    }

    protected static function set_loop_value($loop_number) {
        $option = get_option(static::OPTION_NAME, []);
        if (empty($option)) {$option = [];}
        if (!is_array($option)) {$option = [];}
        $option['loop_number'] = (int)$loop_number;
        update_option(static::OPTION_NAME,$option,false);
    }

    protected static function get_timer_seconds_value() {
        $option = get_option(static::OPTION_NAME, []);
        if (empty($option)) {return false;}
        if (!is_array($option)) {return false;}
        if (!isset($option['timer_seconds'])) {return false;}
        $loop_number = (int)$option['timer_seconds'];
        return $loop_number;
    }

    protected static function set_timer_seconds_value($seconds) {
        $option = get_option(static::OPTION_NAME, []);
        if (empty($option)) {$option = [];}
        if (!is_array($option)) {$option = [];}
        $option['timer_seconds'] = (int)$seconds;
        update_option(static::OPTION_NAME,$option,false);
    }

    protected static function get_running_value() {
        global $wpdb;

        $key = esc_sql(static::STOP_ACTION_NAME);
        $sql = "SELECT option_value FROM wp_options WHERE option_name = '$key'";
        $res = $wpdb->get_results($sql);
        if (empty($res)) {return false;}
        $flag = (int)$res[0]->option_value;
        if ($flag) {return true;}
        return false;

    }

    protected static function set_running_value($b_run) {
        global $wpdb;

        if ($b_run) {
            $flag = 1;
        } else {
            $flag = 0;
        }

        $key = esc_sql(static::STOP_ACTION_NAME);
        $sql = "SELECT option_value FROM wp_options WHERE option_name = '$key'";
        $res = $wpdb->get_results($sql);
        if (empty($res)) {
            $sql = "INSERT INTO wp_options(option_name,option_value,autoload) VALUES ('$key','$flag','no')";
        } else {
            $sql = "UPDATE wp_options SET option_value = '$flag',wp_options.autoload = 'no' WHERE  option_name = '$key'";
        }
        $wpdb->query($sql);
    }

    protected static function get_log_value($b_debug_me = false) {
        $option = get_option(static::OPTION_NAME, []);
        if ($b_debug_me) {
            will_send_to_error_log('cron log debugging name',static::OPTION_NAME);
            will_send_to_error_log('cron log debugging raw',$option);
        }
        if (empty($option)) {return false;}
        if (!is_array($option)) {return false;}
        if (!isset($option['log'])) {return false;}
        return $option['log'];
    }

    public static function set_log($log) {


        if (is_array($log)) {
            $log = static::truncate_array_to_max_length(static::MAX_LOG_SIZE, $log);
        } else {

            will_send_to_error_log("invalid log",$log,false,true);
            $log = [];
            $header = static::get_log_prefix();
            $log[] = "$header Could not set earlier log, invalid format, see debug.log";

        }

        $option = get_option(static::OPTION_NAME, []);
        if (empty($option)) {$option = [];}
        if (!is_array($option)) {$option = [];}
        $option['log'] = $log;
        update_option(static::OPTION_NAME,$option,false);
    }

    public static function is_running() {
        return static::get_running_value();
    }

    public static function can_do_step() {
        if (static::is_running()) {return true;}
        return false;
    }

    public static function stop() {
        static::set_running_value(false);
        $line = static::get_log_prefix() . 'Stopping Run, next iteration will be not done';
        $older_log = static::get_log_value();
        if (empty($older_log)) {
            $older_log = [];
        }
        $older_log[] = $line;
        static::set_log($older_log);
    }

    public static function run() {
        $is_running = static::get_running_value();
        if ($is_running) {
            throw new RuntimeException("Cannot start a process that is already started");
        }
        $page = 0;
        static::set_loop_value($page);
        static::set_running_value(true);
        $init = static::get_log_prefix() . 'Starting Run, adding scheduled action';
        static::set_log([$init]);
        if (static::B_START_IMMEDIATE_ACTION) {
            as_enqueue_async_action( static::ACTION_NAME, [$page] );
        }

    }

    public static function resume() {

        $page = static::get_loop();
        if ($page < 0) {
            throw new RuntimeException("Cannot resume a process that is not running");
        }
        static::set_running_value(true);
        $line = static::get_log_prefix() . 'Resuming Run, adding scheduled action for  '. static::WORD_FOR_PAGE . ' '.$page;
        $older_log = static::get_log_value();
        if (empty($older_log)) {
            $older_log = [];
        }
        $older_log[] = $line;
        static::set_log($older_log);
        if (static::B_START_IMMEDIATE_ACTION) {
            as_enqueue_async_action( static::ACTION_NAME, [$page] );
        }

    }

    public static function get_loop() {
        $what =  static::get_loop_value();
        if ($what === false) {
            throw new RuntimeException("Loop not set in the option of " . static::OPTION_NAME);
        }
        return $what;
    }

    public static function set_next_loop($i) {
        if ($i != intval($i)) { //comparison between '1' and 1 will true
            throw new InvalidArgumentException("Loop can only be an integer");
        }

        static::set_loop_value((int)$i);

    }


    public static function get_timer_seconds() {
        $what =  static::get_timer_seconds_value();
        if ($what === false) {
            throw new RuntimeException("Timer Seconds not set in the option of " . static::OPTION_NAME);
        }
        return $what;
    }

    public static function set_timer_seconds($i) {
        if ($i != intval($i)) { //comparison between '1' and 1 will true
            throw new InvalidArgumentException("Seconds can only be an integer");
        }
        static::set_timer_seconds_value((int)$i);

    }

    public static function get_log() {
        $what =  static::get_log_value();
        if ($what === false) {
            throw new RuntimeException("Log not set in the option of " . static::OPTION_NAME);
        }
        return $what;
    }

    public static function get_last_n_logs($n,$b_debug_me = false) {
        $what =  static::get_log_value($b_debug_me);
        if (empty($what) || (!is_array($what))) {
            return [];
        }
        $reversed = array_reverse($what);
        $ret = [];
        for($i = 0; $i < $n && ($i < count($reversed)); $i++) {
            $ret[] = $reversed[$i];
        }
        return $ret;
    }

    public static function set_up_hook() {
        $our_class_name = static::class;
        add_action( static::ACTION_NAME, [$our_class_name,'main'],10,1 );
    }

    public static function main($extra_command) {
        will_do_nothing($extra_command);
        will_send_to_error_log("Called cron task without overwriting its main function!",static::class);
    }

    public static function get_debug_string_command() {

        $debug_string_command = FREELINGUIST_WILL_RET_STRING;
        if (static::B_DEBUG) {
            $debug_string_command = true;
        }
        return $debug_string_command;
    }

    public static function get_log_time() {
        return date("D M d, Y G:i:s");
    }

    public static function get_log_prefix() {
        return static::class . ' '. static::get_log_time() . ' ';
    }

    protected static function truncate_array_to_max_length($max_length, $arr) {

        $last_index_to_remove = count($arr) - $max_length;
        if ($last_index_to_remove <= 0) {return $arr;}
        array_splice($arr, 0, $last_index_to_remove);
        return $arr;
    }
}
