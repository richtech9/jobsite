<?php

add_action('wp_ajax_generateOrderByCustomerNew', 'generateOrderByCustomerNew');

add_action('wp_ajax_nopriv_generateOrderByCustomerNew', 'generateOrderByCustomerNew');

function generateOrderByCustomerNew(){

    /*
     * current-php-code 2020-Sep-30
     * ajax-endpoint  generateOrderByCustomerNew
     * input-sanitized: job_tags
     * input-sanitized: job_type,lang,project_description,project_title,tags,temp_id
     */

    //ALL-FORM-VARS         generateOrderByCustomerNew
    $lang                   = FLInput::get('lang','en');
    $project_title          = FLInput::get('project_title');
    $project_description    = FLInput::get('project_description');
    $job_type               = FLInput::get('job_type');
    $estimated_budgets      = FLInput::get('estimated_budgets');
    $tags                   = FLInput::get('tags');
    $is_guaranted           = FLInput::get('is_guaranted');
    $already_ins            = FLInput::get('already_ins');
    $temp_id                = FLInput::get('temp_id');
    $standard_delivery      = FLInput::get('standard_delivery');

    if ( is_user_logged_in() ) {



        global $wpdb;

        $prefix             = $wpdb->prefix;


        $user_ID            = get_current_user_id();




        if(empty($project_title)){

            $alert_message = get_custom_string_return('Project title is required field.');

            echo json_encode(array('result'=>'required','alert_message' => $alert_message));
            wp_die();


        }elseif(empty($project_description)){

            $alert_message = get_custom_string_return('Project description is required field.');

            echo json_encode(array('result'=>'required','alert_message' => $alert_message));
            wp_die();


        }elseif($job_type == 'contest' && $estimated_budgets && is_numeric($estimated_budgets) &&  $estimated_budgets < 10 ){
            $alert_message = get_custom_string_return('Budget can not be less than 10$');

            echo json_encode(array('result'=>'required','alert_message' => $alert_message));
            wp_die();
        }elseif($job_type == 'contest' && $estimated_budgets && $estimated_budgets==""){
            $alert_message = get_custom_string_return('Budget can not be empty');

            echo json_encode(array('result'=>'required','alert_message' => $alert_message));
            wp_die();
        }


        //TAGS PROCESS


        $userCurrBalance = get_user_meta( $user_ID, 'total_user_balance', true);


        $contestFee = get_option('contest_fee') ? get_option('contest_fee') : 0;
        //code-notes calculate insurance fee, if any, and add it to the dedecutTotal
        // the charge will be the contest_insurance_fee_base + [rounded to nearest cent]($contBudget * (contest_insurance_fee_rate/100))
        $contBudget = floatval($estimated_budgets);

        $b_do_insurance =  intval($is_guaranted) ;
        //will_send_to_error_log('$b_do_insurance',$b_do_insurance,false,true);
        $getInsuranceCost = 0;
        if ($b_do_insurance) {
            $base = floatval($contBudget);
            $insuranceBaseFee  = get_option('contest_insurance_fee_base') ? floatval(get_option('contest_insurance_fee_base')) : 0;
            $insuranceBasePercentage = get_option('contest_insurance_fee_rate') ? floatval(get_option('contest_insurance_fee_rate')) : 0;
            //will_send_to_error_log('base,base-fee,base-percentage',[$base,$insuranceBaseFee,$insuranceBasePercentage],false,true);
            $insurancePercentageUnrounded = $base * ($insuranceBasePercentage/100);
            //will_send_to_error_log('$insuranceCostUnrounded',$insurancePercentageUnrounded,false,true);
            $insurancePercentage = round($insurancePercentageUnrounded,2);
            $getInsuranceCost = $insuranceBaseFee + $insurancePercentage;
            //will_send_to_error_log('$getInsuranceCost',$getInsuranceCost,false,true);

        }
        $getFee1 = get_option('client_referral_fee') ? get_option('client_referral_fee') : 2;
        $getFee_percentage = get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5;
        $getFee2 = ($contBudget*$getFee_percentage)/100;

        $getFee = $getFee1 + $getFee2;

        $dedecutTotal = $getFee+$contBudget+$contestFee + $getInsuranceCost;

//        will_send_to_error_log('fees',[
//            '$contBudget' => $contBudget,
//             '$contestFee' => $contestFee,
//             '$getInsuranceCost' =>$getInsuranceCost ,
//            '$getFee1' => $getFee1,
//            '$getFee_percentage' => $getFee_percentage,
//            '$getFee2' => $getFee2,
//            '$getFee' =>$getFee ,
//            '$dedecutTotal' => $dedecutTotal
//        ],true,true);


        //if( ($job_type == 'contest') && ($userCurrBalance >= $dedecutTotal) ){
        if( ($job_type == 'contest') ){


            $job_id             = generateTempJob();
            if($job_id != 0){

                $tagIdArray = array();
                if(!empty($tags)){
                    $tagArray = explode(',',$tags);
                    foreach($tagArray as $tag){
                        if($tag){
                            $sql_for_have_tag =
                                "SELECT id FROM wp_interest_tags WHERE tag_name='".$tag."'";
                            $haveTag = $wpdb->get_row( $sql_for_have_tag,ARRAY_A);
                            if($haveTag){
                                $tagIdArray[] = $haveTag['id'];
                            } else {

                                $sql_to_insert = "INSERT INTO wp_interest_tags (tag_name,created_at,modified_at)
                                                          VALUES ('$tag',NOW(),NOW())";
                                $wpdb->query($sql_to_insert);
                                $da_last_id = will_get_last_id($wpdb,'interest_tags');
                                $tagIdArray[] = $da_last_id;
                            }
                        }
                    }
                }

                if($job_type == 'contest'){
                    $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
                } else {
                    $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
                }

                if($tagIdArray){

                    foreach($tagIdArray as $tagIds){
                        $jobCache = $wpdb->get_row(
                            "SELECT * FROM wp_tags_cache_job WHERE job_id=$job_id AND tag_id=$tagIds  AND type = $tagType",ARRAY_A);
                        if(empty($jobCache)){
                            $wpdb->insert( 'wp_tags_cache_job', array('job_id'=>$job_id,'tag_id'=>$tagIds,'type'=>$tagType) );
                        }
                    }



                    $jobCacheActiveJob = $wpdb->get_results(
                        "SELECT  tag_id FROM wp_tags_cache_job WHERE job_id=$job_id AND type = $tagType",ARRAY_A);
                    $jobCacheActiveJob = array_column($jobCacheActiveJob, 'tag_id');
                    $deleteTag = array_diff($jobCacheActiveJob,$tagIdArray);
                    if($deleteTag){
                        $deleteTagIn = implode(",",$deleteTag);
                        $wpdb->query("DELETE FROM wp_tags_cache_job WHERE tag_id IN($deleteTagIn) AND job_id=$job_id AND type = $tagType");
                    }
                }


                if($already_ins){

                    $wpdb->update(  'wp_files',  array(   'type' => FLWPFileHelper::TYPE_POST_DETAILS  ), array( 'post_id' => $job_id ) );

                }
                if($temp_id){

                    $wpdb->update(  'wp_files',  array(   'type' => FLWPFileHelper::TYPE_POST_DETAILS ,'post_id' => $job_id ), array( 'temp_id' => $temp_id ) );

                }

                $is_guaranted               = removePersonalInfo($is_guaranted);
                //code-notes if is_guaranted then add line to wallet to charge

                $finalBaal = $userCurrBalance - $dedecutTotal;

                if(get_post_meta($job_id,'contest_prize',true) != 'deducted'){

                    update_user_meta($user_ID, 'total_user_balance', $finalBaal);


                    fl_transaction_insert( '-'.$contBudget, 'done', 'contest_created',
                        $user_ID, NULL, 'post competition', 'wallet', '',
                        $job_id, NULL,NULL );

                    fl_transaction_insert( '-'.$getFee, 'done', 'contest_created',
                        $user_ID, NULL, 'processing fee', 'wallet',
                        '', $job_id, NULL,NULL );

                    fl_transaction_insert( '-'.$contestFee, 'done', 'contest_created',
                        $user_ID, NULL, 'competition fee', 'wallet', '',
                        $job_id, NULL,NULL );

                    if ($getInsuranceCost) {
                        //will_send_to_error_log('inserting insurance cost',$getInsuranceCost);
                        fl_transaction_insert( '-'.$getInsuranceCost, 'done', 'contest_created',
                            $user_ID, NULL, 'insurance fee', 'wallet', '',
                            $job_id, NULL,NULL );
                    }
                }




                $project_title          = removePersonalInfo($project_title);

                $project_description    = removePersonalInfo($project_description);



                $estimated_budgets      = removePersonalInfo($estimated_budgets);

                $job_type               = removePersonalInfo($job_type);


                update_post_meta($job_id,'project_title',$project_title);

                update_post_meta($job_id,'project_description',$project_description);

                update_post_meta($job_id,'is_guaranted',$is_guaranted);

                //code-notes no more text snapshots






                update_post_meta($job_id,'estimated_budgets',$estimated_budgets);


                update_post_meta($job_id,'fl_job_type',$job_type);

                update_post_meta($job_id,'contest_prize','deducted');



                update_project_status($job_id, 'pending');


                $modified_id = change_the_project_id( FreelinguistProjectAndContestHelper::CONTEST_PREFIX,$da_number );



                update_post_meta($job_id,'modified_id',$modified_id);
                update_post_meta($job_id,'numeric_modified_id',$da_number);
                $my_post = array('ID' => $job_id,'post_status' => 'publish','post_title' => $modified_id);

                wp_update_post($my_post);


                $job_standard_delivery_date = empty($standard_delivery) ? date('Y-m-d') : $standard_delivery;



                update_post_meta( $job_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($job_standard_delivery_date));




                $wpdb->update(  $prefix.'posts',  array(  'post_status' => 'publish'), array( 'ID' => $job_id ) );

                add_post_meta($job_id,'_bid_placed_by', 'empty');


                $wpdb->update(  'wp_files',  array(  'status' => 1  ), array( 'by_user' => $user_ID,'status' => 0 ) );

                $wpdb->delete(  'wp_files', array( 'by_user' => $user_ID,'status' => -1 ) );

                // Add 30 days Or days mentioned by admin

                update_post_meta($job_id,'job_created_date',date("Ymd"));


                $variables = array(

                    'job_link' => get_post_meta($job_id,'modified_id',true),

                    'order_placed_date' => date('Y-m-d'),

                );

                $curr_user_detail = get_userdata(get_current_user_id());



                emailTemplateForUser($curr_user_detail->user_email,PLACE_NEW_ORDER,$variables);



                if($lang){

                    $url =   get_site_url().'/job/'.$modified_id.'?lang='.$lang.'&action=participants-proposals';

                }else{

                    $url =   get_site_url().'/job/'.$modified_id.'?action=participants-proposals';

                }


                FreelinguistProjectAndContestHelper::update_elastic_index($job_id);
                //INDEX IN ELASTIC ENGINE


                $msg = '<strong><u>' .Ucfirst($project_title).'</u></strong><br><br>'.
                    '<em>' .$project_description.'</em>'.
                    '<p>Budget: $'.$contBudget.'</p> ' .
                    '<p>Skills: '.$tags.'</p> ' .
                    '<a class="pull-right btn-success btn-sm fl-view-link" target="_blank" href="' . $url . '" >View</a>';


                //code-notes send new linguist content announcement via async task
                as_enqueue_async_action( 'freelinguist_broadcast_admin_ejabber', [$msg,'Contest'] );

                echo json_encode(array('result'=>'success','url' =>$url));

            }else{


                $url =   freeling_links('order_process');


                $alert_message = get_custom_string_return('Job already generated or you are genearating wrong job');

                echo json_encode(array('result'=>'job_id_not_exist','url'=> $url,'alert_message' => $alert_message));

            }

        }

        elseif($job_type != 'contest'){


            $job_id             = generateTempJob();


            if($job_id != 0){

                $tagIdArray = array();
                if(!empty($tags)){
                    $tagArray = explode(',',$tags);
                    foreach($tagArray as $tag){
                        if($tag){
                            $have_tags_sql =
                                "SELECT id FROM wp_interest_tags WHERE tag_name='".$tag."'";
                            $haveTag = $wpdb->get_row( $have_tags_sql,ARRAY_A);
                            if($haveTag){
                                $tagIdArray[] = $haveTag['id'];
                            } else {
                                $sql_to_insert = "INSERT INTO wp_interest_tags (tag_name,created_at,modified_at)
                                                          VALUES ('$tag',NOW(),NOW())";
                                $wpdb->query($sql_to_insert);
                                $da_last_id = will_get_last_id($wpdb,'interest_tags');
                                $tagIdArray[] = $da_last_id;
                            }
                        }
                    }
                }



                if($job_type == 'contest'){
                    $tagType = FreelinguistTags::CONTEST_TAG_TYPE;
                } else {
                    $tagType = FreelinguistTags::PROJECT_TAG_TYPE; //PROJECT
                }


                if($tagIdArray){

                    foreach($tagIdArray as $tagIds){
                        $jobCache = $wpdb->get_row(
                            "SELECT * FROM wp_tags_cache_job WHERE job_id=$job_id AND tag_id=$tagIds AND type = $tagType",ARRAY_A);
                        if(empty($jobCache)){
                            $wpdb->insert( 'wp_tags_cache_job', array('job_id'=>$job_id,'tag_id'=>$tagIds,'type'=>$tagType) );
                        }
                    }



                    $jobCacheActiveJob = $wpdb->get_results(
                        "SELECT  tag_id FROM wp_tags_cache_job WHERE job_id=$job_id AND type = $tagType",ARRAY_A);
                    $jobCacheActiveJob = array_column($jobCacheActiveJob, 'tag_id');
                    $deleteTag = array_diff($jobCacheActiveJob,$tagIdArray);
                    if($deleteTag){
                        $deleteTagIn = implode(",",$deleteTag);
                        $wpdb->query("DELETE FROM wp_tags_cache_job WHERE tag_id IN($deleteTagIn) AND job_id=$job_id AND type = $tagType");
                    }
                }


                if($already_ins){

                    $prefix         = $wpdb->prefix;



                    $wpdb->update(  'wp_files',  array(   'type' => FLWPFileHelper::TYPE_POST_DETAILS  ), array( 'post_id' => $job_id ) );

                }

                if($temp_id){
                    $wpdb->update(  'wp_files',  array(   'type' => FLWPFileHelper::TYPE_POST_DETAILS ,'post_id' => $job_id ), array( 'temp_id' => $temp_id) );
                }


                $project_title          = removePersonalInfo($project_title);

                $project_description    = removePersonalInfo($project_description);


                $estimated_budgets      = removePersonalInfo($estimated_budgets);


                $job_type               = removePersonalInfo($job_type);


                update_post_meta($job_id,'project_title',$project_title);

                update_post_meta($job_id,'project_description',$project_description);


                update_post_meta($job_id,'estimated_budgets',$estimated_budgets);


                update_post_meta($job_id,'fl_job_type',$job_type);



                update_project_status($job_id, 'pending');



                $modified_id = change_the_project_id( FreelinguistProjectAndContestHelper::PROJECT_PREFIX,$da_number );



                update_post_meta($job_id,'modified_id',$modified_id);
                update_post_meta($job_id,'numeric_modified_id',$da_number);
                $my_post = array('ID' => $job_id,'post_status' => 'publish','post_title' => $modified_id);

                wp_update_post($my_post);



                $job_standard_delivery_date = empty($standard_delivery) ? date('Y-m-d') : $standard_delivery;

                update_post_meta( $job_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($job_standard_delivery_date));

                $wpdb->update(  $prefix.'posts',  array(  'post_status' => 'publish'), array( 'ID' => $job_id ) );

                add_post_meta($job_id,'_bid_placed_by', 'empty');


                $wpdb->update(  'wp_files',  array(  'status' => 1  ), array( 'by_user' => $user_ID,'status' => 0 ) );

                $wpdb->delete(  'wp_files', array( 'by_user' => $user_ID,'status' => -1 ) );

                // Add 30 days Or days mentioned by admin

                update_post_meta($job_id,'job_created_date',date("Ymd"));


                $variables = array(

                    'job_link' => get_post_meta($job_id,'modified_id',true),

                    'order_placed_date' => date('Y-m-d'),

                );

                $curr_user_detail = get_userdata(get_current_user_id());

                emailTemplateForUser($curr_user_detail->user_email,PLACE_NEW_ORDER,$variables);



                if($lang){


                    $url =   get_site_url().'/job/'.$modified_id;

                }else{

                    $url =   get_site_url().'/job/'.$modified_id;

                }



                FreelinguistProjectAndContestHelper::update_elastic_index($job_id);
                //INDEX IN ELASTIC ENGINE



                $display_budget = '';
                if ($contBudget) {
                    $split_prices = explode('_',$estimated_budgets);
                    $what_prices = [];
                    foreach ($split_prices as $split_price) {
                        $split_price = trim($split_price);
                        if ($split_price) {
                            $what_prices[] = '$'.$split_price;
                        }
                    }

                    $display_budget = implode(' to ',$what_prices);
                }

                $msg = '<strong><u>' .Ucfirst($project_title).'</u></strong><br><br>'.
                    '<em>' .$project_description.'</em>'.
                    '<p>Budget: '.$display_budget.'</p> ' .
                    '<p>Skills: '.$tags.'</p> ' .
                    '<a class="pull-right btn-success btn-sm fl-view-link" target="_blank" href="' . $url . '" >View</a>';

                // $ejabber->sendBroadcast($msg,'Project');
                //code-notes send new project announcement via async task
                as_enqueue_async_action( 'freelinguist_broadcast_admin_ejabber', [$msg,'Project'] );


                echo json_encode(array('result'=>'success','url' =>$url));


            }else{

                $url =   freeling_links('order_process');

                $alert_message = get_custom_string_return('Job already generated or you are genearating wrong job');

                echo json_encode(array('result'=>'job_id_not_exist','url'=> $url,'alert_message' => $alert_message));

            }

        }


        else{

            $alert_message = get_custom_string_return('Some error occured');

            echo json_encode(array('result'=>'required','alert_message' => $alert_message));

        }

    }else{

        $url =   freeling_links('registration_url');

        echo json_encode(array('result'=>'failed','url'=> $url));

    }

    wp_die();

}