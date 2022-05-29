<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      hireTranslate

 * Description: Hire linguist

 *

 */

add_action('wp_ajax_hireTranslate', 'hireTranslate');


function hireTranslate(){
    //code-bookmark where the winning bid is hired, and put in the wp_fl_jobs table
    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  hireTranslate
    * input-sanitized : bid_id,bid_note,job_id,translater_id
    */
    global $wpdb;
    try {
        $bid_id = FLInput::get('bid_id');
        $bid_note = FLInput::get('bid_note');
        $project_id = FLInput::get('job_id');
        $translater_id = FLInput::get('translater_id');

        $current_user = wp_get_current_user();

        $current_user_id = $current_user->ID;

        $job_data = get_post($project_id);

        $user_info = get_userdata($job_data->post_author);

        $bid_note = removePersonalInfo($bid_note);


        $pro_jid = gen_pjob_title($project_id);


        $hz_job_title = get_the_title($project_id) . "_" . $pro_jid;

        $user_amount = get_user_meta($current_user_id, 'total_user_balance', true);

        $referral_fee = (float)get_option('client_referral_fee',0);


        $variables = array();


        $modified_id = get_post_meta($project_id, 'modified_id', true);

        $variables['job_path'] = $modified_id;

        $linguist_detail = get_userdata($translater_id);


        if (($current_user_id !== $user_info->ID) || empty($project_id)) {
            throw new RuntimeException("Unauthorized or No data given");
        }


        if ($referral_fee) {


            $jdata = array(

                'job_seq' => $pro_jid,

                'title' => $hz_job_title,

                'content' => $bid_note,

                'author' => get_current_user_id(),

                'linguist_id' => $translater_id,

                'project_id' => $project_id,

                'bid_id' => $bid_id,

                'amount' => 0,

                'meta' => '',

                'post_date' => current_time('Y-m-d H:i:s', $gmt = 1),

                'job_status' => 'pending',

            );

            $wpdb->insert("wp_fl_job", $jdata);
            $fl_job_id = will_get_last_id($wpdb, 'insert job');


            $sql_to_update = "UPDATE wp_fl_job SET post_date = NOW() WHERE ID = $fl_job_id";
            $wpdb->query($sql_to_update);
            will_throw_on_wpdb_error($wpdb, 'update job post_date');

            

            $user_amount = $user_amount - $referral_fee;

            update_user_meta(get_current_user_id(), 'total_user_balance', amount_format($user_amount));


            emailTemplateForUser($linguist_detail->user_email, HIRE_TRANSLATE_TEMPLATE, $variables);


            fl_transaction_insert('-' . $referral_fee, 'done', 'hire', get_current_user_id(), NULL,
                'Referral fee', '', '',
                $project_id, $fl_job_id);



            update_project_status($project_id, 'project_in_progress');


            wp_send_json( ['status' => true, 'message' => 'Hired!']);


        }

        wp_die();


       
    }  catch (Exception $e) {
        will_send_to_error_log('hire for job',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}