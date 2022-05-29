<?php
/**
 * @package Freelinguist_Pre_Flight_Check
 * @version 1.1.1
 */
/*
Plugin Name: PeerOK Pre Flight Check
Plugin URI: test.com
Description: This tiny plugin sets up data to be read from GET and POST without magic quotes. And copies the post and get and cookie super globals, to memory only, before WP puts slashes in front of the quotes. Then the FLInput can use these copies to get the unaltered data. Preflight also adds early hooks before the theme is loaded. Never remove this plugin. It's essential for proper function of this website
Author: Freelinguist
Version: 1.1.1
Author URI: http//:test.com
*/

/**
 * @version-history
 * @1.0.0 Original Setup
 * @1.1.0 Stopped doing full checks on every admin page
 * @1.1.1 2021-March-25 Now returns early if the theme is not installed
 */


/**
 * @logic (this is changed in new version which disables checks. We now always just load in the Input file quietly)
 *      Since this plugin is run for every single operation of wordpress (all pages, posts, ajax) we only want to do the full checks
 * if we are on an admin page, and we are not running ajax.
 *
 * IF we are on an admin page, and not running ajax, do the checks, to show in the admin notices
 *
 * Else, try to include the input file, and quietly log if we cannot
 *
 * Either way, we need to load in the FreeLinguistPreFlightCheck first, which may not even exist,
 * and cannot crash anything if we do not find it
 */


function freelinguist_run_preflight_plugin()
{
    //see if theme is activated first, if not, then quietly go away

    $b_ok = FreelinguistPreflightLoad();
    if (!$b_ok) {return;}
    //code-notes should only run here when testing, else will slow down everything. Use the cron job for the usual checking
    $n_run_option = (int)get_option('freelinguist_preflight_check_full',0);
    if ($n_run_option && is_admin() && !wp_doing_ajax()) {
        //do checks and show on admin screen
        FreeLinguistPreFlightCheck::make_admin_notices();
        FreeLinguistPreFlightCheck::log_notices();
        FreeLinguistPreFlightCheck::run_checks(); //will make admin notices if anything wrong
    }
}

function FreelinguistPreflightLoad() {


    $b_did_not_load_in_class = true;
    $path_we_want = ABSPATH . 'wp-content/themes/the-translator/includes/global-functions/global-hooks/FreelinguistPreFlightCheck.php';
    $real_path_just_in_case = realpath($path_we_want);
    $b_can_read_debug_file = is_readable($real_path_just_in_case);
    if ($b_can_read_debug_file) {
        /** @noinspection PhpIncludeInspection */
        require_once $real_path_just_in_case;
        $b_did_not_load_in_class = false;
    }

    if ($b_did_not_load_in_class) {
        error_log("FreelinguistPreflight fail, could not load $path_we_want");
        return false;
    }
    FreeLinguistPreFlightCheck::do_not_do_admin_notices();
    FreeLinguistPreFlightCheck::log_notices();
    $path_we_want = ABSPATH . 'wp-content/themes/the-translator/includes/global-functions/internal/sitewide/will-debug.php';
    FreeLinguistPreFlightCheck::require_file($path_we_want, ['input']);
    $path_we_want = ABSPATH . 'wp-content/themes/the-translator/includes/global-functions/internal/sitewide/FLInput.php';
    FreeLinguistPreFlightCheck::require_file($path_we_want, ['']);
    $path_we_want = ABSPATH . 'wp-content/themes/the-translator/includes/global-functions/global-hooks/parse_query.php';
    FreeLinguistPreFlightCheck::require_file($path_we_want, ['']);
    return true;
}

freelinguist_run_preflight_plugin();  //the one and only entry point to this plugin, defined above






