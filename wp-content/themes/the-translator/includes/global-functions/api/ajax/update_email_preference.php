<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      update_email_preference

 * Description: update_email_preference

 *

 */

add_action('wp_ajax_update_email_preference', 'update_email_preference');


function update_email_preference(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_email_preference
    * input-sanitized : email_notify
    */

    $email_notify = FLInput::get('email_notify');
    
    if ($email_notify) {

        if($email_notify === 'hourly_notify'){

            update_user_meta( get_current_user_id(), 'new_jobs_notifications', 'hourly_notify' );

        }elseif($email_notify === 'daily_notify'){

            update_user_meta( get_current_user_id(), 'new_jobs_notifications', 'daily_notify' );

        }else{

            update_user_meta( get_current_user_id(), 'new_jobs_notifications', 'never_notify');

        }

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}