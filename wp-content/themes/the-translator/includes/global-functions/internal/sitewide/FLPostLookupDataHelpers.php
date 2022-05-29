<?php

class FLPostLookupDataHelpers {

    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */

    //to select all users at once for deleting post user data
    const ALL_USERS = -100;



    #helper constants for the wp_fl_post_user_lookup
    const POST_USER_DATA_FLAG_BID = 1;
    const POST_USER_DATA_FLAG_PARTICIPANT = 2;
    const POST_USER_DATA_FLAG_COMPLETE_JOB = 4;
    const POST_USER_DATA_FLAG_AWARDED_CONTEST = 8;



    //new status flag


    const POST_DATA_NEW_STATUS_NONE = 0;
    const POST_DATA_NEW_STATUS_WORKING = 1;
    const POST_DATA_NEW_STATUS_COMPLETED = 2;
    const POST_DATA_NEW_STATUS_REJECTED = 3;
    const POST_DATA_NEW_STATUS_DELIVERY = 4;
    const POST_DATA_NEW_STATUS_DISPUTE = 5;
    const POST_DATA_NEW_STATUS_MEDIATION = 6;
    const POST_DATA_NEW_STATUS_DELIVERING = 7;
    const POST_DATA_NEW_STATUS_REVIEW = 8;
    const POST_DATA_NEW_STATUS_ERROR = -100;




    //job type flag
    const POST_DATA_JOB_TYPE_PROJECT = 1;
    const POST_DATA_JOB_TYPE_CONTEST = 2;

    //post type flag
    const POST_DATA_POST_TYPE_JOB = 1;

    //post status flags
    const POST_DATA_STATUS_PUBLISH = 2;


    public static function add_user_lookup_awarded_contest($post_id,$user_id,$project_id ) {
        static::add_user_lookup_by_flag(static::POST_USER_DATA_FLAG_AWARDED_CONTEST,$post_id,$user_id,$project_id);
    }

    public static function delete_user_lookup_bid($post_id,$user_id) {
        static::delete_user_lookup_by_flag(static::POST_USER_DATA_FLAG_BID,$post_id,$user_id);
    }

    public static function add_user_lookup_bid($post_id,$user_id,$value = NULL) {
        static::add_user_lookup_by_flag(static::POST_USER_DATA_FLAG_BID,$post_id,$user_id,$value);
    }

    public static function delete_user_lookup_participant($post_id,$user_id) {
        static::delete_user_lookup_by_flag(static::POST_USER_DATA_FLAG_PARTICIPANT,$post_id,$user_id);
    }

    public static function add_user_lookup_participant($post_id,$user_id,$value = NULL) {
        static::add_user_lookup_by_flag(static::POST_USER_DATA_FLAG_PARTICIPANT,$post_id,$user_id,$value);
    }

    public static function is_user_participant_in_contest($user_id,$contest_id) {
        global $wpdb;
        $flag = static::POST_USER_DATA_FLAG_PARTICIPANT;
        $user_id = (int)$user_id;
        $contest_id = (int) $contest_id;
        $sql= "SELECT id FROM wp_fl_post_user_lookup WHERE author_id = $user_id AND post_id = $contest_id AND lookup_flag = $flag ";
        $what = $wpdb->get_results($sql);
        if (empty($what)) {return false;}
        return true;
    }


    protected static function delete_user_lookup_by_flag($flag,$post_id,$user_id) {
        global $wpdb;
        if (!($post_id || $user_id)) {
            will_send_to_error_log("empty post or user to delete_user_lookup_bid ",
                [$post_id,$user_id],
                false,true);
            return;
        } //maybe passed empty user or post
        if ($user_id === static::ALL_USERS) {
            $sql = /** @lang text */
                "DELETE FROM wp_fl_post_user_lookup WHERE post_id = %d  AND lookup_flag = %d";
            $prepared = $wpdb->prepare($sql,$post_id,$flag);
        } else {
            $sql = /** @lang text */
                "DELETE FROM wp_fl_post_user_lookup WHERE post_id = %d AND author_id = %d AND lookup_flag = %d";
            $prepared = $wpdb->prepare($sql,$post_id,$user_id,$flag);
        }

        $wpdb->query($prepared);
        will_log_on_wpdb_error($wpdb);
    }

    protected static function add_user_lookup_by_flag($flag,$post_id,$user_id,$value) {
        global $wpdb;
        if (!($post_id || $user_id)) {
            will_send_to_error_log("empty post or user to add_user_lookup_bid ",
                [$post_id,$user_id],
                false,true);
            return;
        } //maybe passed empty user or post
        if (empty($value)) {$value = 0;}
        $sql = /** @lang text */
            "INSERT INTO wp_fl_post_user_lookup(post_id,author_id,lookup_flag,lookup_val)
             VALUES(%d,%d,%d,%d)";
        $prepared = $wpdb->prepare($sql,$post_id,$user_id,$flag,$value);
        $wpdb->query($prepared);
        will_log_on_wpdb_error($wpdb);
    }
}