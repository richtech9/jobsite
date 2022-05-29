<?php
use Cielu\Ejabberd\EjabberdClient;

/*
 * temp test notes
 * old xmpp_password,orkxQyHS
new xmpp_password 4GH8o9JK
xmpp_username,willwoodliefbuyer-1001
 id is 156600

------
3587

fcustomer1-1001
 password: b4zom0tz


INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (86800, 3587, 'xmpp_password', 'b4zom0tz');
INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (86801, 3587, 'xmpp_username', 'fcustomer1-1001');
 */
add_action( 'wp_ajax_freelinguist_refresh_chat_credentials', [ FreelinguistRefreshChatCredentials::class,'action'] );

class FLFinishLogic extends RuntimeException {}
class FLFinishLogicWithError extends RuntimeException {}


/**
 * Class FreelinguistResetChatResponse
 *  Used to communicate with the ajax handler on the js side for
 */
class FreelinguistRefreshChatCredentials {

    /*
     * current-php-code 2020-Oct-3
     * ajax-endpoint  freelinguist_refresh_chat_credentials
     * input-sanitized : test_command,test_user_id
    */

    /**
     * @var string $status
     *          valid values are error|success|do_no_action
     *          if set to anything else the js will not know what to do
     */
    public $status  = 'uninitialized';// not a state the js should see, lets us find out if something is slipping through the cracks

    /**
     * @var string $msg
     */
    public $msg = ''; //set to empty string to allow possible concatenation

    /**
     * @var string|null $chat_account_login_name
     */
    public $chat_account_login_name = null; //default null what the early returns or error status should have

    /**
     * @var string|null $chat_account_login_password
     */
    public $chat_account_login_password = null; //default null what the early returns or error status should have

    public function __construct()
    {
        $this->msg = '';
        $this->status = 'uninitialized';
    }

    protected static function get_log_time() {
        return date("D M d, Y G:i:s");
    }

    protected static function get_log_prefix() {
        return static::class . ' '. static::get_log_time() . ' ';
    }

    /**
     * turns on logging for all api calls and logic results
     * @var bool
     */
    protected static $b_debug = false;

    public static function turn_on_debugging() {
        static::$b_debug = true;
    }

    public static function turn_off_debugging() {static::$b_debug = false;}

    protected static function log($prefix,$data,$b_dump = false) {
        if (static::$b_debug) {
            $final_prefix = static::get_log_prefix(). ': '. $prefix;
            will_send_to_error_log($final_prefix,$data,false,$b_dump);
        }
    }



    /**
     * @var EjabberdClient $chat_client
     */
    protected static $chat_client = null;


    //tested
    /**
     * @return EjabberdClient
     * @throws
     */
    public static function get_ejabberd_client() {
        if (static::$chat_client) {return static::$chat_client;}

        $options = get_option('xmpp_settings');
        $uri = empty($options['xmpp_api_address']) ? '' : $options['xmpp_api_address'];
        $type = empty($options['xmpp_token_type']) ? '' : $options['xmpp_token_type'];
        $code = empty($options['xmpp_auth_code']) ? '' : $options['xmpp_auth_code'];
        try {
            static::log('creating chat client',[
               'baseUri' => $uri ,
                'authorization' => $type . " " . $code
            ]);
            static::$chat_client = new EjabberdClient([
                'baseUri' => $uri, // must use http or https
                'authorization' => $type . " " . $code
            ]);
            //static::log('made chat client',static::$chat_client,true);
            return static::$chat_client;
        } catch (Exception $e) {
            will_send_to_error_log("Issue getting chat client",will_get_exception_string($e));
            throw $e;
        }
    }

