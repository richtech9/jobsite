<?php



class FreelinguistCurlHelperException extends RuntimeException {

    protected $data;

    public function __construct( $data, $message, $code = 0, Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
        $this->message = $message;
        $this->data    = $data;

        //overwrites message using the older version of message set above
        $this->message = (string) $this;
    }

    /**
     * Returns the error type of the exception that has been thrown.
     */
    public function getData() {
        return $this->data;
    }

    public function __toString() {
        $data = print_r( $this->data, true );
        $code = $this->getCode();

        return "[$code] " . $this->message . "\n$data";
    }


}

/**
 * Class CurlHelper
 * @since 0.1
 * @version 0.5.1 <p>
 *   json return now default to object, but can do old way by passing in json_force_array instead
 *      @see FreelinguistCurlHelper::curl_helper()
 *
 *   added helper function @see FreelinguistCurlHelper::set_debug_mode()
 * </p>
 */
class FreelinguistCurlHelper {
    protected $ch = null;
    protected $cookie_file = null;
    public $delete_cookie_file = true;
    protected $user_agent = null;
    protected $b_debug = false;
    protected $log = null;
    protected $last_url = null;
    protected $b_local_ignore_verify = false;

    public function set_local_ignore_verify($what) { $this->b_local_ignore_verify = $what;}

    public function get_last_url() { return $this->last_url;}

    public function set_debug_mode() { $this->b_debug = true;}
    public function get_debug_log() { return  $this->log;}
    /**
     * CurlHelper constructor.
     * @param  $b_delete_cookie_file bool
     * @param  $use_cookie_file string|null|boolean
     * @throws FreelinguistCurlHelperException
     */
    public function __construct($b_delete_cookie_file = true,$use_cookie_file = false) {

        $this->log = '';
        $this->ch                 = curl_init();
        $this->delete_cookie_file = $b_delete_cookie_file;

        if ($use_cookie_file === false) {
            $this->cookie_file = null;
        }
        else if ( $use_cookie_file === null ) {
            $this->cookie_file = $use_cookie_file;
        } else {
            $this->cookie_file = tempnam (sys_get_temp_dir(),'ccc');
        }

        if ($this->cookie_file) {
            curl_setopt( $this->ch, CURLOPT_COOKIEJAR, realpath( $this->cookie_file ) );
            curl_setopt( $this->ch, CURLOPT_COOKIEFILE, realpath( $this->cookie_file ) );
        }


    }

    function __destruct() {
        curl_close( $this->ch );
        if ($this->cookie_file) {
            if ($this->delete_cookie_file) {
                unlink($this->cookie_file);
            }
        }
    }


    /**
     * @param $root_name
     * @param $extension
     * @param $contents
     *
     * @return string
     * @throws FreelinguistCurlHelperException
     */
    public static function  write_to_file($root_name,$extension,$contents) {
        $uid = uniqid($root_name,true);
        $temp_name = realpath(dirname(__FILE__)) . '/'.$uid .'.'.$extension  ;

        if (!$contents) {
            $contents = '';
        }

        $b_what = file_put_contents($temp_name,$contents);
        if ($b_what === false) {
            throw new FreelinguistCurlHelperException(null,"could not create $temp_name file");
        }
        //$b_what = chmod( realpath($temp_name), 0777);
        shell_exec('chmod 666 ' . $temp_name);

        return $temp_name;
    }


    /**
     * Returns the decoded json from the input
     * if input is null then returns null
     * if input is empty returns []
     * else converts the string value of the input from a json string to a php structure
     * @param mixed $what
     * @param boolean $b_exception default true <p>
     *  will throw an exception if is not proper json if true
     *  will return the string if not proper json
     * </p>
     * @param boolean $b_array, if false then cast to object when it can
     * @return array|mixed|null
     * @throws FreelinguistCurlHelperException if json error
     */
    public static function json_from_string($what,$b_exception=true,$b_array = false) {
        if (is_null($what) ) { return null;}
        if (empty($what)) {return [];}
        $what = strval($what);
        if ( strcasecmp($what,'null') == 0) {return null;}
        $out = json_decode(strval($what), $b_array);
        if (is_null($out)) {
            if ($b_exception) {
                $oops =  json_last_error_msg();
                $oops .= "\n Data: \n". $what;
                throw  new FreelinguistCurlHelperException(null,$oops);
            }else {
                return $what;
            }

        } else {
            return $out;
        }

    }

