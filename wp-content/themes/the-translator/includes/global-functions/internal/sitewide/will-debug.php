<?php

function will_dump($pre,$someVar='no-dumping',$b_debug = true,$b_print_text = false ) {
    if (!$b_debug) {return;}
    if ($b_print_text) {
        if ( is_array($someVar) || is_object($someVar)) {
            foreach ($someVar as $key => $val) {
                print "$key => $val\n";
            }
        } else {
            print $someVar;
        }

    } else {
        $result = '';
        if ($someVar !== 'no-dumping') {
            ob_start();
            var_dump($someVar);
            $result = ob_get_clean();
        }

        print "<pre><b>$pre </b> " . $result . "</pre>";
    }
}

/**
 * @param $what
 * @param bool $b_print_js_script
 * @param bool $b_to_log
 * @param bool $b_is_error
 * @param string $pre ,
 */
function will_log_in_wp_log_and_js_console($what, $b_print_js_script=true,$b_to_log= true, $b_is_error = true,$pre = '') {
    if ($b_to_log) {will_send_to_error_log($pre,$what);}
    if(!$b_print_js_script) {return;}
    print "<script>";
    if ($pre) {
        $pre = str_replace("'","&apos;",$pre);
        if ($b_is_error) {
            print "console.error('$pre');";
        } else {
            print "console.debug('$pre');";
        }
    }
    if ($what) {
        $json = json_encode($what,JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS);
        $json = str_replace('\\','\\\\',$json);
    } else {
        if (is_null($what)) { $json = 'null';}
        else if(is_numeric($what)) { $json = $what;}
        else if($what === false ) { $json = 'false';}
        else {
            $json = '';
        }
    }
    if ($json) {
        if ($b_is_error) {
            print "console.error(JSON.parse('$json'));";
        } else {
            print "console.debug(JSON.parse('$json'));";
        }
    } else {
        if ($b_is_error) {
            print "console.error('empty string');";
        } else {
            print "console.debug('empty string');";
        }
    }

    print "</script>";
}

/**
 * @author  https://stackoverflow.com/questions/1057572/how-can-i-get-a-hex-dump-of-a-string-in-php
 * @param $data
 * @param string $newline
 */
function will_hex_dump($data, $newline="\n")
{
    static $from = '';
    static $to = '';

    static $width = 16; # number of bytes per line

    static $pad = '.'; # padding for non-visible characters

    if ($from==='')
    {
        for ($i=0; $i<=0xFF; $i++)
        {
            $from .= chr($i);
            $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
        }
    }

    $hex = str_split(bin2hex($data), $width*2);
    $chars = str_split(strtr($data, $from, $to), $width);

    $offset = 0;
    foreach ($hex as $i => $line)
    {
        echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
        $offset += $width;
    }
}

function will_do_nothing($arg ,$b_send_to_log= false,$s_add_file_here = '',$n_add_line_here='') {
    if ($b_send_to_log) {
        will_send_to_error_log("doing nothing $s_add_file_here $n_add_line_here",$arg);
    }
}

define('FREELINGUIST_WILL_LOG_NO_VALUE','no-what-here');
define('FREELINGUIST_WILL_RET_STRING','return-string');
//code-notes will_send_to_error_log is used for debugging
/**
 * @param $pre
 * @param mixed $what , will ignore empty strings and FREELINGUIST_WILL_LOG_NO_VALUE
 * @param bool|string $b_override_wp_debug if string of FREELINGUIST_WILL_RET_STRING will return only
 * @param bool $b_var_dump_instead_of_printr
 * @param bool $b_add_string_hex_dump
 * @param bool $b_stack_trace
 * @return string
 */
function will_send_to_error_log($pre,$what=FREELINGUIST_WILL_LOG_NO_VALUE, $b_override_wp_debug = false,
                                $b_var_dump_instead_of_printr = false,$b_add_string_hex_dump=false,$b_stack_trace=false) {
    $out = [];
    if ($pre) {
        $out[] = $pre;
    }
    if (($what !== FREELINGUIST_WILL_LOG_NO_VALUE) && ($what !== '') ) {
        if ($b_var_dump_instead_of_printr) {
            ob_start();
            var_dump($what);
            $out[] = ob_get_clean();
        } else {
            $out[] = print_r($what,true);
        }
    }
    if ($b_add_string_hex_dump && is_string($what)) {
        ob_start();
        will_hex_dump($what);
        $out[] = ob_get_clean();
    }

    if ($b_stack_trace) {
        $e = new Exception;
        $out[] = "--Stack Trace--";
        $out[] = var_export($e->getTraceAsString(), true);
    }

    $out_string = '';
    if (!empty($out)) {
        $out_string = implode("\n",$out);
    }

    if (WP_DEBUG || $b_override_wp_debug) {
        if ($b_override_wp_debug !== FREELINGUIST_WILL_RET_STRING) {
            if ($out_string) {
                error_log($out_string);
            }
        }

    }
    return $out_string;
}

