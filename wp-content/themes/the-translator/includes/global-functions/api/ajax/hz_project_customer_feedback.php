<?php
add_action( 'wp_ajax_hz_project_customer_feedback',  'hz_project_customer_feedback_cb' );

/**
 * code-notes Only when the user is the owner of the project and the job has at least one completed or rejected milestone
 */
 function hz_project_customer_feedback_cb(){
     /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  hz_project_customer_feedback
    * input-sanitized : data keys->  comments_by_customer,job_id,rating_by_customer
    */
    //code-bookmark ajax for the customer providing review for the freelancer at the end of the project
    global $wpdb;
    try {
        $posted_data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);
        $data = [];

        parse_str($posted_data_string, $data);

        $rating_by_customer = (int)FLInput::clean_data_key($data, 'rating_by_customer');

        $comments_by_customer = FLInput::clean_data_key($data, 'comments_by_customer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $job_id = (int)FLInput::clean_data_key($data, 'job_id');
        $user_id = get_current_user_id();

        $job = $wpdb->get_results(
            "
            SELECT job.*
            FROM wp_fl_job job
            INNER JOIN wp_posts post on job.project_id = post.ID
            INNER JOIN (
                SELECT count(*) as da_count,job_id
                FROM wp_fl_milestones
                WHERE job_id =  $job_id AND status in ('completed','rejected')
                GROUP BY job_id
                       )
                       miles on job.ID = miles.job_id
            WHERE 
              job.ID= $job_id AND 
              post.post_author = $user_id;
            "
        );

        will_throw_on_wpdb_error($wpdb,'Checking job for customer feedback');
        if (empty($job)) {
            throw new RuntimeException("Provide feedback only after completing at least one milestone.");
        }

        $linguist_id = $job[0]->linguist_id;


         $wpdb->update('wp_fl_job',
            array(
                "comments_by_customer" => $comments_by_customer,
                "rating_by_customer" => $rating_by_customer
            ),
            array("ID" => $job_id));
        will_throw_on_wpdb_error($wpdb,'update job for feedback');
        update_freelancer_average_rating($linguist_id);

        wp_send_json( ['status' => true, 'message' => 'Feedback submitted']);
    } catch (Exception $e) {
        will_send_to_error_log('customer project feedback',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }



}