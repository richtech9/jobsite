<?php

class FreelinguistESRecord {
    const FROM_DB_MAPPINGS = [
        'da_job_id'=>'job_id',
        'job_title'=>'title',
        'tag_array'=>'tags', //array string[]  from php not db
        'job_type'=>'job_type',
        'job_description'=>'description',
        'rating_by_customer'=>'rating_as_customer',
        'rating_by_freelancer'=>'rating_as_freelancer',
        'recent_ts'=>'recent_ts'
    ];

    public static function create_record_from_row($job) {
        if (!is_object($job)) {
            will_send_to_error_log("Expected job to be an object",$job,true,true);
            throw new RuntimeException("Expected job to be an object");
        }
        $job->tag_array = [];
        if (!empty($job->tag_names)) {
            $job->tag_array = explode(',', $job->tag_names);
        }

        $node = new FreelinguistESRecord();
        foreach ($job as $name => $value) {
            if (array_key_exists($name,static::FROM_DB_MAPPINGS)) {
                $our_key = static::FROM_DB_MAPPINGS[$name];
                $node->$our_key = $value;

            }
        }
        $node->clamp();
        return $node;
    }

    protected function clamp() {
        $this->job_id = (int)$this->job_id;
        $this->rating_as_freelancer = (int)$this->rating_as_freelancer;
        $this->rating_as_customer = (int)$this->rating_as_customer;
        $this->recent_ts = (int)$this->recent_ts;
    }

    public $job_id = null ; // da_job_id,
    public $title = null ; //  job_title
    public $tags = null ; // $tagArray
    public $job_type = null ; // job_type
    public $description = null ; // job_description,
    public $instruction = '' ; // not used
    public $is_cache = 0 ; // not used
    public $rating_as_freelancer = null ; // rating_by_customer
    public $rating_as_customer = null ; // rating_by_freelancer,
    public $translate_from = '' ; // not used
    public $translate_to = '' ; // not used
    public $recent_ts = null ; // recent_ts
}

class FreelinguistSearchFromDB {

