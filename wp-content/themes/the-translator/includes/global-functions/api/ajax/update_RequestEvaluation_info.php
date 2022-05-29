<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_RequestEvaluation_info

 * Description: update_RequestEvaluation_info

 *

 */

add_action('wp_ajax_update_RequestEvaluation_info', 'update_RequestEvaluation_info'); //


/**
 * @see AdminPageEvaluations  ,only called by a dialog which has been hidden
 */
function update_RequestEvaluation_info(){ //code-unused, however this is the only way to create a evaluation request for the evaluation admin screen

    /*
    * current-php-code 2020-Oct-15
    * ajax-endpoint  update_RequestEvaluation_info
    * input-sanitized :  RequestEvaluation_description
    */

    $description = FLInput::get('RequestEvaluation_description');

    if(isset($_REQUEST['RequestEvaluation_description'])){

        $user = get_userdata(get_current_user_id());

        $variables = array();

        $variables['name'] = $user->display_name;

        emailTemplateForUser($_REQUEST['new_email'],NEW_REQUEST_EVALUTION_TEMPLATE,$variables);

        update_user_meta( get_current_user_id(), 'request_evaluating','Yes' );

        update_user_meta( get_current_user_id(), 'request_evaluation_description', $description);

        update_user_meta( get_current_user_id(), 'request_evaluation_date', date('Y-m-d'));

        echo 'success';

        exit;

    }else{

        echo 'failed';

        exit;

    }

}