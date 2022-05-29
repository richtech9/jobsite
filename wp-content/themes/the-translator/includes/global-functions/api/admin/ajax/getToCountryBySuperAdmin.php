<?php

/*
 * Author Name: Lakhvinder Singh
 * Method:      getToCountryBySuperAdmin
 * Description: getToCountryBySuperAdmin
 *
 */
add_action('wp_ajax_getToCountryBySuperAdmin', 'getToCountryBySuperAdmin');
function getToCountryBySuperAdmin(){

    /*
  * current-php-code 2021-Jan-11
  * ajax-endpoint  hz_conStatus_save
  * input-sanitized : country_from
  */

    if (!current_user_can('manage_options')) {
        exit;
    }

    $from_country = (int)FLInput::get('country_from');

    $html               = '<select class="dont_use_selectpicker" id="country_to" name="country_to" title="country to">';
    $all_countries = get_countries();

    $country_to = get_user_meta( get_current_user_id(), 'assign_country_to', true );

    $user_country_slice = array_slice($all_countries, $from_country, $country_to-$from_country+1,true);

    foreach ($user_country_slice as $key => $value) {
        $html        .= '<option value="'.$key.'">'.$value.'</option>';
    }
    $html               .= '</select><p class="description"> To Country</p>';
    echo $html;
    exit;
}