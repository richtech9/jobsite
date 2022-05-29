<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      email_the_form

 * Description: email_the_form

 *

 */

add_action('wp_ajax_email_the_form', 'email_the_form');


function email_the_form(){

      /*
       * current-php-code 2020-Dec-22
       * ajax-endpoint  email_the_form
       * input-sanitized :form_id
       */

    $form_id = (int)FLInput::get('form_id');
    $upload_dir = wp_upload_dir();

    $user_dirname = $upload_dir['basedir'];

    if($form_id){

        if($form_id == '1'){

          //  $file_path  = get_option('w_9_form_filePath');

         //   $file = $user_dirname.'/'.$file_path;

            $attachments = [];

            $user_data = get_userdata( get_current_user_id() );

            $variables = array();

            emailTemplateForUser($user_data->user_email,W9FORM_TEMPLATE,$variables,$attachments);


        }elseif($form_id == '2'){

            $file_path = get_option('w_8ben_form_filePath');


            $file = $user_dirname.'/'.$file_path;

            $attachments = array($file );

            $user_data = get_userdata( get_current_user_id() );

            $variables = array();

            emailTemplateForUser($user_data->user_email,W8BEN_TEMPLATE,$variables,$attachments);

        }else{

            $file_path = get_option('tax_form_filePath');

            $file = $user_dirname.'/'.$file_path;

            $attachments = array($file );

            $user_data = get_userdata( get_current_user_id() );

            $variables = array();

            emailTemplateForUser($user_data->user_email,TAXFORM_TEMPLATE,$variables,$attachments);

        }

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}