    //tested
    /**
     * gets the calculated chat username, with domain and decoration, and if the password is set, will get that too
     * @param int $user_id IN
     * @param string $xmpp_user_full_name OUTREF
     * @param string $xmpp_user_password OUTREF
     * @throws Exception
     * @return array
     */
    public static function get_saved_ejabberd_account($user_id, &$xmpp_user_full_name, &$xmpp_user_password) {
        $user_id = (int)$user_id;
        if (!$user_id) {throw new InvalidArgumentException("User ID is empty, cannot calculate its account for Ejabberd");}
        $xmpp_user_password = get_user_meta($user_id, 'xmpp_password', true);
        if (empty($xmpp_user_password)) {
            $xmpp_user_password = null;
        }

        $xmpp_user_full_name = get_user_meta($user_id, 'xmpp_username', true);
        if (empty($xmpp_user_full_name)) {
            $xmpp_user_full_name = null;
        }
        static::log("saved info is",[
            '$xmpp_user_full_name' =>$xmpp_user_full_name,
            '$xmpp_user_password' =>$xmpp_user_password,
        ]);
        return ['status'=>true,'client_says'=>null,
            'username'=>$xmpp_user_full_name,'password'=>$xmpp_user_password,'call'=>'get_saved_ejabberd_account'];

    }

    /**
     * if the function returns, it succeeds, else will throw exception
     * will save new password
     * @param int $user_id
     * @param string $force_username (optional if user_id set)
     * @return array , with ejabberd response
     * @throws Exception
     */
    public static function unregister_user($user_id,$force_username) {

        static::log("starting regenerating password",[
            '$user_id' =>$user_id,
            '$force_username' =>$force_username,
        ],true);
        $user_id = (int)$user_id;
        if (!$user_id ) {
            throw new InvalidArgumentException("User ID and username is empty, cannot regenerate is password on Ejabberd");
        }
        try {
            if ($force_username) {
                $xmpp_user_full_name = $force_username;
            } else {
                static::get_saved_ejabberd_account($user_id, $xmpp_user_full_name, $do_not_use_this_password);
                if (!$xmpp_user_full_name) {
                    throw new InvalidArgumentException("User id of $user_id does not have a username stored for the chat system");
                }
            }


            if ( !trim($xmpp_user_full_name)) {
                throw new LogicException("Username '$xmpp_user_full_name' is blank or just whitespace");
            }

            $client = static::get_ejabberd_client();
            static::log("calling client->unregister_user",[
                '$xmpp_user_full_name' =>$xmpp_user_full_name,
            ],true);
            $client_response = $client->unregister($xmpp_user_full_name);

            static::log("response client->unregister_user", $client_response,true);

            if (!is_array($client_response)) {
                throw new RuntimeException("Ejabberd returned something unexpected: ". strval($client_response));
            }
            if (!isset($client_response["status"])) {
                throw new RuntimeException("Ejabberd returned an array without the status key: ".
                    json_encode($client_response));
            }
            if ($client_response["status"] !== "success") {
                throw new RuntimeException("Ejabberd was not successful in using the api changePassword,".
                    json_encode($client_response));
            }


            static::log("deleting chat username from meta xmpp_username ",[
                '$user_id' => $user_id,
                'xmpp_username' => $xmpp_user_full_name
            ],false);

            delete_user_meta($user_id, 'xmpp_password');

            static::log("deleting chat password from meta xmpp_username ",[
                '$user_id' => $user_id
            ],false);
            delete_user_meta($user_id, 'xmpp_username');


            return ['status'=>true,'client_says'=>$client_response,
                'username'=>$xmpp_user_full_name,'call'=>'unregister_user'];

        } catch (Exception $e) {
            will_send_to_error_log("Issue regenerate_xmpp_user_password",will_get_exception_string($e));
            throw $e;
        }
    }

