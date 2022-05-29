<?php

add_action('init', 'admin_language');
function admin_language()
{
    /*
     * current-php-code 2020-Jan-15
     * current-hook
     * input-sanitized :
     */

    if (isset($_REQUEST['page']) &&
        (
            strpos($_REQUEST['page'], 'w3tc_general') !== false ||
            strpos($_REQUEST['page'], 'wpcf7') !== false ||
            strpos($_REQUEST['page'], 'sitepress-multilingual-cms') !== false ||
            strpos($_REQUEST['page'], 'wordpress-social-login') !== false
        )
    ) {
        will_do_nothing("empty statement");
    } else {
        if (!isset($_REQUEST['lang'])) {

            if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/user-edit.php') === false) {
                $base_url_is = $_SERVER['SERVER_NAME'];
                $url = $_SERVER['REQUEST_URI'];
                $url_list = explode('/', $url);
                $https = (isset($_SERVER['HTTPS']) ? "https" : "http");

                //code-notes when disabling the WPML Multilingual CMS we have no need to redirect and drop post data
                //code-notes do not redirect posts, the post data gets dropped
                if (function_exists('icl_get_languages') && empty($_POST)) {
                    if (
                        in_array("wp-admin", $url_list) &&
                        !in_array('admin-ajax.php', $url_list) &&
                        !in_array('plugins.php', $url_list) &&
                        !in_array('options-general.php?settings-updated=true', $url_list) &&
                        !in_array('options.php', $url_list) &&
                        !in_array('options-general.php', $url_list) &&
                        !in_array('async-upload.php', $url_list) &&
                        !in_array('media-new.php', $url_list) &&
                        !in_array('post.php', $url_list)) {
                        if (empty($_SERVER['QUERY_STRING'])) {
                            $lang_ur = $https . '://' . $base_url_is . $url . '?lang=en';
                            wp_redirect($lang_ur);
                            exit;
                        } else {

                            $lang_ur = $https . '://' . $base_url_is . $url . '&lang=en';
                            wp_redirect($lang_ur);
                            exit;
                        }
                    }
                }


            }

        }
    }


    $url = $_SERVER['REQUEST_URI'];
    $url_list = explode('/', $url);

    //code-notes do not redirect posts, the post data gets dropped
    if (in_array("wp-admin", $url_list) && !in_array('admin-ajax.php', $url_list) && empty($_POST)) {
        if (xt_user_role() == "translator" || xt_user_role() == "customer") {
            $lang_ur = get_site_url();
            wp_redirect($lang_ur);
            exit;
        }
    }
}