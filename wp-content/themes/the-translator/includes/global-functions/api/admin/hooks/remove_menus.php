<?php
add_action('admin_menu', 'remove_menus');
//task-future-work There is a function, that is not used much, but is important if admins having different roles are used again, the code to disable features is out of data
function remove_menus()
{

    /*
    * current-php-code 2020-Jan-15
    * current-hook
    * input-sanitized :
    */

    $user_info = get_userdata(get_current_user_id());
    // pr($user_info->roles);
    if (in_array('administrator', $user_info->roles)) {

    } else if (in_array('administrator_for_client', $user_info->roles)) {
        //remove_menu_page( 'edit-comments.php' );          //Comments faq comment tool medi faq
        remove_menu_page('wpcf7');
        remove_menu_page('freelinguist-admin-options');
        remove_menu_page('theme-option-val');
        remove_menu_page('w3tc_dashboard');
        remove_menu_page('options-general.php');
        remove_menu_page('lingotek-translation');
        remove_menu_page('lingotek-translation_manage');
        remove_menu_page('lingotek-translation_settings');
        remove_menu_page('lingotek-translation_tutorial');
        remove_menu_page('edit.php?post_type=acf');
        remove_menu_page('edit.php');
        remove_menu_page('freelinguist-admin-email-templates');
        // Hook into the 'wp_dashboard_setup' action to register our function
        remove_meta_box('dashboard_activity', 'dashboard', 'side');
        add_action('admin_footer', 'custom_js_for_all_sub_admin');

        maybe_show_error_notice_for_permission('edit.php?post_type=wallet', 'add'); // Wallet add new permission
        maybe_show_error_notice_for_permission('edit.php?post_type=job', 'add'); // Wallet add new permission
        $wpml_plugin_disable_page = array(
            'sitepress-multilingual-cms/menu/languages.php',
            'sitepress-multilingual-cms/menu/theme-localization.php',
            'sitepress-multilingual-cms/menu/translation-options.php',
            'sitepress-multilingual-cms/menu/support.php',
            'sitepress-multilingual-cms/menu/menu-sync/menus-sync.php',
            'sitepress-multilingual-cms/menu/taxonomy-translation.php',
            'freelinguist-admin-string-translation',
            'freelinguist-admin-menu-translation',
            'freelinguist-admin-email-templates',
            'acui',
            'wp-bruiser-settings',
            'w3tc_dashboard',
            'updraftplus',
            'export-users-to-csv',
            'export-social-users-to-csv'
        );
        maybe_show_error_notice_for_page($wpml_plugin_disable_page);
        maybe_show_error_notice_for_post(array('acf', 'page', 'faq'));
        ?>
        <style>
            #menu-dashboard {
                display: none !important;
            }

            .toplevel_page_lingotek-translation {
                display: none !important;
            }

            #toplevel_page_edit-post_type-acf {
                display: none !important;
            }

            #toplevel_page_wp-bruiser-settings {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-social-contacts {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-email-templates {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-send-cashier-message {
                display: none;
            }

            #toplevel_page_sitepress-multilingual-cms-menu-languages {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-string-translation {
                display: none;
            }

            #toplevel_page_freelinguist-admin-menu-translation {
                display: none;
            }

            #wp-admin-bar-w3tc {
                display: none;
            }

            #wp-admin-bar-updraft_admin_node {
                display: none
            }

            #wp-admin-bar-updates {
                display: none !important;
            }

            #menu-media {
                display: none !important;
            }

            #menu-dashboard {
                display: none !important;
            }

            #menu-posts-faq {
                display: none !important;
            }

            .menu-icon-users li:nth-of-type(5) {
                display: none !important
            }

            .menu-icon-users li:nth-of-type(6) {
                display: none !important
            }

            .menu-icon-users li:nth-of-type(7) {
                display: none !important
            }

            .menu-icon-tools li:nth-of-type(2) {
                display: none !important
            }

            .menu-icon-tools li:nth-of-type(3) {
                display: none !important
            }

            .menu-icon-tools li:nth-of-type(4) {
                display: none !important
            }
        </style>


        <?php

    } elseif (in_array('super_sub_admin', $user_info->roles)) {
        //remove_menu_page( 'edit-comments.php' );          //Comments page
        remove_menu_page('wpcf7');
        remove_menu_page('freelinguist-admin-options');
        remove_menu_page('theme-option-val');
        remove_menu_page('w3tc_dashboard');
        remove_menu_page('options-general.php');
        remove_menu_page('tools.php');
        remove_menu_page('lingotek-translation');
        remove_menu_page('lingotek-translation_manage');
        remove_menu_page('lingotek-translation_settings');
        remove_menu_page('lingotek-translation_tutorial');
        remove_menu_page('edit.php?post_type=acf');
        remove_menu_page('edit.php');
        add_action('admin_footer', 'custom_js_for_all_sub_admin');
        add_filter('users_list_table_query_args', 'filter_users_list_table_by_cashier', 10, 1);
        add_action('admin_footer', 'custom_super_sub_admin_js');
        remove_meta_box('dashboard_activity', 'dashboard', 'side');
        maybe_show_error_notice_for_permission('edit.php?post_type=wallet', 'add'); // Wallet add new permission
        maybe_show_error_notice_for_permission('edit.php?post_type=job', 'add'); // Wallet add new permission
        add_action('admin_head', 'message_sub_admin_link_hide');
        add_action('admin_footer', 'custom_evalution_sub_admin_js');
        $wpml_plugin_disable_page = array(
            'sitepress-multilingual-cms/menu/languages.php',
            'sitepress-multilingual-cms/menu/theme-localization.php',
            'sitepress-multilingual-cms/menu/translation-options.php',
            'sitepress-multilingual-cms/menu/support.php',
            'sitepress-multilingual-cms/menu/menu-sync/menus-sync.php',
            'sitepress-multilingual-cms/menu/taxonomy-translation.php',
            'freelinguist-admin-string-translation',
            'freelinguist-admin-menu-translation',
            'freelinguist-admin-reminders',
            'freelinguist-admin-social-contacts',
            'freelinguist-admin-email-templates',
            'acui',
            'wp-bruiser-settings',
            'w3tc_dashboard',
            'updraftplus',
            //'freelinguist-admin-project-cases',
            //'JobRejectionClosed-list-table.php',
            'export-users-to-csv',
            'export-social-users-to-csv'
        );
        maybe_show_error_notice_for_page($wpml_plugin_disable_page);
        if (strpos($_SERVER['REQUEST_URI'], 'wp-admin/users.php') !== false) {
            ?>
            <style> .subsubsub {
                    display: none;
                } </style> <?php
        }

        if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'job') {
            if (!isset($_REQUEST['author'])) {
                ?>
                <style type="text/css">
                    .page-title-action {
                        display: none !important;
                    }

                </style>
                <?php
            }
        }
        ?>
        <style>
            #menu-dashboard {
                display: none !important;
            }

            .toplevel_page_lingotek-translation {
                display: none !important;
            }

            #toplevel_page_edit-post_type-acf {
                display: none !important;
            }

            /*#toplevel_page_freelinguist-admin-social-contacts{display: none !important;}*/
            #menu-posts-faq {
                display: none !important;
            }

            #menu-posts-job ul {
                display: none;
            }

            #toplevel_page_sitepress-multilingual-cms-menu-languages {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-email-templates {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-social-contacts {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-reminders {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-reminders {
                display: none !important;
            }

            .wp-list-table .type-wallet .row-actions {
                display: none !important
            }



            #menu-media {
                display: none !important;
            }

            #toplevel_page_wp-bruiser-settings {
                display: none !important;
            }

            #comments-form .bulkactions {
                display: none !important;
            }

            #posts-filter .bulkactions {
                display: none !important;
            }

            .edit-comments-php .subsubsub {
                display: none !important;
            }

            /*#toplevel_page_Coordination-list-table{display: none;*/
            #toplevel_page_freelinguist-admin-string-translation {
                display: none;
            }

            #toplevel_page_freelinguist-admin-menu-translation {
                display: none;
            }

            #wp-admin-bar-w3tc {
                display: none;
            }

            #wp-admin-bar-updraft_admin_node {
                display: none
            }

            /* #toplevel_page_freelinguist-admin-project-cases{display: none !important;}
             #toplevel_page_JobRejectionClosed-list-table{display: none !important;}*/
            #wp-admin-bar-updates {
                display: none !important;
            }

            .menu-icon-users li:nth-of-type(4) {
                display: none !important
            }

            .menu-icon-users li:nth-of-type(5) {
                display: none !important
            }
        </style>

        <?php

    } elseif (in_array('cashier_sub_admin', $user_info->roles)) {
        remove_menu_page('wpcf7');
        remove_menu_page('freelinguist-admin-options');
        remove_menu_page('theme-option-val');
        remove_menu_page('w3tc_dashboard');
        remove_menu_page('options-general.php');
        remove_menu_page('tools.php');
        remove_menu_page('lingotek-translation');
        remove_menu_page('lingotek-translation_manage');
        remove_menu_page('lingotek-translation_settings');
        remove_menu_page('lingotek-translation_tutorial');
        remove_menu_page('edit.php?post_type=acf');
        remove_menu_page('edit.php?post_type=job');
        remove_menu_page('edit.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('freelinguist-admin-reminders');
        remove_menu_page('freelinguist-admin-social-contacts');
        remove_menu_page('freelinguist-admin-evaluation.php');
        remove_menu_page('freelinguist-admin-email-templates');
        remove_meta_box('dashboard_activity', 'dashboard', 'side');
        add_filter('users_list_table_query_args', 'filter_users_list_table_by_cashier', 10, 1);
        add_action('admin_footer', 'custom_cashier_sub_admin_js');

        add_action('admin_footer', 'custom_js_for_all_sub_admin');
        add_filter('manage_users_columns', 'remove_users_columns_for_cashier');
        maybe_show_error_notice_for_permission('edit.php?post_type=wallet', 'add'); // Wallet add new permission
        maybe_show_error_notice_for_permission('edit.php?post_type=job', 'add'); // Wallet add new permission

        $wpml_plugin_disable_page = array(
            'sitepress-multilingual-cms/menu/languages.php',
            'sitepress-multilingual-cms/menu/theme-localization.php',
            'sitepress-multilingual-cms/menu/translation-options.php',
            'sitepress-multilingual-cms/menu/support.php',
            'sitepress-multilingual-cms/menu/menu-sync/menus-sync.php',
            'sitepress-multilingual-cms/menu/taxonomy-translation.php',
            'freelinguist-admin-string-translation',
            'freelinguist-admin-menu-translation',
            'freelinguist-admin-social-contacts',
            'freelinguist-admin-evaluation',
            'freelinguist-admin-email-templates',
            'freelinguist-admin-evaluation-history',
            'freelinguist-admin-coordination',
            'acui',
            'wp-bruiser-settings',
            'w3tc_dashboard',
            'updraftplus',
            'freelinguist-admin-project-cases',
            'JobRejectionClosed-list-table.php'
        );
        maybe_show_error_notice_for_page($wpml_plugin_disable_page);
        maybe_show_error_notice_for_post(array('faq', 'acf'));

        if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'wallet') {
            if (!isset($_REQUEST['author'])) {
                $ur = 'admin.php?page=freelinguist-admin-check-wallet&lang=en';
                $url = admin_url($ur);
                wp_redirect($url);
                exit;
            } else { ?>
                <style type="text/css">
                    .search-box {
                        display: none !important;
                    }

                    .page-title-action {
                        display: none !important;
                    }

                    .alignleft {
                        display: none !important;
                    }

                </style>
                <?php
            }
        }
        ?>
        <style>
            #menu-dashboard {
                display: none !important;
            }

            .toplevel_page_lingotek-translation {
                display: none !important;
            }

            #toplevel_page_wp-bruiser-settings {
                display: none !important;
            }

            #toplevel_page_edit-post_type-acf {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-social-contacts {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-evaluation {
                display: none !important;
            }

            #menu-posts-wallet {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-email-templates {
                display: none !important;
            }

            #menu-posts-faq {
                display: none !important;
            }

            .subsubsub li {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-send-cashier-message {
                display: none;
            }

            #toplevel_page_sitepress-multilingual-cms-menu-languages {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-evaluation {
                display: none;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none;
            }

            #toplevel_page_freelinguist-admin-string-translation {
                display: none;
            }

            #toplevel_page_freelinguist-admin-menu-translation {
                display: none;
            }

            #wp-admin-bar-w3tc {
                display: none;
            }

            #wp-admin-bar-updraft_admin_node {
                display: none
            }

            #toplevel_page_freelinguist-admin-project-cases {
                display: none !important;
            }

            #toplevel_page_JobRejectionClosed-list-table {
                display: none !important;
            }

            #wp-admin-bar-updates {
                display: none !important;
            }

            .tablenav-pages {
                display: none !important;
            }

            .menu-icon-users li:nth-of-type(4) {
                display: none !important
            }

            .menu-icon-users li:nth-of-type(5) {
                display: none !important
            }
        </style>
        <?php
    } elseif (in_array('evaluation_sub_admin', $user_info->roles)) {
        remove_menu_page('wpcf7');
        remove_menu_page('freelinguist-admin-options');
        remove_menu_page('theme-option-val');
        remove_menu_page('w3tc_dashboard');
        remove_menu_page('options-general.php');
        remove_menu_page('tools.php');
        remove_menu_page('lingotek-translation');
        remove_menu_page('lingotek-translation_manage');
        remove_menu_page('lingotek-translation_settings');
        remove_menu_page('lingotek-translation_tutorial');
        remove_menu_page('edit.php?post_type=acf');
        remove_menu_page('edit.php?post_type=job');
        remove_menu_page('edit.php?post_type=wallet');
        remove_menu_page('edit.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('freelinguist-admin-reminders');
        remove_menu_page('freelinguist-admin-social-contacts');
        remove_menu_page('freelinguist-admin-manual-refill');
        remove_menu_page('freelinguist-admin-email-templates');
        // Hook into the 'wp_dashboard_setup' action to register our function
        remove_meta_box('dashboard_activity', 'dashboard', 'side');
        add_action('admin_footer', 'custom_js_for_all_sub_admin');
        maybe_show_error_notice_for_permission('edit.php?post_type=wallet', 'add'); // Wallet add new permission
        maybe_show_error_notice_for_permission('edit.php?post_type=job', 'add'); // Wallet add new permission
        $wpml_plugin_disable_page = array(
            'sitepress-multilingual-cms/menu/languages.php',
            'sitepress-multilingual-cms/menu/theme-localization.php',
            'sitepress-multilingual-cms/menu/translation-options.php',
            'sitepress-multilingual-cms/menu/support.php',
            'sitepress-multilingual-cms/menu/menu-sync/menus-sync.php',
            'sitepress-multilingual-cms/menu/taxonomy-translation.php',
            'freelinguist-admin-string-translation',
            'freelinguist-admin-menu-translation',
            'freelinguist-admin-email-templates',
            'freelinguist-admin-widthdrawls',
            'freelinguist-admin-coordination',
            'acui',
            'wp-bruiser-settings',
            'w3tc_dashboard',
            'updraftplus',
            'freelinguist-admin-check-wallet',
            'freelinguist-admin-project-cases',
            'JobRejectionClosed-list-table.php'


        );
        maybe_show_error_notice_for_page($wpml_plugin_disable_page);
        maybe_show_error_notice_for_post(array('faq', 'wallet', 'acf'));
        ?>
        <style>
            #menu-dashboard {
                display: none !important;
            }

            .toplevel_page_lingotek-translation {
                display: none !important;
            }

            #toplevel_page_edit-post_type-acf {
                display: none !important;
            }

            #toplevel_page_wp-bruiser-settings {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-social-contacts {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-email-templates {
                display: none !important;
            }

            #menu-posts-faq {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-widthdrawls {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-send-cashier-message {
                display: none;
            }

            #toplevel_page_sitepress-multilingual-cms-menu-languages {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-string-translation {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-menu-translation {
                display: none !important;
            }

            #wp-admin-bar-w3tc {
                display: none !important;
            }

            #wp-admin-bar-updraft_admin_node {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-check-wallet {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-project-cases {
                display: none !important;
            }

            #toplevel_page_JobRejectionClosed-list-table {
                display: none !important;
            }

            #wp-admin-bar-updates {
                display: none !important;
            }

            .menu-icon-users li:nth-of-type(4) {
                display: none !important
            }

            .menu-icon-users li:nth-of-type(5) {
                display: none !important
            }
        </style>
        <?php
    } elseif (in_array('message_sub_admin', $user_info->roles)) {
        remove_menu_page('wpcf7');
        remove_menu_page('freelinguist-admin-options');
        remove_menu_page('theme-option-val');
        remove_menu_page('w3tc_dashboard');
        remove_menu_page('options-general.php');
        remove_menu_page('tools.php');
        remove_menu_page('lingotek-translation');
        remove_menu_page('lingotek-translation_manage');
        remove_menu_page('lingotek-translation_settings');
        remove_menu_page('lingotek-translation_tutorial');
        remove_menu_page('edit.php?post_type=acf');
        //remove_menu_page( 'edit.php?post_type=job' );
        remove_menu_page('edit.php?post_type=wallet');
        remove_menu_page('edit.php');
        remove_menu_page('upload.php');
        remove_menu_page('freelinguist-admin-reminders');
        remove_menu_page('freelinguist-admin-social-contacts');
        remove_menu_page('freelinguist-admin-evaluation.php');
        // Hook into the 'wp_dashboard_setup' action to register our function
        remove_meta_box('dashboard_activity', 'dashboard', 'side');
        add_action('admin_footer', 'custom_evalution_sub_admin_js');
        add_action('admin_head', 'message_sub_admin_link_hide');
        add_action('admin_footer', 'custom_js_for_all_sub_admin');
        maybe_show_error_notice_for_permission('edit.php?post_type=wallet', 'add'); // Wallet add new permission
        maybe_show_error_notice_for_permission('edit.php?post_type=job', 'add'); // Wallet add new permission
        $wpml_plugin_disable_page = array(
            'sitepress-multilingual-cms/menu/languages.php',
            'sitepress-multilingual-cms/menu/theme-localization.php',
            'sitepress-multilingual-cms/menu/translation-options.php',
            'sitepress-multilingual-cms/menu/support.php',
            'sitepress-multilingual-cms/menu/menu-sync/menus-sync.php',
            'sitepress-multilingual-cms/menu/taxonomy-translation.php',
            'freelinguist-admin-string-translation',
            'freelinguist-admin-menu-translation',
            'freelinguist-admin-evaluation-history',
            'freelinguist-admin-coordination',
            'acui',
            'wp-bruiser-settings',
            'w3tc_dashboard',
            'updraftplus',
            'freelinguist-admin-check-wallet',
            'freelinguist-admin-project-cases',
            'JobRejectionClosed-list-table.php'
        );
        maybe_show_error_notice_for_page($wpml_plugin_disable_page);
        if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'job') {
            if (!isset($_REQUEST['author'])) {
                ?>
                <style type="text/css">
                    .page-title-action {
                        display: none !important;
                    }

                </style>
                <?php
            }
        }
        ?>

        <style>
            #menu-dashboard {
                display: none !important;
            }

            .toplevel_page_lingotek-translation {
                display: none !important;
            }

            #toplevel_page_wp-bruiser-settings {
                display: none !important;
            }

            #toplevel_page_edit-post_type-acf {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-social-contacts {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-evaluation {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-manual-refill {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-email-templates {
                display: none !important;
            }

            #menu-posts-faq {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-widthdrawls {
                display: none !important;
            }

            #toplevel_page_sitepress-multilingual-cms-menu-languages {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-evaluation {
                display: none;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none;
            }

            #comments-form .bulkactions {
                display: none !important;
            }

            #posts-filter .bulkactions {
                display: none !important;
            }

            .edit-comments-php .subsubsub {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none;
            }

            #toplevel_page_freelinguist-admin-string-translation {
                display: none;
            }

            #toplevel_page_freelinguist-admin-menu-translation {
                display: none;
            }

            #wp-admin-bar-w3tc {
                display: none;
            }

            #wp-admin-bar-updraft_admin_node {
                display: none
            }



            #toplevel_page_freelinguist-admin-check-wallet {
                display: none;
            }

            #toplevel_page_freelinguist-admin-project-cases {
                display: none !important;
            }

            #toplevel_page_JobRejectionClosed-list-table {
                display: none !important;
            }

            #wp-admin-bar-updates {
                display: none !important;
            }

            .menu-icon-users li:nth-of-type(4) {
                display: none !important
            }

            .menu-icon-users li:nth-of-type(5) {
                display: none !important
            }
        </style>
        <?php
    } elseif (in_array('meditation_sub_admin', $user_info->roles)) {
        remove_menu_page('wpcf7');
        remove_menu_page('freelinguist-admin-options');
        remove_menu_page('theme-option-val');
        remove_menu_page('w3tc_dashboard');
        remove_menu_page('options-general.php');
        remove_menu_page('tools.php');
        remove_menu_page('lingotek-translation');
        remove_menu_page('lingotek-translation_manage');
        remove_menu_page('lingotek-translation_settings');
        remove_menu_page('lingotek-translation_tutorial');
        remove_menu_page('edit.php?post_type=acf');
        remove_menu_page('edit.php?post_type=job');
        remove_menu_page('edit.php?post_type=wallet');
        remove_menu_page('edit.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('freelinguist-admin-reminders');
        remove_menu_page('freelinguist-admin-social-contacts');
        remove_menu_page('freelinguist-admin-manual-refill');
        remove_menu_page('freelinguist-admin-email-templates');
        // Hook into the 'wp_dashboard_setup' action to register our function
        remove_meta_box('dashboard_activity', 'dashboard', 'side');
        add_action('admin_footer', 'custom_js_for_all_sub_admin');
        maybe_show_error_notice_for_permission('edit.php?post_type=wallet', 'add'); // Wallet add new permission
        maybe_show_error_notice_for_permission('edit.php?post_type=job', 'add'); // Wallet add new permission
        $wpml_plugin_disable_page = array(
            'sitepress-multilingual-cms/menu/languages.php',
            'sitepress-multilingual-cms/menu/theme-localization.php',
            'sitepress-multilingual-cms/menu/translation-options.php',
            'sitepress-multilingual-cms/menu/support.php',
            'sitepress-multilingual-cms/menu/menu-sync/menus-sync.php',
            'sitepress-multilingual-cms/menu/taxonomy-translation.php',
            'freelinguist-admin-string-translation',
            'freelinguist-admin-menu-translation',
            'freelinguist-admin-email-templates',
            'freelinguist-admin-widthdrawls',
            'freelinguist-admin-coordination',
            'acui',
            'wp-bruiser-settings',
            'w3tc_dashboard',
            'updraftplus',
            'freelinguist-admin-check-wallet',
            'freelinguist-admin-user-wallet'


        );
        maybe_show_error_notice_for_page($wpml_plugin_disable_page);
        maybe_show_error_notice_for_post(array('faq', 'wallet', 'acf'));
        ?>
        <style>
            #menu-dashboard {
                display: none !important;
            }

            .toplevel_page_lingotek-translation {
                display: none !important;
            }

            #toplevel_page_edit-post_type-acf {
                display: none !important;
            }

            #toplevel_page_wp-bruiser-settings {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-social-contacts {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-email-templates {
                display: none !important;
            }

            #menu-posts-faq {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-widthdrawls {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-send-cashier-message {
                display: none;
            }

            #toplevel_page_sitepress-multilingual-cms-menu-languages {
                display: none !important;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none;
            }

            #toplevel_page_freelinguist-admin-coordination {
                display: none;
            }

            #toplevel_page_freelinguist-admin-string-translation {
                display: none;
            }

            #toplevel_page_freelinguist-admin-menu-translation {
                display: none;
            }

            #wp-admin-bar-w3tc {
                display: none;
            }

            #wp-admin-bar-updraft_admin_node {
                display: none
            }

            #toplevel_page_freelinguist-admin-check-wallet {
                display: none;
            }

            #wp-admin-bar-updates {
                display: none !important;
            }

            #toplevel_page_CheckWallet-list-table {
                display: none !important;
            }
        </style>
        <!--<style>
        #toplevel_page_CheckWallet-list-table{ display: none !important;}
        </style>-->
        <?php
    }

}

