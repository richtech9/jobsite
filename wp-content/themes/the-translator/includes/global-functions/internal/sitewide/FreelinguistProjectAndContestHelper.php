<?php

class FreelinguistProjectAndContestHelper {

    /*
    * current-php-code 2020-Oct-05
    * internal-call
    * input-sanitized :
    */

    const PROJECT_PREFIX = '11';
    const CONTEST_PREFIX = '22';

    /**
     *
     * Safe to call, will not break code if something goes wrong
     * @param string|int $job_id_input
     * @param string|null $job_type_to_use , needed when job already deleted, which means cannot get its type
     * @return array|false
     *  returns false on failure, and ES array response if ok
     */
    public static function update_elastic_index($job_id_input, $job_type_to_use = null) {

        /*
         * current-php-code 2020-Oct-16
         * internal-call
         * input-sanitized :
         */
        global $wpdb;
        $job_id = (int)$job_id_input;
        try {

            if (!$job_id) {throw new RuntimeException("Job id is empty");}

            //since this is called after the job_id's meta is loaded, we can get the job type here to customize the SQL below

            $jtype = get_post_meta($job_id, 'fl_job_type', true);
            $tagType = FreelinguistTags::UNKNOWN_TAG_TYPE;
            $index = null;
            if ($job_type_to_use) {
                $jtype = $job_type_to_use;
            }
            if ($jtype == 'contest') {
                $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
                $index = "contest";
            } else if ($jtype == 'project') {
                $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
                $index = "project";
            }
            if ($tagType === FreelinguistTags::UNKNOWN_TAG_TYPE) {
                throw new RuntimeException("Did not recognize the job type for job id of [$job_id], string type of [$jtype]");
            }


            $sql = "
               SELECT
                  wppost.ID as da_job_id,
                  look.job_title as job_title,
                  look.job_description as job_description,
                  (
                    SELECT
                      GROUP_CONCAT(intags.tag_name) as tag_ids
                      FROM  wp_tags_cache_job ijob
                      LEFT JOIN wp_interest_tags  intags ON intags.ID = ijob.tag_id
                      WHERE ijob.type = " . $tagType . " AND
                            ijob.job_id = wppost.ID
                  ) as tag_names,
                  UNIX_TIMESTAMP(if(look.last_update,look.last_update,wppost.post_date_gmt)) as recent_ts
                FROM wp_posts as wppost
                  INNER JOIN wp_fl_post_data_lookup look on wppost.ID = look.post_id
                WHERE
                  wppost.post_type = 'job' and
                  wppost.post_status = 'publish' and
                  wppost.ID = $job_id and
                  look.hide_job = 0
                 ORDER BY wppost.ID
                ";

            $jobs = $wpdb->get_results($sql);
            will_throw_on_wpdb_error($wpdb);
            if (empty($jobs)) {
                //try to delete the index and  return normally
                $log = [];
                //ELASTIC CONNECTION
                try {
                    $es = new FreelinguistElasticSearchHelper();
                    $es_says = $es->delete_id_inside_index('freelinguist',$index,$job_id,$log);
                    will_send_to_error_log('deleting content from es',$log,false,false);
                    return $es_says;
                } catch(Exception $e) {
                    will_send_to_error_log('error removing job from elastic search', $e->getMessage());
                    throw $e;
                }
            } else {
                $job = $jobs[0];


                $tagArray = [];
                if (!empty($job->tag_names)) {
                    $tagArray = explode(',', $job->tag_names);
                }


                //ELASTIC CONNECTION
                try {
                    $log = [];
                    $es = new FreelinguistElasticSearchHelper();
                    $ret = $es->add_index([
                        'index' => $index,
                        'type' => 'freelinguist',
                        'id' => (int)$job->da_job_id,
                        'body' => array(
                            'job_id' => (int)$job->da_job_id,
                            'title' => $job->job_title,
                            'tags' => $tagArray,
                            'job_type' => $index,
                            'description' => $job->job_description,
                            'instruction' => '',
                            'is_cache' => '0',
                            'rating_as_freelancer' => 0,
                            'rating_as_customer' => 0,
                            'translate_from' => '',
                            'translate_to' => '',
                            'price' => (int)0,
                            'recent_ts' => time()
                        )
                    ],$log);
                    return $ret;

                } catch (Exception $e) {
                    will_send_to_error_log('error sending job to elastic search', $e->getMessage());
                    throw $e;
                }
            }
        } catch (Exception $e) {
            will_send_to_error_log("Cannot update ES for job", [
                'exception' => will_get_exception_string($e),
                'call_stack' => debug_backtrace(),
                '$job_id_input' => $job_id_input,
                '$job_id' => $job_id
            ],
                false,
                false
            );
            return false;
        }

    }

    /**
     * @param int $contest_id
     * @return bool|string
     * returns false if not a contest, else returns the post title of the contest
     */
    static function is_contest($contest_id) {
        global $wpdb;
        $contest_id = (int)$contest_id;
        $contest_type = FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST;
        $sql = "SELECT look.id ,p.post_title
                FROM wp_fl_post_data_lookup look
                INNER JOIN wp_posts p ON p.ID = look.post_id 
                WHERE look.post_id = $contest_id && look.fl_job_type=$contest_type";
        $res = $wpdb->get_results($sql);
        if (empty($res)) {return false;}
        return $res[0]->post_title;
    }

    /**
     * @param int $job_id
     * @return int
     * @uses FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST
     * @uses FLPostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT
     */
    static function get_lookup_job_type($job_id) {
        global $wpdb;
        $job_id = (int)$job_id;
        $sql = "SELECT look.id ,look.fl_job_type
                FROM wp_fl_post_data_lookup look
                WHERE look.post_id = $job_id ";
        $res = $wpdb->get_results($sql);
        if (empty($res)) {throw new RuntimeException("Cannot find the post id in the lookup table");}
        return (int)$res[0]->fl_job_type;
    }

    /**
     * @param int $contest_id
     * @return array, keyed by proposal ids that won, with the value being object of user data (id,user_nicename,user_email)
     */
    static public function get_winning_proposals_and_users($contest_id) {
        global $wpdb;
        $map_proposal_ids = [];
        $prizes_awarded_comma_delimited = get_post_meta($contest_id, 'contest_awardedProposalPrizes', true);
        if (empty($prizes_awarded_comma_delimited)) {return [];}
        $maybe_ints = explode(',', $prizes_awarded_comma_delimited);
        foreach ($maybe_ints as $maybe_int) {
            if (intval($maybe_int)) {
                $map_proposal_ids[$maybe_int] = $maybe_int;
            }
        }
        if (empty($map_proposal_ids)) {return [];}
        $proposal_ids = array_keys($map_proposal_ids);
        $proposal_ids_as_comma_delimited = implode(',',$proposal_ids);

        $sql = "SELECT u.ID as id,u.user_nicename,u.user_email ,u.user_login, p.id as proposal_id
                FROM wp_users u
                INNER JOIN wp_proposals p ON p.by_user = u.ID
                WHERE p.id in ($proposal_ids_as_comma_delimited)";

        $res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb,'winning proposal user details');
        foreach ($res as $row) {
            $da_prop_id = (int)$row->proposal_id;
            if (isset($map_proposal_ids[$da_prop_id])) {
                $map_proposal_ids[$da_prop_id] = $row;
            }
        }

        return $map_proposal_ids;

    }


}