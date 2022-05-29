<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      update_user_email

 * Description: update_user_email

 *

 */

add_action('wp_ajax_email_send_all_notifications', 'email_send_all_notifications');


function email_send_all_notifications(){
    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  email_send_all_notifications
    * input-sanitized : email_send_all_notifications
    */

    try {
        $email_send_all_notifications = (int)FLInput::get('email_send_all_notifications');

        $user_id = get_current_user_id();

        $old_setting = (int)get_user_meta($user_id, 'email_send_all_notifications',true);

        if ($old_setting !== $email_send_all_notifications) {
            $b_updated_ok = update_user_meta($user_id, 'email_send_all_notifications', $email_send_all_notifications);
            if (!$b_updated_ok) {throw new RuntimeException("Could not update setting");}
            if ($email_send_all_notifications) {
                $message = "Notifications Turned On";
            } else {
                $message = "Notifications Turned Off";
            }

        } else {
            $message = "Setting unchanged";
        }



        wp_send_json( ['status' => true, 'message' => $message]);

    } catch (Exception $e) {
        will_send_to_error_log('email_send_all_notifications ',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }


}

