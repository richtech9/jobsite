<?php

add_action('wp_ajax_hirelinguistByCustomer', 'hirelinguistByCustomer');


function hirelinguistByCustomer(){
    //code-bookmark activated when a person presses hire me in the users' profile page

    /*
     * current-php-code 2020-Sep-30
     * ajax-endpoint  hirelinguistByCustomer
     * input-sanitized : delivery_date, estimated_budgets, lang, project_description, standard_delivery,user

     */


    $estimated_budgets      = FLInput::get('estimated_budgets');
    $lang                   = FLInput::get('lang','en');
    $project_description    = FLInput::get('project_description');
    $delivery_date          = FLInput::get('delivery_date');

    $user = FLInput::get('user','',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING,FLInput::YES_I_WANT_HTML_ENTITIES);

    $user_db_escaped          = FLInput::get('user');



    if ( is_user_logged_in() ) {



        global $wpdb;

        $job_post = array(

            'post_title'    => 'test',

            'post_content'  => '',

            'post_status'   => 'pending',

            'post_author'   => get_current_user_id(),

            'post_type'     => 'job'

        );

        $project_post_id     = wp_insert_post( $job_post );

        $job_title  = change_the_pending_job_id($project_post_id,$da_number);

        update_post_meta($project_post_id,'modified_id',$job_title);
        update_post_meta($project_post_id,'numeric_modified_id',$da_number);
        $my_post = array('ID' => $project_post_id,'post_title' => $job_title);

        wp_update_post($my_post);
        if($delivery_date){
            $date = strtotime($delivery_date);
        }else{
            $date = strtotime(date('Y-m-d'));
        }

        $job_standard_delivery_date =  date('Y-m-d', $date);


        if($project_post_id != 0){

            $project_description    = removePersonalInfo($project_description);

            update_post_meta( $project_post_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($job_standard_delivery_date));
            update_post_meta($project_post_id,'estimated_budgets',$estimated_budgets);


            update_post_meta($project_post_id,'project_title',"Work for ".$user_db_escaped);
            update_post_meta($project_post_id,'project_description',$project_description);


            update_post_meta($project_post_id,'fl_job_type','project');



            update_project_status($project_post_id, 'pending');




            $fl_prefix = '';


            $modified_id = change_the_project_id( $fl_prefix,$da_number );




            update_post_meta($project_post_id,'modified_id',$modified_id);
            update_post_meta($project_post_id,'numeric_modified_id',$da_number);
            $my_post = array('ID' => $project_post_id,'post_status' => 'publish','post_title' => $modified_id);

            wp_update_post($my_post);




            update_post_meta( $project_post_id, 'job_standard_delivery_date',will_validate_string_date_or_make_future( $job_standard_delivery_date));

            $wpdb->update(  'wp_posts',  array(  'post_status' => 'publish'), array( 'ID' => $project_post_id ) );
            will_log_on_wpdb_error($wpdb,'update post status');

            add_post_meta($project_post_id,'_bid_placed_by', 'empty');


            update_post_meta($project_post_id,'job_created_date',date("Ymd"));



            $translator = get_user_by('login',$user);

            /**** added bid ***********/



            $commentdata = array(

                'comment_post_ID'       => $project_post_id, //to which post the comment will show up

                'comment_author'        => $user, //fixed value - can be dynamic

                'comment_author_email'  => $translator->user_email, //fixed value - can be dynamic

                'comment_author_url'    => $translator->user_url, //fixed value - can be dynamic

                'comment_content'       => "Work for ".$user, //fixed value - can be dynamic

                'comment_type'          => 'job_bid', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks

                'comment_parent'        => 0, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here

                'user_id'               => $translator->ID, //passing current user ID or any predefined as per the demand

            );

            //Insert new comment(BID) and get the comment(BID) ID

            $comment_id = wp_new_comment( $commentdata );



            $wpdb->update( 'wp_comments', array( 'comment_approved' => 1), array( 'comment_ID' => $comment_id ), array(  '%d'  ), array( '%d' ) );
            will_log_on_wpdb_error($wpdb,'update bid comment');

            $meta_value = $estimated_budgets;
            add_comment_meta( $comment_id, 'bid_price', $meta_value);



            //Bid charges

            add_post_meta( $project_post_id, '_bid_placed_by', $translator->ID );

            FLPostLookupDataHelpers::add_user_lookup_bid($project_post_id,$translator->ID);
            /**************************/


            $pro_jid            = gen_pjob_title( $project_post_id );


            $hz_job_title       = get_the_title( $project_post_id )."_".$pro_jid;

            $user_amount        = get_user_meta( get_current_user_id(), 'total_user_balance', true );

            $referral_fee       =  get_option('client_referral_fee');





            $variables              = array();

            $modified_id            = get_post_meta( $project_post_id, 'modified_id', true );

            $variables['job_path']  = $modified_id;



            $jdata  = array(

                'job_seq'       => $pro_jid,

                'title'         => $hz_job_title,


                'author'        => get_current_user_id(),

                'linguist_id'   => $translator->ID,

                'project_id'    => $project_post_id,

                'bid_id'        => $comment_id,

                'amount'        => 0,

                'meta'          => '',

                'post_date'     => current_time('Y-m-d H:i:s', $gmt = 1),

                'job_status'    => 'pending',

            );

            $jid            = $wpdb->insert( "wp_fl_job", $jdata );
            will_log_on_wpdb_error($wpdb,'insert job');

            $fl_job_id         = $wpdb->insert_id;

            $sql_to_update = "UPDATE wp_fl_job SET post_date = NOW() WHERE ID = $fl_job_id";
            $wpdb->query($sql_to_update);
            will_log_on_wpdb_error($wpdb,'update job post_date');



            if( $jid !== false ){


                $user_amount = $user_amount - $referral_fee;

                update_user_meta( get_current_user_id(), 'total_user_balance', amount_format( $user_amount ) );


                fl_transaction_insert( '-'.$referral_fee, 'done', 'hire', get_current_user_id(),
                    NULL, 'Hiring Referral Fee', '','',$project_post_id, $fl_job_id );



                update_project_status($project_post_id, 'project_in_progress');


                FreelinguistProjectAndContestHelper::update_elastic_index($project_post_id);

            }

            $curr_user_detail = get_userdata(get_current_user_id());

            emailTemplateForUser($curr_user_detail->user_email,PLACE_NEW_ORDER,$variables);




            if($lang){

                $url =   get_site_url().'/job/'.$modified_id;

            }else{

                $url =   get_site_url().'/job/'.$modified_id;

            }


            echo json_encode(array('result'=>'success','url' =>$url));

        }else{

            $url =   freeling_links('order_process');

            $alert_message = get_custom_string_return('Job already generated or you are genearating wrong job');

            echo json_encode(array('result'=>'job_id_not_exist','url'=> $url,'alert_message' => $alert_message));

        }





    }else{

        $url =   freeling_links('registration_url');

        echo json_encode(array('result'=>'failed','url'=> $url));

    }


    wp_die();

}