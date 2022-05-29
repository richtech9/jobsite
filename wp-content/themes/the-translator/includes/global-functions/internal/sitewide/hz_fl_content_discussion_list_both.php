<?php

function hz_fl_content_discussion_list_both( $content_id, $post_by ,$post_to){
    /*
    * current-php-code 2020-Dec-2
    * internal-call
    * input-sanitized :
    */
    global $wpdb;


    $rows   = $wpdb->get_results( "SELECT * FROM wp_fl_discussion WHERE content_id =".$content_id." AND post_by =".$post_by." AND post_to =".$post_to." order by time desc" );

    $rows2  = $wpdb->get_results( "SELECT * FROM wp_fl_discussion WHERE content_id =".$content_id." AND post_by =".$post_to  ." AND  post_to=".$post_by." order by time desc" );



    $final_row = array_merge($rows,$rows2);
    $final_row = array_unique($final_row,SORT_REGULAR);

    $sortall = array();

    foreach ($final_row as $key => $vale)

    {

        $sortall[$key] = $vale->ID;

    }

    array_multisort($sortall, SORT_DESC, $final_row);



    $context    = '';

    if( $final_row ){

        foreach( $final_row as $row ){

            $dt     = new DateTime( $row->time );

            $date   = $dt->format('d/m/Y');

            $context .= '<div class="comment-row hire-linguist-detail">

                        <figure class="col-lg-2 col-md-3 col-sm-3 col-xs-12">

                            <img src="'.hz_get_profile_thumb( $row->post_by ).'" />

                            <div class="title  large-text">                                

                            <h5 class="h5"> '.hz_get_pro_name( $row->post_by ).' </h5>

                            <span class="dates small-text"> '.$date.' </span>

                            </div>

                        </figure>                        

                        <div class="col-lg-10 col-md-9 col-sm-9 col-xs-12">

                            <p> '.$row->comment.' </p>

                        </div>

                    </div>';

        }

    }


    return $context;

}