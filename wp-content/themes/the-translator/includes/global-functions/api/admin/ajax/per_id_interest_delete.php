<?php

// gets data in post in the id field
add_action( 'wp_ajax_per_id_interest_delete', 'per_id_interest_delete' );
function per_id_interest_delete(){
    /*
     * current-php-code 2021-Jan-11
     * ajax-endpoint  per_id_interest_delete
     * input-sanitized : id
     */


    if (!current_user_can('manage_options')) {
        exit;
    }
    global $wpdb;
    $data_id = (int)FLInput::get('id');
    $response = ['status'=> 0, 'message'=> 'nothing done','action'=>'per_id_interest_delete'];
    try {
        if ($data_id) {

            $sql = "SELECT homepage_interest_id,wp_user_id,job_id FROM wp_homepage_interest_per_id WHERE id = $data_id";
            $task_info = $wpdb->get_results($sql);
            if (empty($task_info)) {
                throw new InvalidArgumentException("Cannot find information about the per id of $data_id");
            }
            $user_id = (int)$task_info[0]->wp_user_id;
            $content_id = (int)$task_info[0]->job_id;
            $node = new _FreelinguistIdType();
            $node->id = $user_id;
            $node->type = 'user';
            if (!$node->id) {$node->id = $content_id; $node->type = 'content';}
            if (!$node->id) {throw new LogicException("Cannot find user or content for the per id row of $data_id");}
            $homepage_interest_id = (int) $task_info[0]->homepage_interest_id;
            FreelinguistUnitGenerator::remove_compiled_units_from_es_cache($log,$homepage_interest_id,[$node]);
            will_send_to_error_log("Log for removing one per unit from es cache",$log);
            if ($data_id) {
                $res = $wpdb->query("DELETE FROM wp_homepage_interest_per_id WHERE id IN($data_id)");

                if ($wpdb->last_error) {
                    throw new RuntimeException("Error when deleting the homepage_interest_per_id row of $data_id: " . $wpdb->last_error);
                }

                if ($res === false) {
                    throw new RuntimeException("Unknown error deleting the homepage_interest_per_id row of $data_id");
                }
                $response = ['status'=> 1, 'message'=> 'deleted id of '.$data_id,'action'=>'per_id_interest_delete'];
            }
        }
    } catch (Exception $e) {
        $response = ['status'=> 0, 'message'=> $e->getMessage(),'action'=>'per_id_interest_delete'];
    }


    echo wp_json_encode($response);
    exit;
}