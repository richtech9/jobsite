<?php

//code-bookmark this is where the average rating is updated for the user in the meta key of average_rating_freelancer_role
function update_freelancer_average_rating($linguist_id){
    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */
    global $wpdb;

    $a = $wpdb->get_results("select SUM(rating_by_customer) as total, COUNT(rating_by_customer) as total_number from wp_fl_job where linguist_id = $linguist_id AND rating_by_customer IS NOT NULL");

    $b = $wpdb->get_results("select SUM(rating_by_customer) as total, COUNT(rating_by_customer) as total_number from wp_proposals where by_user = $linguist_id AND rating_by_customer IS NOT NULL");

    $c = $wpdb->get_results("select SUM(rating_by_customer) as total, COUNT(rating_by_customer) as total_number from wp_linguist_content where user_id = $linguist_id AND rating_by_customer IS NOT NULL");

    $total = $a[0]->total + $b[0]->total + $c[0]->total;
    $total_number = $a[0]->total_number + $b[0]->total_number + $c[0]->total_number;

    $avg = $total/$total_number;

    update_user_meta($linguist_id,'average_rating_freelancer_role',$avg);

    FreelinguistUserHelper::update_elastic_index($linguist_id); //run after the meta updates the trigger in the user lookup

    //code-notes update units
    FreelinguistUnitGenerator::generate_units($log,[$linguist_id],[]);

}
