<?php


class FreeLinguistPreFlightCheck {

    /*
     * current-php-code 2020-Oct-20
     * current-hook
     * input-sanitized :
    */

    const PLUGIN_NAME = 'PeerOK Pre Flight Check';

    const REQUIRED_FULL_FILE_PATHS = [
        'debug' => [
            'path' =>ABSPATH.'wp-content/themes/the-translator/includes/global-functions/internal/sitewide/will-debug.php',
            'requires'=> []
        ],

        'input' => [
            'path' =>ABSPATH.'wp-content/themes/the-translator/includes/global-functions/internal/sitewide/FLInput.php',
            'requires'=> ['debug']
        ],

        'es' => [
            'path' => ABSPATH.'wp-content/themes/the-translator/includes/all-cron/elastic-search-helper.php',
            'requires'=> ['debug']
        ],

        'ejabberd' => [
            'path' => ABSPATH.'wp-content/themes/the-translator/includes/global-functions/api/ajax/EjabberdWrapper.php',
            'requires'=> ['debug']
        ],

        'ejabberd_checker' => [
            'path' => ABSPATH.'wp-content/themes/the-translator/includes/global-functions/api/ajax/FreelinguistRefreshChatCredentials.php',
            'requires'=> ['ejabberd']
        ]
    ];


    protected static $b_make_admin_notice = false;
    protected static $files_loaded = [];
    protected static $required_by = [];

    protected static $b_log_notice = false;

    public static function make_admin_notices() {static::$b_make_admin_notice = true;}
    public static function do_not_do_admin_notices() {static::$b_make_admin_notice = false;}

    static function log_notices() {static::$b_log_notice = true;}
    public static function do_not_log_notices() {static::$b_log_notice = false;}

    public static function run_checks() {
        $problems = [];
        static::load_requirements();
        foreach (static::$files_loaded as $name => $true_or_error_string) {
            if ($true_or_error_string !== true) {
                $problems[] = "$name : $true_or_error_string ";
            }
        }
        if (count($problems)) {
            return $problems; //do not do checks if not all files loaded
        }

        $tasks = [];
        $tasks['es'] = static::check_es();
        $tasks['chat'] = static::check_chat();
        $tasks['actions'] = static::check_action_queues();

        foreach ($tasks as $name => $task) {
            if ($task !== true) {
                $problems[] = "$name : $task ";
            }
        }

        return $problems;
    }

    protected static function load_requirements() {
        static::$required_by = [];
        static::$files_loaded = [];
        //build dependency list
        foreach (static::REQUIRED_FULL_FILE_PATHS as $name => $dets) {
            $requires = $dets['requires'];
            foreach ($requires as $required) {
                if (!isset($required_by[$required])) {$required_by[$required] = [];}
                static::$required_by[$required][]= $name;
            }
        }

        foreach (static::REQUIRED_FULL_FILE_PATHS as $name => $dets) {
            $filepth = $dets['path'];
            $requires = $dets['requires'];
            foreach ($requires as $required) {
                if (!isset(static::$files_loaded[$required]) || (static::$files_loaded[$required] !== true)) {
                    static::$files_loaded[$name] = "Cannot load $name, because $required not loaded";
                    continue;
                }
            }
            $things_depending_on_this = [];
            if (isset( static::$required_by[$name]) ) {
                $things_depending_on_this = static::$required_by[$name];
            }
            $b_okay_or_message = static::require_file($filepth,$things_depending_on_this);
            static::$files_loaded[$name] = $b_okay_or_message;

        }
    }

    protected static function check_es() {
        try {

            $es = new FreelinguistElasticSearchHelper();
            $es->get_client();
            return true;
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            if (static::$b_make_admin_notice) {
                add_action('admin_notices', function () use ($error_message) {
                    ?>
                    <div class="error notice">
                        <p>
                            <strong><?= static::PLUGIN_NAME ?></strong> reports Elastic Search issue
                            <em><?= $error_message ?></em>

                        </p>
                    </div>
                    <?php
                });
            }
            if(static::$b_log_notice) {
                if (WP_DEBUG ) {
                    error_log(static::PLUGIN_NAME.': '.$error_message);
                }
            }
            return $error_message;
        }
    }