function will_send_to_error_log_and_array(&$log,$pre,$what='no-what-here',$b_write_to_log = false) {

    $debug_string_command = FREELINGUIST_WILL_RET_STRING;
    if ($b_write_to_log) {
        $debug_string_command = true;
    }
    $sent_string = will_send_to_error_log($pre,$what,$debug_string_command);
    if (empty($log)) {$log=[];}
    if ($sent_string) {
        $log[] = $sent_string;
    }
}

$da_temp_timer = 0;
function time_dat_thing($what) {
    global $da_temp_timer;
    $current = (int) round(microtime( true) * 1000);
    if ($da_temp_timer) {
        $diff = $current - $da_temp_timer;
        $da_temp_timer = $current;
        $out =  "$what: $current  [$diff]";
    } else {
        $da_temp_timer = $current;
        $out =  "$what: $current  [start]";
    }
    will_send_to_error_log($what,$out,true);
    return $out;
}

function flagged_time_dat_thing($what) {
    global $will_log_with_dat_flag;
    if (! isset($will_log_with_dat_flag) || empty($will_log_with_dat_flag)) {return;}
    time_dat_thing($what);
}

/**
 * @param wpdb $wpdb
 * @param string $pre
 * @throws RuntimeException
 */
function will_throw_on_wpdb_error($wpdb,$pre='') {
    if ($wpdb->last_error !== '') {
        if ($pre) {
            $pre .= "\n";
        }
        throw new RuntimeException($pre.'wp db error: '. $wpdb->last_error . "\n SQL was\n".$wpdb->last_query. "\n");
    }
}

function will_get_last_id($wpdb, $message) {
    will_throw_on_wpdb_error($wpdb);
    $last = $wpdb->insert_id;
    if (empty($last) || ($wpdb->last_error !== '') ) {
        if (empty($message)) {
            $message = "cannot get insert id: ";
        }
        if ($wpdb->last_error !== '') {
            if ($message) {
                $message .= "\n";
            }
            throw new RuntimeException($message.'wp db error: '. $wpdb->last_error . "\n SQL was\n".$wpdb->last_query. "\n");
        } else {
            throw new RuntimeException($message);
        }

    }
    return $last;
}

/**
 * @param wpdb $wpdb
 * @param string $extra
 */
function will_log_on_wpdb_error($wpdb, $extra = '') {
    if ($wpdb->last_error !== '') {
        will_send_to_error_log($extra . ' WP DB Error AND SQL: ',[
            $wpdb->last_error,$wpdb->last_query]
        );
    }
}

function will_print_admin_notice($msg,$class='notice-success') {
    echo '<div class="notice '.$class.' is-dismissible"><p>'.$msg.'</p></div>';
}

/**
 * @param string $string_date expects yyyy-mm-dd ,
 * @return string
 *  if its empty will return string for date one month from now in yyyy-mm-dd
 *  if it is not a valid format will return string for date one month from now in yyyy-mm-dd
 *  if is a valid string and valid date range will not change the string
 *  if it is a valid string and invalid date range will return string for date one month from now in yyyy-mm-dd
 */