    /**
     * @param $url string
     * @param array $file_names
     * @param array $fields
     * @param string $format
     * @throws FreelinguistCurlHelperException
     * @return string
     */
    function upload_file_and_data($url,array $file_names,array $fields,$format='text') {
// data fields for POST request

        $files = array();
        foreach ($file_names as $input_name => $file_path){
            $content = file_get_contents($file_path);
            $pretty_file_name = basename($file_path);
            $files[$input_name] = ['content'=>$content,'file_name'=>$pretty_file_name];
        }


        $boundary = uniqid();
        $delimiter = '-------------' . $boundary;

        $post_data = FreelinguistCurlHelper::build_data_files($boundary, $fields, $files);

        $headers = array(
            //"Authorization: Bearer $TOKEN",
            "Content-Type: multipart/form-data; boundary=" . $delimiter,
            "Content-Length: " . strlen($post_data)

        );

        $body =  $this->curl_helper($url,$http_code,$post_data,true,$format,false,false,$headers);

        if ($this->b_debug) {
            $this->log .= "\nAfter Upload\n";
            $this->log .= $body;
        }
        if ($http_code >= 400 ) {
            throw new FreelinguistCurlHelperException(null,$body);
        }
        return $body;
    }

    protected function build_data_files($boundary, $fields, $files){
        $data = '';
        $eol = "\r\n";

        $delimiter = '-------------' . $boundary;

        foreach ($fields as $name => $content) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
                . $content . $eol;
        }


        foreach ($files as $name => $details) {
            $content = $details['content'];
            $file_name = $details['file_name'];
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $file_name . '"' . $eol
                //. 'Content-Type: image/png'.$eol
                . 'Content-Transfer-Encoding: binary'.$eol
            ;

            $data .= $eol;
            $data .= $content . $eol;
        }
        $data .= "--" . $delimiter . "--".$eol;


