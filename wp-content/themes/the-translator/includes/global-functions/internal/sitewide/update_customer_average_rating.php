<?php

//code-bookmark where the average rating is updated for the customer, from the db to the the meta of average_rating_customer_role
function update_customer_average_rating($customer_id){
    global $wpdb;
    /*
     * current-php-code 2020-Oct-12
     * internal-call
     * input-sanitized :
     */

    $a = $wpdb->get_results("select SUM(rating_by_freelancer) as total, COUNT(rating_by_freelancer) as total_number
                                    from wp_fl_job where author = $customer_id AND rating_by_freelancer IS NOT NULL");

    $b = $wpdb->get_results("select SUM(rating_by_freelancer) as total, COUNT(rating_by_freelancer) as total_number 
                                    from wp_proposals where customer = $customer_id AND rating_by_freelancer IS NOT NULL");

    $c = $wpdb->get_results("select SUM(rating_by_freelancer) as total, COUNT(rating_by_freelancer) as total_number 
                                  from wp_linguist_content where purchased_by = $customer_id AND rating_by_freelancer IS NOT NULL AND user_id IS NOT NULL");

    $total = $a[0]->total + $b[0]->total + $c[0]->total;
    $total_number = $a[0]->total_number + $b[0]->total_number + $c[0]->total_number;

    $avg = $total/$total_number;

    update_user_meta($customer_id,'average_rating_customer_role',$avg);

    FreelinguistUserHelper::update_elastic_index($customer_id); //run after the meta updates the trigger in the user lookup

}