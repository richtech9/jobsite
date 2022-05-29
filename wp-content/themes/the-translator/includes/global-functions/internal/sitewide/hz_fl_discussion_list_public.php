<?php

if( !function_exists('hz_fl_discussion_list_public') ) {

    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */

    function hz_fl_discussion_list_public( $post_id){

        global $wpdb;


        $rows   = $wpdb->get_results( "SELECT * FROM wp_fl_discussion WHERE post_id =".$post_id." AND post_to  IS NULL  order by time desc" );


        $context    = '';

        if( !empty($rows) ){

            foreach( $rows as $final_row ){

                $dt     = new DateTime( $final_row->time );

                $date   = $dt->format('d/m/Y');

                $context .= '<div class="comment-row hire-linguist-detail">

                        <figure class="col-lg-2 col-md-3 col-sm-3 col-xs-12">

                            <img src="'.hz_get_profile_thumb( $final_row->post_by ).'" />

                            <div class="title  large-text">                                

                            <h5 class="h5"> '.hz_get_pro_name( $final_row->post_by ).' </h5>

                            <span class="dates small-text"> '.$date.' </span>

                            </div>

                        </figure>                        

                        <div class="col-lg-10 col-md-9 col-sm-9 col-xs-12">

                            <p> '.$final_row->comment.' </p>

                        </div>

                    </div>';

            }

        }


        return $context;

    }

}