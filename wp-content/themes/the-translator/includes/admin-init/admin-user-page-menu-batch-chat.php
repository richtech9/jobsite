<?php

class AdminPageUserMenuBatchChat {

    /*
    * current-php-code 2021-Jan-8
    * input-sanitized :
    * current-wp-template:  admin-screen  looking at chat library logs
   */

    const USER_SELECTED_LIMIT = 50;

    const BULK_ACTION_NAME = 'refresh-chat-credentials';

    const BULK_REDIRECT_URL_PARAM = 'refreshed-chats';

    const TXTDOMAIN = 'translator';

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
    protected static $b_debug = true;

    const LOG_DEBUG = 0;
    const LOG_INFO = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;

    public static $n_debug_level = self::LOG_ERROR; //how much information to output to screen or log

    public static function turn_on_debugging($level=self::LOG_ERROR) {
        static::$b_debug = true;
        static::$n_debug_level = $level;
        if ($level <= static::LOG_DEBUG) {
            FreelinguistRefreshChatCredentials::turn_on_debugging();
        } else {
            FreelinguistRefreshChatCredentials::turn_off_debugging();
        }
    }

    public static function turn_off_debugging() {static::$b_debug = false;}

    protected static function log($log_level,$prefix,$data,$b_dump = false) {
        if (static::$b_debug && $log_level >= static::$n_debug_level) {
            $final_prefix = static::get_log_prefix(). ': '. $prefix;
            will_send_to_error_log($final_prefix,$data,false,$b_dump);
        }
    }

    public $parent_slug = null;
    public $position = null;
    /**
     * Constructor will create the menu item

     */
    public function __construct()
    {

        add_filter('bulk_actions-users', function($bulk_actions) {
            $bulk_actions[static::BULK_ACTION_NAME] = __('Refresh Chat Credentials', static::TXTDOMAIN);
            return $bulk_actions;
        });

        add_filter('handle_bulk_actions-users', function($redirect_url, $action, $user_ids) {
            if ($action == static::BULK_ACTION_NAME) {
                $_SESSION[static::BULK_ACTION_NAME] = [];
                static::log(static::LOG_INFO,'user ids found in bulk',$user_ids,true);

                if (count($user_ids) > static::USER_SELECTED_LIMIT) {
                    $msg = "Can only process ".static::USER_SELECTED_LIMIT." at a time. But tried to select ".count($user_ids);
                    $_SESSION[static::BULK_ACTION_NAME][] = $msg;
                    static::log(static::LOG_WARNING,$msg,false);
                    $redirect_url = add_query_arg(static::BULK_REDIRECT_URL_PARAM, count($user_ids), $redirect_url);
                    return $redirect_url;
                }

                foreach ($user_ids as $user_id) {
                    $_SESSION[static::BULK_ACTION_NAME][] = FreelinguistRefreshChatCredentials::refresh_user($user_id);
                }
                $redirect_url = add_query_arg(static::BULK_REDIRECT_URL_PARAM, count($user_ids), $redirect_url);
            }
            return $redirect_url;
        }, 10, 3);

        add_action('admin_notices', function() {
            if (!empty($_REQUEST[static::BULK_REDIRECT_URL_PARAM])) {
                if (isset ($_SESSION[static::BULK_ACTION_NAME]) &&
                    is_array($_SESSION[static::BULK_ACTION_NAME]) &&
                    count($_SESSION[static::BULK_ACTION_NAME])
                ) {
                    $num_changed = (int) $_REQUEST[static::BULK_REDIRECT_URL_PARAM];
                    ?>
                    <div id="message" class="updated notice is-dismissable">
                        <p>
                            <span class="fl-refresh-chat-status fl-refresh-chat-status-header">
                                Processed <?= $num_changed ?> Users for Chat Credentials
                            </span>


                            <?php foreach ($_SESSION[static::BULK_ACTION_NAME] as $result) {?>
                                <?php if (isset($result['status'])) {?>
                                    <?php if ($result['status']) {?>
                                        <span class="fl-refresh-chat-status fl-refresh-chat-status-ok"><?= $result['message']?></span>
                                    <?php } else { ?>
                                        <span class="fl-refresh-chat-status fl-refresh-chat-status-error"><?= $result['message']?></span>
                                    <?php } //end if status is 1 or 0?>
                                <?php } else { //end if isset for status?>
                                        <span class="fl-refresh-chat-status fl-refresh-chat-status-unknown"><?= json_encode($result)?></span>
                                <?php } //end if isset for status?>
                            <?php } //end foreach loop?>


                            <?php ?>
                        </p>
                    </div>
                    <?php
                    $_SESSION[static::BULK_ACTION_NAME] = [];
                } //end if isset for session
            }
        });

    } //end constructor
}
AdminPageUserMenuBatchChat::turn_on_debugging(AdminPageUserMenuBatchChat::LOG_WARNING);
