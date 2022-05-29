<?php

class FLWPFileHelper  extends  FreelinguistDebugging
{
    //each inherited debugging needs their own controls
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;


    const TYPE_INSTRUCTION_FILE = 1;
    const TYPE_POST_DETAILS  = 5;
    const TYPE_FREELANCER_UPLOAD = 2;
    const TYPE_USER_ASSOCIATED = 4; //no files are this type right now

    /**
     * @param int $post_id
     * @param bool $b_remove_job_files if true will remove files of type
     *      @uses FLWPFileHelper::TYPE_FREELANCER_UPLOAD
     * delete any files for instruction, details or uploads  that might exist
     */
    public static function remove_any_files_for_post($post_id,$b_remove_job_files = true) {
        global $wpdb;
        static::log(static::LOG_DEBUG, "starting remove_any_files_for_post ",[
            '$post_id'=> $post_id,
            '$b_remove_job_files'=>  $b_remove_job_files
        ]);
        $post_id = (int)$post_id;

        $partial_where = '1';
        if (!$b_remove_job_files) {
            $partial_where = "  f.type <>  ".static::TYPE_FREELANCER_UPLOAD;
        }
        $sql = "SELECT f.id as file_id FROM wp_files f WHERE f.post_id = $post_id AND $partial_where";
        $res = $wpdb->get_results($sql);
        static::log(static::LOG_DEBUG, "sql for remove_any_files_for_post ", [
            '$sql'=>$sql,
            '$res'=>$res
        ]);
        will_throw_on_wpdb_error($wpdb);
        $ids = [];
        foreach ($res as $row) {
            $ids[] = $row->file_id;
        }
        static::log(static::LOG_DEBUG, "found ids for remove_any_files_for_post  ", [
            '$ids'=>$ids,
        ]);
        static::delete_wp_files_via_id_array($ids);
    }

    /**
     * Removes any files on disk, or rows in wp_files or wp_content_files that are owned by the user
     *
     * IMPORTANT EXCEPTION: if the user uploaded files for jobs, or winning proposal files, these are kept,
     *      and the db will automatically make the user_id who owns this null
     * @param $user_id
     */
    public static function remove_any_files_for_user($user_id) {
        global $wpdb;
        static::log(static::LOG_DEBUG, "starting remove_any_files_for_user ",$user_id);
        $user_id = (int)$user_id;
        //code-notes delete any tax file that might exist
        static::remove_user_tax_file($user_id);

        //code-notes for any proposals made by the user, remove those files
        static::remove_user_proposal_files($user_id);

        //code-notes for any files generally associated with the user, out side of jobs and content and tax, remove those files
        static::remove_user_associated_files($user_id);

        //code-notes for any posts owned by the user, remove any associated files
        $sql = "SELECT ID as post_id FROM wp_posts where post_author = $user_id";
        $posts_ids_to_delete_files_for = [];
        $res = $wpdb->get_results($sql);
        static::log(static::LOG_DEBUG, "sql for remove_any_files_for_user ", [
            '$sql'=>$sql,
            '$res'=>$res
        ]);
        will_throw_on_wpdb_error($wpdb);
        foreach ($res as $row) {
            $posts_ids_to_delete_files_for[] = $row->post_id;
        }

        foreach ($posts_ids_to_delete_files_for as $da_post_id) {
            static::remove_any_files_for_post($da_post_id,false);
        }


    }

    /**
     * @param $user_id
     * @param bool $b_throw_on_error
     */
    public static function remove_user_tax_file($user_id,$b_throw_on_error = false) {
        $upload_dir = wp_upload_dir();
        $user_dirname = $upload_dir['basedir'];
        $file_path = trim(get_user_meta($user_id, '_signed_tax_form', true));
        if (empty($file_path)) {
            static::log(static::LOG_DEBUG, "no tax file for $user_id ");
            return;
        }
        $full_file_path = $user_dirname  . $file_path;
        if (file_exists($full_file_path)) {
            $b_ok = unlink($full_file_path);
            static::log(static::LOG_DEBUG, "unlinked file in remove_user_tax_file ", [
                'unlink result'=>$b_ok,
                '$full_file_path' => $full_file_path,
                '$file_path (partial)' => $file_path
            ]);
            if (!$b_ok) {
                will_send_to_error_log("fl_on_delete_wp_user found a file in the meta that is not exiting: ",$full_file_path);
                throw new RuntimeException("Cannot delete existing file at $full_file_path ");
            }
            delete_user_meta($user_id,FreelinguistUserHelper::META_KEY_NAME_TAX_FORM);
        } else {
            static::log(static::LOG_WARNING, "tax file path does not exist", [
                '$full_file_path' => $full_file_path,
                '$file_path (partial)' => $file_path
            ]);
            if($b_throw_on_error) {
                throw new RuntimeException("File path calculated for tax file does not exist for user id $user_id , $file_path, $full_file_path");
            }
        }
    }