        return $data;
    }

    function get_cookie_file_path() { return $this->cookie_file;}

    function set_user_agent($agent) {
        $this->user_agent = $agent;
        curl_setopt ($this->ch, CURLOPT_USERAGENT, $this->user_agent);
    }
    /**
     * @author Will Woodlief
     * @license MIT
     * @link https://gist.github.com/willwoodlief/1a008ab369ec48968d41d0cec1b9c4d6
     * General Curl Helper. Its multipurpose. Used it in the transcription project and now improved it
     * @example curl_helper('cnn.com',null,$code)
     *          curl_helper('enri.ch',['var1'=>4],$code)
     *
     *   options reset for the curl handle after each call
     *
     * @param $url string the url
 * @param &$http_code integer , will be set to the integer return code of the server. Its only an output variable
     *
     * @param $fields array|object|string|null <p>
     *  the params to pass
     *  May be an array, or object containing properties, or a string, or evaluates for false
     *  </p>
     * @param $b_post boolean , default true . POST is true, GET is false
     * @param  $format string (json|json_force_array|xml|text) default json <p>
     *      Tells how the response is formatted, text means no conversion
     *      json_force_array means that the json is cast to php array when possible
     *      json will cast to objects when possible
     *      xml for older services
     * </p>
     * @param $b_header_only boolean, default false <p>
     *  if true then no body is downloaded, and the return the headers
     * </>
     * @param $ssl_version boolean , default false <p>
     *   if not false, then set CURLOPT_SSLVERSION to the value
     * </p>
     * @param $headers array , default empty <p>
     *   adds to the headers of the request being sent
     * </p>
     * @param $custom_request false|string, default false <p>
     * when set will set custom post instead of post
     * </p>
     *
     * @return array|string|int|null depends on the format and option
     *
     * @throws FreelinguistCurlHelperException <p>
     *   if curl cannot connect
     *   if site gives response in the 500s (if $b_header_only is false)
     *   if the format is json and the the conversion has errors and response is below 500
     * if the format is xml and the conversion has errors and response is below 500
     * </p>
     */
    function curl_helper(
        $url,  &$http_code,$fields = null, $b_post = true, $format = 'json',
        $b_header_only = false, $ssl_version = false, $headers = [], $custom_request = false
    ) {

        if ( ! isset( $url ) ) {
            throw new FreelinguistCurlHelperException($fields, "URL needs to be set" );
        }
        $url = strval( $url );


        try {
            curl_setopt_array( $this->ch, [
                CURLOPT_RETURNTRANSFER => true,
            ] );

            //curl will not print verbose info to the html browser screen. So we have to capture it and replay it
            $verbose = null;
            if ( $this->b_debug ) {
                $verbose = fopen( 'php://temp', 'w+' );
                curl_setopt( $this->ch, CURLOPT_STDERR, $verbose );
                curl_setopt( $this->ch, CURLOPT_VERBOSE, true );
            }

            if ( $b_header_only ) {
                curl_setopt( $this->ch, CURLOPT_HEADER, true );    // we want headers
                curl_setopt( $this->ch, CURLOPT_NOBODY, true );    // we don't need body

                $out_headers = [];
                // this function is called by curl for each header received
                curl_setopt( /**
                 * @param $curl resource
                 * @param $header string
                 *
                 * @return int
                 */
                    $this->ch, CURLOPT_HEADERFUNCTION,
                    function (
                        /** @noinspection PhpUnusedParameterInspection */
                        $curl, $header
                    ) use ( &$out_headers ) {
                        $len    = strlen( $header );
                        $header = explode( ':', $header, 2 );
                        if ( count( $header ) < 2 ) // ignore invalid headers
                        {
                            return $len;
                        }

                        $name = strtolower( trim( $header[0] ) );
                        if ( ! array_key_exists( $name, $out_headers ) ) {
                            $out_headers[ $name ] = [ trim( $header[1] ) ];
                        } else {
                            $out_headers[ $name ][] = trim( $header[1] );
                        }

                        return $len;
                    }
                );
            }

            if ( $headers ) {
                curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $headers );
            }

            //if testing on localhost and url is https, then this gets around it because some localhost do not have ssl certs

            if ($this->b_local_ignore_verify) {
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
                        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
                    }

                }
            }


            if ( $b_post ) {
                if ( $custom_request ) {
                    curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, $custom_request );
                } else {
                    curl_setopt( $this->ch, CURLOPT_POST, count( $fields ) );
                }

                if ( $fields ) {
                    if ( is_object( $fields ) || is_array( $fields ) ) {
                        $b_do_build = true;
                        if ( is_array( $fields ) ) {
                            if ( array_key_exists( 'curl_helper_skip_encoding', $fields ) ) {
                                if ( $fields['curl_helper_skip_encoding'] ) {
                                    $b_do_build = false;
                                }
                            }
                        }
                        if ( is_object( $fields ) ) {
                            if ( property_exists( $fields, 'curl_helper_skip_encoding' ) ) {
                                if ( $fields->curl_helper_skip_encoding ) {
                                    $b_do_build = false;
                                }
                            }
                        }
                        if ( $b_do_build ) {
                            $build = http_build_query( $fields );
                        } else {
                            $build = $fields;
                        }


                        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $build );
                    } else {
                        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $fields );
                    }

                }

            } else {
                if ( $custom_request ) {
                    curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, $custom_request );
                }
                curl_setopt($this->ch,CURLOPT_HTTPGET, true);
                if ( $fields ) {
                    if ( is_object( $fields ) || is_array( $fields ) ) {
                        $query = http_build_query( $fields );
                    } else {
                        $query = $fields;
                    }

                    $url = $url . '?' . $query;
                }
            }

            curl_setopt( $this->ch, CURLOPT_URL, $url );

            curl_setopt( $this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0" );


            curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );

            if ( $ssl_version ) {
                curl_setopt( $this->ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2 );
            }


            $curl_output = curl_exec( $this->ch );




            $http_code = intval( curl_getinfo( $this->ch, CURLINFO_HTTP_CODE ) );

            $this->last_url = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);

            if ($this->b_debug) {
                $this->log .= "\nServer returned code $http_code\n";
            }

            if ( $this->b_debug ) {
                rewind( $verbose );
                $verboseLog = stream_get_contents( $verbose );
                $this->log .= $verboseLog;
            }

            if ( curl_errno( $this->ch ) ) {
                throw new FreelinguistCurlHelperException($fields, "could not open url: $url because of curl error: ". curl_error( $this->ch ) );
            }

            if ( $b_header_only ) {

                $out_headers['effective_url'] = curl_getinfo( $this->ch, CURLINFO_EFFECTIVE_URL );

                return $out_headers; //journey ends here with just the headers
            }

            if ( $http_code == 0 ) {
                throw new FreelinguistCurlHelperException([], "Could not send data to $url", $http_code );
            }

            if ( ! is_string( $curl_output ) || ! strlen( $curl_output ) ) {
                $curl_output = ''; //no longer throwing exception here as sometimes need return code
            }

            //makes it easy to skip formatting
            if ( $format === true || ! $format ) {
                $format = 'none';
            }
            try {
                switch ( $format ) {
                    case 'json':
                        $data_out = FreelinguistCurlHelper::json_from_string( $curl_output );
                        break;
                    case 'json_force_array':
                        $data_out = FreelinguistCurlHelper::json_from_string( $curl_output,true,true );
                        break;
                    case 'xml':

                        $data_out = json_decode( json_encode( (array) simplexml_load_string( $curl_output ) ), 1 );
                        if ( $data_out === null ) {
                            throw new Exception( "failed to decode as xml: $curl_output" );
                        }
                        break;
                    default:
                        {
                            $data_out = $curl_output;
                        }
                }
            } catch ( Exception $c ) {
                $data_out = $curl_output;
            }


            if ( $http_code >= 500 ) {
                throw new FreelinguistCurlHelperException($data_out, 'Server had error', $http_code );
            }


            return $data_out;
        } finally {
            //reset curl options
            curl_reset($this->ch);
            //re add cookie jar
            if ($this->cookie_file) {
                curl_setopt($this->ch, CURLOPT_COOKIEJAR, realpath($this->cookie_file));
                curl_setopt($this->ch, CURLOPT_COOKIEFILE, realpath($this->cookie_file));
            }

            if ($this->user_agent) {
                curl_setopt ($this->ch, CURLOPT_USERAGENT, $this->user_agent);
            }
        }
    }
}


