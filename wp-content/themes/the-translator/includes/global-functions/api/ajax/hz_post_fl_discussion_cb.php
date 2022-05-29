<?php

add_action( 'wp_ajax_hz_post_fl_discussion',  'hz_post_fl_discussion_cb'  );
add_action( 'wp_ajax_nopriv_hz_post_fl_discussion',  'hz_post_fl_discussion_cb'  );



/**
 *
 * code-notes See validation rules below

Needs to have a post_id, or content_id
Anyone can comment if there is not a post_to (comment_to) set

If there is a  project id , and a post_to,
then the logged in user must be project owner, or has been hired to work on it (job status of start associated with this project), and the post_to then same thing

If there is contest id, and a post_to
then the logged in user needs to be the owner of the contest or the user needs to have a proposal submitted (but does not have to be approved), and the post_to then same thing

If there is a content id and a post_to
the logged in user must be the content creator or be the buyer , and the post_to same thing
 */
function hz_post_fl_discussion_cb(){
    /*
     * current-php-code 2020-Oct-10
     * ajax-endpoint  hz_post_fl_discussion
     * input-sanitized : data keys->  comment , comment_to , parent_comment, post_id
    */
    global $wpdb;
    global $_REAL_POST;
    try {
        //will_send_to_error_log("hz_post_fl_discussion GOT DAT",$_REAL_POST);
        $data_string = FLInput::get('data', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);

        parse_str($data_string, $data);

        $comment = FLInput::clean_data_key($data, 'comment', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $posted_to_user_id = (int)FLInput::clean_data_key($data, 'comment_to');

        $post_id = (int)FLInput::clean_data_key($data, 'post_id');

        $content_id = (int)FLInput::clean_data_key($data, 'content_id');

        $parent_comment_id = (int)FLInput::clean_data_key($data, 'parent_comment');

        $context = '';

        if (!$comment) {
            throw new RuntimeException("Need a comment");
        }

        if (!is_user_logged_in()) {
            throw new RuntimeException("User Is Not Logged In");
        }

        if ($post_id && $content_id) {
            throw new LogicException("Cannot post a discussion to both a post $post_id, and a content $content_id at the same time");
        }

        $post_lookup_type = NULL;
        if ($post_id) {
            $post_lookup_type = FreelinguistProjectAndContestHelper::get_lookup_job_type($post_id);
//            will_send_to_error_log("BBB post lookup is",
//                [
//                    'found'=>$post_lookup_type,
//                    'constant'=> FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT,
//                    'simple'=>$post_lookup_type === FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT,
//                    'compound'=>(($post_lookup_type === FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST) ||
//                    ($post_lookup_type === FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT)),
//                ]);

            if (!
            (
                ($post_lookup_type === FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST) ||
                ($post_lookup_type === FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT)
            )
            ) {
                throw new RuntimeException("Post id $post_id needs to be a project or a contest");
            }
        }


        $logged_in_user_id = get_current_user_id();

        if (($post_id || $content_id) && $posted_to_user_id) {

            if ($content_id) {

                if ($posted_to_user_id) {
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

                    will_throw_on_wpdb_error($wpdb, 'Checking permissions for private content participation');
                    if (empty($check_content_discusion_res)) {
                        throw new RuntimeException("Cannot Add Comment. Either one of the people are not valid (creator and purchasor), or wrong content id");
                    }
                }
                //else anyone can make the comment
            }

            if ($post_id) {

                if ($post_lookup_type === FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT) {


                    $project_check_res = $wpdb->get_results(
                        "
                                SELECT job.*, post.post_author
                                FROM wp_posts post
                                LEFT JOIN  wp_fl_job job ON job.project_id = post.ID
                                WHERE
                                  post.ID= $post_id AND
                                  (
                                    (job.linguist_id = $logged_in_user_id AND job.job_status = 'start' )
                                     OR
                                    (  post.post_author = $logged_in_user_id)
                                  );
                                "
                    );

                    will_throw_on_wpdb_error($wpdb, 'Checking job for project discussion participation');
                    if (empty($project_check_res)) {
                        throw new RuntimeException("Cannot Add Comment For Project $post_id. Either the logged in user is not fully on the project, or wrong id");
                    }

                    if ($posted_to_user_id) {
                        $project_other_check_res = $wpdb->get_results(
                            "
                                SELECT job.*, post.post_author
                                FROM wp_posts post
                                LEFT JOIN  wp_fl_job job ON job.project_id = post.ID
                                WHERE
                                  post.ID= $post_id AND
                                  (
                                    (job.linguist_id = $posted_to_user_id AND job.job_status = 'start' )
                                    OR
                                    (  post.post_author = $posted_to_user_id)
                                  );
                                "
                        );

                        will_throw_on_wpdb_error($wpdb, 'Checking job for project discussion participation');
                        if (empty($project_other_check_res)) {
                            throw new RuntimeException("Cannot Add Comment For Project $post_id. The person addressed tois not fully on the project, or wrong id");
                        }
                    }


                } elseif ($post_lookup_type === FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST) {

                    $sql_for_contest_check = "SELECT p.id as proposal_id
                                        FROM wp_proposals p 
                                        INNER JOIN wp_posts post ON post.ID = p.post_id
                                        WHERE post.id=$post_id AND
                                        (
                                            (p.by_user = $logged_in_user_id  )
                                            OR
                                            ( post.post_author = $logged_in_user_id)
                                          );
                                        ";

                    $contest_check_res = $wpdb->get_results($sql_for_contest_check);
                    will_throw_on_wpdb_error($wpdb);

                    if (empty($contest_check_res)) {
                        throw new RuntimeException("Cannot Add Comment For Contest $post_id. Either the logged in user is not participating, or wrong id");
                    }

                    if ($posted_to_user_id) {
                        $sql_for_other_contest_check = "SELECT p.id as proposal_id
                                        FROM wp_proposals p 
                                        INNER JOIN wp_posts post ON post.ID = p.post_id
                                        WHERE post.id=$post_id AND
                                        (
                                            (p.by_user = $posted_to_user_id  )
                                            OR
                                            ( post.post_author = $posted_to_user_id)
                                          );
                                        ";

                        $contest_other_check_res = $wpdb->get_results($sql_for_other_contest_check);
                        will_throw_on_wpdb_error($wpdb);

                        if (empty($contest_other_check_res)) {
                            throw new RuntimeException("Cannot Add Comment For Contest $post_id. Either the logged in user is not participating, or wrong id");
                        }
                    }
                    
                    
                } else {
                    throw new RuntimeException("Post id $post_id is neither a contest or a project");
                }

            }


        }



        $wpdb->insert('wp_fl_discussion', array(

            'post_by' => $logged_in_user_id,

            'comment' => $comment,

            'post_to' => ($posted_to_user_id ? $posted_to_user_id: null),

            'post_id' => ($post_id ? $post_id: null),

            'content_id' => ($content_id ? $content_id: null),

            'parent_comment' => ($parent_comment_id ? $parent_comment_id: null),

        ));
        $inst = will_get_last_id($wpdb,'new discussion entry');



        $context .= '<div class="log_in_wtth_box comment_bottom-box_newcss">
                        <i class="fa col-md-1 thumb-img enhanced-text">
                        <img src="' . hz_get_profile_thumb(get_current_user_id()) . '" ></i>
                        <div class="user_box col-md-11">
                            <h5>
                                <span>' . hz_get_pro_name(get_current_user_id()) . '</span> 
                                <i class="fa fa-circle" aria-hidden="true"></i>' . date("d/m/Y") . ' 
                            </h5>
                            <p>' . $comment . '</p>
                            
                            <strong class="enhanced-text">
                                <i class="fa fa-angle-up larger-text" aria-hidden="true"></i> 
                                <i class="fa fa-angle-down larger-text" aria-hidden="true"></i> 
                                <i class="fa fa-circle" aria-hidden="true"></i> Reply
                            </strong>
                        </div>
                    </div>';


        wp_send_json( ['status' => true, 'message' => 'Posted Successfully.','context'=> $context, 'is_login'=> is_user_logged_in(), 'did'=>$inst]);

    } catch (Exception $e) {
        will_send_to_error_log('project discussion',will_get_exception_string($e));

        wp_send_json( ['status' => false, 'message' => $e->getMessage(),'context'=> '', 'is_login'=> is_user_logged_in()]);
    }

}

