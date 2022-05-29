<?php

add_action( 'wp_ajax_hz_contest_new_proposal_data_proc_save',  'hz_contest_new_proposal_data_proc_save'  );

 function hz_contest_new_proposal_data_proc_save(){

     /*
     * current-php-code 2020-Oct-10
     * ajax-endpoint  hz_contest_new_proposal_data_proc_save
     * input-sanitized : proposal_description, proposal_id
    */


     $proposal_id = (int)FLInput::get('proposal_id');
     $proposal_description = FLInput::get('proposal_description');
     $lang = FLInput::get('lang','en');


    global $wpdb;
    try {


        $user_ID = get_current_user_id();

        $proposal_to_update = $wpdb->get_results(
            "SELECT id,post_id,by_user from  wp_proposals WHERE id = $proposal_id");
        will_throw_on_wpdb_error($wpdb);

        if (empty($proposal_to_update)) {
            throw new RuntimeException("Proposal does not exist ");
        }

        $proposal_user_id = (int)$proposal_to_update[0]->by_user;

        $proposal_link_base = get_permalink($proposal_to_update[0]->post_id);

        $proposal_link = add_query_arg(  ['action'=> 'proposals','lang'=>$lang], $proposal_link_base);

        if ($user_ID !== $proposal_user_id) {
            throw new RuntimeException("No Permissions to set proposal $proposal_id");
        }


        $sql_clause = "UPDATE wp_proposals SET proposal_description = '$proposal_description' WHERE id = $proposal_id";

        $wpdb->query($sql_clause);
        will_throw_on_wpdb_error($wpdb);

        $status = true;
        $message = "updated proposal $proposal_id";

        wp_send_json([
            'status'=>$status,
            'message' => $message,
            'proposal_link' => $proposal_link
        ]);
        exit;

        //code-notes just update the project description


    } catch (Exception $e) {

        $out = [
            'status'=>false,
            'message' => $e->getMessage(),
            'proposal_link' => null
        ];
        will_send_to_error_log('Error in hz_contest_new_proposal_data_proc_save',will_get_exception_string($e));
        wp_send_json($out);
        exit;
    }

}