    protected static function check_chat() {

        /*
         misc:
            curl -XGET  https://chat.freelinguist.com:5443/api
         */

        $maybe_error = static::check_chat_actually_done_here();
        if ($maybe_error=== true) {
            return true;
        }
        if (static::$b_make_admin_notice) {
            add_action('admin_notices', function () use ($maybe_error) {
                ?>
                <div class="error notice">
                    <p>
                        <strong><?= static::PLUGIN_NAME ?></strong> reports Chat Server issue
                        <em><?= $maybe_error ?></em>

                    </p>
                </div>
                <?php
            });
        }
        if(static::$b_log_notice) {
            if (WP_DEBUG ) {
                error_log(static::PLUGIN_NAME.': '.$maybe_error);
            }
        }
        return $maybe_error;
    }

    protected static function check_chat_actually_done_here() {
        //code-notes use FreelinguistRefreshChatCredentials::check_ejabberd_account_login instead

        $guest_broadcast_settings = get_option('guest_broadcast_settings',[]);

        if (array_key_exists('account_name',$guest_broadcast_settings)) {
            $guest_broadcast_user_name = $guest_broadcast_settings['account_name'];
        } else {
            return 'Guest account_name is not set in the option: guest_broadcast_settings ';
        }

        if (array_key_exists('account_password',$guest_broadcast_settings)) {
            $guest_broadcast_user_password = $guest_broadcast_settings['account_password'];
        } else {
            return 'Guest account_name is not set in the option: guest_broadcast_settings ';
        }

        if ($guest_broadcast_user_name && $guest_broadcast_user_password) {
            try {
                $what = FreelinguistRefreshChatCredentials::check_ejabberd_account_login($guest_broadcast_user_name, $guest_broadcast_user_password);
                if ((isset($what['status']) && $what['status']) ) {
                    return true;
                }
                return isset($what['client_says']) ? json_encode($what['client_says']) : 'unknown status';
            } catch (Exception $ewhat) {
                return $ewhat->getMessage();
            }
        } else {
            return 'Guest account_name or account_password is not set in the option: guest_broadcast_settings ';
        }
    }

    protected static function check_action_queues() {
        //checks activated plugin list
        $active_pugin_names = get_option('active_plugins',[]);

        if (in_array('action-scheduler/action-scheduler.php',$active_pugin_names)) {
            return true;
        } else {
            $da_big_issue = "Plugin not working: action-scheduler is not activated as a plugin";

            if (static::$b_make_admin_notice) {
                add_action('admin_notices', function () use ($da_big_issue) {
                    ?>
                    <div class="error notice">
                        <p>
                            <strong><?= static::PLUGIN_NAME ?></strong>
                            <em><?= $da_big_issue ?></em>
                        </p>
                    </div>
                    <?php

                });
            }
            if(static::$b_log_notice) {
                if (WP_DEBUG ) {
                    error_log(static::PLUGIN_NAME.': '.$da_big_issue);
                }
            }
            return $da_big_issue;

        }

    }

    public static function require_file($path_we_want,$requirements = []) {
        $real_path_just_in_case = realpath($path_we_want);
        $b_can_read_file = is_readable($real_path_just_in_case);
        if ($b_can_read_file) {
            /** @noinspection PhpIncludeInspection */
            require_once $real_path_just_in_case;
            return true;
        } else {


            $requirements_talk = '';
            if (count($requirements)) {
                $all_reqs = implode(' | ', $requirements);
                $requirements_talk = "<br>Required by $all_reqs";
            }


            if (static::$b_make_admin_notice) {

                add_action('admin_notices', function () use ($path_we_want, $requirements_talk) {
                    ?>
                    <div class="error notice">
                        <p>
                            <strong><?= static::PLUGIN_NAME ?></strong> cannot find the file
                            <em><?= $path_we_want ?></em>
                            <?= $requirements_talk ?>
                        </p>
                    </div>
                    <?php
                });
            }
            if(static::$b_log_notice) {
                $error_message = static::PLUGIN_NAME.": cannot find the file ".$path_we_want . ' '. $requirements_talk ;
                if (WP_DEBUG ) {
                    error_log($error_message);
                }
            }
            return "cannot read $path_we_want";
        }
    }


}