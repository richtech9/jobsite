<?php

//the interest id to select on is in the post: interest_id
//the type is in the post: per_id_type
//the ids are in the post: ids
//return an array called id_list with node of [id,type,title] for the recently created or updated thing
add_action( 'wp_ajax_per_id_interest_create', 'per_id_interest_create' );


function per_id_interest_create(){
    /*
      * current-php-code 2021-Jan-11
      * ajax-endpoint  per_id_interest_create
      * input-sanitized : ids,interest_id per_id_type
      */


    if (!current_user_can('manage_options')) {
        exit;
    }
    global $wpdb;
    $interest_id = (int)FLInput::get('interest_id');
    $da_ids = FLInput::get('ids');
    $type_of_id = FLInput::get('per_id_type');


    $response = [
        'status'=> 0,
        'message'=> 'nothing done',
        'action'=>'per_id_interest_create',
        'log'=>[],
        'id_list' =>[]
    ];
    try {

        $multiple_ids_raw = [];
        $ids = [];



        if (!$interest_id) {
            throw new RuntimeException("Need to select an interest row first");
        }

        if (!empty($da_ids) ) {
            $multiple_ids_raw =  preg_split('/\s+|,/',$da_ids);
        }

        if ($type_of_id) {

            switch ($type_of_id) {
                case 'content': {
                    $type_of_id = 'content';
                    foreach ($multiple_ids_raw as $raw) {
                        //lookup by id on content table
                        $raw = trim($raw);
                        if (empty($raw)) {continue;}
                        $response['log'][] = "processing content id of: $raw";
                        $test_id =  intval($raw);
                        if ($test_id) {
                            $sql = "select id from wp_linguist_content where user_id IS NOT NULL AND id = $test_id;" ;
                            $response['log'][] = $sql;
                            $res = $wpdb->get_results($sql, ARRAY_A);

                            if ($wpdb->last_error) {
                                throw new RuntimeException("Error getting the content id from  in ($test_id) /$type_of_id : " . $wpdb->last_error);
                            }
                            if ($res === false) {
                                throw new RuntimeException("unknown error getting the content id in  ($test_id)");
                            }
                            if (!empty($res)) {
                                $ok_id = $res[0]['id'];
                                $ids[] = ['id'=> intval($ok_id), 'type'=>$type_of_id] ;
                            }
                        }

                    }
                    break;
                }
                case 'freelancer_profile': {
                    $type_of_id = 'freelancer_profile';
                    foreach ($multiple_ids_raw as $raw) {
                        $raw = trim($raw);
                        if (empty($raw)) {continue;}
                        $response['log'][] = "processing profile id of: $raw";
                        //can be id, name or nicename
                        if (is_numeric($raw)) {
                            //lookup by id

                            $test_id =  intval($raw);
                            $response['log'][] = "Numeric user : $test_id";
                            if ($test_id) {
                                $sql = "select id from wp_users where ID = $test_id;" ;
                                $response['log'][] = $sql;
                                $res = $wpdb->get_results($sql, ARRAY_A);

                                if ($wpdb->last_error) {
                                    throw new RuntimeException("Error getting the user id from  in ($test_id) /$type_of_id : " . $wpdb->last_error);
                                }
                                if ($res === false) {
                                    throw new RuntimeException("unknown error getting the user id in  ($test_id)");
                                }
                                if (!empty($res)) {
                                    $ok_id = $res[0]['id'];
                                    $ids[] = ['id'=> intval($ok_id), 'type'=>$type_of_id];
                                }
                            }
                        } else {
                            $response['log'][] = "String user : $raw";
                            //match on user_login or display_name or user_email
                            $escaped_name = esc_sql($raw);
                            $response['log'][] = "Escaped user : $escaped_name";
                            $sql = "select id from wp_users WHERE
                                user_login = '$escaped_name' OR 
                                display_name = '$escaped_name' OR
                                user_email = '$escaped_name' ;"
                            ;
                            $response['log'][] = $sql;
                            $res = $wpdb->get_results($sql, ARRAY_A);

                            if ($wpdb->last_error) {
                                throw new RuntimeException("Error getting the user id from  in ($escaped_name) /$type_of_id : " . $wpdb->last_error);
                            }
                            if ($res === false) {
                                throw new RuntimeException("unknown error getting the user id in  ($escaped_name)");
                            }
                            if (!empty($res)) {
                                $ok_id = $res[0]['id'];
                                $ids[] = ['id'=> intval($ok_id), 'type'=>$type_of_id];
                            }
                        }
                    }
                    break;
                }
                default: {
                    //do nothing, let it fall through
                }
            }
        }

        $response['log'][] = 'ids next';
        $response['log'][] = $ids;
        $last_ids = [];
        $user_ids = [];
        $content_ids = [];
        if (!empty($ids)) {
            foreach ($ids as $data_thing) {
                $data_id = $data_thing['id'];
                if (empty($data_id)) {continue;}
                $job_id = null;
                $user_id = null;
                if ($type_of_id === 'content') {
                    $response['log'][] = "adding content id [$data_id] to per_id table";
                    $job_id = $data_id;
                    $content_ids[] = $job_id;
                    $user_id = 'NULL';
                } elseif ($type_of_id === 'freelancer_profile') {
                    $response['log'][] = "adding profile id [$data_id] to per_id table";
                    $user_id = $data_id;
                    $user_ids[] = $user_id;
                    $job_id = 'NULL';
                } else {
                    continue;
                }
                $sql = /** @lang text */
                    "INSERT INTO wp_homepage_interest_per_id 
                        (homepage_interest_id,wp_user_id,job_id) 
                        VALUES ($interest_id,$user_id,$job_id)
                        ON DUPLICATE KEY UPDATE
                        wp_user_id = $user_id,
                        job_id = $job_id
                         ";
                $response['log'][] = $sql;

                $res = $wpdb->query($sql);

                if ($wpdb->last_error) {
                    throw new RuntimeException("Error when inserting the homepage_interest_per_id row of ($interest_id,$user_id,$job_id): " . $wpdb->last_error);
                }

                if ($res === false) {
                    throw new RuntimeException("Unknown error when insert the homepage_interest_per_id row of ($interest_id,$user_id,$job_id)");
                }
                $last_id = $wpdb->insert_id;
                $last_ids[] = $last_id;
                $node = ['id'=>$last_id,'type'=>$type_of_id,'title'=>null];

                $response['id_list'][] = [$node];
            }

            //code-notes generate (or regenerate) units for new things
            FreelinguistUnitGenerator::generate_units($response['log'],$user_ids,$content_ids);
            $response['status'] = 1;
            $response['message']= "inserted ids: ". implode(',',$last_ids);
        }
    } catch (Exception $e) {
        $response['status'] = 0;
        $response['message']= $e->getMessage();
    }


    echo wp_json_encode($response);
    exit;
}