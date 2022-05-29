<?php



/*

* Author Name: Sandeep

* Method:      place_the_bid

* Description: place the bid by linguist

*

*/

add_action('wp_ajax_place_the_bid', 'place_the_bid');


function place_the_bid(){

    /*
    * current-php-code 2020-Oct-7
    * ajax-endpoint  place_the_bid
    * input-sanitized : bidPrice,comment,comment_ID,comment_post_ID,
   */

    global $wpdb;

    $job_id = (int)FLInput::get('comment_post_ID');
    $comment = FLInput::get('comment');
    $sent_comment_id = (int)FLInput::get('comment_ID');
    $bid_price = (float)FLInput::get('bidPrice');

    $current_user_id    = get_current_user_id();

    $current_user       = wp_get_current_user();

    $author = get_post_field( 'post_author', $job_id );

    if($current_user_id != $author){



        if( $job_id ){


            $modified_id    = get_post_meta($job_id,'modified_id',true);

            $comment        = substr($comment,0,10000);

            $comment        = removePersonalInfo( $comment );

            $bid_exist      = $wpdb->get_var( "SELECT count(*) FROM wp_comments WHERE comment_post_ID = $job_id AND user_id=$current_user_id" );

            if($bid_exist == 0){

                /************** bid charges amount ****************************/




                $commentdata = array(

                    'comment_post_ID'       => $job_id, //to which post the comment will show up

                    'comment_author'        => $current_user->user_login, //fixed value - can be dynamic

                    'comment_author_email'  => $current_user->user_email, //fixed value - can be dynamic

                    'comment_author_url'    => $current_user->user_url, //fixed value - can be dynamic

                    'comment_content'       => $comment, //fixed value - can be dynamic

                    'comment_type'          => 'job_bid', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks

                    'comment_parent'        => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here

                    'user_id'               => $current_user_id, //passing current user ID or any predefined as per the demand

                );

                //Insert new comment(BID) and get the comment(BID) ID

                $comment_id = wp_new_comment( $commentdata );




                $wpdb->update( 'wp_comments', array( 'comment_approved' => 1), array( 'comment_ID' => $comment_id ), array(  '%d'  ), array( '%d' ) );


                $meta_value = $bid_price;
                add_comment_meta( $comment_id, 'bid_price', $meta_value);


                add_post_meta( $job_id, '_bid_placed_by', get_current_user_id() );

                FLPostLookupDataHelpers::add_user_lookup_bid($job_id,get_current_user_id());




                $post_info  = get_post($job_id );

                $author     = $post_info->post_author;

                $job_path   = esc_url( get_permalink($job_id) );

                $customer_data = get_userdata( $author );


                $variables = array();

                $variables['job_path'] = $job_path;

                $variables['job_title'] = $modified_id;

                emailTemplateForUser($customer_data->user_email,BID_STATEMENT_TEMPLATE,$variables);

                /******************* Send place bid email to user ******************************************************************************/

                $variables = array();

                $user_detail = get_userdata(get_current_user_id());

                $variables['job_title'] = $modified_id;

                emailTemplateForUser($user_detail->user_email,EMAIL_TO_LINGUIST_WHEN_PLACE_THE_BID,$variables);

                /******************* Send place bid email to user ******************************************************************************/

                echo json_encode([
                    'status_code'=>'success',
                    'message'=> get_custom_string_return('You have successfully placed the bid.')
                ]);

                exit;


            }else{
                $comment_ID=$sent_comment_id;
                $wpdb->update( 'wp_comments', array(  'comment_content' => $comment), array( 'comment_post_ID' => $job_id, 'user_id'=>$current_user_id ), array(  '%s'  ), array( '%d','%d' ) );
                $meta_key = 'bid_price';
                $meta_value = $bid_price;
                $bid_price_exist      = $wpdb->get_var( "SELECT count(*) FROM wp_commentmeta WHERE comment_id = $comment_ID AND meta_key= '".$meta_key."'" );

                if($bid_price_exist==0){
                    add_comment_meta( $comment_ID, $meta_key, $meta_value);
                }
                else{
                    update_comment_meta( $comment_ID, $meta_key, $meta_value);
                }
                echo json_encode(array('status_code'=>'update','message'=> get_custom_string_return('update')));

                exit;

            }

        }else{

            // echo "Please fill all the required field";

            //echo 'unautorized';

            echo json_encode(array('status_code'=>'unautorized','message'=> get_custom_string_return('You are an unauthorized user')));

            exit;

        }
    }else{




        // echo "Please fill all the required field";

        //echo 'unautorized';

        echo json_encode(array('status_code'=>'own_bid','message'=> get_custom_string_return('You can not place bid on your own project')));

        exit;

    }


}