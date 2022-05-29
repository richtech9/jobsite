<?php
/*
Template Name: User account Info
*/

/*
* current-php-code 2020-Oct-17
* input-sanitized : profile_type, user
* current-wp-template:  Shows  Profile
* current-wp-top-template
*/
$user_login_asked_for = FLInput::get('user');
$profile_type = FLInput::get('profile_type');
ob_start();
get_header();

if($user_login_asked_for && !empty(get_user_by( 'login',$user_login_asked_for))){

    switch ($profile_type) {
        case 'translator': {
            get_template_part('includes/user/author-user-info/author-user-info', 'translator');
            break;
        }
        case 'customer' : {
            get_template_part('includes/user/author-user-info/author-user-info', 'customer');
            break;
        }
        default: {
            if (empty($profile_type)) {
                get_template_part('includes/user/author-user-info/author-user-info', 'translator');
                //default is translator profile
            } else {
                //profile_type=translator
                will_dump(' profile_type is unknown: use translator or customer ',$profile_type);
            }

        }
    }
}else{
    will_dump('no valid user');
}

get_footer('homepagenew');