    //tested
    /**
     * if the function returns, it succeeds, else will throw exception
     * will save new password
     * @param int $user_id
     * @param string $force_username (optional if user_id set)
     * @param string $force_password (optional, will make random one if not set)
     * @return array , with username and password inside
     * @throws Exception
     */
    public static function regenerate_xmpp_user_password($user_id,$force_username,$force_password) {

        static::log("starting regenerating password",[
            '$user_id' =>$user_id,
            '$force_username' =>$force_username,
            '$force_password' =>$force_password,
        ],true);
        $user_id = (int)$user_id;
        if (!$user_id ) {
            throw new InvalidArgumentException("User ID and username is empty, cannot regenerate is password on Ejabberd");
        }
        try {
            if ($force_username) {
                $xmpp_user_full_name = $force_username;
            } else {
                static::get_saved_ejabberd_account($user_id, $xmpp_user_full_name, $do_not_use_this_password);
                if (!$xmpp_user_full_name) {
                    throw new InvalidArgumentException("User id of $user_id does not have a username stored for the chat system");
                }
            }

            if ($force_password) {
                $xmpp_user_password = $force_password;
            } else {
                static::get_saved_ejabberd_account($user_id, $do_not_use_this_username, $xmpp_user_password);
                if (empty($xmpp_user_password)) {
                    $xmpp_user_password = wp_generate_password(8, false);
                }
            }

            if (!trim($xmpp_user_password) || !trim($xmpp_user_full_name)) {
                throw new LogicException("Username '$xmpp_user_full_name' or password '$xmpp_user_password' is blank or just whitespace");
            }

            $client = static::get_ejabberd_client();
            static::log("calling client->changePassword",[
                '$xmpp_user_full_name' =>$xmpp_user_full_name,
                '$xmpp_user_password' =>$xmpp_user_password,
            ],true);
            $client_response = $client->changePassword($xmpp_user_full_name,$xmpp_user_password);

            static::log("response client->changePassword", $client_response,true);

            if (!is_array($client_response)) {
                throw new RuntimeException("Ejabberd returned something unexpected: ". strval($client_response));
            }
            if (!isset($client_response["status"])) {
                throw new RuntimeException("Ejabberd returned an array without the status key: ".
                    json_encode($client_response));
            }
            if ($client_response["status"] !== "success") {
                throw new RuntimeException("Ejabberd was not successful in using the api changePassword,".
                    json_encode($client_response));
            }
            if (!isset($client_response["ejabberd"])) {
                throw new RuntimeException("Ejabberd returned an array without the ejabberd key: ".
                    json_encode($client_response));
            }
            if ($client_response["ejabberd"] !== 0) { //Status code (0 on success, 1 otherwise)
                throw new RuntimeException("Ejabberd says issue with changing password  for $xmpp_user_full_name".
                    json_encode($client_response));
            }

           //guess its okay, lets save the new password !
            static::log("saving password to meta ",[
                '$user_id' => $user_id,
                'xmpp_password' => $xmpp_user_password
            ],false);

            update_user_meta($user_id, 'xmpp_password', $xmpp_user_password, false);
            return ['status'=>true,'client_says'=>$client_response,
                'username'=>$xmpp_user_full_name,'password'=>$xmpp_user_password,'call'=>'regenerate_xmpp_user_password'];

        } catch (Exception $e) {
            will_send_to_error_log("Issue regenerate_xmpp_user_password",will_get_exception_string($e));
            throw $e;
        }
    }

