<?php
add_action( 'wp_ajax_hz_project_translator_feedback',  'hz_project_translator_feedback_cb'  );

/**
 * code-notes Only when the user has active milestones on the  project and the job has at least one completed or rejected milestone
 */
 function hz_project_translator_feedback_cb(){

    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  hz_project_translator_feedback
    * input-sanitized : data keys->  comments_by_freelancer,job_id,rating_by_freelancer
    */
    //code-bookmark ajax where where the freelancer leaves a review for a customer after project is done
    global $wpdb;
    try {
        $posted_data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);
        $data = [];

        parse_str($posted_data_string, $data);


        $rating_by_freelancer = (int)FLInput::clean_data_key($data, 'rating_by_freelancer');

        $comments_by_freelancer = FLInput::clean_data_key($data, 'comments_by_freelancer', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $job_id = (int)FLInput::clean_data_key($data, 'job_id');

        $user_id = get_current_user_id();

        $job = $wpdb->get_results(
            "
            SELECT job.*, post.post_author
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
              job.linguist_id = $user_id;
            "
        );

        will_throw_on_wpdb_error($wpdb,'Checking job for freelancer feedback');
        if (empty($job)) {
            throw new RuntimeException("Provide feedback only after completing at least one milestone.");
        }

        $job = $wpdb->get_results(
            "select * from wp_fl_job where ID=$job_id");

        $author = $job[0]->author;


         $wpdb->update('wp_fl_job',
            array("rating_by_freelancer" => $rating_by_freelancer, "comments_by_freelancer" => $comments_by_freelancer),
            array("ID" => $job_id));
        will_throw_on_wpdb_error($wpdb,'update job for feedback');
        update_customer_average_rating($author);


        wp_send_json( ['status' => true, 'message' => 'Feedback submitted']);
    } catch (Exception $e) {
        will_send_to_error_log('Freelancer Project Feedback',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}