<?php

/*
 * Method:      getAssignCountryTo
 * Author:      Lakhvinder Singh
 * Description: getAssignCountryTo
 *
 */
add_action('wp_ajax_getAssignCountryTo', 'getAssignCountryTo');
function getAssignCountryTo(){

    /*
    * current-php-code 2021-Jan-11
    * ajax-endpoint  hz_conStatus_save
    * input-sanitized : country_from
    */

    if (!current_user_can('manage_options')) {
        exit;
    }

    $key = (int)FLInput::get('country_from');

    $html               = '<select name="assign_country_to" style="width:80%" id="assign_country_to">';
    $countries = get_countries();
    for($i=$key;$i<count($countries);$i++) {
        $value           = $countries[$i];
        $html           .= '<option value="'.$i.'">'.$value.'</option>';
    }
    $html               .= '</select>';
    echo $html;
    exit;
}