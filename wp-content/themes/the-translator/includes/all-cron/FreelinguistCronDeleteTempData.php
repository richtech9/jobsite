<?php

/**
 * This is the cron task which clears clears out test data
 * Class FreelinguistCronDeleteTempData
 */
class FreelinguistCronDeleteTempData extends FreelinguistCronBase
{
    const OPTION_NAME = 'freelinguist_cron_delete_test_data';
    const ACTION_NAME = 'freelinguist_cron_delete_test_data';
    const STOP_ACTION_NAME = 'freelinguist_cron_delete_test_data_command';

    const DEFAULT_NUMBER_POSTS_TO_DELETE_EACH_TURN = 50;
    const DEFAULT_NUMBER_USERS_TO_DELETE_EACH_TURN = 15;//code-notes might use a 3:1 ratio after testing, a single user can delete several posts and content by itself

    const OPTION_NAME_NUMBER_POSTS_EACH_TURN = 'fl_admin_delete_test_data_posts';
    const OPTION_NAME_NUMBER_USERS_EACH_TURN = 'fl_admin_delete_test_data_users';

    public static function main($extra_command)
    {
        global $wpdb;

        require_once(ABSPATH.'wp-admin/includes/user.php'); //sometimes the user module does not get loaded, its a timing thing

        $current_page = -999;
        will_do_nothing($extra_command);
        $debug_string_command = static::get_debug_string_command();
        $log = static::get_log_value();
        if (empty($log)) {
            $log = [];
        }
        if (!static::can_do_step()) {
            $log[] = static::get_log_prefix() . ' ' .
                will_send_to_error_log(" Was told to stop . Exiting with nothing done, and no follow up action",
                    FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);

            static::set_log($log);
            return ['success' => false, 'message' => "No permission to run", 'code' => 401];
        }


        try {

            $log = self::get_log();
            $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log("Starting cron job for deleting test data!",
                    '', $debug_string_command);

            $current_page = static::get_loop(); //will throw if not set to a number
            $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log("Starting cron job for deleting test data! Page[$current_page]",
                    '', $debug_string_command);



            //find 100 posts to delete with the meta key of create_batch, order by id going up, to do oldest first
            $number_of_posts = (int) get_option(static::OPTION_NAME_NUMBER_POSTS_EACH_TURN,static::DEFAULT_NUMBER_POSTS_TO_DELETE_EACH_TURN);
            $sql_posts = "SELECT look.post_id
                              FROM wp_fl_post_data_lookup look
                            WHERE
                              look.is_test_data = 1
                            ORDER BY post_id
                            LIMIT $number_of_posts;";


            $post_res = $wpdb->get_results($sql_posts);
            will_throw_on_wpdb_error($wpdb,'Getting post data to delete');
            foreach ($post_res as $row) {
                $post_id = $row->post_id;
                $what = wp_delete_post($post_id,true);
                if (empty($what)) {
                    $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log("Could not delete post id of $post_id!",
                            '', $debug_string_command);
                } else {
                    $post_title = $what->post_title;
                    $post_type = $what->post_type;
                    $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log("Deleted Post $post_id! $post_title  [$post_type]",
                            '', $debug_string_command);
                }

                //check for people trying to stop this in mid running
                if (!static::can_do_step()) {
                    $log[] = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(" Was told to stop . Exiting after doing some work",
                            FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);

                    static::set_log($log);
                    return ['success' => false, 'message' => "Stopping early due to command", 'code' => 401];
                }
            }

            //find 100 users to delete with the meta key of create_batch, order by id going up, to do oldest first
            $number_of_users = (int) get_option(static::OPTION_NAME_NUMBER_USERS_EACH_TURN,static::DEFAULT_NUMBER_USERS_TO_DELETE_EACH_TURN);
            $sql_users = "
                SELECT look.user_id
                  FROM wp_fl_user_data_lookup look
                WHERE
                  look.is_test_data = 1
                ORDER BY user_id ASC
                LIMIT $number_of_users;
            ";

            $post_res = $wpdb->get_results($sql_users);
            will_throw_on_wpdb_error($wpdb,'Getting user data to delete');
            foreach ($post_res as $row) {
                $user_id = $row->user_id;
                $what = get_userdata($user_id);
                wp_delete_user($user_id,null);

                $user_name = $what->user_nicename;
                $user_email = $what->user_email;
                $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log("Deleted User $user_id And all its posts too! $user_name  [$user_email]",
                        '', $debug_string_command);


                //check for people trying to stop this in mid running
                if (!static::can_do_step()) {
                    $log[] = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(" Was told to stop . Exiting after doing some work",
                            FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);

                    static::set_log($log);
                    return ['success' => false, 'message' => "Stopping early due to command", 'code' => 401];
                }
            }

            //now see how many are left over
            $count_data = FreelinguistCronDeleteTempData::get_total_counts();
            $data_count = $count_data->users + $count_data->posts;

            if ($data_count > 0) {
                $next_page = $current_page + 1;
                static::set_next_loop($next_page);
                as_enqueue_async_action(static::ACTION_NAME, [$next_page]); //hook up to next run
                $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log("Scheduled next job for page $next_page",
                        '', $debug_string_command);
            } else {
                static::stop();
                static::set_next_loop(-1);
                $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                        "No more test data to delete. Final page is $current_page . Stopped cron job ",
                        FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);
            }

            return ['success' => true, 'message' => static::get_log_prefix(). " Did page ".$current_page, 'code' => 201];

        } catch (Exception $e) {
            static::stop();
            $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log(
                    "Error " . $e->getMessage() . " in current step $current_page, not rescheduling action ",
                    will_get_exception_string($e), $debug_string_command, false);

            return [
                'success' => false,
                'message' => static::get_log_prefix() . " Error in Step $current_page : " . $e->getMessage(),
                'code' => $e->getCode()];
        } finally {
            static::set_log($log);
        }
    }

    /**
     * @return object {members of posts and users, each has an integer value for the count of that
     */
    public static function get_total_counts() {
        global $wpdb;
        $sql = "
        SELECT count(id) AS da_count, 'posts' AS what FROM wp_fl_post_data_lookup WHERE is_test_data = 1
        UNION ALL
        SELECT count(id) AS da_count, 'users' AS what FROM wp_fl_user_data_lookup WHERE is_test_data = 1;
        ";
        $res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb,'Counting test users and posts');
        $ret = [];
        foreach ($res as $row) {
            $ret[$row->what] = (int)$row->da_count;
        }
        return (object)$ret;
    }

    const START_COMMAND = 'start_cron_delete_test_data';
    const PAUSE_COMMAND = 'pause_cron_delete_test_data';
    const RESUME_COMMAND = 'resume_cron_delete_test_data';
    const OPTION_COMMAND = 'options_cron_delete_test_data';

    public static function process_cron_controls() {

        if (isset($_POST[static::OPTION_COMMAND]) ) {
            $number_posts = (int)FLInput::get(static::OPTION_NAME_NUMBER_POSTS_EACH_TURN);
            $number_users = (int)FLInput::get(static::OPTION_NAME_NUMBER_USERS_EACH_TURN);

            update_option(static::OPTION_NAME_NUMBER_POSTS_EACH_TURN,$number_posts);
            update_option(static::OPTION_NAME_NUMBER_USERS_EACH_TURN,$number_users);
        }

        if (isset($_POST[static::START_COMMAND]) ) {

            try {

                    static::stop();
                    static::run();
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p>
                            The cron job for deleting test data has been started
                        </p>
                    </div>
                    <?php

            }   catch(Exception $e) {
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Error starting the cron job for deleting test data:
                        <span class="cron-log-error"><?=$e->getMessage()?></span>
                    </p>
                </div>
                <?php
            }
        } //end starting the cron job

        if (isset($_POST[static::PAUSE_COMMAND])) {
            try {
                static::stop();

                $all_logs = static::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job for Red Dot Actions will not run until started again
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php

            } catch (Exception $e) {
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Error Stopping Repeat Cron Job for Red Dot Actions :
                        <span class="cron-log-error"><?=$e->getMessage()?></span>
                    </p>
                </div>
                <?php
            }



        }

        if (isset($_POST[static::RESUME_COMMAND])) {
            try {
                static::resume();
                $all_logs = static::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size - 1];
                }

                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job For Deleting Test Data will now Resume
                        <span class="cron-log-line"><?= $log_line ?></span>
                    </p>
                </div>
                <?php
            } catch (Exception $e) {
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Error Restarting the Cron Job for Deleting Test Data:
                        <span class="cron-log-error"><?=$e->getMessage()?></span>
                    </p>
                </div>
                <?php
            }
        }
    }
}


FreelinguistCronDeleteTempData::set_up_hook();