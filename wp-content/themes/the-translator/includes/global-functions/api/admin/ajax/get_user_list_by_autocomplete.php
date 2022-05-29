<?php

/**
 * code notes only logged in user who can do admin pages can use this
 * @return string
 */
function get_user_list_by_autocomplete() {

    /*
   * current-php-code 2020-Dec-28
   * ajax-endpoint  get_user_list_by_autocomplete (not a true ajax)
   * input-sanitized : action
   */


    $action = FLInput::get('action');

    $term = FLInput::get('term','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

    $is_all = (int)FLInput::get('all');

    if($action == 'get_user_list_by_autocomplete' && $term){
        if ( !current_user_can( 'manage_options' ) ) {  die('cannot access admin functionality');}
        $json = array();

        $roles = array( 'translator','customer' );
        if ($is_all) {$roles = [];}
        $args = array(
            'search'         =>  $term .'*',
            'search_columns' => array('user_email' ),
            'role__in'           => $roles,
            'number' => 10
        );
        $user_query = new WP_User_Query( $args );
        $authors = $user_query->get_results();

        foreach ($authors as $author)
        {
            // get all the user's data
            $author_info = get_userdata($author->ID);
            $json[]=array( 'value'=> $author_info->user_email );
        }
        echo json_encode($json);
        exit;


    }
}
add_action( 'init', 'get_user_list_by_autocomplete', 1 );