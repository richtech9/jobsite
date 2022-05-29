<?php

add_action( 'wp_ajax_hz_manage_job_status', 'hz_manage_job_status_cb'  );


/**
 * code-notes Only allowed when the current user is the same as the linguist_id in the job
 */
 function hz_manage_job_status_cb(){
    //code-bookmark here is the job start
     /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  hz_manage_job_status
    * input-sanitized : act,job_id
    */
     global $wpdb;

    try {

        $act = FLInput::get('act');
        $project_id = (int)FLInput::get('job_id');
        $lang = FLInput::get('lang','en');

        $user_id = get_current_user_id();
        $user_info = get_userdata(get_current_user_id());
        $username = $user_info->user_login;

        $sql = "SELECT ID,title FROM wp_fl_job WHERE project_id = $project_id AND linguist_id = $user_id";
        $check_res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb);
        if (empty($check_res)) {
            throw new RuntimeException("Either this is invalid job or the user is not authorized");
        }
        
        $job_title = $check_res[0]->title;



        $referral_fee = get_option('linguist_referral_fee');
        $user_amount = get_user_meta(get_current_user_id(), 'total_user_balance', true);

        if ($act == 'reject_job') {
            $wpdb->update('wp_fl_job', array('job_status' => $act), array('project_id' => $project_id, 'linguist_id' => get_current_user_id()));
            will_throw_on_wpdb_error($wpdb,'updating rejection');
            $response = array(

                'status' => true,

                'rejected' => true,

                'message' => "You have rejected the job",

                'redirect_to' => freeling_links('dashboard_url')

            );


            wp_send_json($response);


        } else {

            


            $pre_link = get_permalink($project_id);
            $link = $proposal_link = add_query_arg(  ['job_id'=>$job_title,'lang'=>$lang], $pre_link);;



            $wpdb->update('wp_fl_job', array('job_status' => $act), array('project_id' => $project_id, 'linguist_id' => get_current_user_id()));
            will_throw_on_wpdb_error($wpdb,'Updating job for accepting');
            $user_amount = $user_amount - $referral_fee;

            update_user_meta(get_current_user_id(), 'total_user_balance', $user_amount);

            $jid = hz_linguist_job_id(get_current_user_id(), $project_id);

            update_post_meta($project_id, 'project_new_status', ' ' . ucfirst($username) . ' Delivering');

            fl_transaction_insert('-' . $referral_fee, 'done',
                'job_start', get_current_user_id(), NULL, 'Linguist referral fee',
                '','', $project_id, $jid);

            $resp = array('status' => true, 'rejected' => false,
                'message' => 'You have started the job Successfully!!', 'redirect_to' => $link);



            wp_send_json($resp);

            wp_die();

        }
    } catch (Exception $e) {

        will_send_to_error_log('job status ajax', will_get_exception_string($e));
        $resp = array('status' => false, 'rejected' => null,
            'message' => $e->getMessage(), 'redirect_to' => null);

        wp_send_json($resp);

        wp_die();
    }

}

