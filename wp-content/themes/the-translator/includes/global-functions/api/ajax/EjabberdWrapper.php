<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 10/4/2019
 * Time: 6:40 AM
 */
require_once ABSPATH . 'wp-content/themes/the-translator/includes/ejabber/vendor/autoload.php';

//use Monolog\Logger;
//use Monolog\Handler\StreamHandler;

use Cielu\Ejabberd\EjabberdClient;

class EjabberdWrapper
{
    private $db;
    private $host;
    private $prefix;
    //private $info;
    /**
     * @var EjabberdClient $client
     */
    //private $client;
    private $restClient;
    //private $username;
    //private $password;
    private $assistantRoomId;
    private $assistantUsername;

    public function __construct($b_add_hooks = true)
    {
        $options = get_option('xmpp_settings');

        $this->db = $GLOBALS['wpdb'];
        $this->assistantRoomId = 'project_alerts';
        $this->assistantUsername = 'assistant';
        $this->host = empty($options['xmpp_domain'])?'': $options['xmpp_domain'];
        if (empty($this->host)) {
            will_send_to_error_log("Chat setting host is empty");
            return;
        }
        $this->prefix = empty($options['xmpp_prefix'])?'': '_'. $options['xmpp_prefix'];
        $uri = empty($options['xmpp_api_address']) ? '' : $options['xmpp_api_address'];
        $type = empty($options['xmpp_token_type']) ? 'Bearer' : $options['xmpp_token_type'];
        $code = empty($options['xmpp_auth_code']) ? '' : $options['xmpp_auth_code'];
        try {
            $this->restClient = new EjabberdClient([
                'baseUri' => $uri, // must use http or https
                'authorization' => $type . " " . $code
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
            will_send_to_error_log("Killing script due to chat init error",will_get_exception_string($e));
            die;
        }
        $this->init($b_add_hooks);
    }

    private function init($b_add_hooks = true)
    {
        if ($b_add_hooks) {
            //$this->createAssistantRoom();
            //$this->sendBroadcast("Hello Welcome to FL", 'Test Announcement');die;
            add_action('user_register', [$this, 'create_account']);
            add_action('wp_login', [$this, 'create_account_check'],10,2);
            add_action('wp_ajax_create_chat_room', [$this, 'create_chat_room']);
            add_action('wp_ajax_get_room_user', [$this, 'get_rooms']);
            add_action('wp_ajax_nopriv_get_room_user', [$this, 'get_rooms']); //code-notes added ajax for non logged in user for getting rooms

            add_action('wp_ajax_send_broadcast', [$this, 'broadcast']);
            add_action('wp_ajax_update_chat_block_status', [$this, 'update_chat_block_status']);
            add_action('wp_footer', [$this, 'append_chat_html']);

            //code-notes to make new guest account: cannot make new class instance so will listen on regular instance
            add_action('freelinguist_create_chat_guest_account', [$this, 'create_guest_account']);
        }
    }

    public function broadcast()
    {
        $this->sendBroadcast("this is a server level message 2", 'Announcement');
    }

    public function sendBroadcast($message, $subject)
    {
        if (empty($this->host)) {
            will_send_to_error_log("Chat setting host is empty. Cannot send broadcast");
            return;
        }
        $url_for_broadcast = $this->host . '/announce/all';
        //code-bookmark SendBroadcast for ejabber
        /*$res =  */ $this->restClient->sendMessage('admin@' . $this->host,
            $url_for_broadcast,
            $subject,
            $message,
            'headline'
        );
        //will_send_to_error_log("CHAT BROADCAST URL AND RESULTS ",[$url_for_broadcast,$res]);
        //  print_r($res);die('s');
    }


    public function update_chat_block_status()
    {
        $room_id = $_POST['room_id'];
        $status = $_POST['status'];
        $this->db->update('wp_fl_chat_rooms', ['is_blocked' => $status], ['room_id' => $room_id]);

        die;

    }

    /**
     * code-notes modified get_rooms to deal with users not logged in
     */
    public function get_rooms()
    {
        /*
         * current-php-code 2020-Sep-30
         * ajax-endpoint  get_room_user
         * input-sanitized : (none)
         */
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $usermeta = get_userdata($user->ID);
            $rooms = [];
            $rooms[] = [
                'project_title' => 'Assistant',
                'username' => 'System',
                'nickname' => 'System',
                'avatar' => get_stylesheet_directory_uri() . '/images/fl-chat.png',
                'room_id' => $this->assistantRoomId,
                'isBlocked' => 'false',
                'readonly' => 1
            ];
            if ($user_roles = $usermeta->roles[0] == 'customer') {
                $query = "SELECT * FROM wp_fl_chat_rooms WHERE employer_id = $user->ID";
                $result = $this->db->get_results($query);
                if (!empty($result)) {
                    foreach ($result as &$res) {

                        $avatar_fragment = get_user_meta($res->freelancer_id, 'user_image', true);
                        $avatar = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($avatar_fragment,FreelinguistSizeImages::TINY,true);
                        //code-notes [image-sizing]  now using tiny profile pic for avatar

                        $counter_user = get_userdata($res->freelancer_id);
                        $rooms[] = [
                            'project_title' => $res->room_title,
                            'username' => static::get_xmpp_username($res->freelancer_id),
                            'nickname' => substr($counter_user->display_name, 0, 10),
                            'avatar' => $avatar,
                            'room_id' => $res->room_id,
                            'isBlocked' => $res->is_blocked
                        ];
                    }

                }
            } elseif ($user_roles = $usermeta->roles[0] == 'translator') {
                $query = "SELECT * FROM wp_fl_chat_rooms WHERE freelancer_id = $user->ID";
                $result = $this->db->get_results($query);
                if (!empty($result)) {
                    foreach ($result as &$res) {

                        $avatar_fragment = get_user_meta($res->employer_id, 'user_image', true);
                        $avatar = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($avatar_fragment,FreelinguistSizeImages::TINY,true);
                        //code-notes [image-sizing]  using tiny profile pic for ejabberd

                        $counter_user = get_userdata($res->employer_id);
                        $rooms[] = [
                            'project_title' => $res->room_title,
                            'username' => static::get_xmpp_username($res->employer_id),
                            'nickname' => substr($counter_user->display_name, 0, 10),
                            'avatar' => $avatar,
                            'room_id' => $res->room_id,
                            'isBlocked' => $res->is_blocked
                        ];
                    }

                }
            }
            echo json_encode($rooms);
            exit();
        } else {
            //code-notes get the non logged in room(s), this is a static list for right now
            $rooms = [];
            $rooms[] = [
                'project_title' => 'Assistant',
                'username' => 'System',
                'nickname' => 'System',
                'avatar' => get_stylesheet_directory_uri() . '/images/fl-chat.png',
                'room_id' => $this->assistantRoomId,
                'isBlocked' => 'false',
                'readonly' => 1
            ];
            echo json_encode($rooms);
            exit();
        }

    }

    protected static function get_xmpp_username($user_id) {
        if (!intval($user_id)) {
            will_send_to_error_log("empty user id while asking for ebbard username");
            throw new RuntimeException("empty user id while getting the ejaabberd user name");
        }
        $user_name =  get_user_meta($user_id, 'xmpp_username', true);
        if ($user_name) {return $user_name;}
       return ''; //if there is not a user name set, then return an empty string
    }

    /**
     * returns false if no data for ejabber login else returns array of info
     * @return array|false
     */
    public static function get_xmpp_credentials()
    {
        $options = get_option('xmpp_settings');

        if (is_user_logged_in()) { //code-notes changed this function to not assume log in (wp_get_current_user is always truthful)
            $user = wp_get_current_user();

            $pass = get_user_meta($user->ID, 'xmpp_password', true);

            $avatar_fragment = get_user_meta($user->ID, 'user_image', true);
            $avatar = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($avatar_fragment,FreelinguistSizeImages::TINY,true);
            //code-notes [image-sizing]  getting user tiny icon for profile loaded into js for each page load
            $data = array(
                'jid' => static::get_xmpp_username($user->ID) .  '@' . $options['xmpp_domain'],
                'password' => $pass,
                'login_profile_image' => $avatar,
                'logged_in_id' => get_current_user_id()
            );
            return $data;

        } else {
            //code-notes get guest credentials
            $guest_broadcast_settings = get_option('guest_broadcast_settings',[]);
            if (empty($guest_broadcast_settings)) {return false;}

            $guest_image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory('',FreelinguistSizeImages::TINY,true);

            $guest_broadcast_user_password = $guest_broadcast_user_name = '';
            if (array_key_exists('account_name',$guest_broadcast_settings)) {
                $guest_broadcast_user_name = $guest_broadcast_settings['account_name'];
            }
            if (array_key_exists('account_password',$guest_broadcast_settings)) {
                $guest_broadcast_user_password = $guest_broadcast_settings['account_password'];
            }
            if (empty($guest_broadcast_user_name)) {return false;} //code-notes if guest not set then do not try to return array
            $ret = [
                'jid' => $guest_broadcast_user_name  . '@' . $options['xmpp_domain'],
                'password' => $guest_broadcast_user_password,
                'login_profile_image' => $guest_image,
                'logged_in_id' => 0
            ];

            return $ret;
        }
    }

    public function subscribeToAssistant($user_id,$b_check_login=true)
    {
        if ($b_check_login) {
            if (!is_user_logged_in()) return [];
        }

        if (empty($this->host)) {
            will_send_to_error_log("Chat setting host is empty. Cannot subscribe to assistant");
            if(!$b_check_login) {
                throw new RuntimeException("Chat setting host is empty. Cannot subscribe to assistant");
            }
            return [];
        }
        $info = get_userdata($user_id);
        /**
         * Subscribing user to project alert room
         */
            $ret = [];
         $ret[] = $this->restClient->setRoomAffiliation(static::get_xmpp_username($user_id) . '@' . $this->host, $this->assistantRoomId, 'member');
         $ret[] = $this->restClient->subscribeRoom(static::get_xmpp_username($user_id). '@' . $this->host, $info->data->display_name, $this->assistantRoomId);
        //echo "<pre>"; print_r($res_1); print_r($res_2);die;
        return $ret;
    }

    /**
     * @param string $user_login
     * @param WP_User $user
     */
    public function create_account_check($user_login, $user ) {
        will_do_nothing($user_login);
        $user_id = $user->ID;
        if ($user_id) {
            $this->create_account($user_id);
        }
    }

    /** Create user account on ejabberd
     * @param $user_id
     */
    public function create_account($user_id)
    {
        //code-notes we are going to check to see if the user already has the chat account,
        // if so, then do nothing else, that way can be called each login

        $already_made_chat_username = static::get_xmpp_username($user_id);
        if ($already_made_chat_username) return;
        $info = get_userdata($user_id);

        $xmpp_user = $info->user_login .$this->prefix;

//        $xmpp_user = $info->user_login . '_development';
//        $xmpp_user = $info->user_login . '_live';
        $pass = wp_generate_password(8, false);
        //code-notes , make sure user is unique
        $response = $this->register_user_with_decoration($xmpp_user,$pass,1001, $decorator,$decorated_name);//$this->restClient->register($xmpp_user, $pass);
        if ($response['status'] == 'success') {
            update_user_meta($user_id, 'xmpp_password', $pass, false);
            update_user_meta($user_id,'xmpp_username',$decorated_name);
        } elseif ($response['code'] == '10090') {
            $this->restClient->changePassword($decorated_name, $pass);
            update_user_meta($user_id, 'xmpp_password', $pass, false);
            update_user_meta($user_id,'xmpp_username',$decorated_name);
        }

        $this->subscribeToAssistant($user_id);

    }

    /**
     * code-notes creating the method to add in a guest jabber account
     */
    public function create_guest_account() {

        if (empty($this->host)) {
            will_send_to_error_log("Chat setting host is empty. Cannot create guest account");
            return;
        }
        //if guest account already created , then unregister first (and note in log)
        $guest_broadcast_settings = get_option('guest_broadcast_settings',[]);
        if (empty($guest_broadcast_settings)) {
            $guest_broadcast_settings = [];
        }
        $guest_broadcast_settings['log'] = [];

        $guest_broadcast_user_name = '';
        if (array_key_exists('account_name',$guest_broadcast_settings)) {
            $guest_broadcast_user_name = $guest_broadcast_settings['account_name'];
        }

        //code-notes unregister the user if the guest name is already existing
        if ($guest_broadcast_user_name) {
            $guest_broadcast_settings['log'][] = "Guest user already exists. Trying to unregister user. TS is ". time();
            update_option('guest_broadcast_settings',$guest_broadcast_settings);
            try {
                $this->restClient->unregister($guest_broadcast_user_name);
                $guest_broadcast_settings['log'][] = "unregistered $guest_broadcast_user_name : ". time();
            } catch (Exception $e) {
                $guest_broadcast_settings['log'][] = "Error while unregistering ejabber $guest_broadcast_user_name : ".$e->getMessage();
                will_send_to_error_log("Error while unregistering ejabber $guest_broadcast_user_name : ", [
                   'class'=> get_class($e),
                   'message'=> $e->getMessage(),
                   'code'=> $e->getCode()
                ]);
                return;
            }
        }
        $da_guest_name = 'AnnouncementforGuest';
        $guest_display_name = 'AnnouncementforGuest';
        if (empty($da_guest_name)) {
            $guest_broadcast_settings['log'][] = "Cannot figure out site name. TS is ". time();
            update_option('guest_broadcast_settings',$guest_broadcast_settings);
            return;
        }
        $guest_broadcast_settings['log'][] = "Starting to register guest. TS is ". time();
        $guest_broadcast_settings['log'][] = "Guest User Name is ". $da_guest_name;
        $guest_broadcast_settings['log'][] = "Guest display name is ". $guest_display_name;
        //if got to here make new account and assign it to listen to announcements
        $start_guest_number = (int)get_option('guest_broadcast_decorator_last',1000);
        $start_guest_number++;
        $guest_broadcast_user_name = $xmpp_user = $da_guest_name .$this->prefix;
        $guest_broadcast_settings['log'][] = "Guest user_name is ". $guest_broadcast_user_name;
//        $xmpp_user = $info->user_login . '_development';
//        $xmpp_user = $info->user_login . '_live';
        $guest_broadcast_user_password = $pass = wp_generate_password(8, false);
        $guest_broadcast_settings['log'][] = "Guest password is ". $guest_broadcast_user_password;
        //code-notes , make sure user is unique
        $response = $this->register_user_with_decoration($xmpp_user,$pass,$start_guest_number,$decorator,$decorated_name);
        $xmpp_user = $guest_broadcast_user_name = $decorated_name;
        $guest_broadcast_settings['log'][] = "Response of register is below ";
        $guest_broadcast_settings['log'][] = $response;
        $b_can_save = false;
        if ($response['status'] == 'success') {
            $b_can_save = true;
        } elseif ($response['code'] == '10090') {
            $this->restClient->changePassword($xmpp_user, $pass);
            $b_can_save = true;
        }

        if ($b_can_save) {
            update_option('guest_broadcast_decorator_last',$decorator);
            $response_set_room = $this->restClient->setRoomAffiliation($guest_broadcast_user_name . '@' . $this->host, $this->assistantRoomId, 'member');
            $guest_broadcast_settings['log'][] = "Response of setRoomAffiliation is below ";
            $guest_broadcast_settings['log'][] = $response_set_room;
            if ($response_set_room['status'] !== 'success') {
                $b_can_save = false;
                $guest_broadcast_settings['log'][] = "set room did not return success, so not saving";
            }
        }

        if ($b_can_save) {
            $response_subscribe = $this->restClient->subscribeRoom($guest_broadcast_user_name . '@' . $this->host, $guest_display_name, $this->assistantRoomId);
            $guest_broadcast_settings['log'][] = "Response of subscribeRoom is below ";
            $guest_broadcast_settings['log'][] = $response_subscribe;
            if ($response_subscribe['status'] !== 'success') {
                $b_can_save = false;
                $guest_broadcast_settings['log'][] = "subscribe did not return success, so not saving";
            }
        }

        if ($b_can_save) {
            $guest_broadcast_settings['account_name'] = $guest_broadcast_user_name;
            $guest_broadcast_settings['account_password'] = $guest_broadcast_user_password;
            $guest_broadcast_settings['display_name'] = $guest_display_name;
            update_option('guest_broadcast_settings',$guest_broadcast_settings);
            return;
        }
    }

    public function register_user_with_decoration($name,$password,$start_at,&$decorator,&$new_user_name) {

        $count_max = 100; //give up after a hundred tries
        $decorator = intval($start_at);
        $count = 0;
        while($count < $count_max) {
            $new_user_name = $name.'-'. $decorator;
            $count++;
            try {
                $what = $this->restClient->register($new_user_name, $password);
                return $what;
            } catch (Exception $e) {
                $decorator++;
            }
        }

        return ['status'=>'cannot find unique name','code'=>100];
    }

    public function create_chat_room()
    {
        /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  create_chat_room
    * input-sanitized : freelancer_id,project_id,project_type
    */
        global $wpdb;
        if (empty($this->host)) {
            will_send_to_error_log("Chat setting host is empty. Cannot create chat room");
            return;
        }
        $project_id = FLInput::get('project_id');
        $freelancer_id = (int)FLInput::get('freelancer_id');
        $fl_job_id = (int)FLInput::get('fl_job_id');
        $proposal_id = (int)FLInput::get('proposal_id');
        $content_id = (int)FLInput::get('content_id');
        $project_type = FLInput::get('project_type');
        $freelancer_info = get_userdata($freelancer_id);
        $employer_id = null;
        $current_user = wp_get_current_user();
        if ($project_type == 'project') {
            $prefix_room = $tagType = FreelinguistTags::PROJECT_TAG_TYPE;
            //$cur_jbid = $wpdb->prefix . "fl_job";

            $rowcur_jbid = $wpdb->get_results("SELECT * FROM wp_comments WHERE `user_id` = '" . $freelancer_id .
                "' AND `comment_post_ID` = '" . $project_id . "'");

            if ($rowcur_jbid) {
                $room_title = get_post_meta($project_id, 'project_title', true);
            } else {
                header('Content-type: text/json');
                echo json_encode(['status' => 'failed', 'data' => 'Invalid project']);
                exit();
            }

            $post = get_post($project_id);
            $employer_id = $post->post_author;

            $employer_info = get_userdata($employer_id);
            // print_r($employer_info);die;
            if ($current_user->ID == $freelancer_id) {
                $cur_jbid = $wpdb->prefix . "fl_job";

                $sql_statement = /** @lang text */
                    "SELECT * FROM $cur_jbid WHERE `linguist_id` = '" . $freelancer_id .
                    "' AND `project_id` = '" . $project_id . "' ";
                $hiring_result = $wpdb->get_results($sql_statement);
                if ($hiring_result) {
                    $next_user_id = $employer_id;
                    $next_username = $employer_info->user_login;
                    $jbStatus = $hiring_result[0]->job_status;

                    if ($jbStatus and $jbStatus != 'start') {
                        //freelancers can't initiate chat without getting hired
                        header('Content-type: text/json');
                        echo json_encode(['status' => 'failed', 'data' => 'You can not initiate chat without getting hired']);
                        exit();
                    }
                } else {
                    header('Content-type: text/json');
                    echo json_encode(['status' => 'failed', 'data' => 'You can not initiate chat without getting hired']);
                    exit();
                }

            } else {
                $next_user_id = $freelancer_id;
                $next_username = $freelancer_info->user_login;
            }
        } elseif ($project_type == 'competition') {
            $prefix_room = $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
            $proposal_row = $wpdb->get_results("SELECT * FROM wp_proposals WHERE `by_user` = '" . $freelancer_id .
                "' AND `post_id` = '" . $project_id . "' ");

            if ($proposal_row) {
                $room_title = get_post_meta($project_id, 'project_title', true);
                $post = get_post($project_id);
                $employer_id = $post->post_author;

                $employer_info = get_userdata($employer_id);
                if ($current_user->ID == $freelancer_id) {
                    $next_user_id = $employer_id;
                    $next_username = $employer_info->user_login;
                } else {
                    $next_user_id = $freelancer_id;
                    $next_username = $freelancer_info->user_login;
                }

            } else {
                header('Content-type: text/json');
                echo json_encode(['status' => 'failed', 'data' => 'Invalid proposal']);
                exit();
            }

        } elseif ($project_type == 'content') {
            $prefix_room = $tagType = FreelinguistTags::CONTENT_TAG_TYPE;
            $content_id = str_replace("c_", '', $project_id);
            $content_row = $wpdb->get_results("SELECT * FROM wp_linguist_content WHERE user_id IS NOT NULL AND `id` = " . $content_id);
            if ($content_row) {
                //print_r($content_row[0]); die;
                $room_title = $content_row[0]->content_title;
                $freelancer_info = get_userdata($freelancer_id);
                $employer_id = $content_row[0]->user_id;

                $employer_info = get_userdata($employer_id);

                $next_user_id = $employer_id;
                $next_username = $employer_info->user_login;
            } else {
                header('Content-type: text/json');
                echo json_encode(['status' => 'failed', 'data' => 'Invalid content']);
                exit();
            }

        } else {
            throw new LogicException("The project_type is not known, there is no case set for this. Is this a contest, project, content ? ");
        }

        try {

            $avatar_fragment = get_user_meta($next_user_id, 'user_image', true);
            $avatar = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($avatar_fragment,FreelinguistSizeImages::TINY,true);

            $room_id = $prefix_room . '_' . $project_id . '_' . $freelancer_id .$this->prefix;
            //code-notes the prefix takes care of overlapping ids between posts and content, which will eventually overlap

            //code-notes sometimes the calling code will try to make a room, when its already created, perhaps on another tab, see if it exists first

            $sql_check_room = "select id,is_blocked,room_title FROM wp_fl_chat_rooms where room_id = '$room_id'";
            $maybe_room_is_in_here = $this->db->get_results($sql_check_room);
            will_throw_on_wpdb_error($this->db,'Checking If Room exists in create_chat_room');
            if (count($maybe_room_is_in_here)) {
                $room_pk_id = intval($maybe_room_is_in_here[0]->id);
                if ($proposal_id) {
                    $wpdb->query("UPDATE wp_proposals SET chat_room_id = $room_pk_id WHERE id = $proposal_id");
                    will_throw_on_wpdb_error($wpdb,'updating room id for proposals');
                }
                if ($fl_job_id) {
                    $wpdb->query("UPDATE wp_fl_job SET chat_room_id = $room_pk_id WHERE id = $fl_job_id");
                    will_throw_on_wpdb_error($wpdb,'updating room id for fl job');
                }

                if ($content_id) {
                    $wpdb->query("UPDATE wp_linguist_content SET chat_room_id = $room_pk_id WHERE id = $content_id");
                    will_throw_on_wpdb_error($wpdb,'updating room id for content');
                }

                $is_room_blocked = false;
                if ($maybe_room_is_in_here[0]->is_blocked === 'true') {$is_room_blocked = true;}
                $room_title = $maybe_room_is_in_here[0]->room_title;
                //chat room already exists
                $response = [
                    'room_pk_id'=> $room_pk_id,
                    'avatar' => $avatar,
                    'room_string_identifier' => $room_id,
                    'room_id' => $room_id,
                    'isBlocked' => $is_room_blocked,
                    'nickname' => $next_username ,
                    'project_title' => $room_title,
                    'username' => static::get_xmpp_username($next_user_id),
                ];
                wp_send_json(['status' => true,'message'=> 'Found room, Returning this.  Did not create room', 'data' => $response]);
                exit();
            }


            if (get_user_meta($freelancer_id, 'xmpp_password', true) == '')
                $this->create_account($freelancer_id);
            if(!isset($employer_info)) {throw new LogicException('Employer Info not set');}
            if (get_user_meta($employer_info->ID, 'xmpp_password', true) == '')
                $this->create_account($employer_info->ID);

            $room_options = array(
                array('name' => 'members_only', 'value' => 'true'),
                array('name' => 'title', 'value' => $room_title),
                array('name' => 'public', 'value' => 'false'),
                array('name' => 'persistent', 'value' => 'true'),
            );
//            $room_id = $project_id . '_' . $freelancer_id . '_development';
//            $room_id = $project_id . '_' . $freelancer_id . '_live';
            //$this->restClient->destroyRoom($room_id);
            //$this->restClient->destroyRoom('undefined_undefined');
            //echo $room_id; die;
            //print_r($room_options); die;
            $this->restClient->createRoomWithOpts($room_id, $room_options);

            $this->restClient->setRoomAffiliation(static::get_xmpp_username($freelancer_info->ID) . '@' . $this->host, $room_id, 'member');
            // echo $employer_info->user_login . '@' . $this->host, $room_id, 'owner';die;
            $this->restClient->setRoomAffiliation(static::get_xmpp_username($employer_info->ID) . '@' . $this->host, $room_id, 'owner');

            $this->restClient->subscribeRoom(static::get_xmpp_username($freelancer_info->ID) . '@' . $this->host, $freelancer_info->display_name, $room_id);

            $this->restClient->subscribeRoom(static::get_xmpp_username($employer_info->ID). '@' . $this->host, $employer_info->display_name, $room_id);
            if ($project_type != 'content') {

                $sql_to_insert = "
                INSERT INTO wp_fl_chat_rooms
                  (room_id, room_title, freelancer_id, employer_id,  project_type, created) 
                  VALUES (
                  '$room_id',
                  '$room_title',
                  $freelancer_id,
                  $employer_id,
                 '$project_type',
                  NOW()
                  )
                ";

            } else {

                $sql_to_insert = "
                INSERT INTO wp_fl_chat_rooms
                  (room_id, room_title, freelancer_id, employer_id,  project_type, created) 
                  VALUES (
                  '$room_id',
                  '$room_title',
                  $employer_id, -- these are switched
                  $freelancer_id,
                 '$project_type',
                  NOW()
                  )
                ";

            }
            //print_r($room_data); die;
            $this->db->query($sql_to_insert);
            $room_pk_id = intval(will_get_last_id($this->db,'creating chat room'));
            if ($proposal_id) {
                $wpdb->query("UPDATE wp_proposals SET chat_room_id = $room_pk_id WHERE id = $proposal_id");
                will_throw_on_wpdb_error($wpdb,'updating room id for proposals');
            }
            if ($fl_job_id) {
                $wpdb->query("UPDATE wp_fl_job SET chat_room_id = $room_pk_id WHERE id = $fl_job_id");
                will_throw_on_wpdb_error($wpdb,'updating room id for fl job');
            }

            if ($content_id) {
                $wpdb->query("UPDATE wp_linguist_content SET chat_room_id = $room_pk_id WHERE id = $content_id");
                will_throw_on_wpdb_error($wpdb,'updating room id for content');
            }

            //code-notes [image-sizing]  using tiny profile pic for ejabberd
            $response = [
                'room_pk_id'=> $room_pk_id,
                'avatar' => $avatar,
                'room_string_identifier' => $room_id,
                'room_id' => $room_id,
                'isBlocked' => false,
                'nickname' => $next_username ,
                'project_title' => $room_title,
                'username' => static::get_xmpp_username($next_user_id),
            ];
            wp_send_json(['status' => true, 'message'=> 'created room','data' => $response]);
            exit();
            //print_r($res); die;
        } catch (Exception $e) {
            will_send_to_error_log('Error creating chat room', will_get_exception_string($e));
            wp_send_json(['status' => false, 'message' => $e->getMessage(),'data'=>null]);
            exit();
        }


    }


    public function append_chat_html()
    {

        $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
        if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false)) {
            return;
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/Edge/i', $user_agent)){
            return;
        }

