<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_price_by_date

 * Description: update_price_by_date

 *

 */

add_action('wp_ajax_update_price_by_date', 'update_price_by_date');

/**
 * code-notes Only if the user owns the job
 */
function update_price_by_date(){
    global $wpdb;
    /*
    * current-php-code 2020-Sep-30
    * ajax-endpoint  update_price_by_date
    * input-sanitized : da_job_id, date
    */

    try {
        $date = FLInput::get('date');
        $job_id = FLInput::get('da_job_id', null); //code-notes, if get a job id, then use it to set meta



        $message = "Nothing Done: Date not set with empty id";
        if ($date && $job_id) {
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

            $sane_date = will_validate_string_date_or_make_future($date);
            update_post_meta($job_id, 'job_standard_delivery_date', $sane_date);
            $message = "Updated Delivery Date to $sane_date";
        } elseif ($job_id) {
            throw new RuntimeException("Date is empty or not provided");
        }


        wp_send_json( ['status' => true, 'message' => $message]);
    } catch (Exception $e) {
        will_send_to_error_log('update date',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }

}