    /**
     * Removes any files from proposals the user uploaded
     * However, if a proposal has been awarded already, do not remove that
     * @param int $user_id
     */
    protected static function remove_user_proposal_files($user_id) {
        global $wpdb;

        $flag_for_awarding = FLPostLookupDataHelpers::POST_USER_DATA_FLAG_AWARDED_CONTEST;
        $sql = "SELECT f.id as file_id, prop.id as proposal_id
                FROM wp_proposals prop  
                INNER JOIN wp_files f ON f.proposal_id = prop.id 
                LEFT JOIN wp_fl_post_user_lookup look ON look.author_id = prop.by_user AND look.lookup_flag = $flag_for_awarding
                WHERE prop.by_user = $user_id AND look.id IS NULL";

        $res = $wpdb->get_results($sql);
        static::log(static::LOG_DEBUG, "sql for remove_user_proposal_files ", [
            '$sql'=>$sql,
            '$res'=>$res
        ]);
        will_throw_on_wpdb_error($wpdb);
        $ids = [];
        foreach ($res as $row) {
            $ids[] = $row->file_id;
        }
        static::log(static::LOG_DEBUG, "found ids for remove_user_proposal_files  ", [
            '$ids'=>$ids,
        ]);
        static::delete_wp_files_via_id_array($ids);
    }

    /**
     * Removes any files from that are marked with the
     * @uses FLWPFileHelper::TYPE_USER_ASSOCIATED flag for the user
     * Please note, that at the time none of these exist but the code supports displaying and removing these in case that they later do
     * @param int $user_id
     */
    protected static function remove_user_associated_files($user_id) {
        global $wpdb;
        $sql = "SELECT id as file_id FROM wp_files WHERE by_user = $user_id AND type = ". static::TYPE_USER_ASSOCIATED;
        $res = $wpdb->get_results($sql);
        static::log(static::LOG_DEBUG, "sql for remove_user_associated_files ", [
            '$sql'=>$sql,
            '$res'=>$res
        ]);
        will_throw_on_wpdb_error($wpdb);
        $ids = [];
        foreach ($res as $row) {
            $ids[] = $row->file_id;
        }
        static::log(static::LOG_DEBUG, "found ids for remove_user_associated_files  ", [
            '$ids'=>$ids,
        ]);
        static::delete_wp_files_via_id_array($ids);
    }

    public static function delete_wp_files_via_id_array($id_array) {
        global $wpdb;
        if (empty($id_array)) {return;}
        $cleaned_ids = [];
        foreach ($id_array as $id) {
            $id = (int)$id;
            if ($id) {$cleaned_ids[] = $id;}
        }
        if (empty($cleaned_ids)) {
            static::log(static::LOG_WARNING,"empty id passed in array",$id_array);
            return;
        }

        $id_string = implode(", ",$id_array);

        $sql = "SELECT id as file_id,file_path  FROM wp_files  WHERE id in ($id_string)";
        $doomed_files_res = $wpdb->get_results($sql);
        static::log(static::LOG_DEBUG, "sql for delete_wp_files_via_id_array ", [
            '$sql'=>$sql,
            '$doomed_files_res'=>$doomed_files_res
        ]);
        will_throw_on_wpdb_error($wpdb);
        foreach ($doomed_files_res as $row) {
            static::log(static::LOG_DEBUG, "starting to delete file (disk and row)  ", [
                '$row'=>$row,
            ]);
            try {
                $da_id = $row->file_id;
                $partial_path = $row->file_path;
                static::unlink_partial_file($partial_path);
                $wpdb->delete('wp_files', array('id' => $da_id));
                will_throw_on_wpdb_error($wpdb);
                static::log(static::LOG_DEBUG, "deleted wp_file id of  ", [
                    $da_id
                ]);
            } catch (Exception $e) {
                static::log(static::LOG_ERROR,'issue deleting file for wp_file',will_get_exception_string($e));
            }
        }
    }

    protected static function unlink_partial_file($partial_file_path) {

        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/';
        $full_file_path = $base_path . $partial_file_path;

        $real_file_path = realpath($full_file_path);

        if (!$real_file_path) {
            throw new RuntimeException("File $real_file_path does not exist");
        }

        if (!is_readable($real_file_path)) {
            throw new RuntimeException("File $real_file_path is not readable");
        }
        if (!is_writable($real_file_path)) {
            throw new RuntimeException("File $real_file_path is not writable");
        }
        $what = unlink($real_file_path);
        static::log(static::LOG_DEBUG, "unlinked file  ", [
            '$real_file_path'=>$real_file_path,
            '$partial_file_path'=> $partial_file_path,
            'linking return' => $what
        ]);
        if (!$what) {
            throw new RuntimeException("File $real_file_path cannot be deleted");
        }
    }

}

FLWPFileHelper::turn_on_debugging(FreelinguistDebugging::LOG_WARNING);