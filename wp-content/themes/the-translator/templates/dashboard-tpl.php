<?php
/*
Template Name: Dashboard Template
*/

	/*
	* current-php-code 2020-Sep-30
	* input-sanitized : lang,job_id
	* current-wp-template:  dashboard for customer and freelancer
	* current-wp-top-template
	*/

$lang = FLInput::get('lang', 'en');
check_login_redirection_home($lang);
if(current_user_can('administrator')){
	wp_redirect(admin_url());
}
get_header();
//echo "Current User Role: ".xt_user_role();
add_action( 'wp_head', 'add_meta_tags' , 2 ); // Cache clear
?>
<?php
if(is_user_logged_in() && (xt_user_role() == "translator")){
	get_template_part('includes/user/dashboard/dashboard', 'translator');
}elseif(is_user_logged_in() && (xt_user_role() == "customer")){
	get_template_part('includes/user/dashboard/dashboard', 'customer');
}else{
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	get_template_part( 404 ); exit();
}
?>
<?php get_footer('homepagenew'); ?>