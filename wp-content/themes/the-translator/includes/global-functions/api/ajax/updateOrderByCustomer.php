<?php
/*

 * Author Name: Aarvikinfotech

 * Method:      updateOrderByCustomer

 * Description: Third/Last process to update  order using the ajax

 *

 */

add_action('wp_ajax_updateOrderByCustomer', 'updateOrderByCustomer');

class RuntimeExceptionWithLink extends RuntimeException {
    protected $link;
    public function get_link() { return $this->link;}

    public function __construct(string $message = "", $link = null, int $code = 0, Throwable $previous = null)
    {
        $this->link = $link;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * code-notes Only when the logged in user owns this contest
 */
function updateOrderByCustomer(){
    global $wpdb;
    /*
    * current-php-code 2020-Oct-5
    * ajax-endpoint  updateOrderByCustomer
    * input-sanitized : (see below)
    */

    try {
        $job_id = FLInput::get('project_id');

        $lang = FLInput::get('lang', 'en');
        $project_title = FLInput::get('project_title');
        $project_description = FLInput::get('project_description');
        $estimated_budgets = floatval(FLInput::get('estimated_budgets'));
        $tags = FLInput::get('tags');
        $already_ins = FLInput::get('already_ins');
        $temp_id = FLInput::get('temp_id');
        $standard_delivery = FLInput::get('standard_delivery');

        $user_id = get_current_user_id();
        $url = null;

        $job_check_result = $wpdb->get_row("
                        SELECT ID ,
                                meta_budget.meta_value as estimated_budgets,
                                meta_job_type.meta_value as fl_job_type
                        FROM wp_posts p
                        LEFT JOIN wp_postmeta meta_job_type ON meta_job_type.post_id = p.ID 
                          AND meta_job_type.meta_key =  'fl_job_type' AND meta_job_type.meta_value in ('contest','project')
                        LEFT JOIN wp_postmeta meta_budget ON meta_budget.post_id = p.ID 
                          AND meta_budget.meta_key =  'estimated_budgets' 
                        WHERE p.post_author = $user_id AND ID = $job_id AND meta_job_type.meta_id IS NOT NULL "
            );

        will_throw_on_wpdb_error($wpdb);
        if (empty($job_check_result)) {
            throw new RuntimeException("User does not own this contest");
        }

        $old_estimaged_budgets = $job_check_result->estimated_budgets;
        $job_type = $job_check_result->fl_job_type;

        if ($job_type === 'contest') {
            $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
            $old_estimaged_budgets = floatval($old_estimaged_budgets);
        } else if ($job_type === 'project') {
            $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
        } else {
            throw new InvalidArgumentException("Not a contest or  a project");
        }


        //TAGS PROCESS
        $tagIdArray = [];
        if ($tags) {
            $tagArray = explode(',', $tags);
            foreach ($tagArray as $tag) {
                if ($tag) {
                    $haveTag = $wpdb->get_row(
                        "SELECT id FROM wp_interest_tags WHERE tag_name='" . $tag . "'"
                        , ARRAY_A);
                    if ($haveTag) {
                        $tagIdArray[] = $haveTag['id'];
                    } else {
                        $sql_to_insert = "INSERT INTO wp_interest_tags (tag_name,created_at,modified_at)
                                                      VALUES ('$tag',NOW(),NOW())";
                        $wpdb->query($sql_to_insert);
                        $da_last_id = will_get_last_id($wpdb, 'interest_tags');
                        $tagIdArray[] = $da_last_id;
                    }
                }
            }
        }




        if ($tagIdArray) {

            foreach ($tagIdArray as $tagIds) {
                $jobCache = $wpdb->get_row(
                    "SELECT * FROM wp_tags_cache_job WHERE job_id=$job_id AND tag_id=$tagIds AND type = $tagType",
                    ARRAY_A);
                if (empty($jobCache)) {
                    $wpdb->insert( 'wp_tags_cache_job', array('job_id' => $job_id, 'tag_id' => $tagIds, 'type' => $tagType));
                }
            }


            $jobCacheActiveJob = $wpdb->get_results("SELECT  tag_id FROM wp_tags_cache_job WHERE job_id=$job_id AND type = $tagType", ARRAY_A);
            $jobCacheActiveJob = array_column($jobCacheActiveJob, 'tag_id');
            $deleteTag = array_diff($jobCacheActiveJob, $tagIdArray);
            if ($deleteTag) {
                $deleteTagIn = implode(",", $deleteTag);
                $wpdb->query("DELETE FROM wp_tags_cache_job WHERE tag_id IN($deleteTagIn) AND job_id=$job_id AND type = $tagType");
            }
        }


        if ($already_ins) {

            $insfilename = $already_ins;

            $wpdb->update('wp_files', array('post_id' => $job_id, 'type' => FLWPFileHelper::TYPE_POST_DETAILS), array('file_name' => $insfilename));

        }
        if ($temp_id) {

            $wpdb->update('wp_files', array('post_id' => $job_id, 'type' => FLWPFileHelper::TYPE_POST_DETAILS), array('temp_id' => $temp_id));

        }


        $userCurrBalance = get_user_meta($user_id, 'total_user_balance', true);

        $getFee = 0;

        $contestFee = floatval(get_option('contest_fee') ? get_option('contest_fee') : 0);

        $contBudget = $estimated_budgets;

        $dedecutTotal = $getFee + $contBudget + $contestFee;

        if( ($job_type == 'contest') ) {

            if(!$project_title){
                $alert_message = get_custom_string_return('Project title is required field.');
                throw new RuntimeExceptionWithLink($alert_message);

            }elseif(empty($project_description)){
                $alert_message = get_custom_string_return('Project description is required field.');
                throw new RuntimeExceptionWithLink($alert_message);

            } elseif ($job_type == 'contest' && $estimated_budgets && is_numeric($estimated_budgets) && floatval($estimated_budgets) < 0) {
                $alert_message = get_custom_string_return('Budget can not be negative');
                throw new RuntimeExceptionWithLink($alert_message);

            } elseif ($job_type == 'contest' && !$estimated_budgets) {
                $alert_message = get_custom_string_return('Budget can not be empty');
                throw new RuntimeExceptionWithLink($alert_message);

            } elseif (!$job_id) {

                $url = freeling_links('order_process');
                $alert_message = get_custom_string_return('Job already generated or you are genearating wrong job');
                throw new RuntimeExceptionWithLink($alert_message,$url);
            }

            if (($old_estimaged_budgets < $estimated_budgets) &&  ($dedecutTotal > $userCurrBalance)){

                $alert_message = get_custom_string_return('Your Wallet Balance is low');

                throw new RuntimeExceptionWithLink($alert_message);
            }

            $project_title = removePersonalInfo($project_title);

            $project_description = removePersonalInfo($project_description);

            $estimated_budgets = removePersonalInfo($estimated_budgets);



            $job_type = removePersonalInfo($job_type);



            update_post_meta($job_id, 'project_title', $project_title);

            update_post_meta($job_id, 'project_description', $project_description);
            //code-notes insurance is not changed when contest is updated


            //code-notes no more text snapshots


            update_post_meta($job_id, 'estimated_budgets', $estimated_budgets);



            update_post_meta($job_id, 'fl_job_type', $job_type);

            update_post_meta($job_id, 'contest_prize', 'deducted');




            $job_standard_delivery_date = empty($standard_delivery) ? date('Y-m-d') : $standard_delivery;


            update_post_meta($job_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($job_standard_delivery_date));


            $modified_id = get_post_meta($job_id, 'modified_id', true);

            if ($lang) {
                $url = get_site_url() . '/job/' . $modified_id . '?lang=' . $lang . '&action=participants-proposals';

            } else {
                $url = get_site_url() . '/job/' . $modified_id . '?action=participants-proposals';
            }

            FreelinguistProjectAndContestHelper::update_elastic_index($job_id);


        } else { //do not contest

            if (!$project_title) {
                $alert_message = get_custom_string_return('Project title is required field.');
                throw new RuntimeExceptionWithLink($alert_message);

            } elseif (empty($project_description)) {
                $alert_message = get_custom_string_return('Project description is required field.');
                throw new RuntimeExceptionWithLink($alert_message);

            } elseif (!$job_id) {

                $url = freeling_links('order_process');

                $alert_message = get_custom_string_return('Job already generated or you are genearating wrong job');
                throw new RuntimeExceptionWithLink($alert_message,$url);

            }

            $project_title = removePersonalInfo($project_title);

            $project_description = removePersonalInfo($project_description);

            $estimated_budgets = removePersonalInfo($estimated_budgets);



            $job_type = removePersonalInfo($job_type);



            update_post_meta($job_id, 'project_title', $project_title);

            update_post_meta($job_id, 'project_description', $project_description);

            update_post_meta($job_id, 'estimated_budgets', $estimated_budgets);



            update_post_meta($job_id, 'fl_job_type', $job_type);


            $job_standard_delivery_date = empty($standard_delivery) ? date('Y-m-d') : $standard_delivery;

            update_post_meta($job_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($job_standard_delivery_date));


            update_post_meta($job_id, 'job_created_date', date("Ymd"));


            $modified_id = get_post_meta($job_id, 'modified_id', true);


            if ($lang) {

                $url = get_site_url() . '/job/' . $modified_id . '?lang=' . $lang;
            } else {

                $url = get_site_url() . '/job/' . $modified_id;
            }

            FreelinguistProjectAndContestHelper::update_elastic_index($job_id);


        }

        

        wp_send_json( ['status' => true, 'message' => 'job updated','url'=> $url]);
        die();
        
    }
    catch (RuntimeExceptionWithLink $e) {
        will_send_to_error_log('Update order by customer',[
            will_get_exception_string($e),
                $e->get_link()
        ]
        );
        wp_send_json( ['status' => false, 'message' => $e->getMessage(),'url'=> $e->get_link()]);
    }
    catch (Exception $e) {
        will_send_to_error_log('Update order by customer',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage(),'url'=> null]);
    }

}