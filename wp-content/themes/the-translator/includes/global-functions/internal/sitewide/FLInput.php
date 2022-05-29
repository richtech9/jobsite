<?php

require_once(ABSPATH . '/wp-content/themes/the-translator/vendor/autoload.php');
use ForceUTF8\Encoding;

//will_send_to_error_log("I Live, again..");

global $_REAL_GET, $_REAL_POST, $_REAL_COOKIE, $_REAL_REQUEST;
$_REAL_GET     = $_GET;
$_REAL_POST    = $_POST;
$_REAL_COOKIE  = $_COOKIE;
$_REAL_REQUEST = $_REQUEST;

/**
 * Class FLInput
 * Makes data safe and standardized for db storage
 * If there is an issue will not crash code unless exception propagation is turned on
 * Will log all exceptions through
 *
 * extra debugging is enabled by turning that on
 */
class FLInput {

    /*
    * current-php-code 2020-Oct-17
    * internal-call
    * input-sanitized :
    */

    const NO_DATA_CHANGING = 1;
    const YES_I_WANT_CONVESION = 2;
    const NO_DB_ESCAPING = 3;
    const YES_I_WANT_DB_ESCAPING = 4;
    const NO_HTML_ENTITIES = 5;
    const YES_I_WANT_HTML_ENTITIES = 6;
    const NO_TRIMMING = 7;
    const YES_I_WANT_TRIMMING = 8;

    /**
     * @const string Input::MUST_BE_FILE
     * is used to force finding files in
     * @see get() for how to use
     */
    const MUST_BE_FILE = 'the param must be an uploaded file, it cannot be anything else ';


    /**
     * @const string Input::THROW_IF_MISSING
     * is used to throw an exception if the param is missing from get post or file
     * @see get() for how to use
     */
    const THROW_IF_MISSING = 'the param must exist - Will v2.2@!unique';


    /**
     * @const string Input::THROW_IF_EMPTY
     * is used to throw an exception if the param is missing from get post or file
     * @see get() for how to use
     */
    const THROW_IF_EMPTY = 'the param must exist and must not be empty - Will v2.2@!unique';

    const SOURCE_NONE = '[[not-set]]';
    const SOURCE_FILE = '[[file]]';
    const SOURCE_GET = '[[get]]';
    const SOURCE_POST = '[[post]]';

    public static function exists($param ){
        return isset($_POST[$param]) || isset($_GET[$param]);
    }

    private static $post_only = false;

    /**
     * if set to true, then only post and not get will be read
     * @param bool $b_what, default true
     * @return void
     */
    public static function onlyPost($b_what=true) {
        self::$post_only = $b_what;
    }


    public static function isPost() {
        if ($_POST) {return true;}
        return false;
    }

    protected static $b_debug = false;
    public static $n_debug_level = 1; //how much information to output to screen or log

    public static function turn_on_debugging($level=1) {static::$b_debug = true; static::$n_debug_level = $level;}
    public static function turn_off_debugging() {static::$b_debug = false;}

    protected static $b_propogate_exceptions = false;
    public static function start_exception_propogation() {static::$b_propogate_exceptions = true;}
    public static function stop_exception_propogation() {static::$b_propogate_exceptions = false;}

