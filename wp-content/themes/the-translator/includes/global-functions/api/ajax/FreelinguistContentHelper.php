<?php

//code-notes Now calling the function through a static method
use will_codes\JsonHelper;

add_action('wp_ajax_add_linguist_content', ['FreelinguistContentHelper','add_linguist_content']);

class FreelinguistContentHelper extends  FreelinguistDebugging
{
    //each inherited debugging needs their own controls
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const UPLOAD_SUBDIRECTORY = 'linguistcontent';

    const ID_OFFSET = 0;
    const VALID_SALE_TYPES = [
        'Fixed','Free','Offer'
    ];

    public static function decode_id($encoded_id) {
        if (empty($encoded_id)) {return 0;}
        if (!static::is_digit($encoded_id)) {
            //see if its base encoded
            $maybe = base64_decode($encoded_id) ;
            if ($maybe === false) {
                will_send_to_error_log("encoded id is not numberic or base 64",$encoded_id);
                return 0;
            }
            $maybe_int = (int) $maybe;
            if (!$maybe_int) {
                will_send_to_error_log("base 64 encoded id is not numberic",[$encoded_id,$maybe]);
                return 0;
            }
            $a_number =  $maybe_int;
        } else {
            $a_number = (int)$encoded_id;
        }

        $actual_id = $a_number - static::ID_OFFSET;
        return $actual_id;
    }

    public static function encode_id($actual_id) {
        if (empty($actual_id)) {return '';}
        if (!static::is_digit($actual_id)) {
            throw new InvalidArgumentException("not a numeric ID: " . $actual_id);
        } else {
            $a_number = (int)$actual_id;
            if (!$a_number) {return '';}
            $encoded_id = $a_number + static::ID_OFFSET;
            return $encoded_id;
        }
    }

    protected static function is_digit($digit) {
        if(is_int($digit)) {
            return true;
        } elseif(is_string($digit)) {
                return ctype_digit($digit);
        } else {
            // booleans, floats and others
            return false;
        }
    }
    /**
     * @param int $content_id
     * @param string[] $new_field_values , each key is db_column, each value is what to set on the new copy
     * @param array $log
     * @return int
     * @throws
     */
    public static function copy_content($content_id, $new_field_values = [], &$log = [])
    {
        /*
          * current-php-code 2020-Oct-14
          * internal-call
          * input-sanitized :
         */
        /*
         * Logic:
         * Get the content details to copy
         *  If this content id has a parent_content_id then throw exception, as this is a copy
         *  We only want to copy from master copies
         *
         *  Overwrite the read property values with the new field values
         *  Add this content_id as the parent_content_id to the data
         *  save as new row
         *  Get the new content_id
         *  For each wp_linguist_content_chapter read, and write as new row with the linguist_content_id set to new content id
         *
         *  for each wp_content_files, copy the file physically with adding the new content id and unix time
         *    and make a new row with the new content_id
         *
         *  return the new content_id
         */


        global $wpdb;
        if (empty($log)) {
            $log = [];
        }
        $content_id = (int)$content_id;
        $sql = "SELECT * from wp_linguist_content WHERE user_id IS NOT NULL AND  id = $content_id";
        $da_content_results = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb);
        if (empty($da_content_results)) {
            throw new InvalidArgumentException("Could not find a content of id [$content_id] to copy");
        }

        $da_content = $da_content_results[0];
        if ($da_content->parent_content_id) {
            throw new InvalidArgumentException("Content to copy id [$content_id] is not an original content");
        }

        $new_uns = [];

        $we_do_not_want_these_keys = [
            'id',
            'updated_at',
            'purchased_by',
            'rating_by_customer',
            'rating_by_freelancer',
            'comments_by_customer',
            'comments_by_freelancer',
            'score',
            'max_to_be_sold',
            'chat_room_id'

        ];

        $new_field_values['created_at'] = current_time('Y-m-d H:i:s', $gmt = 1);
        //overwritten below on purpose as value cannot be null

        foreach ($da_content as $da_key => $da_value) {
            if (in_array($da_key, $we_do_not_want_these_keys)) {
                continue;
            }

            if ($da_key === 'content_cover_image') {
                if (empty($da_value)) {
                    continue;
                }
                $new_uns[$da_key] = static::copy_dat_file($da_value,
                    "cp-f-$content_id", "ts-" . time());
                $wp_upload_dir = wp_upload_dir();

                //code-notes need to get full path to the just copied content cover and then make different sizes for it
                $full_path_to_new_content_cover = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR.$new_uns[$da_key];
                $sizer = new FreelinguistSizeImages( $full_path_to_new_content_cover);
                will_do_nothing($sizer);
            } else {
                if (array_key_exists($da_key, $new_field_values)) {
                    $new_uns[$da_key] = $new_field_values[$da_key];
                } else {
                    $new_uns[$da_key] = $da_value;
                }
            }

        }
        $new_uns['parent_content_id'] = $da_content->id;
        $wpdb->insert('wp_linguist_content', $new_uns);
        will_throw_on_wpdb_error($wpdb);
        $new_content_id = $wpdb->insert_id;
        $sql_to_set_time = "UPDATE wp_linguist_content SET created_at = NOW() WHERE id = $new_content_id";
        $wpdb->query($sql_to_set_time);
        will_throw_on_wpdb_error($wpdb);

        $sql = "SELECT * FROM wp_linguist_content_chapter WHERE  user_id IS NOT NULL AND linguist_content_id = $content_id";
        $da_old_chapter_results = $wpdb->get_results($sql, ARRAY_A);
        will_throw_on_wpdb_error($wpdb);
        foreach ($da_old_chapter_results as $da_old_chapter) {
            $da_old_chapter['linguist_content_id'] = $new_content_id;

            $da_old_chapter['created_at'] = current_time('Y-m-d H:i:s', $gmt = 1);
            //overwritten below on purpose as value cannot be null

            $da_old_chapter['updated_at'] = current_time('Y-m-d H:i:s', $gmt = 1);
            //overwritten below on purpose as value cannot be null

            unset($da_old_chapter['id']);
            $wpdb->insert('wp_linguist_content_chapter', $da_old_chapter);
            $new_chapter_id = will_get_last_id($wpdb,'wp_linguist_content_chapter');
            $sql_to_set_time = "UPDATE wp_linguist_content_chapter SET created_at = NOW() , updated_at = NOW() WHERE id = $new_chapter_id";
            $wpdb->query($sql_to_set_time);
            will_throw_on_wpdb_error($wpdb);
        }