       //if (! is_user_logged_in()) return; //code-notes now show chatbox html for logged in and not logged in
        echo '<!-- Chat implementation starts here ---->
<style>
.quadrat {
 -webkit-animation: new-chat-message 1s infinite; /* Safari 4+ */
  -moz-animation:    new-chat-message 1s infinite; /* Fx 5+ */
  -o-animation:      new-chat-message 1s infinite; /* Opera 12+ */
  animation:         new-chat-message 1s infinite; /* IE 10+, Fx 29+ */
}

@-webkit-keyframes new-chat-message {
0%, 49% {
    background-color: rgb(117,209,63);
    border: 0px solid #e50000;
}
50%, 100% {
    background-color: #e50000;
    border: 0px solid rgb(117,209,63);
}
}
</style>
<div id="chat_disconnected" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div  class=" alert alert-danger">Please refresh the page to reconnect chat.</div>
    </div>
  </div>
</div>

<div id="chat_not_there" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div  class=" alert alert-danger">Chat is not enabled.</div>
    </div>
  </div>
</div>
        
<div class="chat_boxes_area">
    <!-- single_chat_view -->
    <div id="chatview" class="ejabber_chat p1" style="display: none;">
        <div class="windows-header wh large-text"><span class="group_nickname">Miro Badev</span>
            <a href="#" class="close_window3"><i class="material-icons" style="float: right; color: white">close </i></a>
            <a href="#" class="close_window2"><i class="material-icons" style="float: right; color: white">remove </i></a>
            <a href="#" class="chat_settings"><i class="material-icons" style="float: right; color: white">settings </i></a>
            <button class="project_title_btn" style="display: none;">HIRE</button>
        </div>
        <div class="chat_settings_window hidden">
            <div class="settings">
                <div class="block_info">
                    <p><input type="checkbox"> <b>Block</b> <br>
                        No member of this chat will be able to send any message in it.</p>
                </div>               
            </div>
        </div>
        <div class="chat-messages">
            <div class="hire" style="display: none;">
                <b class="project_title">Title of the project</b>
                <button class="project_title_btn" style="display: none;">Hire</button>
            </div>
            <label></label>
        </div>
        <div class="sendmessage">
            <textarea type="text" placeholder="Send message..." class="txtMessage" name="txtMessage" autocomplete="off"></textarea>
            <button class="send"></button>
        </div>
    </div>
    <!-- single_chat_view -->
</div>
<div id="chatbox" style="height: 300px;">
    <div class="windows-header large-text">Chat <!--<button onclick="test_broadcast();">Test</button>-->
        <a href="#" id="close_window"><i class="material-icons" style="float: right; color: white">remove </i></a>
        <a href="#" id="bell_notification"><i id="material-icons-notification" class="material-icons" style="float: right; color: white">notifications </i></a>
    </div>
    <div id="friendslist">
        <div id="friends">
        </div>
        <!--<div id="search">
            <input clss="enhanced-text" type="text" id="searchfield" value="Search contacts...">
            <button id="send"></button>
        </div>-->
    </div>
</div>

 
<!-- Chat implementation ends here -->';


    }

}
