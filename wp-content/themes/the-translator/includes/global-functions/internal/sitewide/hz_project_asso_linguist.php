<?php
function hz_project_asso_linguist( $project_id ){
    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */
    global $wpdb;

    $jtbl   = $wpdb->prefix."fl_job";

    $rows   = $wpdb->get_results( /** @lang text */
        "SELECT linguist_id FROM $jtbl WHERE `project_id` = $project_id" );

    if($rows){

        $ling = array();

        foreach( $rows as $row ){

            $ling[] = $row->linguist_id;

        }

        return $ling;

    }else{

        return false;

    }

}