    //tested
    /**
     * Checks if the user_id has its account stored on the server
     * @param string $xmpp_user_full_name
     * @param string $xmpp_user_password
     * @return array , status true if the account name exists on the server, false if it does not
     * @throws Exception, for anything that may happen that is not supposed to
     */
    public static function check_ejabberd_account_login($xmpp_user_full_name,$xmpp_user_password) {

        static::log("starting check_ejabberd_account_login",[
            '$xmpp_user_full_name' =>$xmpp_user_full_name,
            '$xmpp_user_password' =>$xmpp_user_password,
        ],true);

        try {
            if (!trim($xmpp_user_password) || !trim($xmpp_user_full_name)) {
                throw new LogicException("Username '$xmpp_user_full_name' or password '$xmpp_user_password' is blank or just whitespace");
            }

            $client = static::get_ejabberd_client();

            static::log("calling client->checkAccount",[
                '$xmpp_user_full_name' =>$xmpp_user_full_name,
                '$xmpp_user_password' =>$xmpp_user_password,
            ],true);
            $client_response = $client->checkPassword($xmpp_user_full_name,$xmpp_user_password);
            static::log("response client->checkPassword",$client_response,true);


            if (!is_array($client_response)) {
                throw new RuntimeException("Ejabberd returned something unexpected: ". strval($client_response));
            }
            if (!isset($client_response["status"])) {
                throw new RuntimeException("Ejabberd returned an array without the status key: ".
                    json_encode($client_response));
            }

            if ($client_response["status"] !== "success") {
                throw new RuntimeException("Ejabberd was not successful in using the api checkAccount,".
                    " this is different from the user not existing: ". json_encode($client_response));
            }
            if (!isset($client_response["ejabberd"])) {
                throw new RuntimeException("Ejabberd returned an array without the ejabberd key: ".
                    json_encode($client_response));
            }
            if ($client_response["ejabberd"] === 0) {
                static::log("ejabberd is 1 so returning true",$client_response["ejabberd"],true);
                return ['status'=>true,'client_says'=>$client_response,'call'=>'check_ejabberd_account_login'];
            }


            static::log("ejabberd is not 0 so returning false",$client_response["ejabberd"],true);
            return ['status'=>false,'client_says'=>$client_response,'call'=>'check_ejabberd_account_login'];


        } catch (Exception $e) {
            will_send_to_error_log("Issue check_ejabberd_account_login",will_get_exception_string($e));
            throw $e;
        }

    }

    //tested
    /**
     * Checks if the user_id has its account stored on the server
     * @param int $user_id
     * @param string $force_username
     * @return array , status true if the account name exists on the server, false if it does not
     * @throws Exception, for anything that may happen that is not supposed to
     */
    public static function check_ejabberd_account_exists($user_id,$force_username = '') {

        static::log("starting check_ejabberd_account_exists",[
            '$user_id' =>$user_id,
            '$force_username' => $force_username
        ],true);
        $user_id = (int)$user_id;

        try {
            if ($force_username) {
                $xmpp_user_full_name = $force_username;
            } else {
                static::get_saved_ejabberd_account($user_id, $xmpp_user_full_name, $xmpp_user_password);
                if (!$xmpp_user_full_name) {
                    throw new InvalidArgumentException("User id of $user_id does not have a username stored for the chat system");
                }
            }

            if ( !trim($xmpp_user_full_name)) {
                throw new LogicException("Username '$xmpp_user_full_name'  is blank or just whitespace");
            }


            $client = static::get_ejabberd_client();

            static::log("calling client->checkAccount",[
                '$xmpp_user_full_name' =>$xmpp_user_full_name,
            ],true);
            $client_response = $client->checkAccount($xmpp_user_full_name);
            static::log("response client->checkAccount",$client_response,true);


            if (!is_array($client_response)) {
                throw new RuntimeException("Ejabberd returned something unexpected: ". strval($client_response));
            }
            if (!isset($client_response["status"])) {
                throw new RuntimeException("Ejabberd returned an array without the status key: ".
                    json_encode($client_response));
            }
            if ($client_response["status"] !== "success") {
                throw new RuntimeException("Ejabberd was not successful in using the api checkAccount,".
                    " this is different from the user not existing: ". json_encode($client_response));
            }
            if (!isset($client_response["ejabberd"])) {
                throw new RuntimeException("Ejabberd returned an array without the ejabberd key: ".
                    json_encode($client_response));
            }
            if ($client_response["ejabberd"] === 0) {
                static::log("ejabberd is 1 so returning true",$client_response["ejabberd"],true);
                return ['status'=>true,'client_says'=>$client_response,'call'=>'check_ejabberd_account_exists'];
            }


            static::log("ejabberd is not 0 so returning false",$client_response["ejabberd"],true);
            return ['status'=>false,'client_says'=>$client_response,'call'=>'check_ejabberd_account_exists'];



        } catch (Exception $e) {
            will_send_to_error_log("Issue check_ejabberd_account_exists",will_get_exception_string($e));
            throw $e;
        }

    }

