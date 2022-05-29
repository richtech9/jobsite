<?php
/*
Template Name: Account Activation Template
*/
//https://coneldllcstaging.peerok.com/activation/?lang=en&key=e6b1d5572d90c28af477b9d9bc31bc01e2fb363f&user=3597

/*
* current-php-code 2020-Oct-16
* input-sanitized : key,user
* current-wp-template:  searching jobs for customers and freelancers
 * current-wp-top-template
 *
 * task-future-work make this a static url for the activation page
*/

$key = FLInput::get('key');
$user = (int)FLInput::get('user');
$email_activitation_key = (int)FLInput::get('email_activitation_key');

$msg = "";
if($key && $user){
	$meta 	= get_user_meta($user,'has_to_be_activated', true);
	$_utype = get_user_meta($user,'_user_type', true);
	if($meta == $key && !empty($meta)){
		update_user_meta($user, 'has_to_be_activated', "");
		$_user = new WP_User($user);
		$_user->remove_role( 'subscriber' );
		$_user->add_role($_utype);
		$msg = "Your Profile is successfully activated";
	}else{
		wp_redirect(home_url());
	}
}elseif($email_activitation_key && $user){
	$key 	= $email_activitation_key;
	$meta 	= get_user_meta($user,'has_to_be_activated_email', true);
	$user_new_email = get_user_meta($user,'user_new_email', true);
	if($meta == $key && !empty($meta)){
		update_user_meta($user, 'has_to_be_activated_email', "");
		wp_update_user( array( 'ID' => $user, 'user_email' => $user_new_email ) ); 
		$msg = "Your email is changed successfully";
	}else{
		wp_redirect(home_url());
	}
}else{
	wp_redirect(home_url());
	exit;
}

get_header();
?>
<section class="middle-content">
	<div class="container"> 
		<?php

		echo $msg;
		?>

	</div>
</section>
<?php get_footer('homepagenew'); ?>