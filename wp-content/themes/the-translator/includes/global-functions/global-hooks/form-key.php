<?php
    /*
     * current-php-code 2020-Jan-11
     * current-hook
     * input-sanitized :
     */


//code-notes FREELINGUIST_FORM_SECURITY_START_FLAG allows this to play nice with modified wp session manage
define("FREELINGUIST_FORM_SECURITY_START_FLAG",'session-started-by-form-key');


//code-notes FREELINGUIST_FORM_SECURITY_KEY defines the name of the post or get key we are checking for. If this is defined then the php server side knows to check for other things
define("FREELINGUIST_FORM_SECURITY_KEY",'_form_security_name');

//code-notes FREELINGUIST_DEFAULT_NONCE_NAME constant centralizes creating the nonce, to avoid errors by typo
define("FREELINGUIST_DEFAULT_NONCE_NAME",'freelinguist_nonce_is_cool');

//code-notes FREELINGUIST_KILL_NON_COMPLIANT_POSTS_AND_AJAX decides if we block all forms and ajax that do not have a valid security key, if not on the actions whitelist. Obviously, do not set this to true unless all ajax and forms are locked down tight first. Or else things will stop working
define("FREELINGUIST_KILL_NON_COMPLIANT_POSTS_AND_AJAX",false);

//code-notes FREELINGUIST_NOISY_NON_COMPLIANCE determines whether to print to log the famous 'Non-Complient Form or Ajax'
define("FREELINGUIST_NOISY_NON_COMPLIANCE",false);

//code-notes FREELINGUIST_DONT_CHECK_ACTIONS is an array of action names that are never checked for form keys or nonces
define("FREELINGUIST_DONT_CHECK_ACTIONS",[

        //get calls for non-secure information
        'get_custom_tags',

        //chat system
        'get_room_user',


        //WP core actions
        'heartbeat'
        ]
);

define("FREELINGUIST_FLAG_LOG_SESSION_OPTION",'freelinguist_flag_log_session');

//code-notes some ajax and forms will put all their data inside another child key. Here, if we cannot find what we want in the topmost keys, we search these sequentially
define("FREELINGUIST_ALT_KEYS_DATA_IN",['data']);

//code-notes all form-keys are saved per user, in the php session. Wordpress core does not do sessions, but some plugins will start one already. Here, we start a session, safely, if not already started
add_action('init', function() {
    if(!FreeLinguistFormKey::is_session_started()) {
        session_start();
        $_SESSION[FREELINGUIST_FORM_SECURITY_START_FLAG] = 1;
    }
}, 1);

//code-notes When a WP user logs out, we remove all code keys
add_action('wp_logout',function() {
    if (FreeLinguistFormKey::is_session_started()) {
        session_destroy ();
    }

});

//code-notes When a WP user logs in, we remove all code keys. Please note that a php session has nothing to do with a WP login, unless we tie it together here
add_action('wp_login',function() {
    if (FreeLinguistFormKey::is_session_started()) {
        session_destroy ();
    }
});