    /**
     * creates a new username and new password
     * will check to see if user already has stored username, and/or password and will try to use that instead first
     * after creating it successfully, will store in user meta data
     * @param int $user_id
     * @param string $force_username
     * @param string $force_password
     * @throws Exception
     * @return array  with username,password inside
     */
    public static function create_new_chat_user_account($user_id,$force_username,$force_password) {

        static::log("starting create_new_chat_user_account",[
            '$user_id' =>$user_id,
            '$force_username' =>$force_username,
            '$force_password' =>$force_password,
        ],true);
        $user_id = (int)$user_id;
        try {
            if (!$user_id) {
                throw new InvalidArgumentException("User ID is empty, cannot create an account on Ejabberd");
            }
            if ($force_username) {
                $xmpp_user_full_name = $force_username;
                $decorator = 0;
            } else {
                static::get_saved_ejabberd_account($user_id, $xmpp_user_full_name, $do_not_use_this_password);
                if ($xmpp_user_full_name) {
                    $decorator = 0;
                } else {
                    $info = get_userdata($user_id);
                    $options = get_option('xmpp_settings');

                    $prefix = empty($options['xmpp_prefix']) ? '' : '_' . $options['xmpp_prefix'];
                    $xmpp_user_full_name = $info->user_login . $prefix;
                    $decorator = 1;
                }
            }

            if ($force_password) {
                $xmpp_user_password = $force_password;
            } else {
                static::get_saved_ejabberd_account($user_id, $do_not_use_this_username, $xmpp_user_password);
                if (empty($xmpp_user_password)) {
                    $xmpp_user_password = wp_generate_password(8, false);
                }
            }


            if (!trim($xmpp_user_password) || !trim($xmpp_user_full_name)) {
                throw new LogicException("Username '$xmpp_user_full_name' or password '$xmpp_user_password' is blank or just whitespace");
            }

            static::log("create_new_chat_user_account gathered args for server",[
                '$user_id' =>$user_id,
                '$xmpp_user_full_name' =>$xmpp_user_full_name,
                '$decorator' =>$decorator,
            ],false);

            $client = static::get_ejabberd_client();

            $count_max = 100; //give up after a hundred tries
            $count = 0;
            $client_response = null;
            $decorated_name = '';
            $last_exception = null;
            while(($count < $count_max) && empty($client_response)) {
                if ($decorator) {
                    $decorated_name = $xmpp_user_full_name.'-'. $decorator;
                } else {
                    $decorated_name = $xmpp_user_full_name;
                    $decorator = 1000;
                }

                $count++;
                try {
                    static::log("starting call for client->register",[
                        '$decorated_name' =>$decorated_name,
                        '$xmpp_user_password' =>$xmpp_user_password,
                    ],false);

                    $client_response = $client->register($decorated_name, $xmpp_user_password);
                    static::log("response of call client->register",$client_response,false);
                    break;
                } catch (Exception $e) {
                    $decorator++;
                }
            }

            if (empty($client_response)) {
                throw new RuntimeException("Could not successfully create account for $xmpp_user_full_name : ".
                    "last attempt was '$decorated_name' and we tried $count times. Last exception was ".
                    will_get_exception_string($last_exception));
            }

            if (empty($decorated_name)) {
                throw new RuntimeException("no clue what happened when I called api register with an empty username?: ".
                    json_encode($client_response));
            }

            if (!isset($client_response["status"])) {
                throw new RuntimeException("Ejabberd returned an array without the status key in api register: ".
                    json_encode($client_response));
            }
            if ($client_response["status"] === "success") {

                if (!isset($client_response["ejabberd"])) {
                    throw new RuntimeException("Ejabberd returned an array without the ejabberd key: ".
                        json_encode($client_response));
                }

                static::log("Ejabberd says  ",$client_response["ejabberd"],false);

                static::log("response status says success so updating meta for user id of ",$user_id,false);

                static::log("saving password to meta ",[
                    '$user_id' => $user_id,
                    'xmpp_password' => $xmpp_user_password
                ],false);
                update_user_meta($user_id, 'xmpp_password', $xmpp_user_password, false);

                static::log("saving username to meta ",[
                    '$user_id' => $user_id,
                    'xmpp_username' => $decorated_name
                ],false);
                update_user_meta($user_id, 'xmpp_username', $decorated_name);

            } else if (isset($client_response['code']) && ($client_response['code'] == '10090')) {
                static::log("response status is NOT success but code is 10090 so resetting password ",[
                    '$decorated_name' => $decorated_name,
                    '$xmpp_user_password' => $xmpp_user_password
                ],false);
                    static::regenerate_xmpp_user_password(null,$decorated_name,$xmpp_user_password);
                    //password already saved in the regenerate function above

                static::log("saving username to meta ",[
                    '$user_id' => $user_id,
                    'xmpp_username' => $decorated_name
                ],false);
                    update_user_meta($user_id, 'xmpp_username', $decorated_name);

            } else {
                throw new RuntimeException("Ejabberd was not successful in using the api register,".
                    " this is different from the user not existing: ". json_encode($client_response));
            }

            $wrapper = new EjabberdWrapper(false);
            $subscription_talk = $wrapper->subscribeToAssistant($user_id,false);
            return ['status'=>true,'client_says'=>$client_response,'subscription_talk' => $subscription_talk,
                'username'=>$decorated_name,'password'=>$xmpp_user_password,'call'=>'create_new_chat_user_account'];
        } catch (Exception $e) {
            will_send_to_error_log("Issue create_new_chat_user_account",will_get_exception_string($e));
            throw $e;
        }
    }
    
