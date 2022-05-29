<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_display_name

 * Description: update_display_name

 *

 */

add_action('wp_ajax_update_display_name', 'update_display_name');


function update_display_name(){

    /*
    * current-php-code 2020-Oct-16
    * ajax-endpoint  update_display_name
    * input-sanitized : display_name
    */

    $display_name = FLInput::get('display_name');
    
    if(isset($display_name)){
        $user_id = get_current_user_id();
        wp_update_user( array( 'ID' => $user_id, 'display_name' => strip_tags($display_name ) ));
        FreelinguistUserHelper::update_elastic_index($user_id);


        //code-notes update units
        //code-notes get the ids of the content without parents this user has made and pass that in too
        $content_ids = FreelinguistContentHelper::get_original_content_ids_by_user($user_id,false);
        FreelinguistUnitGenerator::generate_units($log,[$user_id],$content_ids);

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}