//code-notes Here is where all the ajax and form calls are intercepted. We make this a really high priority hook, at 999, so its called before the normal ajax and post handlers
add_action( 'init', function() {
    global $_REAL_POST;
    $check_option = (int)get_option(FREELINGUIST_FLAG_LOG_SESSION_OPTION,0);
    if ($check_option) {
        will_send_to_error_log('Session Contents',$_SESSION,false,true);
    }
    //code-notes Only look at ajax or post (so, ignore page requests, gets, refreshes, etc) that have the action defined and not on the whitelist
    if( (defined('DOING_AJAX') && DOING_AJAX) || !(empty($_POST)) ) {
        $b_what = FreeLinguistFormKey::do_auto_check();
        if (!$b_what) {
            //log unsecured ajax requests and form submissions
            $b_log = true;
            if (array_key_exists('action',$_REQUEST)) {
                $test_this = $_REQUEST['action'];
                if (in_array($test_this,FREELINGUIST_DONT_CHECK_ACTIONS)) {$b_log = false;}
            }
            if ($b_log) {
                if (FREELINGUIST_NOISY_NON_COMPLIANCE) {
                    FreeLinguistFormKey::chatter_away("Non-Complient Form or Ajax\n ");
                }

                //code-notes eventually kill the the ajax and form posts that do not do this
                if (FREELINGUIST_KILL_NON_COMPLIANT_POSTS_AND_AJAX) {
                    FreeLinguistFormKey::die_properly(FreeLinguistFormKey::DIE_CASE_NO_FORM_KEY);
                }
            }
        }
        $test_this = 'no action';
        if (isset($_REQUEST['action'])) {
            $test_this = $_REQUEST['action'];
        }

        if (FreelinguistDebugging::TESTING_DEBUG_LEVEL === FreelinguistDebugging::LOG_DEBUG) {
            if (!in_array($test_this, FREELINGUIST_DONT_CHECK_ACTIONS)) {
                if (!empty($_REAL_POST)) {
                    $copy = $_REAL_POST;
                    unset($copy['user_login']);
                    unset($copy['user_password']);
                    FreelinguistDebugFramework::note("Form/Ajax incoming (Real)", $copy); //sends to log if wp debug is on and debugFramework allows it
                } else {
                    $copy = $_REQUEST;
                    unset($copy['user_login']);
                    unset($copy['user_password']);
                    //code-notes sometimes the real post is not set
                    FreelinguistDebugFramework::note("Form/Ajax incoming (Escaped)", $copy); //sends to log if wp debug is on and debugFramework allows it
                }
            }
        }

    }

}, 999, 2);


/**
 * code-notes MOST of the logic for the form-keys, on the php side is in the FreeLinguistFormKey class. The rest is above in the same file
 * Class FreeLinguistFormKey
 */
class FreeLinguistFormKey {

    //code-notes The form keys are 'hand crafted'. We randomly pick some characters and save them to make the key. KEY_BYTE_LENGTH sets the length of the form-key
    const KEY_BYTE_LENGTH = 10;

    //code-notes SESSION_KEY_TO_STORE_FORM_KEYS is which place in the session to store the form keys we remember
    const SESSION_KEY_TO_STORE_FORM_KEYS = 'fl_form_keys';

    //code-notes DEFAULT_PLACES_TO_CHECK_FOR_FORM_KEY is an array of keys to check incoming data, to get the name of the form key
    const DEFAULT_PLACES_TO_CHECK_FOR_FORM_KEY = ['_ajax_form_key',  '_form_key'];

    //code-notes Error messages are defined as constants. If you need multi-lingual, then replace these with calls to dynamic strings
    const DIE_CASE_NO_FORM_KEY = 'No form key for this action. Needs to be added by developer';
    const DIE_CASE_WRONG_FORM_KEY = 'Please refresh this tab. Its out of date';
    const DIE_CASE_NO_NONCE = 'No user nonce for this action. Needs to be added by the developer';
    const DIE_CASE_WRONG_NONCE = 'Please Log out, and Log back in';

    //code-notes B_DO_CHATTER decides whether to put a running commentary of what is done here, inside the WP log
    const B_DO_CHATTER = true;

    const OPTION_NAME_PRINT_SESSION_STATUS = 'freelinguist_print_session_status';

    /**
     * The lifecycle of a form key is that each time a form, or way to access an ajax point, is created, then a new key is made for that
     *  when a new key is made for the same thing, it invalidates the older key for that user
     */

    /**
     * code-notes FreeLinguistFormKey::create_form_key makes a new random key under this name, and remembers it. If there is already a key here by the same name, its erased and will be invalid if still used
     * Creates a new form key, if there is already a form key under the $for_what hash in the session key of fl_form_keys
     *  then it will replace it
     *
     * @param string $for_what
     * @return string
     */
    public static function create_form_key($for_what) {
        $for_what = trim(strval($for_what));
        if (empty($for_what)) {throw new RuntimeException("Form key name is empty");}
        $new_key = bin2hex(random_bytes(static::KEY_BYTE_LENGTH));
        if (!array_key_exists(static::SESSION_KEY_TO_STORE_FORM_KEYS,$_SESSION)) {
            $_SESSION[static::SESSION_KEY_TO_STORE_FORM_KEYS] = [];
        }
        $_SESSION[static::SESSION_KEY_TO_STORE_FORM_KEYS][$for_what] = $new_key;
        //static::chatter_away("$for_what has new key of $new_key");
        return $new_key;
    }