    public static function copy_and_clean_all_post($choose_conversion=self::YES_I_WANT_CONVESION,
                                                   $choose_escape=self::YES_I_WANT_DB_ESCAPING,
                                                   $choose_html_entities = self::YES_I_WANT_HTML_ENTITIES,
                                                   $choose_trim=self::YES_I_WANT_TRIMMING) {
        global $_REAL_POST;
        if (empty($_REAL_POST)) {return [];}

        $ret = self::apply_conversions($_REAL_POST, $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
        return $ret;
    }


    /**
     * @param array $data
     * @param $key
     * @param mixed $alternate
     * @param int $choose_conversion
     * @param int $choose_escape
     * @param int $choose_html_entities
     * @param int $choose_trim
     * @return mixed
     */
    public static function clean_data_key(          $data, $key,$alternate='',
                                                    $choose_conversion=self::YES_I_WANT_CONVESION,
                                                   $choose_escape=self::YES_I_WANT_DB_ESCAPING,
                                                   $choose_html_entities = self::YES_I_WANT_HTML_ENTITIES,
                                                   $choose_trim=self::YES_I_WANT_TRIMMING) {

        if (is_string($data) || is_numeric($data) || is_null($data) || is_bool($data)) {
            will_send_to_error_log("data given for clean_data_key is not array",$data,false,true);
            return $alternate;
        }
        if (empty($data)) {return $alternate;}
        if (!isset($data[$key])) {return $alternate;}
        $thing = $data[$key];
        $ret = self::apply_conversions($thing, $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
        return $ret;
    }

    /**
     * @param $item <p>
     *   The key in the post or get
     * </p>
     * @param mixed $alternate <p>
     *   If the key value is missing then this is returned instead
     *   If cast is used, then this alternate is cast as well to the data type, it must be compatible
     *   If this is set to @see Input::MUST_BE_FILE then the behavior completely changes
     *    and an InvalidArgumentException is thrown if the name is not an uploaded file
     *    Also a check is made to see if the file is not 0 size
     *   If this is set to @see Input::THROW_IF_MISSING then
     *    an InvalidArgumentException is thrown if cannot find the item in get post or files
     *   if this is set to @see Input::THROW_IF_EMPTY then will throw if exists but is not filled
     * </p>
     * @param int $choose_conversion <p>
     *   default false
     *   if true then the data is trimmed and has special chars converted
     * </p>
     *
     * @param int $choose_escape
     *      whether to escape the data for the db
     *
     * @param int $choose_html_entities
     *      whether to convert html tags to entities
     *
     * * @param int $choose_trim
     *      whether to trim whitespace on either side of the string

     * @return array|mixed|string
     * @throws InvalidArgumentException if it cannot find the name
     *
     * @since 0.1
     * @version 0.2.2 Fix a nasty but subtle bug where WP always magic quotes and overrides all post and get.
     *                  We cannot turn off without breaking other plugins perhaps, so copy over the original
     *                  values into the $_REAL_POST,$_REAL_GET as the wp_magic_quotes is called after
     *                  the plugins are loaded. We then use these copies instead of the original.
     *                  This way, do not have to strip slashes from sometimes binary data
     */
    public static function get($item,$alternate='',
                               $choose_conversion=self::YES_I_WANT_CONVESION,
                               $choose_escape=self::YES_I_WANT_DB_ESCAPING,
                               $choose_html_entities = self::YES_I_WANT_HTML_ENTITIES,
                               $choose_trim=self::YES_I_WANT_TRIMMING)
    {
        global $_REAL_POST,$_REAL_GET;
        try {

            if (!is_string($item)) {
                will_send_to_error_log("Asking to get an item , but item is not a string",
                    $item, false, true);
                throw new InvalidArgumentException("Asking to get an item , but item is not a string");

            }
            if (static::$b_debug) {
                will_send_to_error_log('Getting item: ' . $item);
            }


            $b_throw_arg = false;
            if (($alternate === self::THROW_IF_MISSING) || ($alternate === self::THROW_IF_EMPTY)) {
                $b_throw_arg = true;
            }

            $source = static::SOURCE_NONE;

            if ($alternate === self::MUST_BE_FILE) {
                if (isset($_FILES[$item])) {
                    //check not 0 size
                    $filesize = $_FILES[$item]['size'];
                    if ($filesize <= 0) {
                        throw new InvalidArgumentException("The file uploaded is 0 size. Its uploaded as the name $item");
                    }
                    $source = static::SOURCE_FILE;
                    if (static::$b_debug) {
                        will_send_to_error_log("returning $source item: " . $item,
                            $_FILES[$item], false, true, false);
                    }
                    return $_FILES[$item];
                } else {
                    throw new InvalidArgumentException("Cannot find $item in Files");
                }
            }


            //we will test later to see if this value has changed from a value set here
            if (isset($_REAL_POST[$item])) {
                $source = static::SOURCE_POST;
                if ($alternate === self::THROW_IF_EMPTY) {
                    $pre_cast = self::apply_conversions($_REAL_POST[$item], $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
                    if (empty($pre_cast) && (!is_numeric($pre_cast)) && (!is_bool($pre_cast))) {
                        throw new InvalidArgumentException("Cannot find $item in post");
                    }
                } else {
                    $pre_cast = self::apply_conversions($_REAL_POST[$item], $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
                }

            } else if (isset($_REAL_GET[$item])) {
                $source = static::SOURCE_GET;
                //if post only, and not in post above, then just set to alternate, else try to get value from get
                if (self::$post_only) {
                    $pre_cast = $alternate;
                } else {
                    if ($alternate === self::THROW_IF_EMPTY) {
                        $pre_cast = self::apply_conversions($_REAL_GET[$item], $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
                        if (empty($pre_cast) && (!is_numeric($pre_cast)) && (!is_bool($pre_cast))) {
                            throw new InvalidArgumentException("Cannot find $item in get");
                        }
                    } else {
                        $pre_cast = self::apply_conversions($_REAL_GET[$item], $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
                    }

                }

            } else if (isset($_FILES[$item])) {
                $source = static::SOURCE_FILE;
                if (static::$b_debug) {
                    will_send_to_error_log("returning $source item: " . $item,
                        $_FILES[$item], false, true, false);
                }
                return $_FILES[$item];
            } else {
                if ($b_throw_arg) {
                    throw new InvalidArgumentException("Cannot find $item in either get or post or files");
                }
                $pre_cast = null;
            }

            if ($source === static::SOURCE_NONE) {
                $ret = $alternate;
            } else {
                $ret = $pre_cast;
            }


            if (static::$b_debug) {
                will_send_to_error_log("returning $source item: " . $item,
                    $ret, false, true, false);
            }
            return $ret;
        } catch (LogicException|RuntimeException|InvalidArgumentException $e) {
            $prop_string = "[[Propogations OFF]]";
            if (static::$b_propogate_exceptions) {
                $prop_string = "[[Propogations ON]]";
            }
            will_send_to_error_log("Exception in FLInput $prop_string: ",will_get_exception_string($e));
            if (static::$b_propogate_exceptions) {
                throw $e;
            }
            return null;
        } catch (Exception $e) {

            will_send_to_error_log("Unhandled Exception in FLInput: ",
                [will_get_exception_string($e),$e->getTrace()]);
            if (static::$b_propogate_exceptions) {
                throw new RuntimeException($e->getMessage(),$e->getCode(),$e);
            }
            return null;
        }

    }

    /**
     *
     * @internal method for this class, allows both string and nested arrays to be worked on,
     * without needing to know about the data from the caller's perspective
     *
     * handles any nesting of primitive,array,objects and will convert all the string data via options passed
     * will convert objects to arrays
     *
     * @param mixed|string|array $input
     *
     *
     * @param int $choose_conversion
     *   whether to do any sanitizing or data modification at all
     *
     * @param int $choose_escape
     *    whether to escape the data for the db
     *
     * @param int $choose_html_entities
     *      whether to convert html tags to entities
     *
     * * @param int $choose_trim
     *      whether to trim whitespace on either side of the string
     *
     * @return array|string
     * @since 0.1
     * @version 0.4.0 , do not cast non array input as string
     */
    protected static function apply_conversions($input,  $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim){

        if (! (($choose_conversion === self::YES_I_WANT_CONVESION) || ($choose_trim === self::NO_DATA_CHANGING))) {
            throw new InvalidArgumentException("Choose conversion must be choice of YES_I_WANT_CONVESION|NO_DATA_CHANGING");
        }

        if (is_array($input)) {
            $what = [];
            foreach ($input as $key => $hmm) {
                if (is_array($hmm) || is_object($hmm)) {
                    $what[$key] = self::apply_conversions($hmm,$choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
                } else {
                    if (is_string($hmm)) {
                        if ($choose_conversion === self::YES_I_WANT_CONVESION) {
                            $element = static::clean_string($hmm, $choose_escape, $choose_html_entities, $choose_trim);
                        } else {
                            $element =  $hmm;
                        }
                    } else {
                        $element = $hmm;
                    }
                    $what[$key] = $element;
                }
            }
        }
        else if(is_object($input)) {
            $json_temp_object =
                json_encode($input,
                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );

            if (is_null($json_temp_object)) {

                $oops =  json_last_error_msg();
                $oops .= "\n Data to Json Failed:";
                will_send_to_error_log($oops,$input,true,true);
                throw  new RuntimeException($oops);
            }

            $shiney_new_array = json_decode($json_temp_object, true);
            if (is_null($shiney_new_array)) {

                $oops =  json_last_error_msg();
                $oops .= "\n Data from Json Failed:";
                will_send_to_error_log($oops,$input,true,true);
                throw  new RuntimeException($oops);
            }
            return static::apply_conversions($shiney_new_array, $choose_conversion, $choose_escape, $choose_html_entities, $choose_trim);
        }
        else {
            if (empty($input) && (! is_numeric($input)) && (! is_bool($input))) {
                $input = null;
            }
            if (is_string($input)) {
                if ($choose_conversion === self::YES_I_WANT_CONVESION) {
                    $what = static::clean_string($input, $choose_escape, $choose_html_entities, $choose_trim);
                } else {
                    $what = $input;
                }
            } else {
                $what = $input;
            }

        }


        return $what;

    }

    public static function filter_string_allow_html($what,
                                            $choose_escape=self::YES_I_WANT_DB_ESCAPING,
                                            $choose_html_entities = self::NO_HTML_ENTITIES,
                                            $choose_trim=self::YES_I_WANT_TRIMMING) {

        return static::clean_string($what,$choose_escape,$choose_html_entities,$choose_trim);
    }

    public static function filter_string_default($what,
                                                    $choose_escape=self::YES_I_WANT_DB_ESCAPING,
                                                    $choose_html_entities = self::YES_I_WANT_HTML_ENTITIES,
                                                    $choose_trim=self::YES_I_WANT_TRIMMING) {

        return static::clean_string($what,$choose_escape,$choose_html_entities,$choose_trim);
    }

    /**
     *  Will always check and maybe convert the string encoding to use standard unicode
     *   By default it also escapes data for storage in mysql/maria and converts html tags to html entities
     * @param string $what
     *
     * @param int $choose_escape
     *      whether to escape the data for the db
     *
     * @param int $choose_html_entities
     *      whether to convert html tags to entities
     *
     * @param int $choose_trim
     *      whether to trim whitespace on either side of the string
     *
     * @return string
     */
    public static function clean_string($what,$choose_escape,$choose_html_entities,$choose_trim) {

        // check args
        if (! (($choose_escape === self::YES_I_WANT_DB_ESCAPING) || ($choose_escape === self::NO_DB_ESCAPING))) {
            throw new InvalidArgumentException("Choose escape must be choice of YES_I_WANT_DB_ESCAPING|NO_DB_ESCAPING");
        }

        if (! (($choose_html_entities === self::YES_I_WANT_HTML_ENTITIES) || ($choose_html_entities === self::NO_HTML_ENTITIES))) {
            throw new InvalidArgumentException("Choose html entities must be choice of YES_I_WANT_HTML_ENTITIES|NO_HTML_ENTITIES");
        }

        if (! (($choose_trim === self::YES_I_WANT_TRIMMING) || ($choose_trim === self::NO_TRIMMING))) {
            throw new InvalidArgumentException("Choose trimming must be choice of YES_I_WANT_TRIMMING|NO_TRIMMING");
        }

        if (empty($what) && !is_numeric($what)) {return '';}

        if (static::$b_debug) {
            will_send_to_error_log('starting to possibly convert string. Original is ',
                $what,false,true,true);
        }
        $definately_utf8 =  Encoding::toUTF8($what);
        if (static::$b_debug) {
            will_send_to_error_log('end of maybe convert string. New is ',
                $definately_utf8,false,true,true);
        }



        $probably_html_entities = $definately_utf8;

        if ($choose_html_entities === self::YES_I_WANT_HTML_ENTITIES) {
            $probably_html_entities = htmlentities($definately_utf8, ENT_QUOTES, 'UTF-8');
            if (static::$b_debug) {
                will_send_to_error_log('Did HTML entities. New is ',
                    $probably_html_entities,false,true,true);
            }
        }


        $probably_escaped_ok = $probably_html_entities;
        if ($choose_escape === self::YES_I_WANT_DB_ESCAPING) {
            $probably_escaped_ok_before_nl_fix = esc_sql($probably_html_entities);
            //code-notes undo any \n to proper newlines
            if (static::$b_debug) {
                will_send_to_error_log('Did DB escaping.(before new lines fix)  ',
                    $probably_escaped_ok_before_nl_fix,false,true,true);
            }
            $probably_escaped_ok = str_replace('\n',"\n",$probably_escaped_ok_before_nl_fix);
            $probably_escaped_ok = str_replace('\r',"",$probably_escaped_ok);
            if (static::$b_debug) {
                will_send_to_error_log('Did DB escaping. (after new lines fix) ',
                    $probably_escaped_ok,false,true,true);
            }
        }

        $probably_trimmed = $probably_escaped_ok;
        if ($choose_trim === self::YES_I_WANT_TRIMMING) {
            //change the \r\n
            $probably_trimmed = str_replace('\r\n',"\r\n",$probably_escaped_ok);
            $probably_trimmed = trim($probably_trimmed);
            if (static::$b_debug) {
                will_send_to_error_log('Did Trimming. New is ',
                    $probably_trimmed,false,true,true);
            }
        }

        return $probably_trimmed;
    }



}