    /**
     * @param string $search_string
     * @return array
     * @param $page
     * @param $page_size
     */
    static public function search($search_string,$page,$page_size) {
        global $wpdb;

        $search_string = trim($search_string);
        $page = (int)$page;
        $page_size = (int)$page_size;
        $start_page = $page * $page_size;
        $limit_part = "LIMIT $start_page, $page_size";
        //output


        $ret = [
          'hits' => [
              'total'=> 0,
              'hits' => [] //each node is in key of _source
          ]  
        ];

        $many_parts =  preg_split('/\s+/', $search_string);
        $tag_names_as_array = [];
        foreach ($many_parts as $part_of_many) {
            if (!empty($part_of_many)) {
                $tag_names_as_array[] = "'".esc_sql($part_of_many)."'";
            }
        }

        $in_tags_string = 'no-match-here';
        if (!empty($tag_names_as_array)) {
            $in_tags_string = implode(',',$tag_names_as_array);
        }

        $search_clause = '';
        if ($search_string) {
            $escaped_search = esc_sql($search_string);
            $search_clause  = "MATCH(html_generated) AGAINST('$escaped_search' IN NATURAL LANGUAGE MODE) OR";
        }




        $crawling_sql = "
        
        -- driving inner
        (
        SELECT display.user_id, content_id,
        
          -- HUGE IF
        
          IF(display.user_id,
             UNIX_TIMESTAMP(IF(look.last_update,look.last_update,0)),
             IF (display.content_id,
                 UNIX_TIMESTAMP(IF(content.updated_at,content.updated_at,IF (content.created_at,content.created_at,0))),
                 0
             )
          ) as recent_ts
        
        
          -- END HUGE IF
        
        FROM wp_display_unit_user_content display
        LEFT JOIN wp_interest_tags wit ON display.tag_id = wit.ID
        LEFT JOIN wp_linguist_content  content ON content.id = display.content_id AND content.parent_content_id IS NULL AND content.user_id IS NOT NULL 
        LEFT JOIN wp_fl_user_data_lookup look on  look.user_id = display.user_id
        where
          (
            /*search_clause*/
            wit.tag_name IN (/*tag_names*/)
          )
        )
        UNION DISTINCT (
            SELECT  per.wp_user_id , per.job_id,
        
              -- HUGE IF
        
              IF(per.wp_user_id,
                 UNIX_TIMESTAMP(IF(look.last_update,look.last_update,0)),
                 IF (per.job_id,
                     UNIX_TIMESTAMP(IF(content.updated_at,content.updated_at,IF (content.created_at,content.created_at,0))),
                     0
                 )
              ) as recent_ts
        
        
            -- END HUGE IF
        
            FROM wp_homepage_interest_per_id per
              LEFT JOIN wp_homepage_interest i ON i.id = per.homepage_interest_id
              LEFT JOIN wp_interest_tags wit ON i.tag_id = wit.ID
              LEFT JOIN wp_linguist_content  content ON content.id = per.job_id AND content.parent_content_id IS NULL AND content.user_id IS NOT NULL
              LEFT JOIN wp_fl_user_data_lookup look on  look.user_id = per.wp_user_id
            where
              (
                /*search_clause*/
                wit.tag_name IN (/*tag_names*/)
              )
        )
        ORDER BY recent_ts DESC
        #LIMIT_PART

        ";



        $toddling_sql = str_replace('/*tag_names*/',$in_tags_string,$crawling_sql);

        $walking_sql = str_replace('/*search_clause*/',$search_clause,$toddling_sql);

        $sql_for_counting = "SELECT count(*) as da_count FROM ( $walking_sql  ) da_big_count";

        $sql_for_search = str_replace('#LIMIT_PART',$limit_part,$walking_sql);





        $count_results = $wpdb->get_results($sql_for_counting);
        $da_big_count = $count_results[0]->da_count;
        $ret['hits']['total'] = $da_big_count;

        $search_results = $wpdb->get_results($sql_for_search);

        $user_ids_found_in_page = [];
        $content_ids_found_in_page = [];

        foreach ($search_results as  $res) {

            $time = (int)$res->recent_ts;

            if ($res->user_id) {
                $user_ids_found_in_page[(int)$res->user_id ] = $time;
            }

            if ($res->content_id) {
                $content_ids_found_in_page[(int)$res->content_id ] = $time ;
            }
        }

        $user_ids_comma_delimited = '-9999';
        $content_ids_comma_delimited = '-9999';

        if (!empty($user_ids_found_in_page)) {
            $user_ids_comma_delimited = implode(',',array_keys($user_ids_found_in_page));
        }

        if (!empty($content_ids_found_in_page)) {
            $content_ids_comma_delimited = implode(',',array_keys($content_ids_found_in_page));
        }




        $sql_for_users =
            "SELECT
                      users.ID as da_job_id,
                      users.user_nicename  as job_title,
                      desc_meta.meta_value as job_description,
                      look.wp_capabilities,
                      look.rating_as_freelancer,
                      look.rating_as_customer,
                      (
                        SELECT
                          GROUP_CONCAT(intags.tag_name) as tag_ids
                        FROM  wp_tags_cache_job ijob
                          LEFT JOIN wp_interest_tags  intags ON intags.ID = ijob.tag_id
                        WHERE ijob.type = ".FreelinguistTags::USER_TAG_TYPE." AND ijob.job_id = users.ID
                      ) as tag_names,
                      UNIX_TIMESTAMP(if(look.last_update,look.last_update,users.user_registered)) as recent_ts,
                      IF( look.wp_capabilities = ".FreelinguistUserLookupDataHelpers::USER_LOOKUP_CAPABILITIES_BUYER.",
                            'customer','translator')      as job_type
                     
                    FROM wp_users as users
                      INNER JOIN wp_fl_user_data_lookup look on users.ID = look.user_id
                      LEFT JOIN wp_usermeta desc_meta ON desc_meta.user_id = users.ID and desc_meta.meta_key = 'description'
                    WHERE
                      users.ID in ($user_ids_comma_delimited)
                    
                  ";



        ///////////////////////////

        $sql_for_content =

            "SELECT
                  content.id as da_job_id,
                  content.content_title as job_title,
                  content.content_summary as job_description,
                  0 as wp_capabilities,
                  content.rating_by_freelancer,
                  content.rating_by_customer ,
                  (
                    SELECT
                      GROUP_CONCAT(intags.tag_name) as tag_ids
                    FROM  wp_tags_cache_job ijob
                      LEFT JOIN wp_interest_tags  intags ON intags.ID = ijob.tag_id
                    WHERE   ijob.type = ".FreelinguistTags::CONTENT_TAG_TYPE." AND
                            ijob.job_id = content.id
                  ) as tag_names,
                  UNIX_TIMESTAMP(if(content.updated_at,content.updated_at,content.created_at)) as recent_ts,
                  'content' as job_type
                FROM wp_linguist_content as content
                
                WHERE
                  content.parent_content_id IS NULL AND content.user_id IS NOT NULL
                  AND content.id in   ($content_ids_comma_delimited)
                  ";

            $sql_for_union_data_from_db = "
            (
                $sql_for_users
            )
            UNION
            (
                $sql_for_content
            )
            ORDER BY recent_ts DESC
            ";

//        will_send_to_error_log('examine',[
//
//            '$sql_for_union_data_from_db' => $sql_for_union_data_from_db
//        ]);


            $union_data_from_db  = $wpdb->get_results($sql_for_union_data_from_db);


            $union_data_in_es_format = [];
            foreach ($union_data_from_db as $udata) {
                 $shaken_not_stirred = FreelinguistESRecord::create_record_from_row($udata);
                //will_send_to_error_log('shaken',$shaken_not_stirred,true,true);

                 $stirred = (array) $shaken_not_stirred;
                $union_data_in_es_format[] = ['_source'=>$stirred];


            }

            $ret['hits']['hits'] = $union_data_in_es_format;
        $ret['hits']['total'] = (int)$ret['hits']['total'];
       // will_send_to_error_log('$ret',$ret,true,false);
            return $ret;


    }
}

