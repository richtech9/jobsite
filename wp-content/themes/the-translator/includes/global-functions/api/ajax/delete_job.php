<?php

add_action('wp_ajax_delete_job', 'delete_job');


function delete_job(){

    /*
       * current-php-code 2020-Oct-1
       * ajax-endpoint  delete_job
       * input-sanitized : job_id
       */

    $job_id = (int)FLInput::get('job_id');

    $post_tmp = get_post($job_id);

    $author_id = $post_tmp->post_author;

    if(get_post_status( $job_id ) == "publish"){



        if(get_current_user_id() == $author_id ){

            $jtype = get_post_meta($job_id, 'fl_job_type', true);

            wp_delete_post($job_id,true);

            // bid amount refund             

            refundBidAmount_JobCancelByCustomer($job_id);
            FreelinguistProjectAndContestHelper::update_elastic_index($job_id,$jtype);
            echo 'success';

            exit;

        }

    }

    echo 'failed';

    exit;

}