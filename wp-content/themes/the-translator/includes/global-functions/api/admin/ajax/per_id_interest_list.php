<?php




//the interest id to select on is in the post: interest_id
add_action( 'wp_ajax_per_id_interest_list', 'per_id_interest_list' );

function per_id_interest_list(){
    /*
       * current-php-code 2021-Jan-11
       * ajax-endpoint  per_id_interest_create
       * input-sanitized : interest_id
       */


    if (!current_user_can('manage_options')) {
        exit;
    }
    global $wpdb;
    $interest_id = (int)FLInput::get('interest_id');
    $response = ['status'=> 0,
        'message'=> 'nothing done',
        'id_list'=>[],
        'action'=>'per_id_interest_list',
        'flag' => 0,
        'is_title_hidden' => 0
    ];
    try {
        if ($interest_id) {
            if ($interest_id) {
                $res = $wpdb->get_results("
                  SELECT  me.id,
                          homepage_interest_id,
                          wp_user_id,
                          job_id,
                          u.display_name,
                          content.content_title,
                          h.is_per_id ,
                          h.is_title_hidden
                    FROM wp_homepage_interest_per_id me
                    LEFT JOIN wp_users u on me.wp_user_id = u.ID
                    LEFT JOIN wp_linguist_content content on me.job_id = content.id AND user_id IS NOT NULL
                    LEFT JOIN wp_homepage_interest h ON h.id = me.homepage_interest_id
                    WHERE homepage_interest_id = $interest_id",
                    ARRAY_A);


                if ($wpdb->last_error) {
                    throw new RuntimeException("Error getting data for the homepage_interest row of $interest_id: " . $wpdb->last_error);
                }

                if ($res === false) {
                    throw new RuntimeException("Unknown error getting data for the homepage_interest row of $interest_id");
                }

                $nodes = [];
                foreach($res as $node){
                    $uname = $node['display_name'];
                    $content_title= $node['content_title'];
                    $da_type = 'unknown';
                    $da_title = '';
                    $job_id = $node['job_id'];
                    $user_id = $node['wp_user_id'];
                    $da_id = null;
                    if ($job_id) {
                        $da_type = 'content';
                        $da_title = $content_title;
                        $da_id = $job_id;
                    } elseif ($user_id) {
                        $da_type = 'freelancer_profile';
                        $da_title = $uname;
                        $da_id = $user_id;
                    }
                    $is_per_id = $node['is_per_id'];
                    $nodes[] = [
                        'id'=> $node['id'],
                        'da_id' => $da_id,
                        'type'=> $da_type,
                        'title' => $da_title,
                        'is_per_id' => $is_per_id
                    ];
                }


                $response['status'] = 1;
                $response['id_list'] = $nodes;
                if (empty($nodes)) {
                    $response['message'] = "found no data for home page interest of $interest_id ";
                } else {
                    $response['message'] = "found ".count($nodes)." data for home page interest of $interest_id ";
                }

                //get the flag
                $res = $wpdb->get_results("
                  SELECT  h.is_per_id ,h.is_title_hidden, h.title
                    FROM wp_homepage_interest h
                    WHERE h.id = $interest_id",
                    ARRAY_A);


                if ($wpdb->last_error) {
                    throw new RuntimeException("Error getting flag for the homepage_interest row of $interest_id: " . $wpdb->last_error);
                }

                if ($res === false) {
                    throw new RuntimeException("Unknown error getting flag for the homepage_interest row of $interest_id");
                }
                if (!empty($res)) {
                    $flag = intval($res[0]['is_per_id']);
                    $response['flag'] = $flag;

                    $is_title_hidden = intval($res[0]['is_title_hidden']);
                    $response['is_title_hidden'] = $is_title_hidden;

                    $interest_name = $res[0]['title'];
                    $response['interest_name'] = $interest_name;
                }

            }
        }
    } catch (Exception $e) {
        $response = ['status'=> 0, 'message'=> $e->getMessage(),'id_list'=>[],'action'=>'per_id_interest_list'];
    }


    echo wp_json_encode($response);
    exit;


}