//helper functions only for above

function custom_js_for_all_sub_admin()
{
    ?>
    <script>
        jQuery(function () {
            jQuery("#wp-admin-bar-new-content").html(' ');
            jQuery("#wp-admin-bar-comments").html(' ');
        });
    </script>
    <?php
}

function custom_super_sub_admin_js() {

    if(!isset($_REQUEST['s'])){ ?>
        <script type="text/javascript">
            jQuery(function(){
                jQuery(".users").html('<span style="text-align: center"><span class="bold-and-blocking large-text">Search user</span></span>');
                jQuery(".users").siblings('.tablenav').html(' ');
            });
        </script>
    <?php } ?>
    <script type="text/javascript">
        jQuery(function(){
            jQuery(".wp-list-table .type-wallet .page-title").find('a').attr("href", "#");
        });
    </script>
    <?php

}


function custom_evalution_sub_admin_js() {
    ?>
    <script>
        jQuery(function(){
            jQuery("#savebtn").click(function(){
                jQuery("#comments-form").find('.reply').html(' ');
                jQuery("#comments-form").find('.edit').html(' ');
                var data = {'action': 'update_message_revison_history','comment_id': jQuery('#comment_ID').val()};
                //alert(jQuery('#comment_ID').val());
                jQuery.post(ajaxurl, data, function(response) {
                    location.reload();
                    console.log(response);
                });
                setInterval(function(){
                    jQuery("#comments-form").find('.reply').html(' ');
                    jQuery("#comments-form").find('.edit').html(' ');
                    jQuery("#comments-form").find('.column-author a').html(' ');
                    jQuery("#comments-form").find('.comments-view-item-link').html(' ');
                }, 1000);
            });
            jQuery("#comments-form").find('.reply').html(' ');
            jQuery("#comments-form").find('.edit').html(' ');
            jQuery("#comments-form").find('.column-author a').html(' ');
            jQuery("#comments-form").find('.comments-view-item-link').html(' ');
        });
    </script>
    <?php
}