function will_validate_string_date_or_make_future($string_date) {

    /*
     * current-php-code 2020-Oct-16
     * internal-call
     * input-sanitized :
     */

    if (empty($string_date)) {
        $one_month_from_now = date('Y-m-d', strtotime("+30 days"));
        return $one_month_from_now;
    }
    //is valid format ?
    $regex = '/^(19|20)[0-9]{2}-(0?[1-9]|1[012])-(0?[1-9]|1[0-9]|2[0-9]|3[01])$/';
    $b_valid_pattern = preg_match($regex,$string_date,$matches);
    if ($b_valid_pattern === false) {
        will_send_to_error_log("Regex error in will_validate_string_date_or_make_future",[
           'regex' => $regex,
           'target' => $string_date,
            'error' => array_flip(get_defined_constants(true)['pcre'])[preg_last_error()],
            'error_code' => preg_last_error()
        ],false,true);
        return $string_date;
    } elseif ($b_valid_pattern === 0) {
        $one_month_from_now = date('Y-m-d', strtotime("+30 days"));
        return $one_month_from_now;
    } else {
        //check to see if invalid date range
        $d = DateTime::createFromFormat('Y-m-d', $string_date);
        if ($d) {
            return $string_date;
        } else {
            //return now + 1 month
            $one_month_from_now = date('Y-m-d', strtotime("+30 days"));
            return $one_month_from_now;

        }
    }

}
//
function will_get_one_dimensional_array_or_throw($mixed, $b_cast_to_int = false,$debug_name='') {
    $ret = [];
    if (is_array($mixed)) {
        foreach ($mixed as $favc_key => $favc_val) {
            if (is_array($favc_val)) {throw new LogicException("Data has subarray will_get_array_or_throw @ :: $debug_name");}
            if (is_object($favc_val)) {throw new LogicException("Data has subarray will_get_array_or_throw @ :: $debug_name");}
            $favc_val = trim($favc_val);
            if ($b_cast_to_int) {
                $ret[] = (int)$favc_val;
            } else {
                $ret[] = $favc_val;
            }

        }
    } else {
        if (is_string($mixed)) {
            $maybe_archived = is_serialized($mixed);
            if ($maybe_archived) {
                $perhaps_array = maybe_unserialize($mixed);
                if (!is_array($perhaps_array)) {
                    {throw new LogicException("Serialized Data is not array. will_get_array_or_throw @ $debug_name");}
                }
                foreach ($perhaps_array as $favc_key => $favc_val) {
                    if (is_array($favc_val)) {throw new LogicException("Serialized Data has subarray will_get_array_or_throw @ :: $debug_name");}
                    if (is_object($favc_val)) {throw new LogicException("Serialized Data has subarray will_get_array_or_throw @ :: $debug_name");}
                    $favc_val = trim($favc_val);
                    if ($b_cast_to_int) {
                        $ret[] = (int)$favc_val;
                    } else {
                        $ret[] = $favc_val;
                    }
                }
            }

            $temp_array = explode(',',$mixed);
            foreach ($temp_array as $favc_key => $favc_val) {
                $favc_val = trim($favc_val);
                if ($b_cast_to_int) {
                    $ret[] = (int)$favc_val;
                } else {
                    $ret[] = $favc_val;
                }
            }
        } else {
            throw new LogicException("Cannot understand will_get_array_or_throw @ :: $debug_name format");
        }
    }
    return $ret;
}

/**
 * @param string $relative_middle_path
 */
function load_wp_from_directory($relative_middle_path) {
    $relative_middle_path = trim($relative_middle_path);
    $relative_middle_path = trim($relative_middle_path,'/');
    $partial_load_path = dirname(__FILE__) . '/'.$relative_middle_path .'/wp-load.php';
    $real_load_path = realpath($partial_load_path);
    if (!$real_load_path) {
        throw new RuntimeException("Could not find the wp-load.php from: " . $partial_load_path);
    }
    /** @noinspection PhpIncludeInspection */
    require_once $partial_load_path;
}

/**
 * @param Exception $e
 * @return string
 */
function will_get_exception_string($e) {
    return $e->getMessage() . ' ' .  $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getCode();
}

/**
 * @author  https://www.php.net/manual/en/function.debug-print-backtrace.php comments
 */
function will_get_backtrace() {
    ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();

//    // Remove first item from backtrace as it's this function which
//    // is redundant.
//    $trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);
//
//    // Renumber backtrace items.
//    $trace = preg_replace ('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);

    return $trace;
}

function will_check_empty_object($what) {
    if (!is_object($what)) {
        will_send_to_error_log("will_check_empty_object cannot check a non object!",$what);
        return false;
    }
    $thing = (array)$what;
    return empty($thing);
}

function will_send_rate_limited_admin_notice($title,$body,$delta = 60*60) {

    $last_times_lookup = get_option('fl_rate_limited_notices',[]);
    $current_time= time();
    if (array_key_exists($title,$last_times_lookup)) {
        $last_time_sent = (int)$last_times_lookup[$title];

        if ($last_time_sent + $delta > $current_time) {
            return; //not yet ready to send
        }
    }
    $last_times_lookup[$title] = $current_time;
    //will_send_to_error_log("starting to send ",$title);
    $headers = '';
    //$headers     = 'MIME-Version: 1.0'."\r\n";
    $headers    .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";

    $from_email = 'no-reply@peerok.com';//code-notes changed from address from 'technical@peerok.com';
    $headers    .= "From: $from_email";
    //$mail_sent=mail($to, $strSubject, $var, $headers);
    //code-notes add bcc FREELINGUIST_DUMMY_BCC_FOP_QUEUE if $b_use_dummy_bcc
    $email = get_option('admin_email','');
    wp_mail( $email, $title, $body,$headers);
    update_option('fl_rate_limited_notices',$last_times_lookup);


}

/**
 * @param mixed $maybe_nothing
 * @return null|mixed
 * For when you really do not not want empty strings or letter 0 or numeric zero, and want a null instead
 */
function will_cast_emptish_to_null($maybe_nothing) {
    $original = $maybe_nothing;
    if (is_string($maybe_nothing)) {$maybe_nothing = trim($maybe_nothing);}
    if (ctype_digit($maybe_nothing)) { $maybe_nothing = (float)$maybe_nothing;}

    if (empty($maybe_nothing)) {return null;}
    return $original;
}

function will_add_to_array_if_not_empty($value,&$arr) {
    if (!empty($value)) {
        $arr[] = $value;
    }
}
