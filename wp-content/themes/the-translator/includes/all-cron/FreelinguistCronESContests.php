<?php


class FreelinguistCronESContests extends FreelinguistCronBase
{
    const OPTION_NAME = 'freelinguist_rebuild_elastic_contests';
    const ACTION_NAME = 'freelinguist_rebuild_elastic_contests';
    const STOP_ACTION_NAME = 'freelinguist_rebuild_elastic_contests_command';

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


        $index = "contest";
        try {

            global $wpdb;


            $log = self::get_log();
            $log[] = static::get_log_prefix() .' '.will_send_to_error_log("Starting partial cron job $index!",
                '', $debug_string_command);

            $current_page = static::get_loop(); //will throw if not set to a number
            $log[] = static::get_log_prefix() .' '.
                will_send_to_error_log(" Beginning Page $current_page to index on $index",
                    $current_page, $debug_string_command,true);

            $es = new FreelinguistElasticSearchHelper();
            if ($current_page === 0) {
                $es->clear_cache($index, $log);
            }



            //code-notes get total count, find pages, and do a loop to get 1000 at a time

            $count_job_sql = "
            SELECT count(*) as count_jobs
            FROM wp_posts as wppost
              INNER JOIN wp_fl_post_data_lookup look on wppost.ID = look.post_id
            WHERE
              wppost.post_type = 'job' and
              wppost.post_status = 'publish' and
              look.fl_job_type = " . FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST . "
              ;
    ";

//            $log[] = static::get_log_prefix() .' '.will_send_to_error_log("SQL for counting pages",
//                , $debug_string_command);

            $job_count_res = $wpdb->get_results($count_job_sql);
            will_throw_on_wpdb_error($wpdb);
            $total_job_count = floatval($job_count_res[0]->count_jobs);
            $page_size = floatval(FreelinguistElasticSearchHelper::DEFAULT_BULK_LIMIT);

            $total_job_pages = ceil($total_job_count / $page_size);




            $log[] = static::get_log_prefix() .' '.will_send_to_error_log("number of pages and page size",
                ['number-pages' => $total_job_pages, 'page-size' => $page_size, 'total-jobs' => $total_job_count,'current_page'=>$current_page],
                $debug_string_command);

            if ($current_page >= $total_job_pages) {
                $log[] = static::get_log_prefix() .' '.
                    will_send_to_error_log(" The requested page $current_page is greater than or equal to the total number of pages. Stopping cron tasks with no follow up",
                        $current_page, $debug_string_command,true);

                static::stop();
                static::set_log($log);
                return ['success' => false, 'message' => "Out of pages to do", 'code' => 300];
            }

                $start_page = $current_page * $page_size;
                $limit_part = "LIMIT $start_page, $page_size";

                $job_page_sql =
                    /** @lang text */
                    "SELECT
                  wppost.ID as da_job_id,
                  look.job_title as job_title,
                  look.job_description as job_description,
                  (
                    SELECT
                      GROUP_CONCAT(intags.tag_name) as tag_ids
                      FROM  wp_tags_cache_job ijob
                      LEFT JOIN wp_interest_tags  intags ON intags.ID = ijob.tag_id
                      WHERE ijob.type = " . FreelinguistTags::CONTEST_TAG_TYPE . " AND
                            ijob.job_id = wppost.ID
                  ) as tag_names,
                  UNIX_TIMESTAMP(if(look.last_update,look.last_update,wppost.post_date_gmt)) as recent_ts,
                  0 as job_price
                FROM wp_posts as wppost
                  INNER JOIN wp_fl_post_data_lookup look on wppost.ID = look.post_id
                WHERE
                  wppost.post_type = 'job' and
                  wppost.post_status = 'publish' and
                  look.fl_job_type = " . FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST . " and
                  look.hide_job = 0
                 ORDER BY wppost.ID  
                  $limit_part
                  ";

                $jobs = $wpdb->get_results($job_page_sql);
                will_throw_on_wpdb_error($wpdb);
                $count_jobs = count($jobs);
                $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                    "SQL with data completed for page $current_page --> $count_jobs Contests found ",
                    '', $debug_string_command);

                if (count($jobs) > 0) {
                    $params = [];
                    foreach ($jobs as $job) {

                        $tagArray = [];
                        if (!empty($job->tag_names)) {
                            $tagArray = explode(',', $job->tag_names);
                        }

                        $params['body'][] = [
                            'index' => [
                                '_index' => $index,
                                '_type' => 'freelinguist',
                                '_id' => (int)$job->da_job_id
                            ]
                        ];

                        $params['body'][] = [
                            'job_id' => (int)$job->da_job_id,
                            'title' => $job->job_title,
                            'tags' => $tagArray,
                            'job_type' => $index,
                            'description' => $job->job_description,
                            'instruction' => '',
                            'is_cache' => '0',
                            'rating_as_freelancer' => 0,
                            'rating_as_customer' => 0,
                            'translate_from' => '',
                            'translate_to' => '',
                            'price' => (int)$job->job_price,
                            'recent_ts' => (int)$job->recent_ts
                        ];
                    } //end for each job
                    $es->bulk_add($params, $log);

                    $next_page = $current_page+1;
                    static::set_next_loop($next_page);
                    as_enqueue_async_action( static::ACTION_NAME,[$next_page ] ); //hook up to next run
                    $log[] = static::get_log_prefix() .' '.will_send_to_error_log("Scheduled next job for page $next_page",
                            '', $debug_string_command);

                } //end if count jobs > 0
                else {
                    static::stop();
                    static::set_next_loop(-1);
                    $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                        "No Projects in current page $current_page ,nothing to do, and not rescheduling action ",
                            FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);
                }


            return ['success' => true, 'message' => static::get_log_prefix(). " Did page ".$current_page, 'code' => 201];
        } catch (Exception $e) {
            static::stop();
            $log[] = static::get_log_prefix() .' '.will_send_to_error_log(
                    "Error ".$e->getMessage()." in current page $current_page, not rescheduling action ",
                    will_get_exception_string($e), $debug_string_command,false);

            return [
                'success' => false,
                'message' => static::get_log_prefix(). " Error Rebuilding $index Indexes: " . $e->getMessage(),
                'code' => $e->getCode()];
        } finally {
            static::set_log($log);
        }
    }
}


FreelinguistCronESContests::set_up_hook();