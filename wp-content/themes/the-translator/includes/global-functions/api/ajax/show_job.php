<?php

/**************** show hide job ***********/

add_action('wp_ajax_show_job', 'show_job');


/**
 * code-notes the job can only be shown by the owner of the job
 */
function show_job(){
    global $wpdb;
    /*
        * current-php-code 2020-Oct-1
        * ajax-endpoint show_job
        * input-sanitized : job_id
        */

    try {
        $job_id = (int)FLInput::get('job_id');

        $user_id = get_current_user_id();

        $job_check_result = $wpdb->get_row("
                        SELECT ID 
                        FROM wp_posts p
                        WHERE p.post_author = $user_id AND ID = $job_id",
            ARRAY_A);

        will_throw_on_wpdb_error($wpdb);
        if (empty($job_check_result)) {
            throw new RuntimeException("User does not own this job");
        }

        if (get_post_status($job_id) !== "publish") {
            throw new RuntimeException("Job is not published");
        }

        delete_post_meta($job_id, 'hide_job');

        FreelinguistProjectAndContestHelper::update_elastic_index($job_id);
        wp_send_json( ['status' => true, 'message' => 'Job is now shown']);



    } catch (Exception $e) {
        will_send_to_error_log('hide job',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }


}