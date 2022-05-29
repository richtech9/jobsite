<?php

function hz_fl_discussion_list_both2_child( $comment_id){
    global $wpdb;

    /*
    * current-php-code 2020-Dec-2
    * internal-call
    * input-sanitized :
    */


    $rows   = $wpdb->get_results( "SELECT * FROM wp_fl_discussion WHERE parent_comment =".$comment_id." order by time desc" );



    $final_row = $rows;

    $sortall = array();

    foreach ($final_row as $key => $vale){

        $sortall[$key] = $vale->ID;

    }
    array_multisort($sortall, SORT_DESC, $final_row);
    $context    = '';
    if( $final_row ){
        foreach( $final_row as $row ){

            $dt     = new DateTime( $row->time );

            $date   = $dt->format('d/m/Y');

            $context .='<div class="log_in_wtth_box comment_bottom-box_newcss">
								<i class="fa col-md-1 thumb-img enhanced-text">
								<img src="'.hz_get_profile_thumb( $row->post_by ).'" ></i>
								<div class="user_box col-md-11">
									<h5>
										<span>'.hz_get_pro_name( $row->post_by ).'</span> 
										<i class="fa fa-circle" aria-hidden="true"></i>'.$date.' 
									</h5>
									<p>'.$row->comment.'</p>
									
									
									
								</div>
							</div>';
        }
    }

    return $context;

}