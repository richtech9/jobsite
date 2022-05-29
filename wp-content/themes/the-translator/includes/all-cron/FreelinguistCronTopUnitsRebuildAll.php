<?php

//code-notes NEW cron job for the Rebuild top tags
class FreelinguistCronTopUnitsRebuildAll extends FreelinguistCronBase
{
    const OPTION_NAME = 'freelinguist_cron_top_units_rebuild_all';
    const ACTION_NAME = 'freelinguist_cron_top_units_rebuild_all';
    const STOP_ACTION_NAME = 'freelinguist_cron_top_units_rebuild_all_command';

    const PAGE_SIZE_USERS = 500;
    const PAGE_SIZE_CONTENT = 500; //expected worst case speed is 3 seconds for each

    //helper function to be called from the admin page prior to using this task
    public static function clear_top_tags() {
        global $wpdb;

        $sql = "TRUNCATE wp_display_unit_user_content;";
        $wpdb->query($sql);
        will_throw_on_wpdb_error($wpdb);

        $line = static::get_log_prefix() . 'Truncated Top Tags Table (wp_display_unit_user_content)';
        $older_log = static::get_log_value();
        if (empty($older_log)) {
            $older_log = [];
        }
        $older_log[] = $line;
        static::set_log($older_log);
    }

    public static function main($extra_command)
    {
        $current_page = -999;
        will_do_nothing($extra_command);
        $debug_string_command = static::get_debug_string_command();
        $log = static::get_log_value();
        if (empty($log)) {$log = [];}
        if (!static::can_do_step()) {
            $log[] = static::get_log_prefix() .' '.
                will_send_to_error_log(" Was told to stop . Exiting with nothing done, and no follow up action",
                    FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);

            static::set_log($log);
            return ['success' => false, 'message' => "No permission to run", 'code' => 401];
        }


        try {

            global $wpdb;
            $log = self::get_log();

            $current_page = static::get_loop(); //will throw if not set to a number
            $log[] = static::get_log_prefix() .' '.
                will_send_to_error_log(" Beginning Page $current_page to Rebuild the Top Tag table",
                    $current_page, $debug_string_command,true);

            /*
             * Structure: We do half the target users, and half the target content together as one step
             *              This is easier than making two separate jobs, one for users and one for content
             *
             * First we will do the users, get the page count for the users, then do the sql update
             * When that returns we will do the same things for the content
             *
             * Because this is doing two independent tasks, we only stop the job when both are done. To do this, we have two flags
             * b_did_user_work is set to true when there are things done with the users
             * b_did_content_work is set to true when there are things done with the content
             *
             * At the end of this function, if both of these are false, we end the task with a "Well Done!" message
             */


            //code-notes for rebuild all get total count, find pages, and do a loop to get 1000 at a time

            $count_users_sql = "
            SELECT count(*) as count_jobs
            FROM wp_fl_user_data_lookup look 
            WHERE
              1
            ";


            $job_count_res = $wpdb->get_results($count_users_sql);
            will_throw_on_wpdb_error($wpdb);
            $total_job_count = floatval($job_count_res[0]->count_jobs);
            $page_size = floatval(static::PAGE_SIZE_USERS);
            $total_job_pages = ceil($total_job_count / $page_size);


            $log[] = static::get_log_prefix() .' '.will_send_to_error_log("Number of User Pages and Page Size",
                ['number-pages' => $total_job_pages, 'page-size' => $page_size, 'total-jobs' => $total_job_count,'current_page'=>$current_page],
                $debug_string_command);

            if ($current_page >= $total_job_pages) {
                $log[] = static::get_log_prefix() .' '.
                    will_send_to_error_log(" Done with the Users being inserted into display_unit_user_content!",
                        $current_page, $debug_string_command,true);

                $b_did_user_work = false;
            } else {
                //do the update

                $start_page = $current_page * $page_size;
                $limit_part = "LIMIT $start_page, $page_size";

                $place_top_users_sql = /** @lang text */
                    "
                UPDATE wp_fl_user_data_lookup look
                    INNER JOIN (
                    SELECT at_me.id
                    FROM wp_fl_user_data_lookup at_me
                    WHERE 1
                    ORDER BY at_me.id
                    $limit_part
                    ) as driver ON driver.id = look.id
                SET
                  look.score = 0,
                  look.test_flag = look.test_flag + 1
                WHERE 1;
                ";

                $wpdb->query($place_top_users_sql);
                will_throw_on_wpdb_error($wpdb);
                $updated = $wpdb->rows_affected;
                $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                        "Finished block of $updated users  ",
                        '', $debug_string_command);

                $b_did_user_work = true;
            } //end work for users


            /*
             * DO THE CONTENT NOW
             */

            $count_content_sql = "
            SELECT count(*) as count_jobs
            FROM wp_linguist_content content 
            WHERE user_id IS NOT NULL
            ";


            $job_count_res = $wpdb->get_results($count_content_sql);
            will_throw_on_wpdb_error($wpdb);
            $total_job_count = floatval($job_count_res[0]->count_jobs);
            $page_size = floatval(static::PAGE_SIZE_CONTENT);
            $total_job_pages = ceil($total_job_count / $page_size);


            $log[] = static::get_log_prefix() .' '.will_send_to_error_log("Number of Content Pages and Page Size",
                    ['number-pages' => $total_job_pages, 'page-size' => $page_size, 'total-jobs' => $total_job_count,'current_page'=>$current_page],
                    $debug_string_command);


            if ($current_page >= $total_job_pages) {
                $log[] = static::get_log_prefix() .' '.
                    will_send_to_error_log(" Done with the Content being inserted into display_unit_user_content!",
                        $current_page, $debug_string_command,true);

                $b_did_content_work = false;
            } else {
                //do the update

                $start_page = $current_page * $page_size;
                $limit_part = "LIMIT $start_page, $page_size";

                $place_top_content_sql =
                    /** @lang text */
                    "
                UPDATE wp_linguist_content cont
                INNER JOIN (
                      SELECT at_me.id
                      FROM wp_linguist_content at_me
                      ORDER BY at_me.id
                      $limit_part
                    ) as driver ON driver.id = cont.id
                
                
                SET cont.updated_at = NOW()
                WHERE 1;
                ";

                $wpdb->query($place_top_content_sql);
                will_throw_on_wpdb_error($wpdb);
                $updated = $wpdb->rows_affected;
                $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                        "Finished block of $updated content  ",
                        '', $debug_string_command);
                $b_did_content_work = true;
            }


             if (!$b_did_user_work && !$b_did_content_work) {
                static::stop();
                static::set_next_loop(-1);
                $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                    "No More Work to Do in Step $current_page ,Finishing JOb ",
                        FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);
            } else {
                //call the next step
                 $next_page = $current_page+1;
                 static::set_next_loop($next_page);
                 as_enqueue_async_action( static::ACTION_NAME,[$next_page ] ); //hook up to next run
                 $log[] = static::get_log_prefix() .' '.will_send_to_error_log("Scheduled next job for page $next_page",
                         '', $debug_string_command);
             }


            return ['success' => true, 'message' => static::get_log_prefix(). " Did page ".$current_page, 'code' => 201];
        } catch (Exception $e) {
            static::stop();
            $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                    "Error ".$e->getMessage()." in current page $current_page, not rescheduling action ",
                    will_get_exception_string($e), $debug_string_command,false);

            return [
                'success' => false,
                'message' => static::get_log_prefix(). " Error Rebuilding Top Tags: " . $e->getMessage(),
                'code' => $e->getCode()];
        } finally {
            static::set_log($log);
        }
    }
}


FreelinguistCronTopUnitsRebuildAll::set_up_hook();