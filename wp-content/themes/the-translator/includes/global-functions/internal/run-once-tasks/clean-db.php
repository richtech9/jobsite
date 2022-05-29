<?php
/*
select count(* ) as number_in_type, p.post_type from wp_posts p group by p.post_type order by  number_in_type desc ;

-- save only the page types
select * from wp_posts where post_type = 'page' order by ID;

-- save the post meta for the page types

-- saved post meta
select meta.*
from wp_postmeta meta
inner join wp_posts post ON post.ID = meta.post_id AND post.post_type = 'page'
order by post.ID, meta.meta_id;


-- save the admin users

SELECT u.*
FROM wp_users u
INNER JOIN wp_usermeta meta ON  u.ID = meta.user_id AND meta.meta_key =  'wp_capabilities' AND meta_value  LIKE '%\"Administrator\"%'
WHERE 1
ORDER BY user_login ASC;


-- save the admin user meta
SELECT meta.*
FROM wp_usermeta meta
INNER JOIN (
SELECT u.ID
FROM wp_users u
INNER JOIN wp_usermeta meta ON  u.ID = meta.user_id AND meta.meta_key =  'wp_capabilities' AND meta_value  LIKE '%\"Administrator\"%'
WHERE 1
) as admin_users ON admin_users.ID = meta.user_id
ORDER BY  meta.umeta_id;

*/


/*

-- tables not to trim at all:

wp_actionscheduler_groups
wp_email_templates
wp_options

*/



/*
-- tables to trim:

wp_actionscheduler_actions
wp_actionscheduler_claims
wp_actionscheduler_logs
wp_commentmeta
wp_comments
wp_content_files
wp_contest_insurance_refund
wp_coordination
wp_coupons
wp_custom_string_translation
wp_display_unit_user_content
wp_dispute_cases
wp_files
wp_fl_broadcast_messages
wp_fl_chat_logs
wp_fl_chat_rooms
wp_fl_discussion
wp_fl_forum
wp_fl_job
wp_fl_milestones
wp_fl_post_data_lookup
wp_fl_post_lookup_errors
wp_fl_post_user_lookup
wp_fl_red_dots
wp_fl_transaction
wp_fl_user_data_lookup
wp_fl_user_lookup_errors
wp_gdbc_attempts
wp_gdmaq_emails
wp_gdmaq_log
wp_gdmaq_log_email
wp_gdmaq_queue
wp_homepage_interest
wp_homepage_interest_per_id
wp_interest_tags
wp_languages
wp_linguist_content
wp_linguist_content_chapter
wp_links
wp_message_email_history
wp_message_history
wp_messages
wp_payment_history
wp_payment_history_ipn
wp_postmeta
wp_posts
wp_proposals
wp_reports
wp_simple_history
wp_simple_history_contexts
wp_societies
wp_society_log_users
wp_society_logs
wp_society_period_users
wp_society_periods
wp_tags_cache_job
wp_term_relationships
wp_term_taxonomy
wp_termmeta
wp_terms
wp_transaction_lookup
wp_transaction_lookup_errors
wp_usermeta
wp_users
wp_wsluserscontacts
wp_wslusersprofiles
wp_wslwatchdog
*/




