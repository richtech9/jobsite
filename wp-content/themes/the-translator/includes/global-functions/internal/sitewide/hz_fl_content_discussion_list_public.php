<?php

if( !function_exists('hz_fl_content_discussion_list_public') ) {
    function hz_fl_content_discussion_list_public($content_id ){

        /*
         * current-php-code 2020-Oct-13
         * internal-call
         * input-sanitized :
         */
        global $wpdb;


        $rows   = $wpdb->get_results( "SELECT * FROM wp_fl_discussion WHERE content_id ='".$content_id."' AND post_to is NULL AND parent_comment IS NULL  order by time desc" );


        $context    = '';
        if( !empty($rows) ){
            foreach( $rows as $final_row ){

                $dt     = new DateTime( $final_row->time );

                $date   = $dt->format('d/m/Y');

                $context .='<div class="log_in_wtth_box comment_bottom-box_newcss">
								<i class="fa col-md-1 thumb-img">
								<img src="'.hz_get_profile_thumb( $final_row->post_by ).'" ></i>
								<div class="user_box col-md-11">
									<h5>
										<span>'.hz_get_pro_name( $final_row->post_by ).'</span> 
										<i class="fa fa-circle" aria-hidden="true"></i>'.$date.' 
									</h5>
									<p>'.$final_row->comment.'</p>
									
									<strong class="enhanced-text">
									    <i class="fa fa-angle-up larger-text" aria-hidden="true"></i> 
									    <i class="fa fa-angle-down larger-text" aria-hidden="true"></i> 
									    <i class="fa fa-circle" aria-hidden="true"></i> 
									        <a href="#" class="reply_to_comment" data-comment_id="'.$final_row->ID.'">Reply</a> 
                                    </strong>
									'.hz_fl_discussion_list_both2_child($final_row->ID).'
									<div class="" id="reply_comment_'.$final_row->ID.'" style="display:none;">
                                      <span class="commentEmptyMessage" style="color:red"></span>
											<form id="contest_discussion_reply_'.$final_row->ID.'" class="col-md-11">
												<input type="text" name="comment" placeholder="Join the discussion" maxlength="1000" required="required">

												<input type="hidden" name="parent_comment" value="'.$final_row->ID.'">
												<input type="hidden" name="post_id" value="'.$final_row->post_id.'">

												<input type="hidden" name="comment_to" value="'.$final_row->post_by.'">

												<a  href="#" class="submit_reply box-prement2" data-comment_id="'.$final_row->ID.'">Reply</a>

											</form>
									</div>
								</div>
							</div>';
            }
        }

        return $context;

    }
}