    public static function refresh_user($user_id) {
        $ret = [];
        $user_id = (int)$user_id;
        if (!$user_id) {
            return ['status'=>0,'message'=>"User id is empty"];
        }
        $info = get_userdata($user_id);
        $email_span = '<span class="fl-refresh-chat-email">'.$info->user_email.'</span>';
        $id_span = '<span class="fl-refresh-chat-id">'.$user_id.'</span>';
        $user_span = $email_span.$id_span;
        try {

            $ret[] = $info;
            $ret[] = static::get_saved_ejabberd_account($user_id, $chat_username, $chat_password);
            if ($chat_username && $chat_password) {
                $check = static::check_ejabberd_account_login($chat_username, $chat_password);
                $ret[] = $check;
                $b_ok = $check['status'];
                if ($b_ok) { throw new FLFinishLogic("$user_span Can login ok with existing chat acount username <em>$chat_username</em> and existing password <em>$chat_password</em>");}
            }  
            //fall through, could not login with existing, or not enough to login with!
            if($chat_username) {
                $check = static::check_ejabberd_account_exists($user_id);
                $ret[] = $check;
                $b_ok = $check['status'];
                if ($b_ok) {
                    //reset password, account exists
                    $ret[] = static::regenerate_xmpp_user_password($user_id,null,null);
                    $ret[] = static::get_saved_ejabberd_account($user_id, $chat_username, $chat_password);
                    $check = static::check_ejabberd_account_login($chat_username, $chat_password);
                    $ret[] = $check;
                    $b_ok = $check['status'];
                    if ($b_ok) { throw new FLFinishLogic("$user_span  Can login ok with existing chat acount username <em>$chat_username</em> and reset password <em>$chat_password</em>");}
                    throw new FLFinishLogicWithError("$user_span Cannot login with existing chat acount username <em>$chat_username</em> and reset password <em>$chat_password</em>");
                } else {
                    //account does not exist
                    $ret[] = static::create_new_chat_user_account($user_id,null,null);
                    $ret[] = static::get_saved_ejabberd_account($user_id, $chat_username, $chat_password);
                    $check = static::check_ejabberd_account_login($chat_username, $chat_password);
                    $ret[] = $check;
                    $b_ok = $check['status'];
                    if ($b_ok) { throw new FLFinishLogic("$user_span Can login ok with old, recreated chat account username <em>$chat_username</em> and new password <em>$chat_password</em>");}
                    throw new FLFinishLogicWithError("$user_span Cannot login with new old, recreated chat account username <em>$chat_username</em> and new password <em>$chat_password</em>");
                }
            } else {
                //account is not known to php side, cannot guess
                $ret[] = static::create_new_chat_user_account($user_id,null,null);
                $ret[] = static::get_saved_ejabberd_account($user_id, $chat_username, $chat_password);
                $check = static::check_ejabberd_account_login($chat_username, $chat_password);
                $ret[] = $check;
                $b_ok = $check['status'];
                if ($b_ok) { throw new FLFinishLogic("$user_span Can login ok with new chat account username <em>$chat_username</em> and new password <em>$chat_password</em>");}
                throw new FLFinishLogicWithError("$user_span Cannot login with new chat account username <em>$chat_username</em> and new password <em>$chat_password</em>");
            }
        } 
        catch (FLFinishLogic $f) {
            return ['status'=>1,'message'=>$f->getMessage()];
        }
        catch (FLFinishLogicWithError $fe) {
            return ['status'=>0,'message'=>$fe->getMessage()];
        }
        catch (Exception $e) {
            return ['status'=>0,'message'=>"User $user_id had error:".will_get_exception_string($e)];
        }
    }