/*
///////////////////////////////////////////////

$sql_for_project =
    "SELECT
                  wppost.ID as da_job_id,
                  look.job_title as job_title,
                  look.job_description as job_description,
                  (
                    SELECT
                      GROUP_CONCAT(intags.tag_name) as tag_ids
                      FROM  wp_tags_cache_job ijob
                      LEFT JOIN wp_interest_tags  intags ON intags.ID = ijob.tag_id
                      WHERE ijob.type = " . FreelinguistTags::PROJECT_TAG_TYPE . " AND
                            ijob.job_id = wppost.ID
                  ) as tag_names,
                  UNIX_TIMESTAMP(if(look.last_update,look.last_update,wppost.post_date_gmt)) as recent_ts,
                  'project' as job_type,
                  0 as rating_as_freelancer,
                  0 as rating_as_customer
                FROM wp_posts as wppost
                  INNER JOIN wp_fl_post_data_lookup look on wppost.ID = look.post_id
                WHERE
                  wppost.post_type = 'job' and
                  wppost.post_status = 'publish' and
                  look.fl_job_type = " . PostLookupDataHelpers::POST_DATA_JOB_TYPE_PROJECT . " and
                  look.hide_job = 0
                 ORDER BY wppost.ID
                  ";

//mappings for project
$params['body'][] = [
    'job_id' => (int)$job->da_job_id,
    'title' => $job->job_title,
    'tags' => $tagArray,
    'job_type' => $job->job_type,
    'description' => $job->job_description,
    'instruction' => '',
    'is_cache' => '0',
    'rating_as_freelancer' => $job->rating_as_freelancer,
    'rating_as_customer' => $job->rating_as_customer,
    'translate_from' => '',
    'translate_to' => '',
    'recent_ts' => (int)$job->recent_ts
];


///////////////////////////////////////////////

$project_sql =
    "SELECT
                  wppost.ID as da_job_id,
                  look.job_title as job_title,
                  look.job_description as job_description,
                  (
                    SELECT
                      GROUP_CONCAT(intags.tag_name) as tag_ids
                      FROM  wp_tags_cache_job ijob
                      LEFT JOIN wp_interest_tags  intags ON intags.ID = ijob.tag_id
                      WHERE ijob.type = " . FreelinguistTags::CONTEST_TAG_TYPE . " AND
                            ijob.job_id = wppost.ID
                  ) as tag_names,
                  UNIX_TIMESTAMP(if(look.last_update,look.last_update,wppost.post_date_gmt)) as recent_ts,
                  'contest' as job_type,
                  0 as rating_as_freelancer,
                  0 as rating_as_customer
                FROM wp_posts as wppost
                  INNER JOIN wp_fl_post_data_lookup look on wppost.ID = look.post_id
                WHERE
                  wppost.post_type = 'job' and
                  wppost.post_status = 'publish' and
                  look.fl_job_type = " . PostLookupDataHelpers::POST_DATA_JOB_TYPE_CONTEST . " and
                  look.hide_job = 0
                 ORDER BY wppost.ID
                  ";

//mappings for contest
$params['body'][] = [
    'job_id' => (int)$job->da_job_id,
    'title' => $job->job_title,
    'tags' => $tagArray,
    'job_type' => $job->job_type,
    'description' => $job->job_description,
    'instruction' => '',
    'is_cache' => '0',
    'rating_as_freelancer' => $job->rating_as_freelancer,
    'rating_as_customer' => $job->rating_as_customer,
    'translate_from' => '',
    'translate_to' => '',
    'recent_ts' => (int)$job->recent_ts
];

*/