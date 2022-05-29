<?php
/*
  * current-php-code 2020-Oct-31
  * internal-call
  * input-sanitized :
  */
class FreelinguistDebugging {
    protected static $b_debug = false;


    const LOG_DEBUG = 0;
    const LOG_INFO = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;

    const TESTING_DEBUG_LEVEL = self::LOG_WARNING; //code-notes set to debug level needed when working on code, always to warning for master commits

    protected static $n_debug_level = self::LOG_WARNING; //how much information to output to screen or log

    public static function turn_on_debugging($level=self::LOG_ERROR) {
        static::$b_debug = true;
        static::$n_debug_level = $level;
    }

    public static function turn_off_debugging() {static::$b_debug = false;}

    public static function is_at_level($debug_level) {
        if (static::$n_debug_level >= $debug_level) {return true;}
        return false;
    }

    protected static function log($log_level,$prefix,$data = FREELINGUIST_WILL_LOG_NO_VALUE,$b_dump = false) {
//        will_send_to_error_log('prelog',[
//            '$log_level'=>$log_level,
//            '$prefix' => $prefix,
//            'debug_on'=> static::$b_debug? 'yes':'no',
//            'level settings' => static::$n_debug_level
//        ]);
        if (static::$b_debug && $log_level >= static::$n_debug_level) {
            $final_prefix = static::get_log_prefix(). ': '. $prefix;
            return will_send_to_error_log($final_prefix,$data,false,false,false);
        }
        return '';
    }

    protected static function get_log_time() {
        return date("D M d, Y G:i:s");
    }

    protected static function get_log_prefix() {
        return static::class . ' '. static::get_log_time() . ' ';
    }

}