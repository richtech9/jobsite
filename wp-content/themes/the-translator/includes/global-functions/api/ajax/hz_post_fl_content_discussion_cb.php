<?php

add_action( 'wp_ajax_hz_post_fl_content_discussion',  'hz_post_fl_content_discussion_cb' );

/**
 * 
 * code-notes Must have a  content id and a post_to ; And the logged in user must be the content creator or the buyer , and the post_to same thing
 * 
 */
function hz_post_fl_content_discussion_cb(){
    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  hz_post_fl_content_discussion
    * input-sanitized : data keys->  comment , comment_to , content_id, parent_comment
    */
    global $wpdb;
    try {

        FLInput::onlyPost(true);
        $posted_data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);
        FLInput::onlyPost(false);
        $data = [];

        parse_str($posted_data_string, $data);


        $comment = FLInput::clean_data_key($data, 'comment', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $posted_to_user_id = (int)FLInput::clean_data_key($data, 'comment_to');

        $content_id = (int)FLInput::clean_data_key($data, 'content_id');

        $parent_comment = (int)FLInput::clean_data_key($data, 'parent_comment');

        if (!$comment) {
            throw new RuntimeException("Need a comment");
        }

        $context = '';

        if (!is_user_logged_in()) {
            throw new RuntimeException("User Is Not Logged In");
        }

        if (!$content_id) {
            throw new RuntimeException("This ajax needs a content id");
        }

        if (!$posted_to_user_id) {
            throw new RuntimeException("This ajax needs a private discussion with two users");
        }

        $logged_in_user_id = get_current_user_id();

        $parent_comment_id = null;
        if ($parent_comment) {
            $parent_comment_id = (int)$parent_comment;
        }

        $sql_to_check_content_discussion = "
            SELECT id FROM wp_linguist_content 
            WHERE
            id = $content_id AND 
            (
                (user_id = $logged_in_user_id AND purchased_by = $posted_to_user_id)
                OR
                (user_id = $posted_to_user_id  AND purchased_by = $logged_in_user_id)
            )
        ";

        $check_content_discusion_res = $wpdb->get_results($sql_to_check_content_discussion);

        will_throw_on_wpdb_error($wpdb,'Checking permissions for private content participation');
        if (empty($check_content_discusion_res)) {
             throw new RuntimeException("Cannot Add Comment. Either one of the people are not valid (creator and purchasor), or wrong content id");
        }


        $wpdb->insert('wp_fl_discussion', array(

            'post_by' => $logged_in_user_id,

            'comment' => $comment,

            'post_to' => ($posted_to_user_id ? $posted_to_user_id: null),

            'post_id' => null,

            'content_id' => ($content_id ? $content_id: null),

            'parent_comment' => ($parent_comment_id ? $parent_comment_id: null),

        ));

        $inst = will_get_last_id($wpdb,'new discussion entry');




        $context .= '<div class="log_in_wtth_box comment_bottom-box_newcss">
                        <i class="fa col-md-1 thumb-img">
                        <img src="' . hz_get_profile_thumb(get_current_user_id()) . '" ></i>
                        <div class="user_box col-md-11">
                            <h5>
                                <span>' . hz_get_pro_name(get_current_user_id()) . '</span> 
                                <i class="fa fa-circle" aria-hidden="true"></i>' . date("d/m/Y") . ' 
                            </h5>
                            <p>' . $comment . '</p>
                            
                            <strong>
                            <i class="fa fa-angle-up" aria-hidden="true"></i> 
                            <i class="fa fa-angle-down" aria-hidden="true"></i> 
                            <i class="fa fa-circle" aria-hidden="true"></i> Reply</strong>
                        </div>
                    </div>';





        wp_send_json( ['status' => true, 'message' => 'Posted Successfully.','context'=> $context, 'is_login'=> true, 'did'=>$inst]);

    } catch (Exception $e) {
        will_send_to_error_log('content discussion',will_get_exception_string($e));

        wp_send_json( ['status' => false, 'message' => $e->getMessage(),'context'=> '', 'is_login'=> is_user_logged_in()]);
    }

}