/**
 * inits and then releases the curl
 * will do basic auth if the wp option of freelinguist_basic_page_auth is set
 * curl communication is logged to debug log if log_curl is on in the freelinguist_basic_page_auth options
 * @param string $url
 * @param string $message OUT REF  the message returned by the curl call
 * @param int $http_code OUT REF the http code returned
 * @return bool (false if not successful, true if successful)
 */
function freelinguist_call_with_maybe_auth($url,&$message,&$http_code) {
    $ch = null;
    $http_code = -1;
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt_array( $ch, [
            CURLOPT_RETURNTRANSFER => true,
        ] );

        $the_auth_stuff = get_option('freelinguist_basic_page_auth', ['username' => '', 'password' => '', 'log_curl' => false]);
        $b_log = $the_auth_stuff['log_curl'];
        $da_username = $the_auth_stuff['username'];
        $da_password = $the_auth_stuff['password'];
        if ($da_password && $da_username) {
            curl_setopt($ch, CURLOPT_USERPWD, $da_username . ":" . $da_password);
        }
        $verbose = '';
        if ($b_log) {
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        $response_text = curl_exec($ch);
       // will_dump("da curl response",$response_text);
        if ($b_log && $verbose) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            will_send_to_error_log("Curl log for $url ", $verboseLog);
        }

        $json_response = json_decode($response_text);
       // will_dump("what the json is ",$json_response);
        $http_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
      //  will_dump("what the http code is ",$http_code);
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        if ($b_log) {
            will_send_to_error_log("Curl ending url for $url ", $last_url);
        }

        if (is_null($json_response)) {
            $json_error = json_last_error_msg();
            will_send_to_error_log("Curl Invalid Json for $url ", $json_error);
            will_send_to_error_log("Text curl response for $url ", $response_text);
            $message = $response_text;
            return false; //non json is always false success
        } else {
            if ($b_log) {
                will_send_to_error_log("JSON curl response for $url ", $json_response);
            }
        }

        if (property_exists($json_response, 'message')) {
            $message = $json_response->message;
        } else {
            $message = '';
        }

        if (property_exists($json_response, 'success')) {
            return $json_response->success;
        }

        return false;
    } finally {
        if ($ch) {
            curl_close($ch);
        }
    }


}