function custom_cashier_sub_admin_js() {
    ?>
    <script>
        jQuery(function(){
            jQuery(".wp-list-table .column-email span:first").html('Send Message');
        });
        var admin_urlis = '<?php echo admin_url("admin.php?page=freelinguist-admin-send-cashier-message"); ?>';
        jQuery('table tbody tr').each(function(){
            let link = jQuery(this);
            var result = link.attr('id').split('-');
            if(result[1] !== undefined){

                if (!jQuery(link.find('td a')).hasClass("downloaed_form")) {
                    link.find('td a').attr('href',admin_urlis+'&user_is='+result[1]);
                }

            }
        });
    </script>
    <?php if(!isset($_REQUEST['s'])){ ?>
        <script type="text/javascript">
            jQuery(function(){
                jQuery(".users").html('<span style="text-align: center"><span class="bold-and-blocking large-text">Search user</span></span>');
                jQuery(".users").siblings('.tablenav').html(' ');
            });
        </script>
        <?php
    }
}


function message_sub_admin_link_hide(){
    $screen = get_current_screen();
    if($screen->base == 'edit-comments'){ ?>
        <script>
            jQuery(function(){
                jQuery(".wp-list-table").find(".comments-edit-item-link").attr("href", "#");
                jQuery(".wp-list-table").find(".column-date .submitted-on a").attr("href", "#");
                jQuery(".wp-list-table").find(".column-comment a").attr("href", "#");
                jQuery(".wp-list-table").find(".post-com-count-approved").attr("href", "#");

            });
        </script>
        <?php
    }
    ?>

    <?php
}


