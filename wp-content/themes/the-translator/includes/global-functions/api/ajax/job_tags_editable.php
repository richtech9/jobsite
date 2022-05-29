<?php

/******* author aarvik ******/
add_action('wp_ajax_job_tags_editable', 'job_tags_editable');

/**
 * code-notes Only when the logged in user owns the job
 */
function job_tags_editable(){
    global $wpdb;
    /*
     * current-php-code 2020-Oct-5
     * ajax-endpoint  job_tags_editable
     * input-sanitized : hidden_job_id,hidden-project_tags
    */
    try {
        $job_id = (int)FLInput::get('hidden_job_id');
        $tags_words_comma_delimited = FLInput::get('hidden-project_tags');

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


        $tagIdArray = [];

        if (!$job_id) {throw new RuntimeException("job id is empty");}
        if ($tags_words_comma_delimited) {
            $tagArray = explode(',', $tags_words_comma_delimited);
            foreach ($tagArray as $tag_untrimmed) {
                $tag = trim($tag_untrimmed);
                if ($tag) {
                    $sql_statement = /** @lang text */
                        "SELECT id FROM {$wpdb->prefix}interest_tags WHERE tag_name='" . $tag . "'";
                    $haveTag = $wpdb->get_row($sql_statement, ARRAY_A);
                    if ($haveTag) {
                        $tagIdArray[] = $haveTag['id'];
                    } else {

                        $sql_to_insert = "INSERT INTO wp_interest_tags (tag_name,created_at,modified_at)
                                                      VALUES ('$tag',NOW(),NOW())";
                        $wpdb->query($sql_to_insert);
                        $da_last_id = will_get_last_id($wpdb,'interest_tags');
                        $tagIdArray[] = $da_last_id;
                    }
                }
            }
        }



        $jtype = get_post_meta($job_id, 'fl_job_type', true);
        $tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
        if ($jtype == 'contest') {
            $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
        } else if ($jtype == 'project') {
            $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
        }
        if ($tagType === FreelinguistTags::UNKNOWN_TAG_TYPE) {
            throw new RuntimeException("Did not recognize the job type for job id of [$job_id], string type of [$jtype]");
        }


        if ($tagIdArray) {

            foreach ($tagIdArray as $tagIds) {
                $jobCache = $wpdb->get_row("SELECT * FROM wp_tags_cache_job WHERE job_id=$job_id AND tag_id=$tagIds AND type = $tagType", ARRAY_A);
                if (empty($jobCache)) {
                    $wpdb->insert( 'wp_tags_cache_job', array('job_id' => $job_id, 'tag_id' => $tagIds, 'type' => $tagType));
                    will_throw_on_wpdb_error($wpdb);
                }
            }

            $jobCacheActiveJob = $wpdb->get_results("SELECT  tag_id FROM wp_tags_cache_job WHERE job_id=$job_id AND type = $tagType", ARRAY_A);
            will_throw_on_wpdb_error($wpdb);
            $jobCacheActiveJob = array_column($jobCacheActiveJob, 'tag_id');
            $deleteTag = array_diff($jobCacheActiveJob, $tagIdArray);
            if ($deleteTag) {
                $deleteTagIn = implode(",", $deleteTag);
                $wpdb->query("DELETE FROM wp_tags_cache_job WHERE tag_id IN($deleteTagIn) AND job_id=$job_id AND type = $tagType");
                will_throw_on_wpdb_error($wpdb);
            }
        }
        FreelinguistProjectAndContestHelper::update_elastic_index($job_id);
        $resp = array('status' => true, 'message' => 'Tags Updated');
        wp_send_json($resp);
        die(); //above dies, but phpstorm does not know that, so adding it here for editing

    } catch (Exception $e) {
        will_send_to_error_log('Error in job_tags_editable', will_get_exception_string($e));
        $resp = array('status' => false, 'message' => $e->getMessage());
        wp_send_json($resp);
        die();//above dies, but phpstorm does not know that, so adding it here for editing

    }
}