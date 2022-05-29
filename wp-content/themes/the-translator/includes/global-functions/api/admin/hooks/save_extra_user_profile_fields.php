<?php
add_action( 'personal_options_update', 'oneTarek_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'oneTarek_save_extra_user_profile_fields' );
/*
 * current-php-code 2020-Jan-11
 * current-hook
 * input-sanitized :
 */
function oneTarek_save_extra_user_profile_fields( $user_id )
{
    $assigned_country_from = (int)FLInput::get('assign_country_from',-1);
    $assigned_country_to = (int)FLInput::get('assign_country_to',-1);
    if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
    if (isset($_POST['user_translation_technical_level'])) {
        update_user_meta( $user_id, 'user_translation_technical_level', $_POST['user_translation_technical_level'] );
    }

    if (isset($_POST['user_editing_technical_level'])) {
        update_user_meta( $user_id, 'user_editing_technical_level', $_POST['user_editing_technical_level'] );
    }

    if (isset($_POST['user_writing_technical_level'])) {
        update_user_meta( $user_id, 'user_writing_technical_level', $_POST['user_writing_technical_level'] );
    }


    if(!empty($_POST['reported_to'])){
        update_user_meta( $user_id, 'reported_to', $_POST['reported_to'] );
    }
    if(!empty($_POST['user_processing_id'])){
        update_user_meta( $user_id, 'user_processing_id', $_POST['user_processing_id'] );
    }
    if($assigned_country_from >=0 && $assigned_country_to >=0){
        update_user_meta( $user_id, 'assign_country_from', $assigned_country_from );
        update_user_meta( $user_id, 'assign_country_to', $assigned_country_from );
    }



    return true;
}