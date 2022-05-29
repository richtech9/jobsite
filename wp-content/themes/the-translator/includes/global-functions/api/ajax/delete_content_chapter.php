<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_content_chapter

 * Description: delete_content_chapter

 *

 */

add_action('wp_ajax_delete_content_chapter', 'delete_content_chapter');

/*
 * code-notes Only allow if the user is the creator of the chapter
 */
function delete_content_chapter(){
    /*
       * current-php-code 2020-Oct-13
       * ajax-endpoint  delete_content_chapter
       * input-sanitized : delete_content_chapter_id
       */
    if(get_current_user_id()){

        global $wpdb;
        $content_chapter_id = (int)FLInput::get('delete_content_chapter_id',0);

        $return = array();

        $user_id 	= get_current_user_id();

        $content_chapter_detail = $wpdb->get_row(
            "select * from wp_linguist_content_chapter 
                    where id=$content_chapter_id AND user_id = $user_id",
            ARRAY_A);



        if(empty($content_chapter_detail)){

            $return['message'] = get_custom_string_return('This chapter not exist or user does not own it');

            $return['status'] = false;

            $return['scrollToElement'] = true;

            wp_send_json($return);

            exit;

        }else{

            if($content_chapter_detail['user_id'] != get_current_user_id()){

                $return['message'] = get_custom_string_return('You are unautorized user');

                $return['status'] = false;

                $return['scrollToElement'] = true;

                 wp_send_json($return);

                exit;

            }

            $content_chapter_detail = $wpdb->get_row( "select * from wp_linguist_content_chapter where user_id IS NOT NULL AND id=$content_chapter_id",ARRAY_A);

            $wpdb->delete(  'wp_linguist_content_chapter',  array( 'id' => $content_chapter_detail['id'] ) );

            $return['message'] = get_custom_string_return('You are successfully deleted');

            $return['status'] = true;

        }

    }else{

        $return['message'] = get_custom_string_return('Please login/register first');

        $return['status'] = false;

    }

    $return['scrollToElement'] = true;

    wp_send_json($return);

    exit;

}