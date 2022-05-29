<?php

function hz_fl_content_discussion_list_both2($content_id, $post_by , $post_to){
    global $wpdb;

    /*
       * current-php-code 2020-Dec-2
       * internal-call
       * input-sanitized :
       */


    $rows   = $wpdb->get_results(
        "SELECT * FROM wp_fl_discussion WHERE content_id =".$content_id." AND post_by =".$post_by." AND post_to =".$post_to." order by time desc" );


    $rows2  = $wpdb->get_results(
        "SELECT * FROM wp_fl_discussion WHERE content_id =".$content_id." AND post_by =".$post_to." AND post_to IS NULL  order by time desc" );
    $final_row = array_merge($rows,$rows2);

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
									
									<strong class="enhanced-text" >
									    <i class="fa fa-angle-up larger-text" aria-hidden="true"></i> 
									    <i class="fa fa-angle-down larger-text" aria-hidden="true"></i> 
									    <i class="fa fa-circle" aria-hidden="true"></i> 
									        <a href="#" class="reply_to_comment" data-comment_id="'.$row->ID.'">Reply</a> 
                                    </strong>
									'.hz_fl_discussion_list_both2_child($row->ID).'
									<div class="" id="reply_comment_'.$row->ID.'" style="display:none;">
                                    <span class="commentEmptyMessage" style="color:red"></span>
											<form id="contest_discussion_reply_'.$row->ID.'" class="col-md-11">
												<input type="text" name="comment" placeholder="Join the discussion" maxlength="1000" required="required">

												<input type="hidden" name="parent_comment" value="'.$row->ID.'">
												<input type="hidden" name="post_id" value="'.$row->post_id.'">

												<input type="hidden" name="comment_to" value="'.$row->post_by.'">

												<a  href="#" class="submit_reply box-prement2" data-comment_id="'.$row->ID.'">Reply</a>

											</form>
									</div>
								</div>
							</div>';
        }
    }

    return $context;

}