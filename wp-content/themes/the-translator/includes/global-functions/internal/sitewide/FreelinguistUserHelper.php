<?php

class FreelinguistUserHelper extends FreelinguistDebugging {
    //inherited from debugging and need to be overwritten
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    /*
    * current-php-code 2020-Oct-05
    * internal-call
    * input-sanitized :
    */

    const META_KEY_NAME_TAX_FORM = '_signed_tax_form';


    /**
     * @param  int $user_id
     * @param string $relative_folder_path OUTREF
     * @return string
     */
    public static function get_user_directory_and_make_it_if_not_exists($user_id,&$relative_folder_path) {
        $wp_upload_dir = wp_upload_dir();
        $user_id = (int)$user_id;
        if (!$user_id) {throw new InvalidArgumentException("User id needs to be an integer");}
        $our_middle_path = UploadHandler::number_to_path_part_string($user_id, '/') . '/';
        //code-notes [file-paths]  changed the upload to use new path
        $relative_folder_path = '/userprofile/' . $our_middle_path;
        $path = $wp_upload_dir['basedir'] . $relative_folder_path;
        //code-notes because this does not use the upload handler to do the legwork most of the other ajax rely on, we need to create the directory
        if (!is_dir($path)) {
            $b_check = mkdir($path, 0755, true);
            if (!$b_check) {throw new RuntimeException("could not create the folder $path");}
        }

        $rem_path = $path;
        $path = realpath($path);
        if (!$path) {
            throw new RuntimeException("Could not make the path $rem_path");
        }
        return $path;
    }


    public static function update_elastic_index($user_id) {
        $user_id = (int)$user_id;

        global $wpdb;
        $sql = "
                SELECT
                  DISTINCT
                  users.ID,
                  users.ID as job_id,
                  users.user_nicename,
                  desc_meta.meta_value as job_description,
                  tagids,
                  tagsnames,
                  look.rating_as_customer,
                  look.rating_as_freelancer,
                  look.wp_capabilities,
                  look.user_hourly_rate as job_price
                
                FROM wp_users as users
                  LEFT JOIN  wp_fl_user_data_lookup look ON look.user_id = users.ID 
                  LEFT JOIN wp_usermeta desc_meta ON desc_meta.user_id = users.ID and desc_meta.meta_key = 'description'
                  CROSS JOIN (
                      SELECT GROUP_CONCAT(wpts.tag_id) as tagids,
                              GROUP_CONCAT(intags.tag_name) AS tagsnames
                      FROM wp_tags_cache_job wpts
                      LEFT JOIN wp_interest_tags  intags ON intags.ID = tag_id
                        WHERE
                        wpts.type = ".FreelinguistTags::USER_TAG_TYPE." and
                        wpts.job_id = $user_id
                    ) nu_tags
                WHERE
                  users.ID = $user_id;
                ";
        $da_user = $wpdb->get_results($sql)[0];
        will_log_on_wpdb_error($wpdb);

        $tagArray =[];

        $gettagstr = $da_user->tagsnames;
        if ($gettagstr != "") {
            $tagArray = explode(",", $gettagstr);
        }

        $job_type = 'translator';
        if ($da_user->wp_capabilities === FreelinguistUserLookupDataHelpers::USER_LOOKUP_CAPABILITIES_BUYER) {
            $job_type = 'customer';
        }

        //ELASTIC CONNECTION
        try {
            $node = [
                'index' => 'translator',
                'type' => 'freelinguist',
                'id' => $da_user->job_id,
                'body' => array(
                    'job_id' => $da_user->job_id,
                    'title' => $da_user->user_nicename,
                    'tags' => $tagArray,
                    'job_type' => $job_type,
                    'translate_from' => '',
                    'translate_to' => '',
                    'description' => $da_user->job_description,
                    'instruction' => '',
                    'is_cache' => '0',
                    'rating_as_freelancer' =>$da_user->rating_as_freelancer,
                    'rating_as_customer' => $da_user->rating_as_customer,
                    'price' => $da_user->job_price,
                    'recent_ts' => time()
                )
            ];

            $es = new FreelinguistElasticSearchHelper();
            $es->add_index($node);

        } catch (Exception $e) {
            will_send_to_error_log('error sending user to elastic search', $e->getMessage());
        }

    }
}

FreelinguistSizeImages::turn_on_debugging(FreelinguistUserHelper::LOG_WARNING);