    /**
     * code-notes FreeLinguistFormKey::get_form_key will return the key, if it exists, from the session. Else it returns false
     * Gets the value stored in the user session
     * All form keys are stored under fl_form_keys as an associative hash
     * @param string $for_what
     * @return string|false
     */
    public static function get_form_key($for_what) {
        $for_what = trim(strval($for_what));
        if (empty($for_what)) {throw new RuntimeException("Form key name is empty");}
        if (array_key_exists(static::SESSION_KEY_TO_STORE_FORM_KEYS,$_SESSION)) {
           if (array_key_exists($for_what,$_SESSION[static::SESSION_KEY_TO_STORE_FORM_KEYS])) {
               return $_SESSION[static::SESSION_KEY_TO_STORE_FORM_KEYS][$for_what];
           }
        }

        return false;
    }

    /**
     * code-notes FreeLinguistFormKey::check_form_key is the workhorse of this library. It checks the Request, or a part of the request, for a form key, and then does the logic and actions
     * code-notes This is only activated when the form key is sent as part of the data. When activated, the user nonce needs to be correct, as well as the form key
     * checks request
     * @param array|null $source . If null will check request
     * @param string $for_what , the user session sub-key to look up
     * @param string|false $query_arg , if not false, then this is the $_REQUEST key to get the information
     *                                   else it will default to '_ajax_form_key', and '_form_key'
     *                                   (in that order). Default false.
     * @param bool $die, if true, then script will die if check fails. Default true
     *
     * @return bool , only if die is not true
     */
    public static function check_form_key($source , $for_what,$query_arg = false, $die = true) {
        if (empty($source)) {$source = $_REQUEST;}
        $for_what = trim(strval($for_what));
        if (empty($for_what)) {throw new RuntimeException("Form key name is empty");}
        $da_value = '';
        if ($query_arg === false) {
            for($i=0; $i < count(static::DEFAULT_PLACES_TO_CHECK_FOR_FORM_KEY); $i++) {
                if (array_key_exists(static::DEFAULT_PLACES_TO_CHECK_FOR_FORM_KEY[$i],$source)) {
                    $da_value = $source[static::DEFAULT_PLACES_TO_CHECK_FOR_FORM_KEY[$i]];
                    break;
                }
            }
        } else {
            $cleaned_query_arg = trim(strval($query_arg));
            if (empty($cleaned_query_arg) ) {throw new RuntimeException("query arg is empty");}
            if (array_key_exists($cleaned_query_arg,$source)) {
                $da_value = $source[$cleaned_query_arg];
            }
        }

        if (empty($da_value)) {
            if ($die) {
                static::chatter_away("$for_what had no value");
                static::die_properly(static::DIE_CASE_NO_FORM_KEY);
            }
            return false;
        }
        $comp = static::get_form_key($for_what);
        if ($comp === $da_value) {
            static::chatter_away("$for_what security key matched");
            return true;
        }
        if ($die) {
            static::chatter_away("$for_what security key did not match\n". print_r($source,true)."\n".print_r($_SESSION,true) );
            static::die_properly(static::DIE_CASE_WRONG_FORM_KEY,$for_what);
        }
        return false;
    }
    
