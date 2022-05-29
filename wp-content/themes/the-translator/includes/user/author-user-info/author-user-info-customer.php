<?php
/*
* current-php-code 2020-Oct-17
* input-sanitized : lang,user
* current-wp-template:  customer profile view
*/
global $wpdb;
$lang = FLInput::get('lang','en');
$user_login_asked_for = FLInput::get('user');

if (!defined('WPINC')) {
    die;
}

$customer = get_user_by('slug',$user_login_asked_for);

if (empty($customer) || empty($customer->ID)) {
    //trigger 404 page and exit
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}
$customer_id = $customer->ID;
$avatar_url = hz_get_profile_thumb($customer_id,FreelinguistSizeImages::LARGE,false);
?>

<!-- get society css-->
<?php get_template_part('includes/user/society/society', 'style');?>
<!-- end society css-->

<!-- section -->
<section class="middle-content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12" style="text-align: center">
                <!-- empty space top row -->
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-6">

                <figure class="">
                    <img src="<?= $avatar_url?>"
                </figure>

            </div> <!-- /.col -->

            <div class="col-sm-12 col-md-6 col-lg-6">
                <span class="bold-and-blocking large-text">
                    <?= get_da_name($customer_id) ?>
                </span>
                Other info goes here
                <div style="margin-top: 1em">
                    <?php
                    set_query_var( 'referral_code_of_user',$customer_id );
                    get_template_part('includes/user/society/society', 'referral-code');
                    ?>
                </div>
            </div>

        </div> <!-- /.row -->

        <div class="row freelinguist-last-row">
            <div class="accdetal-table">

                <h4><?php get_custom_string("Freelancer reviews"); ?></h4>

                <table class="data-table" id="customer_review_table">

                    <thead>

                    <tr>

                        <th width="14%"><?php echo get_custom_string_return("Rating"); ?></th>

                        <th width="40%"><?php  get_custom_string("Comments"); ?> </th>

                        <th><?php  get_custom_string("Client"); ?> </th>
                        <th><?php  get_custom_string("Job Type"); ?></th>
                        <th width="7%"><?php  get_custom_string("Job"); ?></th>

                        <th><?php  get_custom_string("Date"); ?></th>



                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    $new_feedback_array = array();
                    $content_table = $wpdb->prefix.'linguist_content';
                    $feedback_is = $wpdb->get_results("SELECT * FROM wp_linguist_content WHERE purchased_by = $customer_id and rating_by_customer IS NOT NULL AND rating_by_freelancer IS NOT NULL AND user_id IS NOT NULL ORDER by id desc limit 10",ARRAY_A);


                    for ($i = 0; $i < count($feedback_is); $i++) {

                        array_push($new_feedback_array,$feedback_is[$i]);

                    }
                    $proposal_table = $wpdb->prefix.'proposals';
                    $feedback_is_2 = $wpdb->get_results("SELECT * FROM wp_proposals WHERE customer = $customer_id and rating_by_customer IS NOT NULL  AND rating_by_freelancer IS NOT NULL order by id desc limit 10",ARRAY_A);

                    for ($i = 0; $i < count($feedback_is_2); $i++) {
                        array_push($new_feedback_array,$feedback_is_2[$i]);
                    }

                    $jobs_table = $wpdb->prefix.'fl_job';
                    $feedback_is_3 = $wpdb->get_results("SELECT * FROM wp_fl_job WHERE author = $customer_id and rating_by_customer IS NOT NULL  AND rating_by_freelancer IS NOT NULL order by id desc limit 10",ARRAY_A);

                    for ($i = 0; $i < count($feedback_is_3); $i++) {
                        array_push($new_feedback_array,$feedback_is_3[$i]);
                    }

                    $new_feedback_array = array_sort($new_feedback_array, 'updated_at', SORT_DESC);
                    foreach($new_feedback_array as $k=>$v){

                        ?>
                        <tr>
                            <td>

                                <?php

                                $feedbak_rating = $v['rating_by_freelancer'];

                                echo job_rating($feedbak_rating);

                                ?>

                            </td>

                            <td> <?php echo stripslashes($v['comments_by_freelancer']); ?></td>

                            <td>

                                <?php

                                //$post_data = get_post($feedback_is_2[$i]->post_id);
                                $customer_id = '';
                                $job_type = '';
                                $job_title = '';
                                if(isset($v['content_title']) && $v['content_title']) {
                                    $customer_id = $v['purchased_by'] ;
                                    $job_type = 'Content';
                                    $job_title = $v['content_title'];

                                }elseif(isset($v['customer']) && $v['customer']){
                                    $customer_id = $v['by_user'];
                                    $job_type = 'Proposal';
                                    //$job_title =get_the_title($v['post_id']);
                                    $job_title =get_post_meta( $v['post_id'], 'project_title', true );

                                }else if(isset($v['author']) && $v['author']){
                                    $customer_id = $v['linguist_id'] ;
                                    $job_type = 'Project';
                                    //$job_title =get_the_title($v['project_id']);
                                    $job_title =get_post_meta( $v['project_id'], 'project_title', true );
                                }
                                $post_author = get_userdata($customer_id);

                                echo $post_author->display_name;

                                ?>

                            </td>

                            <td><?php echo $job_type;?></td>
                            <td><?php echo $job_title;?></td>
                            <td><?php echo date_formatted($v['updated_at']); ?></td>





                        </tr>
                        <?php
                    }



                    ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div><!-- /.container -->
</section> <!-- /.middle-content -->


