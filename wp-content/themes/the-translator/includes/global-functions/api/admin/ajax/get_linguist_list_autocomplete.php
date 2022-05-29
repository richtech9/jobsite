<?php

/**
 * code notes only logged in user who can do admin pages can use this
 * @return string
 */
function get_linguist_list_autocomplete() {

    /*
   * current-php-code 2020-Dec-28
   * ajax-endpoint  get_linguist_list_autocomplete (not a true ajax)
   * input-sanitized : action
   */

    if ( !current_user_can( 'manage_options' ) ) { return ''; }

    $action = FLInput::get('action');

    $term = FLInput::get('term','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

    if($action == 'get_linguist_list_autocomplete' && $term){

        $json = array();
        $usersList = getReportedUserByUserId();
        $args = array(
            'search'         =>  $term .'*',
            'search_columns' => array('user_email' ),
            'role__in'           => array( 'translator' ),
            'include' => $usersList,
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
add_action( 'init', 'get_linguist_list_autocomplete', 1 );