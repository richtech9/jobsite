<?php
add_action( 'wp_ajax_freelinguist_proposal_list',  'freelinguist_proposal_list'  );

/*
 *ajax returns FreelinguistBasicAjaxResponse
 *  int status
 *  string message
 */


/**
 * Checks to make sure that the proposal_id is owned by the current user
 * checks to make sure this has not already been done
 */
function freelinguist_proposal_list() {

    /*
    * current-php-code 2020-Nov-9
    * ajax-endpoint  freelinguist_proposal_list
    * input-sanitized:  contest_id
    */
    global $wpdb;

    $contest_id = (int)FLInput::get('contest_id',0);

    try {
        $post_title = FreelinguistProjectAndContestHelper::is_contest($contest_id);

        if (empty($post_title)) {
            throw new InvalidArgumentException("the contest id of '$contest_id' is not valid");
        }
        $proposals_awarded_csv = get_post_meta($contest_id,'contest_awardedProposalPrizes',true);
        if($proposals_awarded_csv){
            $proposals_awarded_ids_raw = explode(',', $proposals_awarded_csv);
        }else{
            $proposals_awarded_ids_raw = [];
        }

        $proposals_awarded_ids = [];
        foreach ($proposals_awarded_ids_raw as $raw_thing) {
            $maybe_id = intval(trim($raw_thing));
            if ($maybe_id) {$proposals_awarded_ids[$maybe_id] = $maybe_id;}
        }
        
        $proposals_result = $wpdb->get_results(
            "SELECT r.id as proposal_id ,r.by_user as by_freelancer,
                            rating_by_customer,rating_by_freelancer,status,
                            unix_timestamp(rejected_at) as rejected_at_ts
                     FROM wp_proposals r
                    WHERE post_id = $contest_id
                    ORDER BY r.by_user,r.id 
                    ");
        
        $proposals = [];
        foreach ($proposals_result as $a_proposal) {
            $pid = (int)$a_proposal->proposal_id;
            if (isset($proposals_awarded_ids[$pid])) {
                $awarded = true;
            } else {
                $awarded = false;
            }
            $rejected = $a_proposal->rejected_at_ts;
            $freelancer = (int)$a_proposal->by_freelancer;
            $rating_by_customer = $a_proposal->rating_by_customer;
            $rating_by_freelancer = $a_proposal->rating_by_freelancer;
            $status = $a_proposal->status;
            $node = [
                'proposal_id' => $pid,
                'contest_id' => $contest_id,
                'contest_title' => $post_title,
                'awarded' => $awarded,
                'rejected_timestamp' => $rejected,
                'freelancer' => $freelancer,
                'rating_by_customer' => $rating_by_customer,
                'rating_by_freelancer' => $rating_by_freelancer,
                'status' => $status
            ];

            $proposals[] = $node;
            
        }


        $ret = [
            'status'=>1,
            'message' => "Proposals",
            'proposals' => $proposals
        ];

        wp_send_json($ret);


    } catch (Exception $e) {
        $ret = [
            'status'=>0,
            'message' => $e->getMessage(),
            'proposals' => []
        ];

        wp_send_json($ret);
    }
}