// define the users_list_table_query_args callback
function filter_users_list_table_by_cashier( $args ) {
    // make filter magic happen here...
    $args['role__in'] =  array('translator','customer');
    $args['include'] =  getReportedUserByUserId();
    $args['number'] =  '5';
    return $args;
};


function remove_users_columns_for_cashier($column_headers) {
    unset($column_headers['name']);
    unset($column_headers['posts']);
    unset($column_headers['roles']);
    return $column_headers;
}


function maybe_show_error_notice_for_permission($parent_page, $action)
{
    //echo $parent_page; exit;
    add_action('admin_notices', function () use ($parent_page, $action) {
        $screen = get_current_screen();
        if ($screen->parent_file == $parent_page && $screen->action == 'add') {
            wp_die(__('You do not have sufficient permissions to access this page.'), 403);
        }
    });
}

//maybe_show_error_notice_for_post
function maybe_show_error_notice_for_page($is_pages)
{
    //echo $parent_page; exit;
    if (isset($_REQUEST['page'])) {
        if (in_array($_REQUEST['page'], $is_pages)) {
            wp_die(__('You do not have sufficient permissions to access this page.'), 403);
        }
    }
}

function maybe_show_error_notice_for_post($post_type)
{
    //echo $parent_page; exit;
    if (isset($_REQUEST['post_type'])) {
        if (in_array($_REQUEST['post_type'], $post_type)) {
            wp_die(__('You do not have sufficient permissions to access this page.'), 403);
        }
    }
}