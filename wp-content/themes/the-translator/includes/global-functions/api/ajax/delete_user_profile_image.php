<?php


/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_user_profile_image

 * Description: delete_user_profile_image

 *

 */

add_action('wp_ajax_delete_user_profile_image', 'delete_user_profile_image');


/**
 * will always delete the user image entry, even if cannot find the file
 */
function delete_user_profile_image(){

    /*
   * current-php-code 2020-Oct-31
   * ajax-endpoint  delete_user_profile_image
   * input-sanitized :
   */

     delete_logged_in_user_profile_image_internal(true);
     echo 'success';

    exit();


}