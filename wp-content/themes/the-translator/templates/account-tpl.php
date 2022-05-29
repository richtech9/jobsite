<?php
/*
Template Name: Account Template
*/

/*
* current-php-code 2020-Oct-15
* input-sanitized :
* current-wp-template:  profile settings for customer and translator
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
	get_template_part('includes/user/account/account', 'translator');
}elseif(is_user_logged_in() && (xt_user_role() == "customer")){
	get_template_part('includes/user/account/account', 'customer');
}else{
	exit;
}
?>
<?php get_footer('homepagenew'); ?>