<?php

/**
 * called from user_add_favorite
 * @param  int $userId
 * @return array
 */
function addToFav($userId){
    /*
         * current-php-code 2020-Oct-15
         * internal-call
         * input-sanitized :
        */

    $c_type = FLInput::get('c_type');
    $fav = (int)FLInput::get('fav');
    $id = (int)FLInput::get('id');

    $userId = (int)$userId;

    if($id && $c_type){
        if($c_type=='content'){
            $key = '_favorite_content';
        } else {
            $key = '_favorite_translator';
        }

        $favIds = get_user_meta($userId,$key,true);
        $savedFavArr = [];
        if($favIds){
            $savedFavArr = explode(',',$favIds);
        }
        if(count($savedFavArr) && ($fav===0) && in_array($id,$savedFavArr)){
            $foundPos = array_search($id, $savedFavArr);
            unset($savedFavArr[$foundPos]);
            update_user_meta($userId,$key,implode(',',$savedFavArr));
            $response = array('status'=>1,'message'=>'Remove Successfully.');
        } else {
            if(count($savedFavArr) && in_array($id,$savedFavArr)){
                $foundPos = array_search($id, $savedFavArr);
                unset($savedFavArr[$foundPos]);
            } else {
                array_push($savedFavArr,$id);
            }
            update_user_meta($userId,$key,implode(',',$savedFavArr));
            $response = array('status'=>1,'message'=>'Added Successfully.');
        }
    } else {
        $response = array('status'=>0,'message'=>'Invalid Tag.');
    }
    return $response;
}