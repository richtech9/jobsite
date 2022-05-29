<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_summary_info_data

 * Description: update_summary_info_data

 *

 */
add_action('wp_ajax_update_summary_info_data', 'update_summary_info_data');


function update_summary_info_data(){
    /*
    * current-php-code 2020-Oct-15
    * ajax-endpoint  update_summary_info_data
    * input-sanitized :  hidden-project_tags,user_description
    */

    $hidden_project_tags = FLInput::get('hidden-project_tags');

    $user_description = FLInput::get('user_description', [], FLInput::YES_I_WANT_CONVESION,
        FLInput::YES_I_WANT_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);

    //code-bookmark ajax function to update profile information
    $log = [];
    try {
        global $wpdb;

        //TAGS PROCESS
        $tagIdArray = array();
        if ($hidden_project_tags) {
            $tagArray = explode(',', $hidden_project_tags);
            $log[] = ['$tagArray'=>$tagArray];

            foreach ($tagArray as $tag) {
                $log[] = ['starting loop on tag'=>$tag];
                if ($tag) {
                    $sql_statement =
                        "SELECT id FROM wp_interest_tags WHERE tag_name='" . $tag . "'";
                    $haveTag = $wpdb->get_row($sql_statement, ARRAY_A);
                    will_throw_on_wpdb_error($wpdb);
                    $log[] = $wpdb->last_query;
                    $log[] = ['$haveTag'=>$haveTag];
                    if ($haveTag) {
                        $tagIdArray[] = $haveTag['id'];
                        $log[] = ['adding to tag id array - already existing'=>$haveTag['id']];
                    } else {
                        $sql_to_insert = "INSERT INTO wp_interest_tags (tag_name,created_at,modified_at)
                                                          VALUES ('$tag',NOW(),NOW())";
                        $wpdb->query($sql_to_insert);
                        $da_last_id = will_get_last_id($wpdb,'interest_tags');
                        $tagIdArray[] = $da_last_id;
                        will_throw_on_wpdb_error($wpdb);
                        $log[] = $wpdb->last_query;
                        $log[] = ['adding to tag id array - new insert'=>$wpdb->insert_id];
                    }
                }
            }
            $log[] = ['$tagIdArray'=>$tagIdArray];
        }
        $content_id = get_current_user_id();
        if (!$content_id) {
            throw new RuntimeException('Unauthorized user');
        }
        $log[] = ['$content_id'=>$content_id];
        if (!empty($tagIdArray)) {
            // $jobCacheActiveJob = $wpdb->get_row( "SELECT GROUP_CONCAT(tag_id) tag_id FROM {$wpdb->prefix}tags_cache_job WHERE job_id=$content_id",ARRAY_A);
            $sql_statement = /** @lang text */
                "SELECT  tag_id FROM {$wpdb->prefix}tags_cache_job WHERE job_id=$content_id AND type = ".FreelinguistTags::USER_TAG_TYPE;
            $jobCacheActiveJob = $wpdb->get_results($sql_statement, ARRAY_A);

            will_throw_on_wpdb_error($wpdb);
            $log[] = $wpdb->last_query;
            $log[] = ['$jobCacheActiveJob'=>$jobCacheActiveJob];
            $jobCacheActiveJob = array_column($jobCacheActiveJob, 'tag_id');
            $log[] = ['$jobCacheActiveJob'=>$jobCacheActiveJob];
            $deleteTag = array_diff($jobCacheActiveJob, $tagIdArray);
            $log[] = ['$deleteTag'=>$deleteTag];

            foreach($tagIdArray as $tagIds){
                $log[] = ['top of loop for tag_id'=>$tagIds];

                $jobCache = $wpdb->get_row( /** @lang text */
                    "SELECT * FROM wp_tags_cache_job WHERE job_id=$content_id AND tag_id=$tagIds AND type = ".FreelinguistTags::USER_TAG_TYPE,ARRAY_A);
                will_throw_on_wpdb_error($wpdb);
                $log[] = $wpdb->last_query;
                $log[] = ['$jobCache'=>$jobCache];

                if(empty($jobCache)){
                    $wpdb->insert( 'wp_tags_cache_job', array('job_id'=>$content_id,'tag_id'=>$tagIds,'type'=>FreelinguistTags::USER_TAG_TYPE) );
                    will_throw_on_wpdb_error($wpdb);
                    $log[] = $wpdb->last_query;
                }
            }



            if (!empty($deleteTag)) {
                $deleteTagIn = implode(",", $deleteTag);
                $wpdb->query(/** @lang text */
                    "DELETE FROM wp_tags_cache_job WHERE tag_id IN($deleteTagIn) AND job_id=$content_id AND type = ".FreelinguistTags::USER_TAG_TYPE);
                will_throw_on_wpdb_error($wpdb);
                $log[] = $wpdb->last_query;
            }


            //code-notes refreshed the user score so that the top tag list is recalculated
            //this is done by adding one second to the last login time, when they update their profile
            $sql_statment = "UPDATE wp_fl_user_data_lookup 
                        SET score = 0 ,
                        last_login_time = DATE_ADD(if(last_login_time,last_login_time,NOW()), INTERVAL 1 second)
                        WHERE user_id = $content_id" ;
            $wpdb->query($sql_statment);
            will_throw_on_wpdb_error($wpdb);
            $log[] = $wpdb->last_query;
        }

        $allowed_tags = /** @lang text */
            '<hr><br><p><a><span><div><strong><s><b><i>'.
            '<blockquote><sub><sup><ol><ul><li><img><table><tbody><thead><tr><td><th>';
        $user_description_filtered = strip_tags($user_description,$allowed_tags);


        if (!$user_description_filtered) { throw new RuntimeException("user_description not set"); }
        //code-notes no more text snapshots
        update_user_meta(get_current_user_id(), 'description', $user_description_filtered);

        FreelinguistUserHelper::update_elastic_index(get_current_user_id()); //run after the meta updates the trigger in the user lookup

        //code-notes update units
        FreelinguistUnitGenerator::generate_units($log,[get_current_user_id()],[]);

        wp_send_json(['success'=>true,'message'=>'update_summary_info_data worked without error','code'=>201,'log'=>$log],201);


    } catch(Exception $e) {
        wp_send_json(['success'=>false,'message'=>'Could not successfully run update_summary_info_data (stopped execution after): '.$e->getMessage(),'code'=>$e->getCode(),'log'=>$log],200);
    }


}