        $sql = "SELECT * FROM wp_content_files WHERE content_id = $content_id";
        $da_old_file_results = $wpdb->get_results($sql, ARRAY_A);
        will_throw_on_wpdb_error($wpdb);
        foreach ($da_old_file_results as $da_old_file) {
            $file_path = $da_old_file['file_path'];
            if (empty($file_path)) {
                continue;
            }

            $da_old_file['content_id'] = $new_content_id;
            $da_old_file['file_path'] = static::copy_dat_file($da_old_file['file_path'],
                "cp-f-$content_id", "t-$new_content_id");
            unset($da_old_file['id']);
            $wpdb->insert('wp_content_files', $da_old_file);
        }

        //code-notes copy over tags
        $sql_for_tags = "INSERT INTO wp_tags_cache_job ( tag_id, job_id, type, test_flag)
                          SELECT 
                          tags.tag_id,
                           $new_content_id as job_id,
                           ".FreelinguistTags::CONTENT_TAG_TYPE. " as type,
                           0 as test_flag
                           FROM wp_tags_cache_job tags 
                           WHERE tags.type = ".FreelinguistTags::CONTENT_TAG_TYPE. " AND tags.job_id = $content_id";

        $wpdb->query($sql_for_tags);
        will_throw_on_wpdb_error($wpdb,'copying tags');

