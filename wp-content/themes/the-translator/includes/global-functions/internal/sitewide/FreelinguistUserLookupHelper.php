<?php

/**
 * Class FreelinguistUserLookupHelper
 * This helps with working in the data for the DB table of wp_fl_user_data_lookup
 * Specifically, it provides easy ways to add login times from the php code, and also helps automate
 * the recording of login times by hooking into the WP hook wp_login, which is activated when a user logs in
 * so, the upshot is that by including this class in the startup files for the theme,
 * it automates storing login information
 *
 * We need to store login information to this db table as the user score is calculated from login times
 * And , updating this column here starts a cascading trigger and db proc series on the db which
 *  - recalculates the score
 *  - recalculates the user's possible entry to wp_display_unit_user_content (the top taggers list)
 *      because the user's score is calculated by
 *          SET NEW.score :=  (NEW.rating_as_freelancer + 1) * UNIX_TIMESTAMP(NEW.last_login_time);
 *      in both the before-insert trigger to the wp_fl_user_data_lookup
 *               and the before-update trigger
 *
 * copies of the current procs and triggers are kept in the docs
 *
 * @see docs/unit_generation_sql/manage_top_list_for_users.sql
 * @see docs/unit_generation_sql/wp_fl_user_data_lookup_on_before_create.sql
 * @see docs/unit_generation_sql/wp_fl_user_data_lookup_on_before_update.sql
 *
 * The proc manage_top_list_for_users is called (indirectly through manage_top_list) by the after triggers
 * @see docs/unit_generation_sql/wp_fl_user_data_lookup_on_after_update.sql
 * @see docs/unit_generation_sql/wp_fl_user_data_lookup_on_after_create.sql
 * @see docs/unit_generation_sql/manage_top_list.sql
 *
 */
class FreelinguistUserLookupHelper {
    static $b_have_setup_hooks = false;

    /**
     * @param int $user_id
     */
    static protected function set_login_for_user($user_id) {
        global $wpdb;
        $user_id = (int)$user_id;
        $sql = "UPDATE wp_fl_user_data_lookup SET last_login_time = NOW() WHERE user_id = $user_id ";
        $wpdb->query($sql);
        will_log_on_wpdb_error($wpdb);
    }

    /**
     * Designed to be called by the wordpress hook when a user logs in
     * @param string $user_login
     * @param WP_User|string $user
     */
    static public function record_login_time($user_login, $user='') {
        //code-notes update user lookup last login
        if ($user == ''){
            //Try and get user object
            $user = get_user_by('login', $user_login); //This should return WP_User obj
            if (!$user){
                will_send_to_error_log("Cannot get login user in freelinguist_form_key_login_user");
            }
        }
        if ($user && is_object($user)) {
            static::set_login_for_user($user->ID);
        } else {
            will_send_to_error_log("User is not what is expected, should be object",$user,false,true);
        }
    }

    /**
     * Called once , to setup the callbacks to WP
     */
    static public function setup_hooks() {
        if (static::$b_have_setup_hooks) {return;}

        add_action('init', function() {
            add_action('wp_login',['FreelinguistUserLookupHelper','record_login_time'],10,2);
        }, 0);

        static::$b_have_setup_hooks = true;
    }
}

FreelinguistUserLookupHelper::setup_hooks();