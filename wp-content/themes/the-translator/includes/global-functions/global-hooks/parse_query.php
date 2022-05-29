<?php

//code-notes set this to be higher priority from the WPML parse_query hook, so we overwrite any id it sets if its on and doing things
add_action( 'parse_query', 'freelinguist_parse_query' ,200);

/*
 * current-php-code 2020-Jan-11
 * current-hook
 * input-sanitized :
 */

/**
 * @param WP_Query $q
 */
function freelinguist_parse_query($q) {
    global $wpdb;
//    global $wp_rewrite;

    /*
     * current-php-code 2020-Nov-11
     * internal-call
     * input-sanitized :
     */

    //$rewrite = $wp_rewrite->rules;
    //will_send_to_error_log('rules',$rewrite);

//    will_send_to_error_log("AAQA1 my parse query",$q);


    if (isset($q->query['post_type']) && $q->query['post_type'] === 'job') {
        if (isset($q->query['job']) && !empty($q->query['job'])) {
            //find a post title with this and set the id
            $job_id_string = $q->query['job'];
            $job_id_string_escaped = FLInput::filter_string_allow_html($job_id_string);
//            will_send_to_error_log("AAQA2 my job id string: '$job_id_string_escaped'",$job_id_string);
            $sql = "SELECT *,ID as post_id from wp_posts WHERE post_title = '$job_id_string_escaped'";
            $what = $wpdb->get_results($sql);
            will_log_on_wpdb_error($wpdb);
            if (count($what)) {
                $da_id = $what[0]->post_id;
                $q->query_vars['page_id'] = $da_id;
//                will_send_to_error_log('AAQA4 id',$da_id);
            }
//            else {
//                will_send_to_error_log("AAOC4 did not find title");
//            }
        }
    }

}