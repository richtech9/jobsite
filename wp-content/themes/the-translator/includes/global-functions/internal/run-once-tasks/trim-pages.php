<?php

/**
 * We trash the pages not on the protected list, which was found in the docs/current-code/current-page-posts-needed.txt
 * @param bool $b_dry_run default false
 */
function fl_task_trim_pages($b_dry_run = false) {
    global $wpdb;

    $pages_sql = "
        SELECT p.id as post_id, post_title, m.meta_value as path, d.meta_value as duplicate
        FROM wp_posts p
          LEFT JOIN wp_postmeta m ON m.post_id = p.ID AND m.meta_key = '_wp_page_template'
          LEFT JOIN wp_postmeta d ON d.post_id = p.ID AND d.meta_key = '_icl_lang_duplicate_of'
        WHERE
        p.post_type = 'page' and p.post_status <> 'trash'
          AND p.ID NOT IN (7,9,371,572,24776,13,681,27436,24708,24789,3002,369,835,24710,24712,1,29110,25319,22,5,828,465,406)
        ORDER BY path,duplicate ;
            ";

    $res = $wpdb->get_results($pages_sql);
    will_throw_on_wpdb_error($wpdb,'getting posts to trash');
    if (empty($res)) {will_send_to_error_log("No pages to trash");}
    $ret = [];
    foreach ($res as $row) {
        $page_id = (int)$row->post_id;
        if ($b_dry_run) {
            $what = get_post($page_id);
        } else {
            $what = wp_trash_post($page_id);
        }
        $node = [
            'Post ID' => $row->post_id,
            'Template' => $what->page_template,
            'Title' => $what->post_title,
            'Name' => $what->post_name,
            'Post Type' => $what->post_type
        ];

        if (!$b_dry_run) {
            if ($what) {
                will_send_to_error_log("Trashed page ", $node);
            } else {
                will_send_to_error_log("Could not trash the post id of $page_id");
            }
        }
        $ret[] = $node;
    }
    if ($b_dry_run) {
        will_send_to_error_log('dry run to trim pages: will be removing these',$ret);
    }
}