        return $new_content_id;

    }

    /**
     * Will copy a file with the same name but with old tag and new tag appended to the name before the extension
     * Depends on the partial file being based on the uploads directory for WP
     *
     * returns the partial path of the new file, relative to the uploads directory
     * @param string $partial_file_path
     * @param string $old_tag
     * @param string $new_tag
     * @return string
     */
    protected static function copy_dat_file($partial_file_path, $old_tag, $new_tag)
    {
        /*
          * current-php-code 2020-Oct-14
          * internal-call
          * input-sanitized :
         */

        require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
        $my_filesystem = new WP_Filesystem_Direct(array());
        if (empty($partial_file_path)) {
            return '';
        }

        //trim off the possible left hand side /
        $partial_file_path = ltrim($partial_file_path, '/ ');
        $partial_path_info = pathinfo($partial_file_path);
        $partial_dir = $partial_path_info['dirname'];

        //check to make sure the file exists
        $test_old_path_before_real = ABSPATH . 'wp-content/uploads/' . $partial_file_path;
        $full_old_file_path = realpath($test_old_path_before_real);
        if (!$full_old_file_path) {
            throw new RuntimeException("Could not get real filepath for " . $test_old_path_before_real);
        }
        if (!is_readable($full_old_file_path)) {
            throw new RuntimeException("Can not copy file, not readable " . $test_old_path_before_real);
        }

        $path_parts = pathinfo($full_old_file_path);
        $base_name_without_extension = $path_parts['filename'];
        $nu_file_name = $base_name_without_extension . '-' . $old_tag . '-' . $new_tag;
        $ext = '';
        if (array_key_exists('extension', $path_parts) && $path_parts['extension']) {
            $ext = '.' . $path_parts['extension'];
        }
        $nu_file_name_with_extension = $nu_file_name . $ext;
        $new_file_path = $path_parts['dirname'] . '/' . $nu_file_name_with_extension;

        //get the base name without the extension

        //add our unique things to the end of the base name and put back on the extension

        //copy!

        $my_filesystem->copy($full_old_file_path, $new_file_path, false, false);
        return $partial_dir . '/' . $nu_file_name_with_extension;
    } //end copy_dat_file

    public static function add_linguist_content()
    {
        global $wpdb;

        //code-bookmark called to add the new content or to update content
        /*
        * current-php-code 2020-Oct-13
        * ajax-endpoint  add_linguist_content
        * input-sanitized : already_ins,chapters_id,content_amount,content_id,content_sale_type,content_summary,
        * input-sanitized : content_title,content_type,hidden-project_tags,max_to_be_sold,public_file_name,publish_type,
        * input-sanitized :  sub_content,sub_title,sub_title_id,visible_after_buy
        */

        try {
            //FLInput::turn_on_debugging();
            $content_type_raw = FLInput::get('content_type');
            $string_tags_comma_delimited = FLInput::get('hidden-project_tags');
            //$chapters_ids = FLInput::get('chapters_id');
            $content_amount = floatval(FLInput::get('content_amount', 0.0));
            $content_id_encoded = FLInput::get('content_id', 0);
            $content_sale_type_raw = FLInput::get('content_sale_type');

            $content_summary_raw = FLInput::get('content_summary', '', FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

            $content_title_raw = FLInput::get('content_title', '', FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

            $max_to_be_sold = (int)FLInput::get('max_to_be_sold');
            $publish_type_command_raw = FLInput::get('publish_type');//code-notes will be save_content and next_status_for_content
            switch ($publish_type_command_raw) {
                case 'save_content':
                case 'next_status_for_content':
                    $publish_type_command = $publish_type_command_raw;
                    break;
                default:
                    $publish_type_command = 'save_content';
            }

            $sub_content = FLInput::get('sub_content', [], FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

            $sub_title = FLInput::get('sub_title', [], FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

            $sub_page_number = FLInput::get('sub_page_number', []);

            $sub_title_id = FLInput::get('sub_title_id', []);
            $visible_after_buy = FLInput::get('visible_after_buy', []);

            $c_content_id = FreelinguistContentHelper::decode_id($content_id_encoded);

            $c_content_detail = null;
            $old_publish_type = '';
            if ($c_content_id) {
                $c_content_detail = $wpdb->get_row("select * from wp_linguist_content where  user_id IS NOT NULL AND id=$c_content_id", ARRAY_A);
                if (empty($c_content_detail)) {
                    throw new InvalidArgumentException("Cannot find content of : $c_content_id");
                }
                $old_publish_type = $c_content_detail['publish_type'];
                switch ($publish_type_command) {
                    case 'save_content': {
                        $publish_type = $c_content_detail['publish_type'];
                        break;
                    }
                    case 'next_status_for_content': {
                        if ( 'publish' === strtolower($c_content_detail['publish_type'])) {
                            $publish_type = 'Pending';
                        } elseif ( 'pending' === strtolower($c_content_detail['publish_type'])) {
                            $publish_type = 'Publish';
                        } else {
                            $publish_type = $c_content_detail['publish_type'];
                            if (empty($publish_type)) {$publish_type = 'Pending';} //default to pending if bug unsets this column
                        }
                        break;
                    }

                    default:
                        throw new LogicException("Should never have other than next_status_for_content|save_content");
                }
            } else {
                //new content, if save then pending, else publish it
                switch ($publish_type_command) {
                    case 'save_content': {
                        $publish_type =  'Pending';
                        break;
                    }
                    case 'next_status_for_content': {
                        $publish_type =  'Publish';
                        break;
                    }

                    default:
                        throw new LogicException("Should never have other than next_status_for_content|save_content");
                }
            }

            if ($c_content_detail && empty($content_sale_type_raw)) {
                if ($c_content_detail['purchased_by']) {
                    $content_sale_type_raw = $c_content_detail['content_sale_type'];
                }
            }
            if (!in_array($content_sale_type_raw, static::VALID_SALE_TYPES)) {

                throw new InvalidArgumentException("Sale type '$content_sale_type_raw' not one of ".
                    implode('|',static::VALID_SALE_TYPES));
            }
            if ($content_sale_type_raw === 'Offer') {
                $max_to_be_sold = 1;
            }

            if (get_current_user_id()) {

                $return = array();


                will_log_on_wpdb_error($wpdb, 'statement A @ ' . __LINE__);
                if (!empty($c_content_detail) && $c_content_detail['user_id'] != get_current_user_id()) {

                    $return['message'] = get_custom_string_return('Unauthorized');

                    $return['status'] = false;

                    $return['scrollToElement'] = true;

                    wp_send_json($return);
                    exit; //exists in wp_send_json but mark it here for humans

                }

                $return['status'] = true;

                $current_user = wp_get_current_user();

                $current_user_id = $current_user->data->ID;


                $content_summary = removePersonalInfo($content_summary_raw);

                $content_sale_type = $content_sale_type_raw;

                $content_title = removePersonalInfo($content_title_raw);


                $content_type = '';
                if ($content_type_raw) {
                    $content_type = removePersonalInfo($content_type_raw);
                }

                if (!empty($content_title)) {


                    /**************************************content_cover_image ****************************************/
                    //code-notes where cover image upload used to be



                    /***************************************data Submit ***********************************************************/

                    if ($return['status'] == true) {


                        $content_id =  FreelinguistContentHelper::decode_id($content_id_encoded);

                        $content_detail = null;
                        if ($content_id) {
                            $content_detail = $wpdb->get_row("select * from wp_linguist_content where  user_id IS NOT NULL AND id=$content_id", ARRAY_A);
                        }

                        will_log_on_wpdb_error($wpdb, 'statement B @ ' . __LINE__);
                        $imgName = '';//code-notes no more text snapshots
                        if (count($visible_after_buy)) {
                            $where_array = [];
                            foreach ($visible_after_buy as $dat_id) {
                                $where_array[] = (int)$dat_id;
                            }
                            $where_in_clause = implode(',', $where_array);
                            $wpdb->query("UPDATE wp_linguist_content_chapter SET content_visible='' WHERE id in ($where_in_clause)");
                            will_throw_on_wpdb_error($wpdb);
                        }
                        if (count($visible_after_buy)) {
                            $after_buy = $visible_after_buy;

                            foreach ($after_buy as $key => $value) {
                                $wpdb->update(

                                    $wpdb->prefix . 'linguist_content_chapter',
                                    array('content_visible' => $value),
                                    array('id' => $key)
                                );
                            }
                        }
                        if (empty($content_detail)) {

                            /*********************** Add Data ***********************************************************************/
                            if ($max_to_be_sold < 1) { $max_to_be_sold = 1;}
                            $linguist_contentdata = array(

                                'max_to_be_sold' => $max_to_be_sold,
                                'content_amount' => $content_amount, // to which post the comment will show up

                                'content_sale_type' => $content_sale_type, 

                                'content_summary' => $content_summary, 

                                'content_title' => $content_title, 

                                'publish_type' => $publish_type, 

                                'content_type' => $content_type, 
                                'user_id' => $current_user_id, //passing current user ID or any predefined as per the demand


                                'description_image' => $imgName,

                                'updated_at' =>current_time('Y-m-d H:i:s', $gmt = 1),
                            //overwritten below on purpose as value cannot be null

                                'created_at' => current_time('Y-m-d H:i:s', $gmt = 1),

                            );


                            $wpdb->insert( 'wp_linguist_content', $linguist_contentdata);
                            will_throw_on_wpdb_error($wpdb,'trying to insert content');
                            $content_id = $wpdb->insert_id;

                            //do conver image, and if not valid, delete this and return an error
                            $cover_status = static::add_or_replace_cover_image($content_id);
                            if (!$cover_status) {
                                $sql_to_delete = "DELETE FROM wp_linguist_content WHERE id = $content_id";
                                $wpdb->query($sql_to_delete);
                                will_throw_on_wpdb_error($wpdb,'trying to delete content after failed content file upload');
                            }

                            $sql_to_set_time = "UPDATE wp_linguist_content SET created_at = NOW() , updated_at = NOW() WHERE id = $content_id";
                            $wpdb->query($sql_to_set_time);
                            will_throw_on_wpdb_error($wpdb,'trying to update content times');


                        } else { //updating

                                static::add_or_replace_cover_image($content_id);
                                //do not change the current cover if the upload fails
                                $arr = array(

                                    'max_to_be_sold' => $max_to_be_sold,

                                    'content_amount' => $content_amount, // to which post the comment will show up

                                    'content_sale_type' => $content_sale_type, 

                                    'content_summary' => $content_summary, 

                                    'description_image' => $imgName,

                                    'content_title' => $content_title, 

                                    //'content_cover_image'   => $content_cover_image,

                                    'publish_type' => $publish_type, 

                                    'content_type' => $content_type, 
                                    'user_id' => $current_user_id, //passing current user ID or any predefined as per the demand

                                );



                                $wpdb->update(

                                    $wpdb->prefix . 'linguist_content',

                                    $arr,

                                    array('id' => $content_id),

                                    array(
                                        '%d', '%f', '%s', '%s','%s', '%s','%s', '%s', '%s', '%s'
                                    ),

                                    array('%d')

                                );




                        }//end if else !empty already existing content


                        $extended_content_info = (object)static::get_content_extended_information($content_id);


                        //code-notes only broadcast content if publish type is publish
                        if (
                            ($publish_type === 'Publish') &&
                            ($extended_content_info->line_type === 'parent') &&
                            (strtolower($publish_type) !== strtolower($old_publish_type))
                        ) {
//                            will_send_to_error_log("going to announce for $content_id",[
//                                '$publish_type'=> $publish_type,
//                                '$old_publish_type' => $old_publish_type
//                            ]);
                            //code-notes [image-sizing]  content add a tiny image to the announcement
                            $tiny_cover_url = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
                                $extended_content_info->content_cover_image,
                                FreelinguistSizeImages::TINY,
                                false);

                            $url = freeling_links('content_detail_url') . '&mode=view&content_id=' . FreelinguistContentHelper::encode_id($content_id);
                            $msg = '<strong><u>' . Ucfirst($content_title) . '</u></strong><br><br>' .
                                '<em>' . $content_summary . '</em>' .
                                '<p>Budget: $' . $content_amount . '</p> ' .
                                '<img class="pull-left "  src="' . $tiny_cover_url . '" >'.
                                '<a class="pull-right btn-success btn-sm fl-view-link" target="_blank" href="' . $url . '" >View</a>';

                            //code-notes send new linguist content announcement via async task, if it is already created but is being published
                            as_enqueue_async_action('freelinguist_broadcast_admin_ejabber', [$msg, 'Content']);

                        }
//                        else {
//                            will_send_to_error_log("NOT going to announce for $content_id",[
//                                '$publish_type'=> $publish_type,
//                                '$old_publish_type' => $old_publish_type
//                            ]);
//                        }



                        //TAGS PROCESS
                        $tagIdArray = array();
                        $tagArray = [];
                        if ($string_tags_comma_delimited) {
                            $tagArray = explode(',', $string_tags_comma_delimited);
                            foreach ($tagArray as $tag) {
                                if ($tag) {
                                    $sql_statment =
                                        "SELECT id FROM wp_interest_tags WHERE tag_name='" . $tag . "'";
                                    $haveTag = $wpdb->get_row($sql_statment, ARRAY_A);
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

                        if ($tagIdArray) {
                            foreach ($tagIdArray as $tagIds) {
                                $sql_statment =
                                    "SELECT * FROM wp_tags_cache_job WHERE job_id=$content_id AND tag_id=$tagIds AND type= " . FreelinguistTags::CONTENT_TAG_TYPE;
                                $jobCache = $wpdb->get_row($sql_statment, ARRAY_A);
                                if (empty($jobCache)) {
                                    $wpdb->insert( 'wp_tags_cache_job', array('job_id' => $content_id, 'tag_id' => $tagIds, 'type' => FreelinguistTags::CONTENT_TAG_TYPE));
                                }
                            }


                            $sql_statement =
                                "SELECT  tag_id FROM wp_tags_cache_job 
                            WHERE job_id=$content_id AND type= " . FreelinguistTags::CONTENT_TAG_TYPE;

                            $jobCacheActiveJob = $wpdb->get_results($sql_statement, ARRAY_A);
                            $jobCacheActiveJob = array_column($jobCacheActiveJob, 'tag_id');
                            $deleteTag = array_diff($jobCacheActiveJob, $tagIdArray);
                            if ($deleteTag) {
                                $deleteTagIn = implode(",", $deleteTag);
                                $sql_statement =
                                    "DELETE FROM wp_tags_cache_job 
                                WHERE tag_id IN($deleteTagIn) AND job_id=$content_id 
                                AND type=" . FreelinguistTags::CONTENT_TAG_TYPE;
                                $wpdb->query($sql_statement);
                            }
                        }


                        //code-notes adding in the public file name for the new content file row


                        $sql_for_existing_chapter_ids = "SELECT chapter.id as chapter_id 
                                                      FROM wp_linguist_content_chapter chapter 
                                                      WHERE  user_id IS NOT NULL AND chapter.linguist_content_id = $content_id";
                        $result_for_existing_chapter_ids = $wpdb->get_results($sql_for_existing_chapter_ids);
                        $existing_chapter_ids = [];
                        foreach ($result_for_existing_chapter_ids as $existing_chapter_id) {
                            $chapter_id_as_int = (int)$existing_chapter_id->chapter_id;
                            $existing_chapter_ids[$chapter_id_as_int] = $chapter_id_as_int;
                        }
                        if (!empty($sub_title)) {

                            for ($i = 0; $i < count($sub_title); $i++) {

                               // $n_sub_title = removePersonalInfo($sub_title[$i]);
                                $n_sub_title = $sub_title[$i];//task-future-work no filters on chapter titles right now; disabled filter as it did not like chapter numbers


                                $n_sub_title_id = (int)$sub_title_id[$i];
                                if ($n_sub_title_id && !isset($existing_chapter_ids[$n_sub_title_id])) {
                                    will_send_to_error_log("Chapter id is not part of the existing ones",
                                        ['$n_sub_title_id' => $n_sub_title_id, '$existing_chapter_ids' => $existing_chapter_ids]);
                                    continue;
                                }

                                $n_sub_page_number = (int)$sub_page_number[$i];
                                if (empty($n_sub_page_number)) {
                                    $n_sub_page_number = $i +1;
                                }
                                static::log(static::LOG_DEBUG,' content loop page number ',$n_sub_page_number);

                                $n_content_pre_filtered = $sub_content[$i];

                                static::log(static::LOG_DEBUG,' content chapter before insert',$n_content_pre_filtered);

                                $content_html = JsonHelper::html_from_bb_code($n_content_pre_filtered);;
                                $content_bb_code = $n_content_pre_filtered; //task-future-work validate bb code in case people send in raw bb code later


                                if (trim($n_sub_title) != '') {

                                    $content_chapter_detail = [];
                                    if ($n_sub_title_id) {
                                        $sql_statment =
                                            "select id from wp_linguist_content_chapter where  user_id IS NOT NULL AND  id=$n_sub_title_id";
                                        $content_chapter_detail = $wpdb->get_row($sql_statment, ARRAY_A);
                                    }
                                    will_log_on_wpdb_error($wpdb, 'statement F @ ' . __LINE__);

                                    if (empty($content_chapter_detail)) {

                                        $linguist_content_chapterdata = array(

                                            'linguist_content_id' => $content_id, // to which post the comment will show up

                                            'page_number' => $n_sub_page_number,

                                            'title' => $n_sub_title, 

                                            'user_id' => $current_user_id, //passing current user ID or any predefined as per the demand

                                            'content_html' => $content_html,

                                            'content_bb_code' => $content_bb_code,

                                            'updated_at' =>  current_time('Y-m-d H:i:s', $gmt = 1), //overwritten

                                            'created_at' => current_time('Y-m-d H:i:s', $gmt = 1) //overwritten

                                        );

                                        $wpdb->insert( 'wp_linguist_content_chapter', $linguist_content_chapterdata);

                                        $da_last_id = will_get_last_id($wpdb,'linguist_content_chapter');
                                        $sql_to_set_time = "UPDATE wp_linguist_content_chapter 
                                                            SET created_at = NOW() , updated_at = NOW() WHERE id = $da_last_id";
                                        $wpdb->query($sql_to_set_time);
                                        will_throw_on_wpdb_error($wpdb);


                                    } else {

                                        //code-notes update time here, rest below so don't change sql escaping
                                        $sql_to_update = "UPDATE wp_linguist_content_chapter SET
                                                         updated_at = NOW(),
                                                         page_number = $n_sub_page_number
                                                        WHERE id = $n_sub_title_id
                                                        ";

                                        $wpdb->query($sql_to_update);
                                        will_throw_on_wpdb_error($wpdb);
                                        $wpdb->update(

                                             'wp_linguist_content_chapter',

                                            array(

                                                'title' => $n_sub_title, 

                                                'content_html' => $content_html,

                                                'content_bb_code' => $content_bb_code,

                                            ),

                                            array('id' => $n_sub_title_id),

                                            array(

                                                '%s',

                                                '%s',

                                            ),

                                            array('%d')

                                        );

                                        will_throw_on_wpdb_error($wpdb);

                                    }

                                }

                            }

                        }

                        //do not index sold copies that are being updated

                        if ($extended_content_info->line_type === 'parent') {

                            //get current rating, if any
                            $sql_to_get_rating = "
                        SELECT rating_by_freelancer,rating_by_customer
                        FROM wp_linguist_content
                        WHERE  user_id IS NOT NULL AND id = $content_id
                        ";

                            $rating_res = $wpdb->get_results($sql_to_get_rating);
                            $rating_by_freelancer = 0;
                            $rating_by_customer = 0;
                            if (count($rating_res)) {
                                $rating_by_freelancer = $rating_res[0]->rating_by_freelancer;
                                $rating_by_customer = $rating_res[0]->rating_by_customer;
                            }

                            try {
                                $es = new FreelinguistElasticSearchHelper();
                                $es->add_index([
                                    'index' => 'content',
                                    'type' => 'freelinguist',
                                    'id' => $content_id,
                                    'body' => array(
                                        'job_id' => $content_id,
                                        'title' => $content_title,
                                        'job_type' => 'content',
                                        'tags' => $tagArray,
                                        'translate_from' => '',
                                        'translate_to' => '',
                                        'description' => $content_summary,
                                        'is_cache' => '0',
                                        'rating_as_freelancer' => $rating_by_customer, //switch : rating by crosses rating as
                                        'rating_as_customer' => $rating_by_freelancer,
                                        'price' => (int)0,
                                        'recent_ts' => time()
                                    )
                                ]);

                            } catch (Exception $e) {
                                will_send_to_error_log('error sending content to elastic search', $e->getMessage());
                            }
                        }
                        $return['message'] = get_custom_string_return('Success in submission');

                        //code-notes update content, updates existing per and top only
                        FreelinguistUnitGenerator::generate_units($log,[],[$content_id]);

                        $return['status'] = true;

                    }

                } else {

                    $return['message'] = get_custom_string_return('Please enter title');

                    $return['status'] = false;

                }

            } else {

                $return['message'] = get_custom_string_return('Please login/register first');

                $return['status'] = false;

            }



            $return['scrollToElement'] = true;

            wp_send_json($return);
            exit; //exists in wp_send_json but mark it here for humans
        } catch (Exception $e) {
            will_send_to_error_log('Error on add_linguist_content',will_get_exception_string($e));
            wp_send_json(['status'=>false,'message'=>$e->getMessage()]);
            exit;
        }

    }

    /**
     * @param $ref_content_id
     * @param bool $b_get_tag_ids
     * @return array
     */
    public static function get_content_extended_information($ref_content_id,$b_get_tag_ids = false) {
        /*
         * current-php-code 2020-Oct-13
         * internal-call
         * input-sanitized :
        */
        global $wpdb;
        $ref_content_id = (int)$ref_content_id;
        $sql =
            "SELECT 
                    line.*,
                    if(sold_summary.da_count,sold_summary.da_count,0) as number_copies_sold,
                    IF(line.parent_content_id,'child','parent') as line_type,
                    UNIX_TIMESTAMP(updated_at) as updated_at_ts,
                    UNIX_TIMESTAMP(requested_completion_at) as  requested_completion_ts ,
                    freelancer_user.user_email as freelancer_email,
                    customer_user.user_email as customer_email
                    
                    FROM wp_linguist_content line
                    
                      LEFT JOIN (
                                  SELECT count(*) as da_count, parent_content_id
                                  FROM wp_linguist_content
                                  WHERE parent_content_id IS NOT NULL
                                        AND purchased_by IS NOT NULL
                                        AND parent_content_id = $ref_content_id
                                  GROUP BY parent_content_id
                                ) as sold_summary ON sold_summary.parent_content_id  = line.id
                                
                        LEFT JOIN wp_users freelancer_user ON freelancer_user.ID = line.user_id
                        LEFT JOIN wp_users customer_user ON customer_user.ID = line.purchased_by
                        
                    where  line.user_id IS NOT NULL AND line.id = $ref_content_id
                    
                    ORDER BY
                      CASE line_type
                      WHEN 'child' THEN line.parent_content_id
                      WHEN 'parent' THEN line.id
                      ELSE 1 END
                    
                    DESC,
                    
                    
                      CASE line_type
                      WHEN 'child' THEN line.id
                      WHEN 'parent' THEN 99999999
                      ELSE 1 END
                    
                    DESC;
                      
                  ";


        $row = $wpdb->get_row($sql, ARRAY_A);
        static::log(static::LOG_DEBUG,'sql for extended content information',$wpdb->last_query);
        will_throw_on_wpdb_error($wpdb);
        if (empty($row)) {
            throw new RuntimeException("Could not find original content using content id of [$ref_content_id]");
        }

        $row['ref_content_id'] = $ref_content_id;
        $row['user_id'] = intval($row['user_id']);
        $row['purchased_by'] = intval($row['purchased_by'])?intval($row['purchased_by']): null;
        $row['number_copies_sold'] = intval($row['number_copies_sold']);
        $row['updated_at_ts'] = intval($row['updated_at_ts']);
        $row['requested_completion_ts'] = intval($row['requested_completion_ts']);
        //code-notes if this is sold, but does not have a parent, increment number_copies_sold by one
        if (!$row['parent_content_id'] && $row['purchased_by']) {
            $row['number_copies_sold'] ++;
        }


        if ($b_get_tag_ids) {

            if ($row['parent_content_id']) {
                $content_id_for_tags = $row['parent_content_id'];
            } else {
                $content_id_for_tags = $ref_content_id;
            }
            $sql_thing =
                "SELECT tag_id  FROM  wp_tags_cache_job 
                  WHERE job_id= $content_id_for_tags AND type = " . FreelinguistTags::CONTENT_TAG_TYPE;

            $tag_array = $wpdb->get_results($sql_thing);

            $tag_ids = [];
            foreach ($tag_array as $ta) {
                $tag_id = (int)$ta->tag_id;
                $tag_ids[] = $tag_id;
            }
            $row['tag_ids'] = $tag_ids;

        }
        return $row;
    }

    public static function hz_buy_content_ajxcback_cb(){
        /*
        * current-php-code 2020-Oct-14
        * internal-call
        * input-sanitized : content_id,lang
       */


        $ref_content_id = (int)FLInput::get('content_id');
        $lang             = FLInput::get('lang','en');
        global $wpdb;

        try {
            //code-bookmark php function for finalizing the purchase, but this is the part that deducts from the wallet


            $customer_id = get_current_user_id();

            $balance = get_user_meta($customer_id, 'total_user_balance', true);
            if (!$balance) {
                $balance = 0.0;
            }

            $row = static::get_content_extended_information($ref_content_id);
            $amount = floatval($row['content_amount']);
            $fees = getReferralProcessingCharges($amount);
            $max_to_be_sold = $row['max_to_be_sold'];
            $number_copies_sold =  $row['number_copies_sold'];
            if ($number_copies_sold < $max_to_be_sold) {
                $content_id = FreelinguistContentHelper::copy_content($ref_content_id);
            } else {
                throw new RuntimeException("Number of copies bought is maxed out");
            }


            //code-bookmark where the content has the purchased_by set


            $sql_to_update = "UPDATE wp_linguist_content SET
                                                        updated_at = NOW(),
                                                        purchased_at = NOW(),
                                                        purchased_by = $customer_id,
                                                        purchase_amount = $amount,
                                                        publish_type = 'Purchased'
                                                        WHERE id = $content_id
                                                        ";

            $wpdb->query($sql_to_update);
            will_throw_on_wpdb_error($wpdb);



            update_user_meta(get_current_user_id(), 'total_user_balance', ($balance - $amount));

            $receipts = [];

            fl_transaction_insert((-1 * $amount), 'done', 'buy_content', get_current_user_id(),
                NULL, 'Content purchase', '', '',
                NULL,NULL,NULL, 0);

            $lastid = $wpdb->insert_id;
            $sql_statement = "UPDATE wp_fl_transaction SET content_id = $content_id WHERE ID = $lastid ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            $contentreciept = "action=contentreciept&receipt=" . $lastid;
            $receipts[] = $contentreciept;

            fl_transaction_insert((-1 * $fees), 'done', 'buy_content', get_current_user_id(),
                NULL, 'Processing fee', '', '', NULL,
                NULL,NULL, 0);

            $lastid = $wpdb->insert_id;
            $sql_statement = "UPDATE wp_fl_transaction SET content_id = $content_id WHERE ID = $lastid ";
            $wpdb->query($sql_statement);
            will_throw_on_wpdb_error($wpdb);

            $receipts[] ="action=contentreciept&receipt=" . $lastid;;


            $content_link = site_url() . '/content/' .
                '?lang=' . $lang . '&mode=view&content_id=' . FreelinguistContentHelper::encode_id($content_id);
            //code-bookmark content link made here


            //code-notes , depending on purchase type, we may want to update the original content id or the new one
            //code-notes update content, updates existing per and top only
            $content_to_work_with_units = $content_id;
            if ($ref_content_id !== $content_id) {
                $content_to_work_with_units = $ref_content_id;
            }
            FreelinguistUnitGenerator::generate_units($log,[],[$content_to_work_with_units]);

            emailTemplateForUser( $row['freelancer_email'],
                EMAIL_TEMPLATE_CONTENT_PURCHASED,
                [
                    'job_id'=> $row['id'],
                    'job_title' => $row['content_title'],
                    'job_status' => ''
                ] );
            wp_send_json( [
                'status' => 1,
                'message' => 'Content purchased successfully!!',
                'contentreciept' => $contentreciept,
                'reciepts' => $receipts,
                'content_link' => $content_link
            ]);
            exit; //exists in wp_send_json but mark it here for humans



        } catch (Exception $e) {
            will_send_to_error_log("Cannot complete content purchase: ",[
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            wp_send_json( ['status' => 0, 'message' => 'Error. Please Try again.']);
            exit; //exists in wp_send_json but mark it here for humans

        }


    }

    /**
     * @param int $user_id
     * @param bool $b_wrap_in_class, default false. If true will return array of  _FreelinguistIdType[]
     * @return _FreelinguistIdType[]|int[]
     */
    public static function get_original_content_ids_by_user($user_id,$b_wrap_in_class = false) {
        global $wpdb;
        $deese_nodes = [];
        $deese_ids = [];
        $sql_statement = "SELECT id FROM wp_linguist_content WHERE   parent_content_id IS NULL AND user_id = $user_id";
        $contents_to_un_unit = $wpdb->get_results($sql_statement);
        foreach ($contents_to_un_unit as $goodbye) {
            $an_id = (int)$goodbye->id;
            $deese_ids[] = $an_id;
            $node = new _FreelinguistIdType();
            $node->type = 'content';
            $node->id = $an_id;
            $deese_nodes[] = $node;

        }

        if ($b_wrap_in_class) {
            return $deese_nodes;
        }
        return $deese_ids;
    }

    /**
     * @param  int $content_id
     * @param string $relative_folder_path OUTREF
     * @return string
     */
    public static function get_content_directory_and_make_it_if_not_exists($content_id,&$relative_folder_path) {

        $content_id = (int)$content_id;
        if (!$content_id) {
            throw new InvalidArgumentException("content id needs to be an integer");
        }

        $upload_dir = wp_upload_dir();

        $relative_folder_path = 'linguistcontent/'.UploadHandler::number_to_path_part_string($content_id,'/') .'/';
        $content_folder = $upload_dir['basedir'] . '/' . $relative_folder_path;

        if (!is_dir($content_folder)) {
            $b_check = mkdir($content_folder, 0755, true);
            if (!$b_check) {throw new RuntimeException("could not create the folder $content_folder");}
        }

        $rem_path = $content_folder;
        $path = realpath($content_folder);
        if (!$path) {
            throw new RuntimeException("Could not make the path $rem_path");
        }
        return $path;
    }

    /**
     * @param int $content_id
     * @return bool
     * @throws Exception
     */
    protected static function add_or_replace_cover_image($content_id) {
        global $wpdb;
        try {

            $valid_formats = array("jpg", "png", "jpeg"); //Supported file types

            $max_file_size = 1024 * 2500; //in kb

            $wp_upload_dir = wp_upload_dir();


            $our_middle_path = UploadHandler::number_to_path_part_string($content_id,'/') .'/';
            $relative_path = static::UPLOAD_SUBDIRECTORY.'/'.$our_middle_path  ;
            $path = $wp_upload_dir['basedir'] . '/'.$relative_path;

            if (isset($_FILES['content_cover_image']['name']) && !empty($_FILES['content_cover_image']['name'])) {

                $name = $_FILES['content_cover_image']['name'];

                $extension = pathinfo($name, PATHINFO_EXTENSION);

                $new_filename = 'content-cover-' . $content_id ."-" . time() . '-'.
                    UploadHandler::generate_random_safe_characters(). '.' . $extension;

                if ($_FILES['content_cover_image']['size'] > $max_file_size) {

                    throw new InvalidArgumentException( get_custom_string_return('Image is too large'). ' ' . $_FILES['content_cover_image']['size']);

                } elseif (!in_array(strtolower($extension), $valid_formats)) {

                    throw new InvalidArgumentException(get_custom_string_return('Please upload valid image.(png,jpg,jpeg)').' not '. $extension);

                }
                //passed the first checks
                $validate_error_message = '';
                try {
                    $b_ok = FileUploadWhitelist::validate_file(
                        $_FILES["content_cover_image"]["tmp_name"],
                        $_FILES['content_cover_image']['name'],
                        [FileUploadWhitelist::IMAGE_TYPES],
                        $mime_type,

                        $valid_extension,
                        $new_file_path
                    );
                    if (!$b_ok) {
                        $validate_error_message = "The mime type of $mime_type is not allowed when uploading " . $_FILES['files']['name'];
                    }
                } catch (FileUploadWhitelistException $ew) {
                    $validate_error_message = $ew->getMessage();
                }

                if ($validate_error_message) {
                    throw new InvalidArgumentException($validate_error_message);
                }

                $calculated_full_file_path = $path . $new_filename;
                //code-notes create new path, if not already made
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }
                $calculated_relative_file_path = $relative_path . $new_filename;
                //code-notes [file-paths]  changed the upload to use new path and format
                $old_path = $_FILES["content_cover_image"]["tmp_name"];
                if (move_uploaded_file($old_path, $calculated_full_file_path)) {
                    try {
                        //code-notes [image-sizing]  where to put the image size converter
                        $sizer = new FreelinguistSizeImages($calculated_full_file_path);
                        will_do_nothing($sizer);
                    } catch (Exception $e) {
                        static::log(static::LOG_ERROR,'error in sizing content cover',$e);
                    }
                } else {
                    throw new RuntimeException("Could not move image from $old_path to $calculated_full_file_path ");
                }

                try {
                    static::delete_content_cover_file($content_id);
                } catch (Exception $meh) {
                    static::log(static::LOG_ERROR,'add_or_replace_cover_image issue with deleting older cover ',
                        will_get_exception_string($meh));
                    //if cannot delete older, still update with newer, will fix issues
                }

                //code-notes [image-sizing]  remove any previous content file
                $escaped_cover_image =  esc_sql($calculated_relative_file_path);

                $sql_to_set_time = "UPDATE wp_linguist_content SET 
                                updated_at = NOW() ,
                                content_cover_image = '$escaped_cover_image'
                                WHERE id = $content_id";
                $wpdb->query($sql_to_set_time);
                will_throw_on_wpdb_error($wpdb);

            } //end if there is something to upload
            //else nothing to do here



            return true;
        } catch (Exception $e) {
            static::log(static::LOG_ERROR,'add_or_replace_cover_image issue with cover image upload ',will_get_exception_string($e));
            throw $e;
        }
    }

    static function delete_content_cover_file($content_id) {
        global $wpdb;

        $sql_to_get_partial_path = "SELECT content_cover_image FROM wp_linguist_content WHERE  user_id IS NOT NULL AND  id = $content_id";
        $cover_info = $wpdb->get_results($sql_to_get_partial_path);
        will_throw_on_wpdb_error($wpdb,'getting content cover image for deletion of the file');
        if (empty($cover_info)) {
            throw new InvalidArgumentException("Cannot find content from id of $content_id");
        }

        $partial_image_path = $cover_info[0]->content_cover_image;
        if (empty($partial_image_path)) {
            return false; //our job is done here
        }


        //code-notes [image-sizing]  also delete the sized images
        $b_deleted = false;
        if ($partial_image_path) {
            $wp_upload_dir      = wp_upload_dir();
            $base               = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR;
            $calculated_path          = $base.$partial_image_path;
            FreelinguistSizeImages::remove_associated_sizes_from_original_path($calculated_path);
            $real_path = realpath($calculated_path);
            if ($real_path) {
                if (is_writable($real_path)) {
                    $b_what = unlink($real_path);
                    if ($b_what) {
                        $b_deleted = true;
                    }
                    else{
                        $message = "delete_content_cover_file had issues deleting the file ";
                        static::log(static::LOG_ERROR,$message,[
                            'base' => $base,
                            'partial_image_path' => $partial_image_path,
                            'calculated_path' => $calculated_path,
                            'real_path' => $real_path
                        ]);
                        throw new RuntimeException($message);
                    }
                } else {
                    $message = "delete_content_cover_file cannot write to the file ";
                    static::log(static::LOG_ERROR, $message,[
                        'base' => $base,
                        'partial_image_path' => $partial_image_path,
                        'calculated_path' => $calculated_path,
                        'real_path' => $real_path
                    ]);
                    throw new RuntimeException($message);
                }
            } else {
                $message = "content cover image path not found ";
                static::log(static::LOG_INFO,$message,[
                    'base' => $base,
                    'partial_image_path' => $partial_image_path,
                    'calculated_path' => $calculated_path,
                    'real_path' => $real_path
                ]);

            }


        }


        return $b_deleted;
    }

    protected static function delete_all_content_files($content_id) {
        global $wpdb;
        $content_file_ids = $wpdb->get_results("select id from wp_content_files where content_id=$content_id");
        will_throw_on_wpdb_error($wpdb);
        static::log(static::LOG_DEBUG, "all content file ids to delete ",[
            $content_file_ids
        ]);
        if (empty($content_file_ids)) {return;}
        foreach ($content_file_ids as $content_file_id) {
            static::delete_content_file($content_file_id);
        }
    }

    static function delete_content_file($content_file_id) {
        global$wpdb;
        $content_file_id = (int)$content_file_id;

        $content_file_detail = $wpdb->get_row("select * from wp_content_files where id=$content_file_id", ARRAY_A);
        if (empty($content_file_detail)) {
            throw new RuntimeException("No such content file entry for $content_file_id");
        }
        static::log(static::LOG_DEBUG, "Found content file to delete ",[
            $content_file_detail
        ]);
        $wp_upload_dir = wp_upload_dir();
        $basepath = $wp_upload_dir['basedir'];
        $file_path = $content_file_detail['file_path'];
        $full_file_path = $basepath . '/' . $file_path;
        if (file_exists($full_file_path)) {
            $b_ok = unlink($full_file_path);
            if ($b_ok) {
                static::log(static::LOG_DEBUG, "unlinked file ",[
                    $full_file_path
                ]);
            } else {
                throw new RuntimeException("Could not delete the file $full_file_path from content file id of $content_file_id");
            }

        }

        $wpdb->delete('wp_content_files', array('id' => $content_file_detail['id']));
        will_throw_on_wpdb_error($wpdb);
        static::log(static::LOG_DEBUG, "deleted content file id of  ",[
            $content_file_detail['id']
        ]);
    }

    static function delete_content($content_id,$b_check_current_user = false,$b_remove_es_entry = true) {
        global $wpdb;

        $content_detail = FreelinguistContentHelper::get_content_extended_information($content_id);

        if ($b_check_current_user) {
            $user_id = get_current_user_id();
            if ($content_detail['user_id'] !== $user_id ) {
                throw new InvalidArgumentException( get_custom_string_return('You are unautorized user'));
            }
        }

        if ($content_detail['number_copies_sold']) {
            throw new InvalidArgumentException("Cannot delete content that is sold");
        }


        //code-notes Delete generated units when mass deleting content (if exists)
        $deese_nodes = [];
        $node = new _FreelinguistIdType();
        $node->type = 'content';
        $node->id = (int)$content_detail['id'];
        $deese_nodes[] = $node;
        FreelinguistUnitGenerator::remove_compiled_units_from_es_cache($log, null, $deese_nodes);

        //code-notes [image-sizing]  now deleting the cover file
        static::delete_content_cover_file($content_id);
        static::delete_all_content_files($content_id);
        $wpdb->delete('wp_linguist_content', array('id' => $content_detail['id']));
        will_throw_on_wpdb_error($wpdb);
        if ($b_remove_es_entry) {
            //ELASTIC CONNECTION
            try {
                $log = [];
                $es = new FreelinguistElasticSearchHelper();
                $es->delete_id_inside_index('freelinguist', "content", $content_detail['id'], $log);
                static::log(static::LOG_DEBUG, 'deleting content from es', $log);
            } catch (Exception $e) {
                will_send_to_error_log('error deleting content from elastic search', $e, false, true);
            }
        }
    }



    /**
     * Deletes any owned content, and created content without children, removes files too from disk
     * @param int $user_id
     *
     */
    static function delete_own_content($user_id) {
        global $wpdb;
        $user_id = (int)$user_id;

        //do owned first in case of self-own
        $sql = "SELECT content.id as content_id FROM wp_linguist_content content where user_id IS NOT NULL AND purchased_by = $user_id;";
        $res = $wpdb->get_results($sql);
        foreach ($res as $row) {
            $content_id = $row->content_id;
            try {
                FreelinguistContentHelper::delete_content($content_id);
            } catch (Exception $e) {
                static::log(static::LOG_ERROR,"issue deleting content $content_id",will_get_exception_string($e));
            }
        }


        $sql = "    SELECT
                      top_content.id   as content_id
                    FROM wp_linguist_content top_content
                      LEFT JOIN wp_linguist_content child_content
                        ON top_content.id = child_content.parent_content_id
                    WHERE top_content.user_id = $user_id and child_content.id IS NULL ;";

        $res = $wpdb->get_results($sql);
        foreach ($res as $row) {
            $content_id = $row->content_id;
            try {
                FreelinguistContentHelper::delete_content($content_id);
            } catch (Exception $e) {
                static::log(static::LOG_ERROR,"issue deleting content $content_id",will_get_exception_string($e));
            }
        }

    }
} //end class

FreelinguistContentHelper::turn_on_debugging(FreelinguistDebugging::LOG_WARNING);

