<?php
/*
Template Name: Linguist Support Template
*/

/*
* current-php-code 2021-Feb-10
* input-sanitized :
* current-wp-template:  redirect from the homepage faq link to the faqs
* current-wp-top-template
*/

get_header();
add_action( 'wp_head', 'add_meta_tags' , 2 ); // Cache clear
wp_redirect('/peerok-faq');
exit();

//code-notes the linguist homepage links to here via support-for-linguists
