<?php

function hz_get_profile_thumb( $user_id ,$size = FreelinguistSizeImages::TINY,$b_use_default=true){
    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */

    $avatar_fragment = get_user_meta($user_id, 'user_image', true);
    $avatar = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($avatar_fragment,$size,$b_use_default);
    //code-notes [image-sizing]  getting user icon for many pages here, most are tiny, but some might need other sizes, so made new param

    return $avatar;

}