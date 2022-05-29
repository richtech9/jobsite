<?php
/*
Template Name: Setting Template
*/
/*
* current-php-code 2020-Oct-16
* input-sanitized :
* current-wp-template:  settings for users
* current-wp-top-template
*/
ob_start();
check_login_redirection();
add_action( 'wp_head', 'add_meta_tags' , 2 ); // Cache clear
if(current_user_can('administrator')){
	wp_redirect(admin_url());
}
get_header();

if(is_user_logged_in() && (xt_user_role() == "translator")){
	get_template_part('includes/user/setting/setting', 'translator');
}elseif(is_user_logged_in() && (xt_user_role() == "customer")){
	get_template_part('includes/user/setting/setting', 'customer');
}else{
	exit;
}
?>

<?php get_footer('homepagenew');

