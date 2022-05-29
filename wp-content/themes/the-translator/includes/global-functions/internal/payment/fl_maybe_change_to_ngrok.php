<?php


add_filter('site_url', 'fl_maybe_change_to_ngrok', 100, 4);
add_filter('home_url', 'fl_maybe_change_to_ngrok', 100, 4);
add_filter('bloginfo_url', 'fl_maybe_change_to_ngrok', 100, 1);
add_filter('bloginfo', 'fl_maybe_change_to_ngrok', 100, 1);
add_filter('script_loader_src', 'fl_maybe_change_to_ngrok', 100, 2);
add_filter('style_loader_src', 'fl_maybe_change_to_ngrok', 100, 2);
add_filter('template_directory_uri', 'fl_maybe_change_to_ngrok', 100, 3);
add_filter('stylesheet_directory_uri', 'fl_maybe_change_to_ngrok', 100, 3);
add_filter('get_site_icon_url', 'fl_maybe_change_to_ngrok', 100, 3);




function fl_maybe_change_to_ngrok($url, $path = null, $scheme = null, $blog_id= null) {
//    will_send_to_error_log("my site url",[
//        $url, $path, $scheme, $blog_id
//    ]);
    /*
    * current-php-code 2021-Jan-24
    * internal-call
    * input-sanitized :
   */

    if( is_admin() && !wp_doing_ajax() ) {
        //will_send_to_error_log('early return (admin)');
        return $url;
    }



    $ngrok_url_for_testing = trim(get_option('fl_ngrok_testing',''));
    //will_send_to_error_log('ngrok url',$ngrok_url_for_testing);
    if (!$ngrok_url_for_testing) {
        //will_send_to_error_log('early return (no grok)');
        return $url;
    }

    $site_url = get_option( 'siteurl','' );
    if (empty($site_url)) {
        //will_send_to_error_log('early return (empty site url)');
        return $url;
    }
    if ($ngrok_url_for_testing) {
        $safety_first = trim($ngrok_url_for_testing,'/');
        //will_send_to_error_log('safety url',$safety_first);
        $new_url =  str_replace($site_url,$safety_first,$url);
        //will_send_to_error_log('new url',$new_url);
        return $new_url;
    } else {
        return $url;
    }
}