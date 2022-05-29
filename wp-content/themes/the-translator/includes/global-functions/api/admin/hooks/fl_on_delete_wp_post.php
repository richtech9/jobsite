<?php

add_action('delete_post', 'fl_on_delete_wp_post' , 10, 2);


/*
 * current-php-code 2021-Feb-23
 * current-hook
 * input-sanitized :
 */

/**
 * Fired immediately before a post is deleted from the database.
 * By the time we see this, the meta and comments are deleted already
 * @param int $post_id Post ID.
 */
function fl_on_delete_wp_post($post_id) {
    //code-notes delete any files for instruction, details or uploads  that might exist
    try {
        FLWPFileHelper::remove_any_files_for_post($post_id);
    }  catch (Exception $e) {
        will_send_to_error_log(
            "Exception while deleting post files for post id $post_id, will not stop the process",
            will_get_exception_string($e)
        );
    }
}