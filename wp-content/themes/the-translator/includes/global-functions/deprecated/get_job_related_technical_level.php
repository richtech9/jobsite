<?php

/**
 * @deprecated
 * @param $user_id
 * @param $job_translation_type
 * @return int|mixed
 */
function get_job_related_technical_level($user_id,$job_translation_type){

    /*
     * current-deprecated 2020-Oct-06
     * internal-call
     * input-sanitized :
     */

    if($job_translation_type == 'translation'){

        $price = empty(get_user_meta($user_id,'user_translation_technical_level',true)) ? 0 : get_option(get_user_meta($user_id,'user_translation_technical_level',true));

    }elseif($job_translation_type == 'editing'){

        $price = empty(get_user_meta($user_id,'user_editing_technical_level',true)) ? 0 : get_option(get_user_meta($user_id,'user_editing_technical_level',true));

    }elseif($job_translation_type == 'writing'){

        $price = empty(get_user_meta($user_id,'user_writing_technical_level',true)) ? 0 : get_option(get_user_meta($user_id,'user_writing_technical_level',true));

    }else{

        $price = 0;

    }

    return $price;

}