    /**
     * code-notes FreeLinguistFormKey::die_properly If something goes wrong, we need to terminate the WP load sequence. We die. But, if this is a form, we do not just want to leave a blank page. So we redirect to the modified 404 template, with a user friendly message. Otherwise we encode json in such a way that the js handlers know something is wrong, and we add in a message just for this library on the js side to display
     * @param string $reason
     * @param string $mo_data
     */
    public static function die_properly($reason,$mo_data=null) {
        global $wp_query,$freelinguist_not_found_security_reason;

        $message_out = $reason;

        if (defined('DOING_AJAX') && DOING_AJAX) {
            //print out json so the tab can tell the user to refresh, or tell the tester the form key is not defined
            $out = [
                'success' => false,
                'msg' => $message_out,
                'extra_msg' =>$mo_data,
                'do_refresh_message' => true
            ];
            wp_send_json($out,0); //this may be problematic, as some of the js ajax handlers are not sending out json headers, so the js is casting them back
        } else {
            //redirect to 404
            $freelinguist_not_found_security_reason = $message_out;
           $wp_query->set_404();
           status_header( 404 );
           get_template_part( 404 );
           exit();
        }
    }

    /**
     * code-notes FreeLinguistFormKey::do_auto_check starts the check sequence. First we check the Request top level keys, then we work our way down until we find something or out of things to try
     * @return bool or will die
     */
    public static function do_auto_check() {
        $b_pinged = static::do_auto_check_call(null);
        if ($b_pinged) {return true;}
        if ($b_pinged === false){
            for($i = 0; $i < count(FREELINGUIST_ALT_KEYS_DATA_IN); $i++) {
                $other_key = FREELINGUIST_ALT_KEYS_DATA_IN[$i];
                if (array_key_exists($other_key,$_REQUEST)) {
                    $b_ping = static::do_auto_check_call($_REQUEST[$other_key]);
                    if ($b_ping) {return true;}
                }
            }
        }
        return false;
    }

    //code-notes FreeLinguistFormKey::chatter_away will write to the debug log if it has permission to
    public static function chatter_away($what) {
        if (static::B_DO_CHATTER) {
            FreelinguistDebugFramework::note($what);
        }
    }
    /**
     * code-notes FreeLinguistFormKey::do_auto_check_call is the main part of the check call
     * @param null|string|array $data
     * @return bool
     */
    protected static function do_auto_check_call($data=null) {
        if (is_null($data)) {$data = $_REQUEST;}
        if (empty($data)) {$data = [];}
        if (!is_array($data)) {
            //parse it
            $new_data = [];
            parse_str( $data, $new_data );
            if (!empty($new_data)) {
                $data = $new_data;
            } else {
                $data = [];
            }

        }
        //check to see if $data['form_security_name'] is set,
        //if it is, then check the nonce and check the form key
        if (array_key_exists(FREELINGUIST_FORM_SECURITY_KEY,$data)) {
            $security_key_name = trim($data[FREELINGUIST_FORM_SECURITY_KEY]);
            static::chatter_away(FREELINGUIST_FORM_SECURITY_KEY." exists as $security_key_name");
            $nonce_name = FREELINGUIST_DEFAULT_NONCE_NAME;
            if (array_key_exists('nonce_name',$data)) {
                $nonce_name = $data['nonce_name'];
            }
            $nonce = '';
            if (array_key_exists('_ajax_nonce',$data)) {
                $nonce = $data['_ajax_nonce'];
            } else if (array_key_exists('_wpnonce',$data)) {
                $nonce = $data['_wpnonce'];
            }
            if (empty($nonce)) {
                static::die_properly(static::DIE_CASE_NO_NONCE,$security_key_name);
            }
            $b_is_nonce_ok = wp_verify_nonce($nonce, $nonce_name);
            if (!$b_is_nonce_ok) {
                static::chatter_away("$security_key_name failed nonce");

                static::die_properly(static::DIE_CASE_WRONG_NONCE,$security_key_name);
            } else {
                static::chatter_away("$security_key_name matched nonce");
            }


            if (!empty($security_key_name)) {
                FreeLinguistFormKey::check_form_key($data,$security_key_name);
                //reset the form key
                FreeLinguistFormKey::create_form_key($security_key_name);
            }
            return true;
        } else {
            return false;
        }
    }

    public static function is_session_started()
    {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {

                $n_print_status = (int)get_option(static::OPTION_NAME_PRINT_SESSION_STATUS,0);
                if ($n_print_status) {
                    will_send_to_error_log('session status',session_status());
                }

                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }

}