<?php

add_filter('parse_query', 'mypo_parse_query_useronly');

/**
 * @param WP_Query $wp_query
 */
function mypo_parse_query_useronly($wp_query)
{
    if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/edit.php') !== false && isset($_REQUEST['post_type']) &&
        in_array($_REQUEST['post_type'], array("job", "wallet")))
    {
        global $current_user;
        wp_enqueue_style('subsubsub', get_template_directory_uri() . '/includes/admin-init/css/hide-subsubsub.css', array(), '1.0', 'all');
        $current_user = wp_get_current_user();
        if (!in_array('administrator', $current_user->roles) && !in_array('admin', $current_user->roles) && !isset($_REQUEST['author'])) {

            $wp_query->set('author__in', getReportedUserByUserId());
        }
       // if (in_array('administrator', $current_user->roles) || in_array('super_sub_admin', $current_user->roles) || in_array('admin', $current_user->roles)) {
          //  if ($_REQUEST['post_type'] == 'job') {
               //code-notes was repeat hiding and added count
           // }
       // }
    }
}