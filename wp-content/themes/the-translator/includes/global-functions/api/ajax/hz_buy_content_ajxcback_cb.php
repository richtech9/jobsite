<?php

add_action( 'wp_ajax_hz_buy_content_ajxcback',  'hz_buy_content_ajxcback_cb' );

 function hz_buy_content_ajxcback_cb(){

     /*
    * current-php-code 2020-Oct-14
    * ajax-endpoint  hz_buy_content_ajxcback
    * input-sanitized :
    */
    //code-bookmark Here is regular purchase by for content
    FreelinguistContentHelper::hz_buy_content_ajxcback_cb();
}