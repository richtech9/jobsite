<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      job_instruction_editable

 * Description: Update the instruction editable(textarea value) using the ajax

 *

 */

add_action('wp_ajax_job_instruction_editable', 'job_instruction_editable');


function job_instruction_editable(){

    /*
     * current-php-code 2020-Oct-5
     * ajax-endpoint  job_instruction_editable
     * input-sanitized : author_id,job_id
    */
    try {
        $author_id = FLInput::get('author_id');
        $job_id = FLInput::get('job_id');
        $job_instruction_unfiltered = FLInput::get('job_instruction');

        $current_user = wp_get_current_user();

        $current_user_id = $current_user->data->ID;

        if ($author_id == $current_user_id) {

            $job_instruction = removePersonalInfo($job_instruction_unfiltered);

            $b_ok = update_post_meta($job_id, 'project_description', $job_instruction);

            $search_says = FreelinguistProjectAndContestHelper::update_elastic_index($job_id);

            will_do_nothing([$b_ok, $search_says]);

            wp_send_json( ['status' => true, 'message' => 'Contest Saved']);

            exit;

        } else {

            throw new RuntimeException("Caller is Not the owner");

        }

    } catch (Exception $e) {
        will_send_to_error_log('admin save contest',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}