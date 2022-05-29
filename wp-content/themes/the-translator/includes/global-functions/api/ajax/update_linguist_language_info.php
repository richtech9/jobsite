<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      update_linguist_language_info

 * Description: update_linguist_language_info

 *

 */

add_action('wp_ajax_update_linguist_language_info', 'update_linguist_language_info');


function update_linguist_language_info(){

    /*
    * current-php-code 2020-Oct-15
    * ajax-endpoint  update_linguist_language_info
    * input-sanitized : areas_expertise,form_type,language,language_level,year_of_experince
    */


    $areas_expertise = FLInput::get('areas_expertise',[]);
    $form_type = FLInput::get('form_type');
    $language = FLInput::get('language',[]);
    $language_level = FLInput::get('language_level',[]);
    $year_of_experince = FLInput::get('year_of_experince',[]);
    

    $current_user = wp_get_current_user();

    $current_user_id = $current_user->ID;

    $data = $language_level;

    $counter = 0;

    $first_column = $second_column = $third_column = $fourth_column = $counter_is =  '';

    if($form_type == "language"){

        $first_column   = 'language_';

        $second_column  = 'language_level_';

        $third_column   = 'year_of_experince_';

        $fourth_column  = 'areas_expertise_';

        $counter_is     = 'language_counter';

    }elseif($form_type == "translation"){

        $first_column   = 'translation_language_';

        $second_column  = 'translation_language_level_';

        $third_column   = 'translation_year_of_experince_';

        $fourth_column  = 'translation_areas_expertise_';

        $counter_is     = 'translation_language_counter';

    }elseif($form_type == "editing"){

        $first_column   = 'editing_language_';

        $second_column  = 'editing_language_level_';

        $third_column   = 'editing_year_of_experince_';

        $fourth_column  = 'editing_areas_expertise_';

        $counter_is     = 'editing_language_counter';

    }elseif($form_type == "writing"){

        $first_column   = 'writing_language_';

        $second_column  = 'writing_language_level_';

        $third_column   = 'writing_year_of_experince_';

        $fourth_column  = 'writing_areas_expertise_';

        $counter_is     = 'writing_language_counter';

    }

    if ($first_column && $second_column && $third_column && $fourth_column && $counter_is ) {
    for($i=0;$i<count($data);$i++) {

        if (!empty($language[$i]) && !empty($language_level[$i]) && !empty($year_of_experince[$i]) && !empty($areas_expertise[$i])) {


            update_user_meta($current_user_id, $first_column . $i, strip_tags($language[$i]));

            update_user_meta($current_user_id, $second_column . $i, strip_tags($language_level[$i]));

            update_user_meta($current_user_id, $third_column . $i, strip_tags($year_of_experince[$i]));

            update_user_meta($current_user_id, $fourth_column . $i, strip_tags($areas_expertise[$i]));

            $counter++;

        }

        update_user_meta($current_user_id, $counter_is, $counter);
    }

    }

    echo 'success';

    exit;

}