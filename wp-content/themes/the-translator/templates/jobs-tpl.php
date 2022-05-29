<?php
/*
Template Name: Job Listing Page
*/

/*
* current-php-code 2020-Oct-10
* input-sanitized : curRole,is,type
* current-wp-template:  searching jobs for customers and freelancers
 * current-wp-top-template
*/
$type = FLInput::get('type');
$is = FLInput::get('is');
$current_role = FLInput::get('curRole');
ob_start();
check_login_redirection();

if (is_user_logged_in() && (xt_user_role() == "customer")) {

    $u = new WP_User(get_current_user_id());
    will_send_to_error_log('jobs template was customer now translator  ');
    $u->remove_role("customer");

    $u->add_role('translator');

}
if (is_user_logged_in() && (xt_user_role() == "translator")) : ?>
    <?php

        get_header();
        get_template_part('includes/user/jobs/jobs-my', 'all');
        get_footer('homepagenew');

    ?>
<?php
else:
    if (is_user_logged_in() && (xt_user_role() == "customer")) {

        $u = new WP_User(get_current_user_id());
        will_send_to_error_log('jobs template was customer now translator  ', $_POST);
        $u->remove_role($current_role);

        $u->add_role('translator');

    } else {
        wp_redirect(home_url());
    }
endif;