function fl_truncate_db($b_dry_run = false) {
    global $wpdb;

    /*
        save admin users info: wp_users, wp_usermeta
    */

    $sql_users = '
        SELECT u.*
        FROM wp_users u
        INNER JOIN wp_usermeta meta ON  u.ID = meta.user_id AND meta.meta_key =  \'wp_capabilities\' AND meta_value  LIKE \'%\"Administrator\"%\'
        WHERE 1
        ORDER BY user_login ASC;
    ';
    
    $user_saves = $wpdb->get_results($sql_users);
    will_throw_on_wpdb_error($wpdb,'getting user data');

    if ($b_dry_run) {
        will_send_to_error_log('[clean-db] Dry Run (data user )', json_encode($user_saves));
    }


    $sql_user_meta = '
        SELECT meta.*
        FROM wp_usermeta meta
        INNER JOIN (
        SELECT u.ID
        FROM wp_users u
        INNER JOIN wp_usermeta meta ON  u.ID = meta.user_id AND meta.meta_key =  \'wp_capabilities\' AND meta_value  LIKE \'%\"Administrator\"%\'
        WHERE 1
        ) as admin_users ON admin_users.ID = meta.user_id
        ORDER BY  meta.umeta_id;
    ';

    $user_meta_saves = $wpdb->get_results($sql_user_meta);
    will_throw_on_wpdb_error($wpdb,'getting user meta data');

    if ($b_dry_run) {
        will_send_to_error_log('[clean-db] Dry Run (data user meta)',json_encode($user_meta_saves));
    }

    /*
     * Save page posts and their meta
     */

    $sql_posts = "
        SELECT * FROM wp_posts WHERE post_type = 'page' ORDER BY ID;
    ";

    $post_saves = $wpdb->get_results($sql_posts);
    will_throw_on_wpdb_error($wpdb,'getting post data');

    if ($b_dry_run) {
        will_send_to_error_log('[clean-db] Dry Run (data post)',json_encode($post_saves));
    }

    $sql_post_meta = "
        SELECT meta.*
        FROM wp_postmeta meta
        INNER JOIN wp_posts post ON post.ID = meta.post_id AND post.post_type = 'page'
        ORDER BY post.ID, meta.meta_id;
    ";



    $post_meta_saves = $wpdb->get_results($sql_post_meta);
    will_throw_on_wpdb_error($wpdb,'getting post meta data');

    if ($b_dry_run) {
        will_send_to_error_log('[clean-db] Dry Run (data post meta)',json_encode($post_meta_saves));
    }

    $tables_to_trim = [
        'wp_actionscheduler_actions',
        'wp_actionscheduler_claims',
        'wp_actionscheduler_logs',
        'wp_commentmeta',
        'wp_comments',
        'wp_content_files',
        'wp_contest_insurance_refund',
        'wp_coordination',
        'wp_coupons',
        'wp_custom_string_translation',
        'wp_display_unit_user_content',
        'wp_dispute_cases',
        'wp_files',
        'wp_fl_broadcast_messages',
        'wp_fl_chat_logs',
        'wp_fl_chat_rooms',
        'wp_fl_discussion',
        'wp_fl_forum',
        'wp_fl_job',
        'wp_fl_milestones',
        'wp_fl_post_data_lookup',
        'wp_fl_post_lookup_errors',
        'wp_fl_post_user_lookup',
        'wp_fl_red_dots',
        'wp_fl_transaction',
        'wp_fl_user_data_lookup',
        'wp_fl_user_lookup_errors',
        'wp_gdbc_attempts',
        'wp_gdmaq_emails',
        'wp_gdmaq_log',
        'wp_gdmaq_log_email',
        'wp_gdmaq_queue',
        'wp_homepage_interest',
        'wp_homepage_interest_per_id',
        'wp_interest_tags',
        'wp_languages',
        'wp_linguist_content',
        'wp_linguist_content_chapter',
        'wp_links',
        'wp_message_email_history',
        'wp_message_history',
        'wp_messages',
        'wp_payment_history',
        'wp_payment_history_ipn',
        'wp_postmeta',
        'wp_posts',
        'wp_proposals',
        'wp_reports',
        'wp_simple_history',
        'wp_simple_history_contexts',
        'wp_societies',
        'wp_society_log_users',
        'wp_society_logs',
        'wp_society_period_users',
        'wp_society_periods',
        'wp_tags_cache_job',
        'wp_term_relationships',
        'wp_term_taxonomy',
        'wp_termmeta',
        'wp_terms',
        'wp_transaction_lookup',
        'wp_transaction_lookup_errors',
        'wp_usermeta',
        'wp_users',
        'wp_wsluserscontacts',
        'wp_wslusersprofiles',
        'wp_wslwatchdog',
    ];

    //disable fk before trimming
    $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');

    foreach ($tables_to_trim as $data_be_gone_here) {
        $sql_to_truncate = "TRUNCATE $data_be_gone_here;";

        if ($b_dry_run) {
            will_send_to_error_log('[clean-db] Dry Run (truncate)',$sql_to_truncate);
        } else {
            $wpdb->query($sql_to_truncate);
            will_throw_on_wpdb_error($wpdb,'truncate');
            will_send_to_error_log('[clean-db] *Real* (truncate)',$sql_to_truncate);
        }
    }


    //enable fk checks again
    $wpdb->query('SET FOREIGN_KEY_CHECKS=1;');

    //add in saved data

    if (!$b_dry_run) {
        will_send_to_error_log('[clean-db] *Real* (restoring user data)');
    }

    foreach ($user_saves as $da_user) {
        //avoid writing to db using the WP helper method, use raw only
        $sql_to_insert_user = "
        INSERT INTO wp_users(ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name)
        VALUES (
         ".$da_user->ID.",
         '".esc_sql($da_user->user_login)."',
         '".esc_sql($da_user->user_pass)."',
         '".esc_sql($da_user->user_nicename)."',
         '".esc_sql($da_user->user_email)."',
         '".esc_sql($da_user->user_url)."',
         '".esc_sql($da_user->user_registered)."',
         '".esc_sql($da_user->user_activation_key)."',
         '".esc_sql($da_user->user_status)."',
         '".esc_sql($da_user->display_name)."'
        );
        ";

        if ($b_dry_run) {
            will_send_to_error_log('[clean-db] Dry Run (user)',$sql_to_insert_user);
        } else {
            $wpdb->query($sql_to_insert_user);
            will_throw_on_wpdb_error($wpdb,'insert user');
        }
    }


    if (!$b_dry_run) {
        will_send_to_error_log('[clean-db] *Real* (restoring user meta data)');
    }

    foreach ($user_meta_saves as $da_user_meta) {
        //avoid writing to db using the WP helper method, use raw only
        $sql_to_insert_user_meta = "
        INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) 
        VALUES (
         ".$da_user_meta->umeta_id.",
          ".$da_user_meta->user_id.",
          '".$da_user_meta->meta_key."',
          '".esc_sql($da_user_meta->meta_value)."'
        );
        ";

        if ($b_dry_run) {
            will_send_to_error_log('[clean-db] Dry Run (user meta)',$sql_to_insert_user_meta);
        } else {
            $wpdb->query($sql_to_insert_user_meta);
            will_throw_on_wpdb_error($wpdb,'insert user meta');
        }
    }


    if (!$b_dry_run) {
        will_send_to_error_log('[clean-db] *Real* (restoring post data)');
    }

    foreach ($post_saves as $da_post) {
        //avoid writing to db using the WP helper method, use raw only
        $sql_to_insert_post = "
        INSERT INTO wp_posts(
          ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status,
          comment_status, ping_status, post_password, post_name, to_ping, pinged,
          post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order,
          post_type, post_mime_type, comment_count
        ) 
        VALUES (
             ".$da_post->ID.",
             '".esc_sql($da_post->post_author)."',
             '".esc_sql($da_post->post_date)."',
             '".esc_sql($da_post->post_date_gmt)."',
             '".esc_sql($da_post->post_content)."',
             '".esc_sql($da_post->post_title)."',
             '".esc_sql($da_post->post_excerpt)."',
             '".esc_sql($da_post->post_status)."',
             '".esc_sql($da_post->comment_status)."',
             '".esc_sql($da_post->ping_status)."',
             '".esc_sql($da_post->post_password)."',
             '".esc_sql($da_post->post_name)."',
             '".esc_sql($da_post->to_ping)."',
             '".esc_sql($da_post->pinged)."',
             '".esc_sql($da_post->post_modified)."',
             '".esc_sql($da_post->post_modified_gmt)."',
             '".esc_sql($da_post->post_content_filtered)."',
             '".esc_sql($da_post->post_parent)."',
             '".esc_sql($da_post->guid)."',
             '".esc_sql($da_post->menu_order)."',
             '".esc_sql($da_post->post_type)."',
             '".esc_sql($da_post->post_mime_type)."',
             '".esc_sql($da_post->comment_count)."'
        )   ;
        ";

        if ($b_dry_run) {
            will_send_to_error_log('[clean-db] Dry Run (post)',$sql_to_insert_post);
        } else {
            $wpdb->query($sql_to_insert_post);
            will_throw_on_wpdb_error($wpdb,'insert post');
        }
    }

    if (!$b_dry_run) {
        will_send_to_error_log('[clean-db] *Real* (restoring post meta data)');
    }

    foreach ($post_meta_saves as $da_post_meta) {
        //avoid writing to db using the WP helper method, use raw only
        $sql_to_insert_post_meta = "
        INSERT INTO wp_postmeta(meta_id, post_id, meta_key, meta_value) 
        VALUES (
          ".$da_post_meta->meta_id.",
          ".$da_post_meta->post_id.",
          '".$da_post_meta->meta_key."',
          '".esc_sql($da_post_meta->meta_value)."'
        );
        ";

        if ($b_dry_run) {
            will_send_to_error_log('[clean-db] Dry Run (post meta)',$sql_to_insert_post_meta);
        } else {
            $wpdb->query($sql_to_insert_post_meta);
            will_throw_on_wpdb_error($wpdb,'insert post meta');
        }
    }


    if (!$b_dry_run) {
        will_send_to_error_log('[clean-db] *Real* (complete!)');
    }


}




