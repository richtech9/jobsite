<?php

/*
  * current-php-code 2020-Oct-31
  * internal-call
  * input-sanitized :
  */

/**
 * Class FreelinguistDebugFramework
 * For recording ajax and templates for development and debugging
 */
class FreelinguistDebugFramework extends FreelinguistDebugging {
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    public static function note($what,$thing = FREELINGUIST_WILL_LOG_NO_VALUE) {

        static::log(static::LOG_DEBUG,$what,$thing);
    }
}

FreelinguistDebugFramework::turn_on_debugging(FreelinguistDebugging::TESTING_DEBUG_LEVEL);