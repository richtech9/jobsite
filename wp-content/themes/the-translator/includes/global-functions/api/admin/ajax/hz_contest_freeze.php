<?php

add_action('wp_ajax_hz_contest_freeze', 'hz_contest_freeze');

function hz_contest_freeze()
{

    /*
         * current-php-code 2021-Jan-11
         * ajax-endpoint  hz_contest_freeze
         * input-sanitized : dbId
         */

    global $wpdb;
    if (!current_user_can('manage_options')) {
        exit;
    }
    try {

        $dbId = (int)FLInput::get('dbId');


        $sql_statment = /** @lang text */
            "SELECT * FROM wp_dispute_cases WHERE `ID` = %d";
        $result = $wpdb->get_row($wpdb->prepare($sql_statment, array($dbId)), ARRAY_A);


        $contentId = $result['contestId'];
        $proposal_id = $result['proposal_id'];


        if ($contentId) {

            $proposal = $wpdb->get_row($wpdb->prepare(/** @lang text */
                "SELECT * FROM wp_proposals WHERE `ID` = %d", array($proposal_id)), ARRAY_A);


            /*Start Paying to customer*/
            $linguId = $proposal['by_user'];

            if (!in_array($linguId, get_post_meta($contentId, 'job_freeze_user'))) {
                add_post_meta($contentId, 'job_freeze_user', $linguId);
            }

            $execut = $wpdb->query($wpdb->prepare(/** @lang text */
                "UPDATE wp_dispute_cases SET freeze_job = %d  WHERE proposal_id = %d", 1, $proposal_id));


            if ($execut) {
                wp_send_json( ['status' => true, 'message' => 'Contest Freeze state saved']);
            } else {
                throw new RuntimeException( 'Could not update');
            }

            wp_die();

        } else {
            throw new RuntimeException( 'Could not update');
        }
    } catch (Exception $e) {
        will_send_to_error_log('admin contest freeze',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }
}