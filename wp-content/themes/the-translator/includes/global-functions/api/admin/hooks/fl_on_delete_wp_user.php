<?php

add_action('delete_user', 'fl_on_delete_wp_user' , 10, 2);


/*
 * current-php-code 2021-Feb-23
 * current-hook
 * input-sanitized :
 */

/**
 * Fires before a user is deleted from the database.
 *
 * @param int      $user_id  ID of the deleted user.
 * @param int|null $reassign ID of the user to reassign posts and links to.
 *                           Default null, for no reassignment.
 */
function fl_on_delete_wp_user($user_id, $reassign) {
    will_do_nothing($reassign);

    $user_batch_id_from_test = get_user_meta($user_id,'create_batch',true);
    try {
        //code-notes do not try to delete chat user if this is a test user
        if (empty($user_batch_id_from_test)) {
            FreelinguistRefreshChatCredentials::unregister_user($user_id,null);
        }

    } catch (Exception $e) {
        will_send_to_error_log(
            "Exception while deleting user chat account for user id $user_id, but will not stop the process",
            will_get_exception_string($e)
        );
    }

    try {
        FreelinguistContentHelper::delete_own_content($user_id);
    } catch (Exception $e) {
        will_send_to_error_log(
            "Exception while deleting content for user id $user_id,but will not stop the process",
            will_get_exception_string($e)
        );
    }

    try {
        FLWPFileHelper::remove_any_files_for_user($user_id);
    } catch (Exception $e) {
        will_send_to_error_log(
            "Exception while deleting user files for user id $user_id,but will not stop the process",
            will_get_exception_string($e)
        );
    }
}