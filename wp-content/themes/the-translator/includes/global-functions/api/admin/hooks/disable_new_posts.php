<?php

add_action('admin_menu', 'disable_new_posts');

function disable_new_posts() {
    // Hide sidebar link
    // Hide link on listing page
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'wallet') {
        wp_enqueue_style('disable-new-wallet-posts', get_template_directory_uri() . '/includes/admin-init/css/disable-wallet-posts.css', array(), '1.0', 'all');
    }
}