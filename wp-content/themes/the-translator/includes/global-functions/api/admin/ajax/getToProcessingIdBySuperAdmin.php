<?php

/*
 * Author Name: Lakhvinder Singh
 * Method:      getToCountryBySuperAdmin
 * Description: getToCountryBySuperAdmin
 *
 */
add_action('wp_ajax_getToProcessingIdBySuperAdmin', 'getToProcessingIdBySuperAdmin');

function getToProcessingIdBySuperAdmin(){

    /*
      * current-php-code 2021-Jan-11
      * ajax-endpoint  getToProcessingIdBySuperAdmin
      * input-sanitized : from_processing_id
      */

    if (!current_user_can('manage_options')) {
        exit;
    }

    $from_processing_id = (int)FLInput::get('from_processing_id');

    $html               = '<select class="dont_use_selectpicker" id="to_processing_id" name="to_processing_id">';
    $all_processing_id = get_processing_id();

    for($i = $from_processing_id; $i<count($all_processing_id); $i++) {
        $value        = $all_processing_id[$i];
        $html        .= '<option value="'.$i.'">'.$value.'</option>';
    }
    $html               .= '</select><p class="description"> To Processing ID</p>';
    echo $html;
    exit;
}