    public static function action() {


        if (current_user_can('administrator') ) {

            //test stuff here
            $test_command = FLInput::get('test_command');
            $test_user_id = FLInput::get('test_user_id');
            $any_username = FLInput::get('any_username');
            $any_password = FLInput::get('any_password');

            if ($test_command) {
                $what = [];
                try {
                    switch ($test_command) {
                        case 'ping' : {
                            $what = ['pinged!'];
                            break;
                        }
                        case 'check_ejabberd_account':
                            {
                                $what = static::check_ejabberd_account_exists($test_user_id);
                                break;
                            }

                        case 'check_ejabberd_account_any':
                            {
                                $what = static::check_ejabberd_account_exists(null,$any_username);
                                break;
                            }

                            //check_ejabberd_account_exists
                        case 'check_ejabberd_account_login':
                            {
                                static::get_saved_ejabberd_account($test_user_id,$username,$password);
                                $what = static::check_ejabberd_account_login($username,$password);
                                break;
                            }
                        case 'check_ejabberd_account_login_any':
                            {
                                if (!$any_password || !$any_username) {throw new InvalidArgumentException("Need both any_username and any_password");}
                                $what = static::check_ejabberd_account_login($any_username,$any_password);
                                break;
                            }
                        case 'create_new_chat_user_account': {
                            $what = [];
                            $what[] = static::get_saved_ejabberd_account($test_user_id,$username,$password);
                            $what[] = static::create_new_chat_user_account($test_user_id,$username,$password);
                            break;
                        }
                        case 'regenerate_xmpp_user_password': {
                            $what = static::regenerate_xmpp_user_password($test_user_id,null,null);
                            break;
                        }
                        case 'regenerate_xmpp_user_password_old': {
                            $what = [];
                            $what[] = static::get_saved_ejabberd_account($test_user_id,$username,$password);
                            $what[] = static::regenerate_xmpp_user_password($test_user_id,$username,$password);
                            break;
                        }
                        case 'get_saved_ejabberd_account': {
                            $what = static::get_saved_ejabberd_account($test_user_id,$username,$password);
                            break;
                        }
                        case 'unregister' : {
                            $what = static::unregister_user($test_user_id,null);
                            break;
                        }
                        case 'test_run' : {
                            $ret = [];
                            try {
                                $ret[] = static::get_saved_ejabberd_account($test_user_id, $username, $password);
                                if ($username && $password) {
                                    $check = static::check_ejabberd_account_login($any_username, $any_password);
                                    $ret[] = $check;
                                    $b_ok = $check['status'];
                                    if ($b_ok) { throw new FLFinishLogic('Can login ok with existing password and username');}
                                } elseif ($username) {
                                    $check = static::check_ejabberd_account_exists($test_user_id);
                                    $ret[] = $check;
                                    $b_ok = $check['status'];
                                    if ($b_ok) {
                                        //reset password, account exists
                                        $ret[] = static::regenerate_xmpp_user_password($test_user_id,null,null);
                                        $ret[] = static::get_saved_ejabberd_account($test_user_id, $username, $password);
                                        $check = static::check_ejabberd_account_login($any_username, $any_password);
                                        $ret[] = $check;
                                        $b_ok = $check['status'];
                                        if ($b_ok) { throw new FLFinishLogic('Can login ok with reset password and username');}
                                    } else {
                                        //account does not exist
                                        $ret[] = static::create_new_chat_user_account($test_user_id,null,null);
                                        $ret[] = static::get_saved_ejabberd_account($test_user_id, $username, $password);
                                        $check = static::check_ejabberd_account_login($any_username, $any_password);
                                        $ret[] = $check;
                                        $b_ok = $check['status'];
                                        if ($b_ok) { throw new FLFinishLogic('Can login ok with new username and password');}
                                    }
                                }



                            }
                            catch (FLFinishLogic $logic) {
                                $ret[] = $logic->getMessage();
                            }
                            catch (Exception $ie) {
                                $ret[] = "Error: ". will_get_exception_string($ie);
                            }
                            $what = $ret;
                            break;
                        }
                        default: {
                            throw new InvalidArgumentException("Need to pick an existing test command, you chose ". $test_command);
                        }
                    }
                    wp_send_json(['test-success'=> true,'data'=>$what]);
                } catch (Exception $e) {
                    wp_send_json(['test-success'=> false,'data'=>$what,'message'=>will_get_exception_string($e)]);
                }
                //if there is a test done, we do not execute the rest of the ajax
            }
        }


        //the user id is the logged in user_id, or its 0 for public not logged in
        $user_id = (int)get_current_user_id();

        /**
         * The ajax response is structured like this: (copied from the js handler file)
         * @typedef {object} ResetChatResponse
         * @property {string} status error|success|do_no_action
         * @property {string} msg
         * @property {string} chat_account_login_name
         * @property  {string} chat_account_login_password
         */
        $ret = new FreelinguistRefreshChatCredentials();
        /*
         * If no id
         *    log a notice that the guest user account is having issues
         *          we may want to add in a message queue for notices, that throttle emails out to once an hour for repeated thousands of these
         *     tell the ajax to not restart the chat library with new credentials
         *              simply do not make new credentials for the guest user
         *              return a non-error, do nothing status in the json
         *              exit
          */
        if (!$user_id) {
            will_send_to_error_log("Guest account is having login issues");
            $ret->status = 'do_no_action';
            $ret->msg = 'Guest account issues are fixed on the admin page';
            wp_send_json($ret);
            exit; //wp_send_json dies , but added here for clarity
        }


        try {


            throw new LogicException("Not implemented yet, but will be the test code above, mostly");
        } catch (Exception $e) {

            /**
             * If any errors or exceptions in the above, they will be thrown to here, and the steps below skipped
             * If error
             *     return an error status in the json
             *     exit
             */
            $ret->status = 'error';
            $ret->msg = $e->getMessage();
            wp_send_json($ret);
            exit; //wp_send_json dies , but added here for clarity
        }
    }
}
